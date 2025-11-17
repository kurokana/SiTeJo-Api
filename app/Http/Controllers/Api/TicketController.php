<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Get all tickets (filtered by role)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Ticket::with(['student', 'lecturer', 'documents']);

        // Filter berdasarkan role
        if ($user->isMahasiswa()) {
            // Mahasiswa hanya lihat ticket miliknya
            $query->where('student_id', $user->id);
        } elseif ($user->isDosen()) {
            // Dosen lihat ticket yang di-assign ke dia
            $query->where('lecturer_id', $user->id);
        }
        // Admin bisa lihat semua ticket

        // Filter berdasarkan status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Create new ticket (mahasiswa only)
     */
    public function store(Request $request)
    {
        $request->validate([
            'lecturer_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:surat_keterangan,surat_rekomendasi,ijin,lainnya',
            'priority' => 'sometimes|in:low,medium,high',
        ]);

        // Validasi lecturer_id adalah dosen
        $lecturer = User::find($request->lecturer_id);
        if (!$lecturer || !$lecturer->isDosen()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid lecturer selected'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $ticket = Ticket::create([
                'student_id' => $request->user()->id,
                'lecturer_id' => $request->lecturer_id,
                'title' => $request->title,
                'description' => $request->description,
                'type' => $request->type,
                'priority' => $request->priority ?? 'medium',
                'status' => 'pending',
            ]);

            // Create history
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $request->user()->id,
                'action' => 'created',
                'new_status' => 'pending',
                'notes' => 'Ticket created'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ticket created successfully',
                'data' => $ticket->load(['student', 'lecturer'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get ticket detail
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $ticket = Ticket::with(['student', 'lecturer', 'documents.uploader', 'histories.user'])->find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        // Check access
        if ($user->isMahasiswa() && $ticket->student_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($user->isDosen() && $ticket->lecturer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $ticket
        ]);
    }

    /**
     * Update ticket (mahasiswa can only update pending tickets)
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        // Check access
        if ($user->isMahasiswa()) {
            if ($ticket->student_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            if ($ticket->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only update pending tickets'
                ], 422);
            }
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:surat_keterangan,surat_rekomendasi,ijin,lainnya',
            'priority' => 'sometimes|in:low,medium,high',
        ]);

        DB::beginTransaction();
        try {
            $oldData = $ticket->toArray();
            
            $ticket->update($request->only(['title', 'description', 'type', 'priority']));

            // Create history
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'action' => 'updated',
                'notes' => 'Ticket updated'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ticket updated successfully',
                'data' => $ticket->load(['student', 'lecturer'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Review ticket (dosen only)
     */
    public function review(Request $request, $id)
    {
        $user = $request->user();
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        if ($ticket->lecturer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'lecturer_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $ticket->status;
            
            $ticket->update([
                'status' => 'in_review',
                'lecturer_notes' => $request->lecturer_notes,
                'reviewed_at' => now(),
            ]);

            // Create history
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'action' => 'reviewed',
                'old_status' => $oldStatus,
                'new_status' => 'in_review',
                'notes' => $request->lecturer_notes ?? 'Ticket reviewed by lecturer'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ticket marked as in review',
                'data' => $ticket->load(['student', 'lecturer'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to review ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve ticket (dosen only)
     */
    public function approve(Request $request, $id)
    {
        $user = $request->user();
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        if ($ticket->lecturer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'lecturer_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $ticket->status;
            
            $ticket->update([
                'status' => 'approved',
                'lecturer_notes' => $request->lecturer_notes,
                'approved_at' => now(),
            ]);

            // Create history
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'action' => 'approved',
                'old_status' => $oldStatus,
                'new_status' => 'approved',
                'notes' => $request->lecturer_notes ?? 'Ticket approved by lecturer'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ticket approved successfully',
                'data' => $ticket->load(['student', 'lecturer'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject ticket (dosen only)
     */
    public function reject(Request $request, $id)
    {
        $user = $request->user();
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        if ($ticket->lecturer_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $ticket->status;
            
            $ticket->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
            ]);

            // Create history
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'action' => 'rejected',
                'old_status' => $oldStatus,
                'new_status' => 'rejected',
                'notes' => $request->rejection_reason
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ticket rejected',
                'data' => $ticket->load(['student', 'lecturer'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send ticket to lecturer for review (admin only)
     */
    public function sendToLecturer(Request $request, $id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        if ($ticket->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Can only send pending tickets to lecturer'
            ], 422);
        }

        $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $ticket->status;
            
            $ticket->update([
                'status' => 'in_review',
                'admin_notes' => $request->admin_notes,
            ]);

            // Create history
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $request->user()->id,
                'action' => 'sent_to_lecturer',
                'old_status' => $oldStatus,
                'new_status' => 'in_review',
                'notes' => $request->admin_notes ?? 'Ticket sent to lecturer by admin'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ticket sent to lecturer successfully',
                'data' => $ticket->load(['student', 'lecturer'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to send ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin reject ticket
     */
    public function adminReject(Request $request, $id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $ticket->status;
            
            $ticket->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'admin_notes' => $request->rejection_reason,
            ]);

            // Create history
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $request->user()->id,
                'action' => 'rejected_by_admin',
                'old_status' => $oldStatus,
                'new_status' => 'rejected',
                'notes' => $request->rejection_reason
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ticket rejected successfully',
                'data' => $ticket->load(['student', 'lecturer'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete ticket (admin only)
     */
    public function complete(Request $request, $id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        if ($ticket->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Can only complete approved tickets'
            ], 422);
        }

        $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $ticket->status;
            
            $ticket->update([
                'status' => 'completed',
                'admin_notes' => $request->admin_notes,
                'completed_at' => now(),
            ]);

            // Create history
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $request->user()->id,
                'action' => 'completed',
                'old_status' => $oldStatus,
                'new_status' => 'completed',
                'notes' => $request->admin_notes ?? 'Ticket completed by admin'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ticket completed successfully',
                'data' => $ticket->load(['student', 'lecturer'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete ticket (admin only or mahasiswa for pending tickets)
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        // Check access
        if ($user->isMahasiswa()) {
            if ($ticket->student_id !== $user->id || $ticket->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized or ticket cannot be deleted'
                ], 403);
            }
        }

        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ticket deleted successfully'
        ]);
    }

    /**
     * Get ticket statistics
     */
    public function statistics(Request $request)
    {
        $user = $request->user();
        $query = Ticket::query();

        // Filter berdasarkan role
        if ($user->isMahasiswa()) {
            $query->where('student_id', $user->id);
        } elseif ($user->isDosen()) {
            $query->where('lecturer_id', $user->id);
        }

        $statistics = [
            'total' => $query->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'in_review' => (clone $query)->where('status', 'in_review')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }

    /**
     * Get list of lecturers (for mahasiswa to select)
     */
    public function getLecturers()
    {
        $lecturers = User::where('role', 'dosen')
            ->select('id', 'name', 'email', 'nim_nip')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $lecturers
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Ticket;
use App\Models\TicketHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    /**
     * Upload document to ticket
     */
    public function upload(Request $request, $ticketId)
    {
        \Log::info('Document upload attempt', [
            'ticket_id' => $ticketId,
            'user_id' => $request->user()->id,
            'has_file' => $request->hasFile('file'),
            'document_type' => $request->input('document_type')
        ]);

        $user = $request->user();
        $ticket = Ticket::find($ticketId);

        if (!$ticket) {
            \Log::warning('Ticket not found for document upload', ['ticket_id' => $ticketId]);
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }

        // Check access
        if ($user->isMahasiswa() && $ticket->student_id !== $user->id) {
            \Log::warning('Unauthorized document upload attempt by student', [
                'user_id' => $user->id,
                'ticket_student_id' => $ticket->student_id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($user->isDosen()) {
            if ($ticket->lecturer_id !== $user->id) {
                \Log::warning('Unauthorized document upload attempt by lecturer', [
                    'user_id' => $user->id,
                    'ticket_lecturer_id' => $ticket->lecturer_id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            // Dosen hanya bisa akses dokumen jika tiket sudah dikirim oleh admin
            if (!in_array($ticket->status, ['in_review', 'approved', 'rejected', 'completed'])) {
                \Log::warning('Lecturer tried to upload to pending ticket', [
                    'ticket_id' => $ticketId,
                    'status' => $ticket->status
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket not yet sent to lecturer'
                ], 403);
            }
        }

        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
            'document_type' => 'required|in:attachment,signed_document',
        ]);

        DB::beginTransaction();
        try {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('documents', $fileName, 'public');

            \Log::info('File stored successfully', [
                'file_name' => $fileName,
                'file_path' => $filePath
            ]);

            $document = Document::create([
                'ticket_id' => $ticket->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'document_type' => $request->document_type,
                'uploaded_by' => $user->id,
            ]);

            // Create history
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'action' => 'document_uploaded',
                'notes' => 'Document uploaded: ' . $file->getClientOriginalName()
            ]);

            DB::commit();

            \Log::info('Document uploaded successfully', ['document_id' => $document->id]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => $document->load('uploader')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Document upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all documents for a ticket
     */
    public function index(Request $request, $ticketId)
    {
        $user = $request->user();
        $ticket = Ticket::find($ticketId);

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

        if ($user->isDosen()) {
            if ($ticket->lecturer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            // Dosen hanya bisa akses dokumen jika tiket sudah dikirim oleh admin
            if (!in_array($ticket->status, ['in_review', 'approved', 'rejected', 'completed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket not yet sent to lecturer'
                ], 403);
            }
        }

        $documents = Document::where('ticket_id', $ticketId)
            ->with('uploader:id,name,role')
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'file_name' => $doc->file_name,
                    'file_type' => $doc->file_type,
                    'file_size' => $doc->file_size,
                    'file_size_human' => $doc->file_size_human,
                    'document_type' => $doc->document_type,
                    'file_url' => $doc->file_url,
                    'uploaded_by' => $doc->uploader,
                    'created_at' => $doc->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Download document
     */
    public function download(Request $request, $id)
    {
        $user = $request->user();
        $document = Document::with('ticket')->find($id);

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        }

        $ticket = $document->ticket;

        // Check access
        if ($user->isMahasiswa() && $ticket->student_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($user->isDosen()) {
            if ($ticket->lecturer_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            
            // Dosen hanya bisa download dokumen jika tiket sudah dikirim oleh admin
            if (!in_array($ticket->status, ['in_review', 'approved', 'rejected', 'completed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket not yet sent to lecturer'
                ], 403);
            }
        }

        $filePath = storage_path('app/public/' . $document->file_path);

        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);
        }

        return response()->download($filePath, $document->file_name);
    }

    /**
     * Delete document
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $document = Document::with('ticket')->find($id);

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        }

        $ticket = $document->ticket;

        // Check access - only uploader or admin can delete
        if (!$user->isAdmin() && $document->uploaded_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Delete file from storage
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            // Create history
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'action' => 'document_deleted',
                'notes' => 'Document deleted: ' . $document->file_name
            ]);

            $document->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document: ' . $e->getMessage()
            ], 500);
        }
    }
}

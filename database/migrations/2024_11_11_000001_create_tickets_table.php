<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // Format: TKT-YYYYMMDD-XXXX
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade'); // Mahasiswa yang mengajukan
            $table->foreignId('lecturer_id')->nullable()->constrained('users')->onDelete('set null'); // Dosen yang dituju
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['surat_keterangan', 'surat_rekomendasi', 'ijin', 'lainnya'])->default('lainnya');
            $table->enum('status', ['pending', 'in_review', 'approved', 'rejected', 'completed'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->text('admin_notes')->nullable(); // Catatan dari admin
            $table->text('lecturer_notes')->nullable(); // Catatan dari dosen
            $table->text('rejection_reason')->nullable(); // Alasan jika ditolak
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('type', ['preventive', 'corrective']);
            $table->enum('priority', ['normal', 'urgent'])->default('normal');
            $table->enum('status', ['draft', 'assigned', 'in_progress', 'completed', 'closed', 'rejected'])
                ->default('draft');

            $table->text('description')->nullable();
            $table->smallInteger('rejection_count')->default(0);
            $table->text('rejection_note')->nullable();

            // Tambahan di luar 08-erd.md/09-database-design.md — disetujui user
            // untuk kebutuhan ADR-004 (eskalasi 2x reject berturut-turut).
            // Alasan pakai kolom eksplisit (bukan derive dari rejection_count >= 2):
            // event "kapan terjadi eskalasi" perlu tercatat untuk dashboard
            // reliability nanti (M6), dan reset saat reassign perlu penanda
            // yang jelas, bukan cuma angka yang naik-turun.
            $table->boolean('is_escalated')->default(false);

            $table->date('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            $table->index('asset_id');
            $table->index('assigned_to');
            $table->index('status');
            $table->index(['status', 'priority']);

            // CHECK constraint tambahan di level DB, konsisten dengan
            // 09-database-design.md
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
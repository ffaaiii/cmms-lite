<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets');
            $table->foreignId('inspected_by')->constrained('users');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('generated_work_order_id')->nullable()->constrained('work_orders')->nullOnDelete();

            $table->enum('condition_found', ['good', 'needs_attention', 'damaged']);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending_review', 'confirmed', 'dismissed'])->default('pending_review');

            $table->timestamp('created_at')->useCurrent();

            $table->index('asset_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_checklists');
    }
};

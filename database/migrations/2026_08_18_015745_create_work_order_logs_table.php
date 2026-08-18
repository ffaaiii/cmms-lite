<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Append-only audit trail — sengaja tidak ada updated_at.
        // Aplikasi TIDAK BOLEH melakukan UPDATE/DELETE ke tabel ini,
        // hanya INSERT (lihat 09-database-design.md).
        Schema::create('work_order_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders');
            $table->foreignId('user_id')->constrained('users');
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_logs');
    }
};
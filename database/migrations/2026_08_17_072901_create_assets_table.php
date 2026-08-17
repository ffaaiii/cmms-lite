<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->enum('category', ['turbine', 'well', 'pipe', 'cooling_tower', 'other']);
            $table->string('location', 150)->nullable();
            $table->date('installed_at')->nullable();
            $table->enum('condition', ['good', 'needs_attention', 'damaged'])->default('good');
            $table->integer('pm_interval_days')->default(90);
            $table->date('last_pm_at')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};

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
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('exception_class', 255);
            $table->text('message');
            $table->string('file', 500);
            $table->integer('line');
            $table->longText('trace');
            $table->string('url', 500);
            $table->string('method', 10);
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->json('request_data')->nullable();
            $table->integer('status_code')->default(500);
            $table->string('severity', 20)->default('error');
            $table->boolean('resolved')->default(false);
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['exception_class', 'created_at']);
            $table->index(['severity', 'resolved']);
            $table->index(['user_id', 'created_at']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['status_code', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};
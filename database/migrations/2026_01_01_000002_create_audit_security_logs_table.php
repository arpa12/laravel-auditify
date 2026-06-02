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
        Schema::create('audit_security_logs', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('user');
            $table->string('severity')->default('medium');
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_read')->default(false);
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_security_logs');
    }
};

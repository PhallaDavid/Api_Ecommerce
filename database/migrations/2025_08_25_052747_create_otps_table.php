<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // links to users.id
            $table->string('code', 6); // 6-digit OTP
            $table->timestamp('expires_at'); // OTP expiration time
            $table->timestamps();

            $table->index(['user_id', 'code']); // improves query performance
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};

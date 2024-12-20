<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('log_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('type')->nullable();
            $table->json('errors');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_errors');
    }
};

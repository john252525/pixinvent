<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->uuid('payment_id')->nullable();
            $table->string('service');
            $table->string('status');
            $table->unsignedInteger('amount');
            $table->unsignedInteger('income_amount')->nullable();
            $table->json('metadata')->nullable();
            $table->json('payment_method')->nullable();
            $table->json('refunded_amount')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

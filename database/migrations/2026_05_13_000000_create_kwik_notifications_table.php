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
        Schema::create('kwik_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('notification_id')->unique()->index();
            $table->string('phone')->nullable();
            $table->string('template')->nullable();
            $table->string('status')->default('sent');
            $table->text('payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kwik_notifications');
    }
};

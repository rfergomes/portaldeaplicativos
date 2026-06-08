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
        Schema::table('clientes', function (Blueprint $table) {
            $table->boolean('email_valido')->default(true)->after('email');
            $table->string('email_bounce_code')->nullable()->after('email_valido');
            $table->text('email_bounce_description')->nullable()->after('email_bounce_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['email_valido', 'email_bounce_code', 'email_bounce_description']);
        });
    }
};

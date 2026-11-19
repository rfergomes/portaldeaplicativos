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
        Schema::table('socio_caixas', function (Blueprint $table) {
            if (!Schema::hasColumn('socio_caixas', 'email')) {
                $table->string('email', 191)->nullable()->after('telefone');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('socio_caixas', function (Blueprint $table) {
            if (Schema::hasColumn('socio_caixas', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};

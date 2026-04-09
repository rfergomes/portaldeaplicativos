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
        Schema::table("ativo_anexos", function (Blueprint $table) {
            $table->foreignId("licenca_id")->nullable()->after("cessao_id")->constrained("ativo_licencas")->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("ativo_anexos", function (Blueprint $table) {
            $table->dropForeign(["licenca_id"]);
            $table->dropColumn("licenca_id");
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('colonia_acomodacaos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colonia_id')->constrained('colonias')->onDelete('cascade');
            $table->string('tipo')->nullable(); // Ex: "Chalé", "Térreo", "1º Andar"
            $table->string('identificador'); // Ex: "1", "12A"
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colonia_acomodacaos');
    }
};

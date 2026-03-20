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
        Schema::create('ativo_licencas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('chave')->nullable();
            $table->enum('tipo_licenca', ['vitalicia', 'assinatura'])->default('assinatura');
            $table->date('data_validade')->nullable();
            $table->foreignId('fabricante_id')->nullable()->constrained('ativo_fabricantes')->onDelete('set null');
            $table->integer('quantidade_seats')->default(1);
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ativo_licencas');
    }
};

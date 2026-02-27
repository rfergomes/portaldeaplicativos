<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfis', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('permissoes', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->unique();
            $table->string('nome');
            $table->string('descricao')->nullable();
            $table->timestamps();
        });

        Schema::create('usuario_perfil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('perfil_id')->constrained('perfis')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('perfil_permissao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perfil_id')->constrained('perfis')->cascadeOnDelete();
            $table->foreignId('permissao_id')->constrained('permissoes')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfil_permissao');
        Schema::dropIfExists('usuario_perfil');
        Schema::dropIfExists('permissoes');
        Schema::dropIfExists('perfis');
    }
};


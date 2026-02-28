<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tipo_protocolos', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // PROTOCOLO, OFÍCIO, E-MAIL ESPORÁDICO, NOTIFICAÇÃO EXTRAJUDICIAL
            $table->string('icone')->default('fa-solid fa-file'); // classe Font Awesome
            $table->string('cor')->default('primary'); // cor Bootstrap: primary, success, danger, warning, info, secondary
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_protocolos');
    }
};

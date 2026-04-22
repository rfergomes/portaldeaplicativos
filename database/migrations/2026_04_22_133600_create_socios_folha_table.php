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
        Schema::create('socios_folha', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lancamento_id')->unique()->comment('Id do lançamento no Excel');
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('regiao_id')->nullable()->constrained('regioes')->onDelete('set null');
            
            $table->integer('ano');
            $table->integer('mes');
            $table->date('data_vencimento')->nullable();
            
            $table->decimal('valor_mensalidade', 12, 2)->default(0);
            $table->string('situacao')->default('ABERTO');
            $table->date('data_autenticacao')->nullable();
            
            $table->decimal('multa', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();
            $table->decimal('vl_credit', 12, 2)->nullable();
            $table->string('origem')->nullable();
            
            $table->timestamp('data_lista')->nullable()->comment('Data em que o recebimento da lista foi confirmado');
            $table->timestamp('data_baixa')->nullable()->comment('Data em que a baixa no sistema ERP foi confirmada');
            $table->decimal('valor_pago', 12, 2)->nullable()->comment('Valor confirmado no pagamento');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('socios_folha');
    }
};

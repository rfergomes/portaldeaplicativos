<?php

namespace App\Models;

use App\Domain\Shared\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Protocolo extends Model
{
    use HasFactory;
    use Auditable;

    protected $fillable = [
        'tipo_protocolo_id',
        'user_id',
        'empresa_id',
        'assunto',
        'corpo',
        'canal',
        'status',
        'referencia_documento',
        'agendado_para',
    ];

    protected $casts = [
        'agendado_para' => 'datetime',
    ];

    public function tipo()
    {
        return $this->belongsTo(TipoProtocolo::class, 'tipo_protocolo_id');
    }

    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function destinatarios()
    {
        return $this->hasMany(ProtocoloDestinatario::class);
    }

    public function anexos()
    {
        return $this->hasMany(ProtocoloAnexo::class);
    }

    public function envios()
    {
        return $this->hasMany(ProtocoloEnvio::class);
    }

    public function comprovante()
    {
        return $this->hasOne(ProtocoloComprovante::class);
    }

    public function carimbos()
    {
        return $this->hasMany(CarimboTempo::class);
    }

    /**
     * Atualiza o status geral do protocolo baseando-se no estágio mais avançado dos envios.
     * Hierarquia: lido > entregue > enviado > queued > falha.
     */
    public function atualizarStatusGeral()
    {
        $envios = $this->envios()->get();
        if ($envios->isEmpty()) {
            return;
        }

        $statusWeights = [
            'lido' => 50,
            'concluido' => 50, // se aplicavel
            'entregue' => 40,
            'enviado' => 30,
            'queued' => 20,
            'falha' => 10,
        ];

        $bestStatus = null;
        $maxWeight = -1;

        foreach ($envios as $envio) {
            $peso = $statusWeights[$envio->status] ?? 0;
            if ($peso > $maxWeight) {
                $maxWeight = $peso;
                $bestStatus = $envio->status;
            }
        }

        if ($bestStatus && $this->status !== $bestStatus) {
            $this->update(['status' => $bestStatus]);
        }
    }
}


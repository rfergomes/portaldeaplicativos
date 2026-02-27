<?php

namespace App\Domain\Shared\Traits;

use App\Models\AuditoriaEvento;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            self::registrarAuditoria($model, 'created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            self::registrarAuditoria($model, 'updated', $model->getOriginal(), $model->getAttributes());
        });

        static::deleted(function ($model) {
            self::registrarAuditoria($model, 'deleted', $model->getOriginal(), null);
        });
    }

    protected static function registrarAuditoria($model, string $operacao, ?array $antes, ?array $depois): void
    {
        AuditoriaEvento::create([
            'entidade' => $model->getTable(),
            'operacao' => $operacao,
            'entidade_id' => $model->getKey(),
            'user_id' => Auth::id(),
            'dados_antes' => $antes,
            'dados_depois' => $depois,
            'ocorreu_em' => now(),
        ]);
    }
}


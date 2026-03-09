<?php
// Padroniza status dos protocolos
\App\Models\Protocolo::whereIn('status', ['lido', 'entregue', 'concluido', 'processado'])
    ->update(['status' => 'sucesso']);

\App\Models\Protocolo::whereIn('status', ['queued', 'pendente'])
    ->update(['status' => 'pendente']);

// Recalcula baseado nos envios para garantir que nada ficou pra trás
\App\Models\Protocolo::all()->each(function ($p) {
    $p->atualizarStatusGeral();
});

echo "Banco de dados sincronizado com o novo padrão de status.\n";

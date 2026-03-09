$prots = \App\Models\Protocolo::whereIn('status', ['lido', 'entregue', 'concluido', 'processado'])->get();
echo "Protocolos com status legado: " . $prots->count() . "\n";
foreach($prots as $p) {
    echo "ID: " . $p->id . " | Status: " . $p->status . " | Envios: " . $p->envios()->count() . "\n";
    $p->atualizarStatusGeral();
    echo "  -> Novo Status: " . $p->status . "\n";
}

$met = \App\Models\Protocolo::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
    ->groupBy('status')
    ->pluck('total', 'status')
    ->toArray();
print_r($met);

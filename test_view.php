
$s = new \App\Models\SocioFolha();
$s->empresa_id = 1;
$s->regiao_id = 1;
$s->ano = 2026;
$s->mes = 6;
$s->valor_mensalidade = 100;
$s->data_lista = '2026-06-01 10:00:00';
$s->save();

$request = request();
$request->merge(['ano' => 2026]);
$pendentes = \App\Models\SocioFolha::whereNull('data_lista')->orWhereNull('data_baixa')->get();

try {
    echo view('socio_folha.pdf_pendentes_lista_baixa', compact('pendentes', 'request'))->render();
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine();
}

$s->delete();

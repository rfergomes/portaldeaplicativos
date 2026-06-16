
$pendentes = \App\Models\SocioFolha::whereNull('data_lista')->orWhereNull('data_baixa')->get();
$request = request();
$request->merge(['ano' => 2026]);
try {
    echo view('socio_folha.pdf_pendentes_lista_baixa', compact('pendentes', 'request'))->render();
} catch (\Exception $e) {
    echo $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine();
}

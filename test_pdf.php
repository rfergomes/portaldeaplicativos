
$controller = app(\App\Http\Controllers\SocioFolhaController::class);
try {
    $controller->exportPendentesListaBaixaPdf(request());
} catch (\Exception $e) {
    echo $e->getMessage() . "\n" . $e->getFile() . ":" . $e->getLine();
}

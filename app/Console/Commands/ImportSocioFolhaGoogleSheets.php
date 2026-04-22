<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SocioFolhaImport;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImportSocioFolhaGoogleSheets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'arrecadacao:importar-google-sheets {url?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa a base de Sócio Folha diretamente de uma planilha do Google Sheets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->argument('url');
        
        if (!$url) {
            // Default URL based on user input
            $url = 'https://docs.google.com/spreadsheets/d/13Ib3kVMjahdlLSkyhgqjvlcooM42ABa9SIRoA2ISo6s/export?format=xlsx';
            $this->info("Nenhuma URL fornecida. Usando URL padrão da base Sócio Fabrica...");
        } else {
            // Ensure URL is format=xlsx for download if it's a google sheets link
            if (strpos($url, 'google.com') !== false && strpos($url, 'export?format=xlsx') === false) {
                // Extract document ID and construct export URL
                preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $url, $matches);
                if (isset($matches[1])) {
                    $url = "https://docs.google.com/spreadsheets/d/{$matches[1]}/export?format=xlsx";
                }
            }
        }

        $this->info("Baixando planilha de: {$url}");

        try {
            $response = Http::timeout(60)->get($url);

            if ($response->successful()) {
                $tempFile = 'temp_import_' . time() . '.xlsx';
                Storage::put($tempFile, $response->body());
                $filePath = Storage::path($tempFile);

                $this->info("Download concluído. Iniciando importação...");

                Excel::import(new \App\Imports\LegacySocioFolhaImport, $filePath);

                $this->info("Importação concluída com sucesso!");

                // Limpa o arquivo temporário
                Storage::delete($tempFile);
            } else {
                $this->error("Falha ao baixar o arquivo. Status: " . $response->status());
            }
        } catch (\Exception $e) {
            $this->error("Ocorreu um erro durante a importação: " . $e->getMessage());
        }
    }
}

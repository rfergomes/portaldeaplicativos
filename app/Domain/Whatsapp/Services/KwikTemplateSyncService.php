<?php

declare(strict_types=1);

namespace App\Domain\Whatsapp\Services;

use App\Domain\Whatsapp\DTOs\KwikSyncResultDTO;
use App\Models\WhatsappTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KwikTemplateSyncService
{
    private const KWIK_TEMPLATES_URL = 'https://kwik.app.br/api/api/public/v1/templates/';

    /**
     * Sincroniza todos os templates da empresa homologados no Kwik/Meta.
     */
    public function sincronizar(?int $userId = null): KwikSyncResultDTO
    {
        $token = config('services.kwik.token');

        if (empty($token)) {
            $msg = 'Token de acesso à API Kwik não configurado no ambiente (KWIK_API_TOKEN).';
            Log::error("KwikTemplateSyncService: {$msg}");
            return new KwikSyncResultDTO(erros: [$msg]);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . trim((string) $token),
                'Accept' => 'application/json',
            ])->timeout(15)->get(self::KWIK_TEMPLATES_URL);

            if (!$response->successful()) {
                $status = $response->status();
                $body = $response->body();
                $msg = "A API Kwik retornou status HTTP {$status}: {$body}";
                Log::error("KwikTemplateSyncService Erro: {$msg}");
                return new KwikSyncResultDTO(erros: [$msg]);
            }

            $templatesApi = $response->json();

            if (!is_array($templatesApi)) {
                $msg = 'Formato de resposta inesperado da API Kwik (esperava array/objeto de templates).';
                Log::error("KwikTemplateSyncService: {$msg}");
                return new KwikSyncResultDTO(erros: [$msg]);
            }

            // Normaliza a lista de templates suportando tanto array plano quanto agrupado por canal WABA
            $listaTemplates = $this->normalizarListaTemplates($templatesApi);

            $totalEncontrados = count($listaTemplates);
            $totalImportados = 0;
            $totalAtualizados = 0;
            $totalAprovados = 0;
            $totalRejeitados = 0;

            foreach ($listaTemplates as $item) {
                if (empty($item['name'])) {
                    continue;
                }

                $nome = trim((string) $item['name']);
                $statusMeta = strtolower(trim((string) ($item['status'] ?? 'pending')));
                $categoria = !empty($item['category']) ? trim((string) $item['category']) : null;
                $language = !empty($item['language']) ? trim((string) $item['language']) : 'pt_BR';
                $namespace = !empty($item['namespace']) ? trim((string) $item['namespace']) : null;
                $rejectedReason = !empty($item['rejected_reason']) ? trim((string) $item['rejected_reason']) : null;

                // Extração do texto do componente BODY
                $corpoExemplo = $this->extrairCorpoTemplate($item['components'] ?? []);

                // Extração inteligente de variáveis {{1}}, {{2}}, etc.
                $parametrosExtras = $this->extrairParametrosExtras($corpoExemplo);

                $isAprovado = ($statusMeta === 'approved');
                if ($isAprovado) {
                    $totalAprovados++;
                } else {
                    $totalRejeitados++;
                }

                $existente = WhatsappTemplate::where('nome', $nome)->first();

                if ($existente) {
                    $totalAtualizados++;
                    $descricao = !empty($existente->descricao) ? $existente->descricao : $this->gerarDescricaoAmigavel($nome);
                } else {
                    $totalImportados++;
                    $descricao = $this->gerarDescricaoAmigavel($nome);
                }

                WhatsappTemplate::updateOrCreate(
                    ['nome' => $nome],
                    [
                        'descricao' => $descricao,
                        'corpo_exemplo' => $corpoExemplo,
                        'parametros_esperados' => $parametrosExtras,
                        'ativo' => $isAprovado,
                        'status_meta' => $statusMeta,
                        'categoria' => $categoria,
                        'language' => $language,
                        'namespace' => $namespace,
                        'rejected_reason' => $rejectedReason,
                        'sincronizado_em' => now(),
                        'user_id' => $existente?->user_id ?? $userId,
                    ]
                );
            }

            $result = new KwikSyncResultDTO(
                totalEncontrados: $totalEncontrados,
                totalImportados: $totalImportados,
                totalAtualizados: $totalAtualizados,
                totalAprovados: $totalAprovados,
                totalRejeitados: $totalRejeitados
            );

            Log::info("KwikTemplateSyncService: " . $result->resumoMensagem());

            return $result;

        } catch (\Throwable $e) {
            $msg = 'Exceção ao comunicar com a API Kwik: ' . $e->getMessage();
            Log::error("KwikTemplateSyncService Falha Crítica: {$msg}");
            return new KwikSyncResultDTO(erros: [$msg]);
        }
    }

    /**
     * Normaliza a resposta da API Kwik para um array único indexado por nome de template.
     *
     * @param array<string|int, mixed> $templatesApi
     * @return array<int, array<string, mixed>>
     */
    private function normalizarListaTemplates(array $templatesApi): array
    {
        // Caso 1: Array plano direto com objetos de template [{ name: ... }]
        if (isset($templatesApi[0]['name'])) {
            return $templatesApi;
        }

        // Caso 2: Objeto direto com chave waba_templates
        if (isset($templatesApi['waba_templates']) && is_array($templatesApi['waba_templates'])) {
            return $templatesApi['waba_templates'];
        }

        // Caso 3: Dicionário indexado por número de telefone ["5519...": { "waba_templates": [...] }]
        $templatesPorNome = [];
        foreach ($templatesApi as $conteudo) {
            if (is_array($conteudo) && isset($conteudo['waba_templates']) && is_array($conteudo['waba_templates'])) {
                foreach ($conteudo['waba_templates'] as $tpl) {
                    if (is_array($tpl) && !empty($tpl['name'])) {
                        $nome = (string) $tpl['name'];
                        // Preserva ou sobrescreve se o status for approved
                        if (!isset($templatesPorNome[$nome]) || strtolower((string)($tpl['status'] ?? '')) === 'approved') {
                            $templatesPorNome[$nome] = $tpl;
                        }
                    }
                }
            } elseif (is_array($conteudo) && !empty($conteudo['name'])) {
                $templatesPorNome[(string)$conteudo['name']] = $conteudo;
            }
        }

        return array_values($templatesPorNome);
    }

    /**
     * Gera uma descrição amigável a partir do nome do template (ex: portal_verao_cefol -> Portal Verao Cefol).
     */
    private function gerarDescricaoAmigavel(string $nome): string
    {
        $limpo = str_replace(['_', '-'], ' ', $nome);
        return 'Modelo ' . mb_convert_case($limpo, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Extrai o texto principal do componente BODY da mensagem.
     */
    private function extrairCorpoTemplate(array $components): ?string
    {
        foreach ($components as $comp) {
            if (isset($comp['type']) && strtoupper((string) $comp['type']) === 'BODY' && !empty($comp['text'])) {
                return (string) $comp['text'];
            }
        }

        return null;
    }

    /**
     * Identifica variáveis dinâmicas no corpo ({{1}}, {{2}}, etc.) e mapeia as variáveis extras a partir de {{2}}.
     *
     * @return array<int, string>
     */
    private function extrairParametrosExtras(?string $texto): array
    {
        if (empty($texto)) {
            return [];
        }

        preg_match_all('/\{\{(\d+)\}\}/', $texto, $matches);

        if (empty($matches[1])) {
            return [];
        }

        $numeros = array_unique(array_map('intval', $matches[1]));
        sort($numeros);

        $parametros = [];
        foreach ($numeros as $num) {
            // {{1}} é reservado convencionalmente para o Nome do Associado
            if ($num > 1) {
                $parametros[] = "Parâmetro {$num}";
            }
        }

        return $parametros;
    }
}

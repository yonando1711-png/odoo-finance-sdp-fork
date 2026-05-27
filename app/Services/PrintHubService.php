<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrintHubService
{
    protected string $url;
    protected string $apiKey;
    protected int $timeout;

    public function __construct(?string $url = null, ?string $apiKey = null)
    {
        $this->url = rtrim($url ?? Setting::get('print_hub_url', ''), '/');
        $this->apiKey = $apiKey ?? Setting::get('print_hub_api_key', '');
        $this->timeout = (int) Setting::get('print_hub_timeout', 15);
    }

    /**
     * Get a pre-configured HTTP client.
     * SSL verification is only disabled in non-production environments.
     * All internal HTTP calls go through this method for consistency.
     */
    protected function http(): \Illuminate\Http\Client\PendingRequest
    {
        $request = Http::withHeaders([
            'X-Api-Key' => $this->apiKey,
            'Accept'     => 'application/json',
        ])->timeout($this->timeout);

        // Only skip SSL verification in local/non-production environments
        if (app()->environment('local', 'testing')) {
            $request->withoutVerifying();
        }

        return $request;
    }

    /**
     * Execute an HTTP call with retry and exponential backoff.
     *
     * @param  callable $fn         The HTTP call to execute (must return array with 'success' key)
     * @param  int      $maxAttempts Maximum number of attempts (default: 3)
     * @return array
     */
    protected function retryHttp(callable $fn, int $maxAttempts = 3): array
    {
        $attempt   = 0;
        $lastError = ['success' => false, 'message' => 'Max retries exceeded'];

        while ($attempt < $maxAttempts) {
            try {
                $result = $fn();
                if (($result['success'] ?? false)) {
                    return $result;
                }
                $lastError = $result;
            } catch (\Exception $e) {
                Log::warning("PrintHub retry attempt " . ($attempt + 1) . "/{$maxAttempts}: " . $e->getMessage());
                $lastError = ['success' => false, 'message' => $e->getMessage()];
            }

            $attempt++;
            if ($attempt < $maxAttempts) {
                usleep(500_000 * $attempt); // 0.5s, 1s, 1.5s backoff
            }
        }

        return $lastError;
    }

    // ──────────────────────────────────────────────
    //  Agents & Printers
    // ──────────────────────────────────────────────

    /**
     * Get list of online agents (with their printers) from the hub.
     */
    public function getOnlineAgents(): array
    {
        if (empty($this->url)) {
            return ['success' => false, 'agents' => []];
        }

        try {
            $response = $this->http()->get("{$this->url}/api/v1/agents/online");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'agents'  => $response->json('data.agents', []),
                ];
            }

            return ['success' => false, 'agents' => []];
        } catch (\Exception $e) {
            Log::error("PrintHub Error (getOnlineAgents): " . $e->getMessage());
            return ['success' => false, 'agents' => []];
        }
    }

    /**
     * Get a flat list of printers (name + agent) from online agents.
     * Convenience wrapper around getOnlineAgents().
     */
    public function getPrinters(): array
    {
        $agentsData = $this->getOnlineAgents();
        if (!$agentsData['success']) {
            return [
                'success'  => false,
                'message'  => $agentsData['message'] ?? 'Failed to fetch agents.',
                'printers' => [],
            ];
        }

        $printers = [];
        foreach ($agentsData['agents'] as $agent) {
            foreach ($agent['printers'] as $printer) {
                $printers[] = [
                    'name'  => $printer,
                    'agent' => $agent['name'],
                ];
            }
        }

        return [
            'success'  => true,
            'printers' => $printers,
        ];
    }

    // ──────────────────────────────────────────────
    //  Queues
    // ──────────────────────────────────────────────

    /**
     * Get list of queues from the hub.
     */
    public function getQueues(): array
    {
        if (empty($this->url)) {
            return ['success' => false, 'queues' => []];
        }

        try {
            $response = $this->http()->get("{$this->url}/api/v1/queues");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'queues'  => $response->json('data.queues', []),
                ];
            }

            return ['success' => false, 'queues' => []];
        } catch (\Exception $e) {
            Log::error("PrintHub Error (getQueues): " . $e->getMessage());
            return ['success' => false, 'queues' => []];
        }
    }

    // ──────────────────────────────────────────────
    //  Print
    // ──────────────────────────────────────────────

    /**
     * Send a PDF to the hub for printing via a named queue.
     *
     * @param  string      $queue     Queue name (e.g. 'invoice', 'kuitansi', 'journal')
     * @param  string      $pdfBase64 Base64-encoded PDF content
     * @param  int|null    $agentId   Optional agent ID override
     * @param  string|null $printer   Optional printer name override
     * @param  string|null $profile   Optional print profile (paper size, orientation, margins)
     * @param  array       $extra     Additional payload keys (e.g. reference_id)
     * @return array
     */
    public function printQueue(
        string  $queue,
        string  $pdfBase64,
        ?int    $agentId  = null,
        ?string $printer  = null,
        ?string $profile  = null,
        array   $extra    = []
    ): array {
        if (empty($this->url)) {
            return ['success' => false, 'message' => 'Print Hub URL not configured.'];
        }

        return $this->retryHttp(function () use ($queue, $pdfBase64, $agentId, $printer, $profile, $extra) {
            $payload = array_merge([
                'queue'           => $queue,
                'document_base64' => $pdfBase64,
                'type'            => 'pdf',
            ], array_filter([
                'agent_id' => $agentId,
                'printer'  => $printer,
                'profile'  => $profile ?: Setting::get('print_hub_default_profile'),
            ], fn($v) => $v !== null && $v !== ''), $extra);

            $response = $this->http()->post("{$this->url}/api/v1/print", $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'job_id'  => $response->json('data.job_id'),
                    'message' => 'Print job sent successfully via queue.',
                ];
            }

            return [
                'success' => false,
                'message' => 'Hub Error: ' . ($response->json('error.message') ?? $response->status()),
            ];
        });
    }

    /**
     * Send a PDF to a specific printer (bypasses queue routing).
     *
     * @deprecated Use printQueue() instead for queue-based routing.
     */
    public function printPdf(string $printerName, string $pdfBase64, array $options = [], ?string $profile = null): array
    {
        if (empty($this->url)) {
            return ['success' => false, 'message' => 'Print Hub URL not configured.'];
        }

        return $this->retryHttp(function () use ($printerName, $pdfBase64, $options, $profile) {
            $targetProfile = $profile ?: Setting::get('print_hub_default_profile');

            $payload = array_filter([
                'printer'         => $printerName,
                'profile'         => $targetProfile,
                'document_base64' => $pdfBase64,
                'type'            => 'pdf',
                'options'         => $options,
            ], fn($v) => $v !== null && $v !== '');

            $response = $this->http()->post("{$this->url}/api/v1/print", $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'job_id'  => $response->json('data.job_id'),
                    'message' => 'Print job sent successfully.',
                ];
            }

            return [
                'success' => false,
                'message' => 'Hub Error: ' . ($response->json('error.message') ?? $response->status()),
            ];
        });
    }

    /**
     * Send raw data (ZPL/ESC/POS) to the hub for printing.
     */
    public function printRaw(string $printerName, string $data, array $options = []): array
    {
        if (empty($this->url)) {
            return ['success' => false, 'message' => 'Print Hub URL not configured.'];
        }

        return $this->retryHttp(function () use ($printerName, $data, $options) {
            $payload = array_filter([
                'printer'         => $printerName,
                'document_base64' => base64_encode($data),
                'type'            => 'raw',
                'options'         => $options,
            ], fn($v) => $v !== null && $v !== '');

            $response = $this->http()->post("{$this->url}/api/v1/print", $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'job_id'  => $response->json('data.job_id'),
                    'message' => 'Raw print job sent successfully.',
                ];
            }

            return [
                'success' => false,
                'message' => 'Hub Error: ' . ($response->json('error.message') ?? $response->status()),
            ];
        });
    }

    // ──────────────────────────────────────────────
    //  Schemas
    // ──────────────────────────────────────────────

    /**
     * Register a data schema with the hub for template design.
     */
    public function registerSchema(string $name, array $data): array
    {
        if (empty($this->url)) {
            return ['success' => false, 'message' => 'Print Hub URL not configured.'];
        }

        try {
            $payload = array_merge(['schema_name' => $name], $data);

            $response = $this->http()->post("{$this->url}/api/v1/schema", $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => "Schema '{$name}' registered successfully.",
                ];
            }

            return [
                'success' => false,
                'message' => 'Hub Error: ' . ($response->json('error.message') ?? $response->status()),
            ];
        } catch (\Exception $e) {
            Log::error("PrintHub Error (registerSchema): " . $e->getMessage());
            return ['success' => false, 'message' => 'Schema registration failed: ' . $e->getMessage()];
        }
    }

    // ──────────────────────────────────────────────
    //  Connection
    // ──────────────────────────────────────────────

    /**
     * Test connection to the hub.
     */
    public function testConnection(): array
    {
        if (empty($this->url)) {
            return ['success' => false, 'message' => 'Print Hub URL not configured.'];
        }

        try {
            $response = $this->http()->get("{$this->url}/api/v1/test");

            if ($response->successful()) {
                return [
                    'success'  => true,
                    'app_name' => $response->json('data.app_name'),
                    'agents'   => $response->json('data.agents'),
                    'message'  => $response->json('data.message'),
                ];
            }

            return [
                'success' => false,
                'message' => 'Hub returned error: ' . ($response->json('error.message') ?? $response->status()),
            ];
        } catch (\Exception $e) {
            Log::error("PrintHub Error (testConnection): " . $e->getMessage());
            return ['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()];
        }
    }
}

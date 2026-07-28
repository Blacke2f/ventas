<?php
/**
 * TasaCambio — Obtiene la tasa oficial BCV (USD → VES/Bs)
 *
 * Fuentes en orden de prioridad:
 * 1. exchangerate-api.com  (gratis, muy confiable, tasa VES oficial BCV)
 * 2. Scraping directo bcv.org.ve
 * 3. Cache en disco (aunque esté vencido)
 * 4. Valor por defecto hardcodeado
 *
 * Se actualiza automáticamente cada hora.
 */
class TasaCambio {

    private string $cacheFile;
    private int    $cacheTime = 3600; // 1 hora en segundos
    private float  $defaultRate = 567.68; // Actualizar si la API no responde

    public function __construct() {
        $this->cacheFile = BASE_PATH . '/cache/tasa_cambio.json';
        if (!is_dir(BASE_PATH . '/cache')) {
            mkdir(BASE_PATH . '/cache', 0755, true);
        }
    }

    // ── Pública: obtener tasa ────────────────────────────────────────
    public function getTasa(): float {
        // 1. Cache válido (menos de 1 hora)
        $cached = $this->readCache();
        if ($cached && (time() - $cached['timestamp']) < $this->cacheTime) {
            return (float)$cached['tasa'];
        }

        // 2. Intentar obtener desde internet
        $tasa = $this->fetchFromExchangeRateAPI()
             ?? $this->fetchFromBCVDirect();

        if ($tasa && $tasa > 1) {
            $this->writeCache($tasa);
            return $tasa;
        }

        // 3. Cache vencido pero existe
        if ($cached && isset($cached['tasa'])) {
            return (float)$cached['tasa'];
        }

        // 4. Valor por defecto
        return $this->defaultRate;
    }

    public function forceUpdate(): float {
        $this->deleteCache();
        return $this->getTasa();
    }

    // ── Fuente 1: exchangerate-api.com (tasa BCV oficial) ────────────
    private function fetchFromExchangeRateAPI(): ?float {
        $url = 'https://api.exchangerate-api.com/v4/latest/USD';
        $json = $this->httpGet($url, 8);
        if (!$json) return null;

        $data = json_decode($json, true);
        $ves = $data['rates']['VES'] ?? null;

        if ($ves && $ves > 1) {
            return round((float)$ves, 2);
        }
        return null;
    }

    // ── Fuente 2: scraping del BCV ────────────────────────────────────
    private function fetchFromBCVDirect(): ?float {
        $html = $this->httpGet('https://www.bcv.org.ve/', 10);
        if (!$html) return null;

        // El BCV muestra el dólar como "567,68" (coma decimal)
        // Buscar el valor en el HTML con varios patrones
        $patterns = [
            '/<strong[^>]*>\s*(\d{2,3}[,\.]\d{2})\s*<\/strong>/i',
            '/USD[^<]*<[^>]+>\s*(\d{2,3}[,\.]\d{2})/i',
            '/(\d{3,4}[,\.]\d{2})\s*<\/span>\s*<\/div>\s*<\/div>\s*<\/div>\s*<div[^>]+>\s*USD/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $m)) {
                $tasa = (float)str_replace(',', '.', $m[1]);
                // Validar rango razonable (entre 10 y 50000 Bs por dólar)
                if ($tasa > 10 && $tasa < 50000) {
                    return round($tasa, 2);
                }
            }
        }
        return null;
    }

    // ── HTTP helper ───────────────────────────────────────────────────
    private function httpGet(string $url, int $timeout = 8): ?string {
        if (!function_exists('curl_init')) return null;
        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => $timeout,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 3,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; AbasPOS/1.0)',
                CURLOPT_HTTPHEADER     => ['Accept: application/json, text/html'],
            ]);
            $response = curl_exec($ch);
            $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return ($code === 200 && $response) ? $response : null;
        } catch (Exception $e) {
            return null;
        }
    }

    // ── Cache ─────────────────────────────────────────────────────────
    private function readCache(): ?array {
        if (!file_exists($this->cacheFile)) return null;
        $data = json_decode(file_get_contents($this->cacheFile), true);
        return is_array($data) ? $data : null;
    }

    private function writeCache(float $tasa): void {
        file_put_contents($this->cacheFile, json_encode([
            'tasa'      => $tasa,
            'timestamp' => time(),
            'fecha'     => date('Y-m-d H:i:s'),
            'fuente'    => 'exchangerate-api / BCV',
        ]));
    }

    private function deleteCache(): void {
        if (file_exists($this->cacheFile)) unlink($this->cacheFile);
    }

    // ── Conversiones ─────────────────────────────────────────────────
    public function usdToBs(float $usd): float { return $usd * $this->getTasa(); }
    public function bsToUsd(float $bs):  float { return $bs  / $this->getTasa(); }

    public function formatUSD(float $amount): string {
        return '$' . number_format($amount, 2, '.', ',');
    }
    public function formatBs(float $amount): string {
        return 'Bs. ' . number_format($amount, 2, ',', '.');
    }

    // ── Info de última actualización ──────────────────────────────────
    public function getLastUpdate(): ?array {
        $cached = $this->readCache();
        if (!$cached) return null;
        $diff = time() - ($cached['timestamp'] ?? 0);
        if ($diff < 60)       $hace = 'Hace ' . $diff . ' segundos';
        elseif ($diff < 3600) $hace = 'Hace ' . floor($diff / 60) . ' minutos';
        else                  $hace = 'Hace ' . floor($diff / 3600) . ' horas';
        return [
            'tasa'      => $cached['tasa']      ?? null,
            'fecha'     => $cached['fecha']     ?? null,
            'timestamp' => $cached['timestamp'] ?? null,
            'hace'      => $hace,
        ];
    }
}
?>

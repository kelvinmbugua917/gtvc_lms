<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Config\Database;
use App\Config\AppConfig;

/**
 * System Health Diagnostic Controller
 */
class HealthController extends Controller
{
    /**
     * GET /api/v1/health
     */
    public function check(Request $request): void
    {
        $dbStatus = Database::testConnection();

        $data = [
            'status'     => 'OK',
            'service'    => 'Gilgil Technical & Vocational College LMS API',
            'version'    => '1.0.0-mvc-foundation',
            'php'        => [
                'version' => PHP_VERSION,
                'sapi'    => PHP_SAPI,
            ],
            'environment' => [
                'app_env'   => AppConfig::env('APP_ENV', 'production'),
                'debug'     => filter_var(AppConfig::env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
                'timezone'  => date_default_timezone_get(),
            ],
            'database'   => [
                'connected' => $dbStatus['connected'],
                'details'   => $dbStatus['connected'] ? $dbStatus['version'] : $dbStatus['error'],
            ],
            'timestamp'  => date('Y-m-d\TH:i:sP'),
        ];

        $statusCode = $dbStatus['connected'] ? 200 : 503;
        $message = $dbStatus['connected'] ? 'System health check passed' : 'Degraded state: Database connection failed';

        $this->json($data, $message, $statusCode);
    }
}

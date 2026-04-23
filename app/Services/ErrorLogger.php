<?php

namespace App\Services;

use App\Models\ErrorLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ErrorLogger
{
    /**
     * Log an application error. Values are stored with timestamps in UTC.
     */
    public function log(string $httpCode, string $content): void
    {
        ErrorLog::query()->create([
            'http_code' => $httpCode,
            'content' => $content,
            'timestamp' => now('UTC'),
        ]);
    }

    public function logThrowable(Throwable $e, ?Request $request = null): void
    {
        $request ??= function_exists('request') ? \request() : null;

        $this->log(
            $this->httpCodeFor($e, $request),
            $this->formatThrowable($e, $request)
        );
    }

    public function httpCodeFor(Throwable $e, ?Request $request): string
    {
        if ($e instanceof HttpExceptionInterface) {
            return (string) $e->getStatusCode();
        }

        if (app()->runningInConsole()) {
            return '0';
        }

        return '500';
    }

    private function formatThrowable(Throwable $e, ?Request $request): string
    {
        $lines = [];

        if ($request) {
            $method = $request->getMethod();
            $uri = $request->getRequestUri() ?: $request->getPathInfo() ?: $request->getUri();
            if ($uri !== '') {
                $lines[] = "Request: {$method} {$uri}";
            }
        }

        $lines[] = (string) $e;
        $lines[] = 'Stack trace:';
        $lines[] = $e->getTraceAsString();

        return implode("\n", $lines);
    }
}

<?php

namespace Tests\Feature;

use App\Models\ErrorLog;
use App\Services\ErrorLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ErrorLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_persists_row_with_utc_timestamp(): void
    {
        app(ErrorLogger::class)->log('500', 'Stacktrace: there was an error...');

        $this->assertDatabaseCount('errors', 1);

        $row = ErrorLog::query()->first();
        $this->assertSame('500', $row->http_code);
        $this->assertSame('Stacktrace: there was an error...', $row->content);
        $this->assertNotNull($row->timestamp);
        $this->assertSame('UTC', $row->timestamp->timezone->getName());
    }

    public function test_log_throwable_stores_code_and_message(): void
    {
        app(ErrorLogger::class)->logThrowable(new RuntimeException('Something broke'));

        $row = ErrorLog::query()->first();
        $this->assertSame('0', $row->http_code);
        $this->assertStringContainsString('RuntimeException', $row->content);
        $this->assertStringContainsString('Something broke', $row->content);
        $this->assertStringContainsString('Stack trace:', $row->content);
    }
}

<?php

namespace Tests\Unit;

use App\Services\Mpesa\DarajaService;
use RuntimeException;
use Tests\TestCase;

class DarajaServiceTest extends TestCase
{
    private DarajaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DarajaService;
    }

    public function test_normalizes_local_07_phone(): void
    {
        $this->assertSame('254712345678', $this->service->normalizePhone('0712345678'));
    }

    public function test_normalizes_01_phone(): void
    {
        $this->assertSame('254112345678', $this->service->normalizePhone('0112345678'));
    }

    public function test_normalizes_plus_254(): void
    {
        $this->assertSame('254712345678', $this->service->normalizePhone('+254712345678'));
    }

    public function test_normalizes_254_without_plus(): void
    {
        $this->assertSame('254712345678', $this->service->normalizePhone('254712345678'));
    }

    public function test_rejects_invalid_phone(): void
    {
        $this->expectException(RuntimeException::class);
        $this->service->normalizePhone('12345');
    }

    public function test_generates_password_from_shortcode_passkey_timestamp(): void
    {
        $password = $this->service->generatePassword('174379', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919', '20131220221130');
        $this->assertSame(
            base64_encode('174379bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c91920131220221130'),
            $password
        );
    }
}

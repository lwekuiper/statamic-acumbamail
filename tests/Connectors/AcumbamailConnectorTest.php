<?php

namespace Lwekuiper\StatamicAcumbamail\Tests\Connectors;

use Lwekuiper\StatamicAcumbamail\Connectors\AcumbamailConnector;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AcumbamailConnectorTest extends TestCase
{
    #[Test]
    public function it_returns_false_when_auth_token_is_null()
    {
        config()->set('statamic.acumbamail.auth_token', null);

        $connector = new AcumbamailConnector;

        $this->assertFalse($connector->isConfigured());
    }

    #[Test]
    public function it_returns_false_when_auth_token_is_empty()
    {
        config()->set('statamic.acumbamail.auth_token', '');

        $connector = new AcumbamailConnector;

        $this->assertFalse($connector->isConfigured());
    }

    #[Test]
    public function it_returns_true_when_auth_token_is_set()
    {
        config()->set('statamic.acumbamail.auth_token', 'test-token');

        $connector = new AcumbamailConnector;

        $this->assertTrue($connector->isConfigured());
    }
}

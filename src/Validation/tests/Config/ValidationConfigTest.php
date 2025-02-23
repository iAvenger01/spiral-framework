<?php

declare(strict_types=1);

namespace Spiral\Tests\Validation\Config;

use PHPUnit\Framework\TestCase;
use Spiral\Validation\Config\ValidationConfig;

final class ValidationConfigTest extends TestCase
{
    public function testDefaultValidatorIsNotSet(): void
    {
        $config = new ValidationConfig();

        self::assertNull($config->getDefaultValidator());
    }

    public function testDefaultValidator(): void
    {
        $config = new ValidationConfig(['defaultValidator' => 'some']);

        self::assertSame('some', $config->getDefaultValidator());
    }
}

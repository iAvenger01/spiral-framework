<?php

declare(strict_types=1);

namespace Spiral\Tests\Scaffolder;

use PHPUnit\Framework\Attributes\RequiresFunction;
use PHPUnit\Framework\TestCase;
use Spiral\Tests\Scaffolder\App\TestApp;

#[RequiresFunction('\Spiral\Framework\Kernel::init')]
abstract class BaseTestCase extends TestCase
{
    /** @var TestApp */
    protected $app;

    /**
     * @throws \Throwable
     */
    protected function setUp(): void
    {
        $this->app = TestApp::create([
            'root' => __DIR__ . '/App',
        ], false)->run();
    }
}

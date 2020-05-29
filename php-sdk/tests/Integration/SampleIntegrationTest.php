<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Integration;

use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;

/**
 * Class SampleIntegrationTest
 *
 * @package Pipes\PhpSdk\Tests\Integration
 */
final class SampleIntegrationTest extends DatabaseTestCaseAbstract
{

    /**
     *
     */
    public function testSample(): void
    {
        self::assertFake();
    }

}

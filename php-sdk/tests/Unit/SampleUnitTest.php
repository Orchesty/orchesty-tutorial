<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Unit;

use Pipes\PhpSdk\Tests\KernelTestCaseAbstract;

/**
 * Class SampleUnitTest
 *
 * @package Pipes\PhpSdk\Tests\Unit
 */
final class SampleUnitTest extends KernelTestCaseAbstract
{

    /**
     *
     */
    public function testSample(): void
    {
        self::assertFake();
    }

}

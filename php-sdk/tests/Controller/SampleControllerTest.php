<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Controller;

use Pipes\PhpSdk\Tests\ControllerTestCaseAbstract;

/**
 * Class SampleControllerTest
 *
 * @package Pipes\PhpSdk\Tests\Controller
 */
final class SampleControllerTest extends ControllerTestCaseAbstract
{

    /**
     *
     */
    public function testSample(): void
    {
        self::assertFake();
    }

}

<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Live;

use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;

/**
 * Class SampleLiveTest
 *
 * @package Pipes\PhpSdk\Tests\Live
 */
final class SampleLiveTest extends DatabaseTestCaseAbstract
{

    /**
     * @group live
     */
    public function testSample(): void
    {
        self::assertFake();
    }

}

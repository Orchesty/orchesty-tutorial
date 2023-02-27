<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests;

use Exception;

/**
 * Class DatabaseTestCaseAbstract
 *
 * @package Pipes\PhpSdk\Tests
 */
abstract class DatabaseTestCaseAbstract extends KernelTestCaseAbstract
{

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

}

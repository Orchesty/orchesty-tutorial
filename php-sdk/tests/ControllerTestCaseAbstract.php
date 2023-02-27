<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests;

use Exception;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\ControllerTestTrait;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\CustomAssertTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ControllerTestCaseAbstract
 *
 * @package Pipes\PhpSdk\Tests
 */
abstract class ControllerTestCaseAbstract extends WebTestCase
{

    use ControllerTestTrait;
    use CustomAssertTrait;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recreateClient();
    }

    /**
     *
     */
    protected function recreateClient(): void
    {
        $this->startClient();
    }

}

<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests;

use Exception;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\ControllerTestTrait;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\CustomAssertTrait;
use Hanaboso\PhpCheckUtils\PhpUnit\Traits\DatabaseTestTrait;
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
    use DatabaseTestTrait;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->recreateClient();
        $this->clearMongo();
    }

    /**
     *
     */
    protected function recreateClient(): void
    {
        $this->startClient();
        $this->dm = self::$container->get('doctrine_mongodb.odm.default_document_manager');
    }

}

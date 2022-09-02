<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Unit\Mapper;

use Exception;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Pipes\PhpSdk\Mapper\HelloWorld;
use Pipes\PhpSdk\Tests\KernelTestCaseAbstract;

/**
 * Class HelloWorldTest
 *
 * @package Pipes\PhpSdk\Tests\Unit\Mapper
 */
final class HelloWorldTest extends KernelTestCaseAbstract
{

    /**
     * @return void
     * @throws Exception
     */
    public function testName(): void
    {
        /** @var HelloWorld $node */
        $node = self::getContainer()->get(sprintf('hbpf.custom_node.%s', HelloWorld::NAME));
        self::assertEquals(HelloWorld::NAME, $node->getName());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testProcess(): void
    {
        /** @var HelloWorld $node */
        $node = self::getContainer()->get(sprintf('hbpf.custom_node.%s', HelloWorld::NAME));
        self::assertEquals(['message' => 'Hello world'], $node->processAction(new ProcessDto())->getJsonData());
    }

}

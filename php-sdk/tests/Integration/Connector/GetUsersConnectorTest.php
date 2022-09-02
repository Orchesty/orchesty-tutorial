<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Integration\Connector;

use Exception;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\RequestDto;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\ResponseDto;
use Hanaboso\CommonsBundle\Transport\CurlManagerInterface;
use Pipes\PhpSdk\Connector\GetUsersConnector;
use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;

/**
 * Class GetUsersConnectorTest
 *
 * @package Pipes\PhpSdk\Tests\Integration\Connector
 */
final class GetUsersConnectorTest extends DatabaseTestCaseAbstract
{

    /**
     * @return void
     * @throws Exception
     */
    public function testName(): void
    {
        self::assertEquals(GetUsersConnector::NAME, $this->getNode()->getName());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testProcess(): void
    {
        $node = $this->getNode();
        $node->setSender($this->mockCurl());

        $dto = $node->processAction(new ProcessDto());
        self::assertEquals(['body' => 'ok'], $dto->getJsonData());
    }

    /**
     * @return GetUsersConnector
     * @throws Exception
     */
    private function getNode(): GetUsersConnector
    {
        /** @var GetUsersConnector $node */
        $node = self::getContainer()->get(sprintf('hbpf.connector.%s', GetUsersConnector::NAME));

        return $node;
    }

    /**
     * @return CurlManagerInterface
     */
    private function mockCurl(): CurlManagerInterface
    {
        $mock = self::createMock(CurlManagerInterface::class);
        $mock->method('send')->willReturnCallback(static function (RequestDto $req) {
            self::assertEquals(CurlManager::METHOD_GET, $req->getMethod());
            self::assertEquals('https://jsonplaceholder.typicode.com/users', $req->getUri(TRUE));

            return new ResponseDto(200, '', '{"body":"ok"}', []);
        });

        return $mock;
    }

}

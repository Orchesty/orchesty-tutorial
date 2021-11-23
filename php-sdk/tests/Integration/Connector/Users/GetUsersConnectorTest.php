<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Integration\Connector\Users;

use Exception;
use Hanaboso\CommonsBundle\Exception\OnRepeatException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\ResponseDto;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\Utils\File\File;
use Pipes\PhpSdk\Connector\Users\GetUsersConnector;
use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;
use Pipes\PhpSdk\Tests\DataProvider;

/**
 * Class GetUsersConnectorTest
 *
 * @package Pipes\PhpSdk\Tests\Integration\Connector\Users
 */
final class GetUsersConnectorTest extends DatabaseTestCaseAbstract
{

    /**
     * @covers \Pipes\PhpSdk\Connector\Users\GetUsersConnector::getId
     * @covers \Pipes\PhpSdk\Connector\Users\GetUsersConnector::__construct
     *
     * @throws Exception
     */
    public function testGetId(): void
    {
        self::assertEquals('get-users', $this->createConnector(DataProvider::createResponseDto())->getId());
    }

    /**
     * @covers \Pipes\PhpSdk\Connector\Users\GetUsersConnector::processEvent
     *
     * @throws Exception
     */
    public function testProcessEvent(): void
    {
        self::expectException(ConnectorException::class);
        self::expectExceptionCode(ConnectorException::CONNECTOR_DOES_NOT_HAVE_PROCESS_EVENT);
        $this->createConnector(DataProvider::createResponseDto())->processEvent(DataProvider::getProcessDto());
    }

    /**
     * @covers \Pipes\PhpSdk\Connector\Users\GetUsersConnector::processAction
     *
     * @throws Exception
     */
    public function testProcessAction(): void
    {
        $content  = File::getContent(__DIR__ . '/data/users.json');
        $response = DataProvider::createResponseDto($content);
        $res      = $this->createConnector($response)->processAction(DataProvider::getProcessDto());

        self::assertEquals($content, $res->getData());
    }

    /**
     * @covers \Pipes\PhpSdk\Connector\Users\GetUsersConnector::processAction
     *
     * @throws Exception
     */
    public function testProcessActionRepeater(): void
    {
        $response = DataProvider::createResponseDto('', 500);

        self::expectException(OnRepeatException::class);
        $this->createConnector($response)->processAction(DataProvider::getProcessDto());
    }


    /**
     * -------------------------------------------- HELPERS ------------------------------------
     */

    /**
     * @param ResponseDto $dto
     *
     * @return GetUsersConnector
     */
    private function createConnector(ResponseDto $dto): GetUsersConnector
    {
        $sender = self::createMock(CurlManager::class);
        $sender->method('send')->willReturn($dto);

        return new GetUsersConnector($sender);
    }

}

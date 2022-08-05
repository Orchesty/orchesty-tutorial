<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Integration\Connector\Users;

use Exception;
use Hanaboso\CommonsBundle\Exception\OnRepeatException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\ResponseDto;
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
     * @covers \Pipes\PhpSdk\Connector\Users\GetUsersConnector::getName
     *
     * @throws Exception
     */
    public function testGetId(): void
    {
        self::assertEquals('get-users', $this->createConnector(DataProvider::createResponseDto())->getName());
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

        $conn = new GetUsersConnector();
        $conn->setSender($sender);

        return $conn;
    }

}

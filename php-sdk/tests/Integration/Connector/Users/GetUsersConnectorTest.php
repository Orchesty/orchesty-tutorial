<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Integration\Connector\Users;

use Exception;
use Hanaboso\CommonsBundle\Exception\OnRepeatException;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\ResponseDto;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Pipes\PhpSdk\Connector\Users\GetUsersConnector;
use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;

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
        self::assertEquals('get-users', $this->createConnector($this->createResponseDto())->getId());
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
        $this->createConnector($this->createResponseDto())->processEvent(new ProcessDto());
    }

    /**
     * @covers \Pipes\PhpSdk\Connector\Users\GetUsersConnector::processAction
     *
     * @throws Exception
     */
    public function testProcessAction(): void
    {
        $content  = (string) file_get_contents(__DIR__ . '/data/users.json');
        $response = $this->createResponseDto($content);
        $res      = $this->createConnector($response)->processAction(new ProcessDto());

        self::assertEquals($content, $res->getData());
    }

    /**
     * @covers \Pipes\PhpSdk\Connector\Users\GetUsersConnector::processAction
     *
     * @throws Exception
     */
    public function testProcessActionRepeater(): void
    {
        $response = $this->createResponseDto('', 500);

        self::expectException(OnRepeatException::class);
        $this->createConnector($response)->processAction(new ProcessDto());
    }


    /**
     * -------------------------------------------- HELPERS ------------------------------------
     */

    /**
     * @param ResponseDto    $dto
     * @param Exception|null $exception
     *
     * @return GetUsersConnector
     */
    private function createConnector(ResponseDto $dto, ?Exception $exception = NULL): GetUsersConnector
    {
        $sender = self::createMock(CurlManager::class);

        if ($exception) {
            $sender->method('send')->willThrowException($exception);
        } else {
            $sender->method('send')->willReturn($dto);
        }

        return new GetUsersConnector($sender);
    }

    /**
     * @param string $body
     * @param int    $code
     *
     * @return ResponseDto
     */
    private function createResponseDto(string $body = '', int $code = 200): ResponseDto
    {
        return new ResponseDto($code, '', $body, []);
    }

}

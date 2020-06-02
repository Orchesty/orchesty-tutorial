<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Integration\Connector\SendGrid;

use Exception;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\ResponseDto;
use Hanaboso\PipesPhpSdk\Application\Base\ApplicationInterface;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\Utils\String\Json;
use Hanaboso\Utils\System\PipesHeaders;
use Pipes\PhpSdk\Application\SendGridApplication;
use Pipes\PhpSdk\Connector\SendGrid\SendGridSendEmailConnector;
use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;

/**
 * Class SendGridSendEmailConnectorTest
 *
 * @package Pipes\PhpSdk\Tests\Integration\Connector\SendGrid
 */
final class SendGridSendEmailConnectorTest extends DatabaseTestCaseAbstract
{

    /**
     * @var SendGridApplication
     */
    private SendGridApplication $app;

    /**
     * @covers \Pipes\PhpSdk\Connector\SendGrid\SendGridSendEmailConnector::getId
     * @covers \Pipes\PhpSdk\Connector\SendGrid\SendGridSendEmailConnector::__construct
     *
     * @throws Exception
     */
    public function testGetId(): void
    {
        self::assertEquals('send-grid.send-email', $this->createConnector($this->createResponseDto())->getId());
    }

    /**
     * @covers \Pipes\PhpSdk\Connector\SendGrid\SendGridSendEmailConnector::processEvent
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
     * @covers \Pipes\PhpSdk\Connector\SendGrid\SendGridSendEmailConnector::processAction
     *
     * @throws Exception
     */
    public function testProcessAction(): void
    {
        $appInstall = new ApplicationInstall();
        $appInstall
            ->setSettings([ApplicationInterface::AUTHORIZATION_SETTINGS => [SendGridApplication::API_KEY => 'key']])
            ->setKey($this->app->getKey())
            ->setUser('user');

        $this->dm->persist($appInstall);
        $this->dm->flush();
        $this->dm->clear();

        $dto = new ProcessDto();
        $dto
            ->setData(
                Json::encode(['email' => 'noreply@johndoe.com', 'name' => 'John Doe', 'subject' => 'Hello, World!'])
            )
            ->setHeaders(
                [
                    PipesHeaders::createKey(PipesHeaders::USER)        => 'user',
                    PipesHeaders::createKey(PipesHeaders::APPLICATION) => $this->app->getKey(),
                ]
            );

        $res = $this->createConnector($this->createResponseDto())->setApplication($this->app)->processAction($dto);
        self::assertEquals('', $res->getData());
    }

    /**
     * @covers \Pipes\PhpSdk\Connector\SendGrid\SendGridSendEmailConnector::processAction
     *
     * @throws Exception
     */
    public function testProcessActionDataException(): void
    {
        $appInstall = new ApplicationInstall();
        $appInstall
            ->setSettings([ApplicationInterface::AUTHORIZATION_SETTINGS => [SendGridApplication::API_KEY => 'key']])
            ->setKey($this->app->getKey())
            ->setUser('user');

        $this->dm->persist($appInstall);
        $this->dm->flush();
        $this->dm->clear();

        $dto = new ProcessDto();
        $dto->setHeaders(
            [
                PipesHeaders::createKey(PipesHeaders::USER)        => 'user',
                PipesHeaders::createKey(PipesHeaders::APPLICATION) => $this->app->getKey(),
            ]
        );

        self::expectException(ConnectorException::class);
        $this
            ->createConnector($this->createResponseDto(), new CurlException())
            ->setApplication($this->app)
            ->processAction($dto);
    }

    /**
     * @covers \Pipes\PhpSdk\Connector\SendGrid\SendGridSendEmailConnector::processAction
     *
     * @throws Exception
     */
    public function testProcessActionRequestException(): void
    {
        $appInstall = new ApplicationInstall();
        $appInstall
            ->setSettings([ApplicationInterface::AUTHORIZATION_SETTINGS => [SendGridApplication::API_KEY => 'key']])
            ->setKey($this->app->getKey())
            ->setUser('user');

        $this->dm->persist($appInstall);
        $this->dm->flush();
        $this->dm->clear();

        $dto = new ProcessDto();
        $dto
            ->setData(
                Json::encode(['email' => 'noreply@johndoe.com', 'name' => 'John Doe', 'subject' => 'Hello, World!'])
            )
            ->setHeaders(
                [
                    PipesHeaders::createKey(PipesHeaders::USER)        => 'user',
                    PipesHeaders::createKey(PipesHeaders::APPLICATION) => $this->app->getKey(),
                ]
            );

        self::expectException(ConnectorException::class);
        $this
            ->createConnector($this->createResponseDto(), new CurlException())
            ->setApplication($this->app)
            ->processAction($dto);
    }

    /**
     * @covers \Pipes\PhpSdk\Connector\SendGrid\SendGridSendEmailConnector::processAction
     *
     * @throws Exception
     */
    public function testProcessActionException(): void
    {
        self::expectException(ConnectorException::class);
        self::expectExceptionCode(ConnectorException::CONNECTOR_FAILED_TO_PROCESS);
        $this->createConnector($this->createResponseDto())->setApplication($this->app)->processAction(new ProcessDto());
    }

    /**
     * -------------------------------------------- HELPERS ------------------------------------
     */

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new SendGridApplication();
    }

    /**
     * @param ResponseDto    $dto
     * @param Exception|null $exception
     *
     * @return SendGridSendEmailConnector
     */
    private function createConnector(ResponseDto $dto, ?Exception $exception = NULL): SendGridSendEmailConnector
    {
        $sender = self::createMock(CurlManager::class);

        if ($exception) {
            $sender->method('send')->willThrowException($exception);
        } else {
            $sender->method('send')->willReturn($dto);
        }

        return new SendGridSendEmailConnector($this->dm, $sender);
    }

    /**
     * @return ResponseDto
     */
    private function createResponseDto(): ResponseDto
    {
        return new ResponseDto(200, '', '', []);
    }

}

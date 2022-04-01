<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Integration\Connector\SendGrid;

use Exception;
use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\ResponseDto;
use Hanaboso\PipesPhpSdk\Application\Base\ApplicationInterface;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\PipesPhpSdk\Application\Exception\ApplicationInstallException;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\Utils\String\Json;
use Pipes\PhpSdk\Application\SendGridApplication;
use Pipes\PhpSdk\Connector\SendGrid\SendGridSendEmailConnector;
use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;
use Pipes\PhpSdk\Tests\DataProvider;

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
        self::assertEquals('send-grid.send-email', $this->createConnector(DataProvider::createResponseDto())->getId());
    }

    /**
     * @covers \Pipes\PhpSdk\Connector\SendGrid\SendGridSendEmailConnector::processAction
     *
     * @throws Exception
     */
    public function testProcessAction(): void
    {
        $this->pfd($this->createApplicationInstall());
        $this->dm->clear();

        $dto = DataProvider::getProcessDto(
            $this->app->getName(),
            'user',
            Json::encode(['email' => 'noreply@johndoe.com', 'name' => 'John Doe', 'subject' => 'Hello, World!']),
        );

        $res = $this->createConnector(DataProvider::createResponseDto())
            ->setApplication($this->app)
            ->processAction($dto);
        self::assertEquals('{}', $res->getData());
    }

    /**
     * @covers \Pipes\PhpSdk\Connector\SendGrid\SendGridSendEmailConnector::processAction
     *
     * @throws Exception
     */
    public function testProcessActionDataException(): void
    {
        $this->pfd($this->createApplicationInstall());
        $this->dm->clear();

        $dto = DataProvider::getProcessDto($this->app->getName());

        self::expectException(ConnectorException::class);
        $this
            ->createConnector(DataProvider::createResponseDto(), new CurlException())
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
        $this->pfd($this->createApplicationInstall());
        $this->dm->clear();

        $dto = DataProvider::getProcessDto(
            $this->app->getName(),
            'user',
            Json::encode(['email' => 'noreply@johndoe.com', 'name' => 'John Doe', 'subject' => 'Hello, World!']),
        );

        self::expectException(ConnectorException::class);
        $this
            ->createConnector(DataProvider::createResponseDto(), new CurlException())
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
        self::expectException(ApplicationInstallException::class);
        self::expectExceptionCode(ApplicationInstallException::APP_WAS_NOT_FOUND);
        $this
            ->createConnector(DataProvider::createResponseDto())
            ->setApplication($this->app)
            ->processAction(DataProvider::getProcessDto());
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
     * @return ApplicationInstall
     * @throws Exception
     */
    private function createApplicationInstall(): ApplicationInstall
    {
        $appInstall = DataProvider::getBasicAppInstall($this->app->getName());
        $appInstall
            ->setSettings([ApplicationInterface::AUTHORIZATION_SETTINGS => [SendGridApplication::API_KEY => 'key']]);

        return $appInstall;
    }

}

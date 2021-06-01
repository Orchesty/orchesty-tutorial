<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Integration\Connector\HubSpot;

use Exception;
use Hanaboso\CommonsBundle\Exception\OnRepeatException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\ResponseDto;
use Hanaboso\PipesPhpSdk\Application\Base\ApplicationAbstract;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\Utils\String\Json;
use Pipes\PhpSdk\Application\HubSpotApplication;
use Pipes\PhpSdk\Connector\HubSpot\HubSpotCreateContactConnector;
use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;
use Pipes\PhpSdk\Tests\DataProvider;
use Psr\Log\NullLogger;

/**
 * Class HubSpotCreateContactConnectorTest
 *
 * @package Pipes\PhpSdk\Tests\Integration\Connector\HubSpot
 */
final class HubSpotCreateContactConnectorTest extends DatabaseTestCaseAbstract
{

    /**
     * @var HubSpotApplication
     */
    private HubSpotApplication $app;

    /**
     * @covers \Pipes\PhpSdk\Connector\HubSpot\HubSpotCreateContactConnector::getId
     * @covers \Pipes\PhpSdk\Connector\HubSpot\HubSpotCreateContactConnector::__construct
     *
     * @throws Exception
     */
    public function testGetId(): void
    {
        self::assertEquals(
            'hub-spot.create-contact',
            $this->createConnector(DataProvider::createResponseDto())->getId(),
        );
    }

    /**
     * @covers \Pipes\PhpSdk\Connector\HubSpot\HubSpotCreateContactConnector::setLogger
     *
     * @throws Exception
     */
    public function testSetLogger(): void
    {
        $this->createConnector(DataProvider::createResponseDto())->setLogger(new NullLogger());
        self::assertFake();
    }

    /**
     * @covers \Pipes\PhpSdk\Connector\HubSpot\HubSpotCreateContactConnector::processEvent
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
     * @covers \Pipes\PhpSdk\Connector\HubSpot\HubSpotCreateContactConnector::processAction
     *
     * @throws Exception
     */
    public function testProcessAction(): void
    {
        $this->pfd($this->createApplicationInstall());
        $this->dm->clear();

        $dto = DataProvider::getProcessDto(
            $this->app->getKey(),
            'user',
            Json::encode(['name' => 'John Doe', 'email' => 'noreply@johndoe.com', 'phone' => '555-555']),
        );

        $res = $this->createConnector(DataProvider::createResponseDto())
            ->setApplication($this->app)
            ->processAction($dto);
        self::assertEquals('{}', $res->getData());
    }

    /**
     * @covers \Pipes\PhpSdk\Connector\HubSpot\HubSpotCreateContactConnector::processAction
     *
     * @throws Exception
     */
    public function testProcessActionDuplicitData(): void
    {
        $this->pfd($this->createApplicationInstall());
        $this->dm->clear();

        $dto = DataProvider::getProcessDto(
            $this->app->getKey(),
            'user',
            Json::encode(['name' => 'John Doe', 'email' => 'noreply@johndoe.com', 'phone' => '555-555']),
        );

        $ex  = (string) file_get_contents(__DIR__ . '/data/hubspot409Response.json');
        $res = $this->createConnector(
            DataProvider::createResponseDto($ex, 409),
        )
            ->setApplication($this->app)
            ->processAction($dto);
        self::assertEquals($ex, $res->getData());
    }

    /**
     * @covers \Pipes\PhpSdk\Connector\HubSpot\HubSpotCreateContactConnector::processAction
     *
     * @throws Exception
     */
    public function testProcessActionRequestException(): void
    {
        $this->pfd($this->createApplicationInstall());
        $this->dm->clear();

        $dto = DataProvider::getProcessDto(
            $this->app->getKey(),
            'user',
            Json::encode(['name' => 'John Doe', 'email' => 'noreply@johndoe.com', 'phone' => '555-555']),
        );

        self::expectException(OnRepeatException::class);
        $this
            ->createConnector(DataProvider::createResponseDto(), new CurlException())
            ->setApplication($this->app)
            ->processAction($dto);
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

        $this->app = new HubSpotApplication(self::$container->get('hbpf.providers.oauth2_provider'));
    }

    /**
     * @param ResponseDto    $dto
     * @param Exception|null $exception
     *
     * @return HubSpotCreateContactConnector
     */
    private function createConnector(ResponseDto $dto, ?Exception $exception = NULL): HubSpotCreateContactConnector
    {
        $sender = self::createMock(CurlManager::class);

        if ($exception) {
            $sender->method('send')->willThrowException($exception);
        } else {
            $sender->method('send')->willReturn($dto);
        }

        return new HubSpotCreateContactConnector($this->dm, $sender);
    }

    /**
     * @return ApplicationInstall
     * @throws Exception
     */
    private function createApplicationInstall(): ApplicationInstall
    {
        $appInstall = DataProvider::getOauth2AppInstall($this->app->getKey());
        $appInstall->setSettings(
            array_merge(
                $appInstall->getSettings(),
                [ApplicationAbstract::FORM => [HubSpotApplication::APP_ID => 'app_id'],],
            ),
        );

        return $appInstall;
    }

}

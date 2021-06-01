<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Integration\Connector\HubSpot;

use Exception;
use Hanaboso\PipesPhpSdk\Application\Base\ApplicationAbstract;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\Utils\String\Json;
use Pipes\PhpSdk\Application\HubSpotApplication;
use Pipes\PhpSdk\Connector\HubSpot\HubSpotContactCreatedConnector;
use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;
use Pipes\PhpSdk\Tests\DataProvider;

/**
 * Class HubSpotContactCreatedConnectorTest
 *
 * @package Pipes\PhpSdk\Tests\Integration\Connector\HubSpot
 */
final class HubSpotContactCreatedConnectorTest extends DatabaseTestCaseAbstract
{

    /**
     * @var HubSpotApplication
     */
    private HubSpotApplication $app;

    /**
     * @covers \Pipes\PhpSdk\Connector\HubSpot\HubSpotContactCreatedConnector::getId
     *
     * @throws Exception
     */
    public function testGetId(): void
    {
        self::assertEquals(
            'hub-spot.contact-created',
            $this->createConnector()->getId(),
        );
    }

    /**
     * @covers \Pipes\PhpSdk\Connector\HubSpot\HubSpotContactCreatedConnector::processAction
     *
     * @throws Exception
     */
    public function testProcessAction(): void
    {
        self::expectException(ConnectorException::class);
        self::expectExceptionCode(ConnectorException::CONNECTOR_DOES_NOT_HAVE_PROCESS_ACTION);
        $this->createConnector()->processAction(DataProvider::getProcessDto());
    }

    /**
     * @covers \Pipes\PhpSdk\Connector\HubSpot\HubSpotContactCreatedConnector::processEvent
     *
     * @throws Exception
     */
    public function testProcessEvent(): void
    {
        $this->pfd($this->createApplicationInstall());
        $this->dm->clear();

        $dto = DataProvider::getProcessDto(
            $this->app->getKey(),
            'user',
            Json::encode(['name' => 'John Doe', 'email' => 'noreply@johndoe.com', 'phone' => '555-555']),
        );

        $res = $this->createConnector()
            ->setApplication($this->app)
            ->processEvent($dto);
        self::assertEquals($dto->getData(), $res->getData());
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
     * @return HubSpotContactCreatedConnector
     */
    private function createConnector(): HubSpotContactCreatedConnector
    {
        return new HubSpotContactCreatedConnector();
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

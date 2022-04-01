<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Integration\Batch\Connector\HubSpot;

use Exception;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\ResponseDto;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\Utils\File\File;
use Hanaboso\Utils\String\Json;
use Hanaboso\Utils\System\PipesHeaders;
use Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector;
use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;
use Pipes\PhpSdk\Tests\DataProvider;
use Psr\Log\NullLogger;

/**
 * Class HubSpotListContactsConnectorTest
 *
 * @package Pipes\PhpSdk\Tests\Integration\Batch\Connector\HubSpot
 */
final class HubSpotListContactsConnectorTest extends DatabaseTestCaseAbstract
{

    /**
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::getId
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::__construct
     *
     * @throws Exception
     */
    public function testGetId(): void
    {
        self::assertEquals('hub-spot.list-contacts', $this->createConnector()->getId());
    }

    /**
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::setLogger
     *
     * @throws Exception
     */
    public function testSetLogger(): void
    {
        $this->createConnector()->setLogger(new NullLogger());

        self::assertFake();
    }

    /**
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::processAction
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::doPageLoop
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::getUri
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::createSuccessMessage
     *
     * @throws Exception
     */
    public function testProcessAction(): void
    {
        $app = self::getContainer()->get('hbpf.application.hub-spot');

        $response  = new ResponseDto(200, '',File::getContent(__DIR__ . '/data/listContacts.json'), []);
        $response2 = new ResponseDto(200, '', File::getContent(__DIR__ . '/data/emptyListContacts.json'), []);

        $expect  = Json::decode(File::getContent(__DIR__ . '/data/listContacts.json'))['contacts'];
        $expect2 = Json::decode(File::getContent(__DIR__ . '/data/emptyListContacts.json'))['contacts'];

        $this->assertBatch(
            $this->createConnector([$response, $response2]),
            DataProvider::getProcessDto($app->getName()),
            [
                (new ProcessDto())
                    ->setData(Json::encode($expect))
                    ->setHeaders(
                        [
                            PipesHeaders::createKey(PipesHeaders::BATCH_CURSOR) => '1',
                            PipesHeaders::createKey(PipesHeaders::RESULT_MESSAGE) =>
                                'Message will be used as a iterator with cursor [1]. Data will be send to follower(s).',
                            PipesHeaders::createKey(PipesHeaders::RESULT_CODE) => '1010',
                        ],
                    ),
                (new ProcessDto())->setData(Json::encode($expect2)),
            ],
        );
    }

    /**
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::processAction
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::doPageLoop
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::getUri
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::createSuccessMessage
     *
     * @throws Exception
     */
    public function testProcessActionBadData(): void
    {
        $app      = self::getContainer()->get('hbpf.application.hub-spot');
        $response = new ResponseDto(200,'','{}', []);

        $this->expectException(ConnectorException::class);
        $this->assertBatch(
            $this->createConnector([$response]),
            DataProvider::getProcessDto($app->getName()),
            [DataProvider::getProcessDto()],
        );
    }

    /**
     * -------------------------------------------- HELPERS ------------------------------------
     */

    /**
     * @param ResponseDto[] $responses
     *
     * @return HubSpotListContactsConnector
     * @throws Exception
     */
    private function createConnector(array $responses = []): HubSpotListContactsConnector
    {
        $app  = self::getContainer()->get('hbpf.application.hub-spot');
        $curl = $this->createMock(CurlManager::class);
        $curl
            ->expects(self::exactly(count($responses)))
            ->method('send')
            ->willReturnOnConsecutiveCalls(...$responses);

        $c = new HubSpotListContactsConnector($this->dm, $curl);
        $c->setApplication($app);

        $appInstall = DataProvider::getOauth2AppInstall($app->getName());
        $this->pfd($appInstall);
        $this->dm->clear();

        return $c;
    }

}

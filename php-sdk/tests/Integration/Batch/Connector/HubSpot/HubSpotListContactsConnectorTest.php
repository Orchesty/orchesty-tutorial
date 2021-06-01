<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Integration\Batch\Connector\HubSpot;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Response;
use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
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
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::processEvent
     *
     * @throws Exception
     */
    public function testProcessEvent(): void
    {
        self::expectException(ConnectorException::class);
        self::expectExceptionCode(ConnectorException::CONNECTOR_DOES_NOT_HAVE_PROCESS_EVENT);
        $this->createConnector()->processEvent(DataProvider::getProcessDto());
    }

    /**
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::processBatch
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::doPageLoop
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::getUri
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::createSuccessMessage
     *
     * @throws Exception
     */
    public function testProcessBatch(): void
    {
        $app = self::$container->get('hbpf.application.hub-spot');

        $response = new Response(200, [], (string) file_get_contents(__DIR__ . '/data/listContacts.json'));
        $promise  = $this->createPromise(static fn() => $response);

        $response2 = new Response(200, [], (string) file_get_contents(__DIR__ . '/data/emptyListContacts.json'));
        $promise2  = $this->createPromise(static fn() => $response2);

        $this->assertBatch($this->createConnector([$promise, $promise2]), DataProvider::getProcessDto($app->getKey()));
    }

    /**
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::processBatch
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::doPageLoop
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::getUri
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::createSuccessMessage
     *
     * @throws Exception
     */
    public function testProcessBatchBadData(): void
    {
        $app      = self::$container->get('hbpf.application.hub-spot');
        $response = new Response(200, [], '{}');
        $promise  = $this->createPromise(static fn() => $response);

        $this->createConnector([$promise])
            ->processBatch(
                DataProvider::getProcessDto($app->getKey()),
                fn() => $this->createPromise(),
            )
            ->then(
                NULL,
                static function (Exception $e): void {
                    self::assertInstanceOf(ConnectorException::class, $e);
                },
            )->wait();
    }

    /**
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::processBatch
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::doPageLoop
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::getUri
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::batchConnectorError
     *
     * @throws Exception
     */
    public function testProcessBatchRejected(): void
    {
        $app     = self::$container->get('hbpf.application.hub-spot');
        $promise = new RejectedPromise(new CurlException('some error'));

        $this->createConnector([$promise])
            ->processBatch(
                DataProvider::getProcessDto($app->getKey()),
                fn() => $this->createPromise(),
            )
            ->then(
                NULL,
                static function (Exception $e): void {
                    self::assertInstanceOf(CurlException::class, $e);
                },
            )->wait();
    }


    /**
     * -------------------------------------------- HELPERS ------------------------------------
     */

    /**
     * @param PromiseInterface[] $responses
     *
     * @return HubSpotListContactsConnector
     * @throws Exception
     */
    private function createConnector(array $responses = []): HubSpotListContactsConnector
    {
        $app  = self::$container->get('hbpf.application.hub-spot');
        $curl = $this->createMock(CurlManager::class);
        $curl
            ->expects(self::exactly(count($responses)))
            ->method('sendAsync')
            ->willReturnOnConsecutiveCalls(...$responses);

        $c = new HubSpotListContactsConnector($this->dm, $curl);
        $c->setApplication($app);

        $appInstall = DataProvider::getOauth2AppInstall($app->getKey());
        $this->pfd($appInstall);
        $this->dm->clear();

        return $c;
    }

}

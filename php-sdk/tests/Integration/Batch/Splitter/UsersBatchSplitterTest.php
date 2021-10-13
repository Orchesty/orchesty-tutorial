<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Integration\Batch\Splitter;

use Exception;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\PipesPhpSdk\RabbitMq\Impl\Batch\SuccessMessage;
use Hanaboso\Utils\File\File;
use Hanaboso\Utils\String\Json;
use Pipes\PhpSdk\Batch\Splitter\UsersBatchSplitter;
use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;
use Pipes\PhpSdk\Tests\DataProvider;

/**
 * Class UsersBatchSplitterTest
 *
 * @package Pipes\PhpSdk\Tests\Integration\Batch\Splitter
 */
final class UsersBatchSplitterTest extends DatabaseTestCaseAbstract
{

    /**
     * @covers \Pipes\PhpSdk\Batch\Splitter\UsersBatchSplitter::getId
     *
     * @throws Exception
     */
    public function testGetId(): void
    {
        self::assertEquals('user-batch-splitter', (new UsersBatchSplitter())->getId());
    }

    /**
     * @covers \Pipes\PhpSdk\Batch\Splitter\UsersBatchSplitter::processAction
     *
     * @throws Exception
     */
    public function testProcessAction(): void
    {
        self::expectException(ConnectorException::class);
        self::expectExceptionCode(ConnectorException::CONNECTOR_DOES_NOT_HAVE_PROCESS_ACTION);
        (new UsersBatchSplitter())->processAction(DataProvider::getProcessDto());
    }

    /**
     * @covers \Pipes\PhpSdk\Batch\Splitter\UsersBatchSplitter::processEvent
     *
     * @throws Exception
     */
    public function testProcessEvent(): void
    {
        self::expectException(ConnectorException::class);
        self::expectExceptionCode(ConnectorException::CONNECTOR_DOES_NOT_HAVE_PROCESS_EVENT);
        (new UsersBatchSplitter())->processEvent(DataProvider::getProcessDto());
    }

    /**
     * @covers \Pipes\PhpSdk\Batch\Splitter\UsersBatchSplitter::processBatch
     *
     * @throws Exception
     */
    public function testProcessBatch(): void
    {
        $batch = new UsersBatchSplitter();
        $batch->processBatch(
            DataProvider::getProcessDto('', '', File::getContent(__DIR__ . '/data/users.json')),
            static function (SuccessMessage $message): void {
                $data = Json::decode($message->getData());

                self::assertArrayHasKey('id', $data);
            },
        )->then(
            static function (): void {
                self::assertTrue(TRUE);
            },
            static function (): void {
                self::fail('Something gone wrong!');
            },
        );
    }

}

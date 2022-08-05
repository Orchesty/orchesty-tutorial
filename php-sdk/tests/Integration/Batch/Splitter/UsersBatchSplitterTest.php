<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Integration\Batch\Splitter;

use Exception;
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
     * @covers \Pipes\PhpSdk\Batch\Splitter\UsersBatchSplitter::getName
     *
     * @throws Exception
     */
    public function testGetId(): void
    {
        self::assertEquals('user-batch-splitter', (new UsersBatchSplitter())->getName());
    }

    /**
     * @covers \Pipes\PhpSdk\Batch\Splitter\UsersBatchSplitter::processAction
     *
     * @throws Exception
     */
    public function testProcessAction(): void
    {
        $this->assertBatch(
            new UsersBatchSplitter(),
            DataProvider::getBatchProcessDto('', '', File::getContent(__DIR__ . '/data/users.json')),
            [
                DataProvider::getBatchProcessDto('', '', File::getContent(__DIR__ . '/data/users.json'))
                    ->setItemList(Json::decode(File::getContent(__DIR__ . '/data/users.json'))),
            ],
        );
    }

}

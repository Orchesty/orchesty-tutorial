<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Integration\CommonNode;

use Exception;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\PipesPhpSdk\Storage\DataStorage\DataStorageManager;
use Pipes\PhpSdk\CommonNode\LoadRepositories;
use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;

/**
 * Class LoadRepositoriesTest
 *
 * @package Pipes\PhpSdk\Tests\Integration\CommonNode
 */
final class LoadRepositoriesTest extends DatabaseTestCaseAbstract
{

    /**
     * @return void
     * @throws Exception
     */
    public function testName(): void
    {
        self::assertEquals(LoadRepositories::NAME, $this->getNode()->getName());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testProcess(): void
    {
        /** @var DataStorageManager $dataStorageManager */
        $dataStorageManager = self::getContainer()->get('hbpf.data_storage_manager');
        $dataStorageManager->store('2', ['d2']);

        $node = $this->getNode();

        $dto = $node->processAction((new ProcessDto())->setJsonData(['collection' => '2']));
        self::assertEquals('2', $dto->getJsonData()[0]['processId']);
    }

    /**
     * @return LoadRepositories
     * @throws Exception
     */
    private function getNode(): LoadRepositories
    {
        /** @var LoadRepositories $node */
        $node = self::getContainer()->get(sprintf('hbpf.common_node.%s', LoadRepositories::NAME));

        return $node;
    }

}

<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Integration\Batch;

use Exception;
use Hanaboso\CommonsBundle\Process\BatchProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\RequestDto;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\ResponseDto;
use Hanaboso\CommonsBundle\Transport\CurlManagerInterface;
use Hanaboso\PipesPhpSdk\Application\Base\ApplicationInterface;
use Hanaboso\PipesPhpSdk\Storage\DataStorage\DataStorageManager;
use Hanaboso\Utils\String\Json;
use Hanaboso\Utils\System\PipesHeaders;
use Pipes\PhpSdk\Application\GitHubApplication;
use Pipes\PhpSdk\Batch\GitHubStoreRepositoriesBatch;
use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;
use Pipes\PhpSdk\Tests\DataProvider;

/**
 * Class GitHubStoreRepositoryBatchTest
 *
 * @package Pipes\PhpSdk\Tests\Integration\Batch
 */
final class GitHubStoreRepositoryBatchTest extends DatabaseTestCaseAbstract
{

    /**
     * @return void
     * @throws Exception
     */
    public function testName(): void
    {
        self::assertEquals(GitHubStoreRepositoriesBatch::NAME, $this->getNode()->getName());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testProcess(): void
    {
        $this->createApplicationInstall();
        $node = $this->getNode();
        $node->setSender($this->mockCurl());

        $dto = new BatchProcessDto();
        $dto
            ->setBridgeData('{"org":"org"}')
            ->setUser('user')
            ->addHeader(PipesHeaders::PROCESS_ID, '2');

        $dto  = $node->processAction($dto);
        $data = Json::decode($dto->getBridgeData());

        self::assertCount(1, $data);

        /** @var DataStorageManager $dataStorageManager */
        $dataStorageManager = self::getContainer()->get('hbpf.data_storage_manager');
        $data               = $dataStorageManager->load('2');
        self::assertEquals(1, sizeof($data ?? []));
        if ($data) {
            self::assertEquals('2', $data[0]->getProcessId());
        }
    }

    /**
     * @return GitHubStoreRepositoriesBatch
     * @throws Exception
     */
    private function getNode(): GitHubStoreRepositoriesBatch
    {
        /** @var GitHubStoreRepositoriesBatch $node */
        $node = self::getContainer()->get(sprintf('hbpf.batch.%s', GitHubStoreRepositoriesBatch::NAME));

        return $node;
    }

    /**
     * @return CurlManagerInterface
     * @throws Exception
     */
    private function mockCurl(): CurlManagerInterface
    {
        $mock = self::createMock(CurlManagerInterface::class);
        $mock->method('send')->willReturnCallback(static function (RequestDto $req) {
            self::assertEquals(CurlManager::METHOD_GET, $req->getMethod());
            self::assertEquals('https://api.github.com/orgs/org/repos?per_page=5&page=1', $req->getUri(TRUE));

            return new ResponseDto(200, '', '[{"body":"ok"},{"body":"ok"}]', []);
        });

        return $mock;
    }

    /**
     * @throws Exception
     */
    private function createApplicationInstall(): void
    {
        $appInstall = DataProvider::getBasicAppInstall(GitHubApplication::NAME);
        $appInstall
            ->setSettings([
                              ApplicationInterface::AUTHORIZATION_FORM => [
                                  GitHubApplication::USER  => 'usr',
                                  GitHubApplication::TOKEN => 'tkn',
                              ],
                          ]);

        $this->pfd($appInstall);
    }

}

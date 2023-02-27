<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Integration\Connector;

use Exception;
use GuzzleHttp\Psr7\Response;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\RequestDto;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\ResponseDto;
use Hanaboso\CommonsBundle\Transport\CurlManagerInterface;
use Hanaboso\PipesPhpSdk\Application\Base\ApplicationInterface;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\Utils\String\Json;
use Pipes\PhpSdk\Application\GitHubApplication;
use Pipes\PhpSdk\Connector\GitHubRepositoryConnector;
use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;
use Pipes\PhpSdk\Tests\DataProvider;
use Pipes\PhpSdk\Tests\MockServer\Mock;
use Pipes\PhpSdk\Tests\MockServer\MockServer;

/**
 * Class GitHubRepositoryConnectorTest
 *
 * @package Pipes\PhpSdk\Tests\Integration\Connector
 */
final class GitHubRepositoryConnectorTest extends DatabaseTestCaseAbstract
{

    /**
     * @var MockServer $mockServer
     */
    private MockServer $mockServer;

    /**
     * @return void
     * @throws Exception
     */
    public function testName(): void
    {
        self::assertEquals(GitHubRepositoryConnector::NAME, $this->getNode()->getName());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testProcess(): void
    {
        $this->mockServer->addMock(
            new Mock(
                '/document/ApplicationInstall?filter={"names":["git-hub"],"users":["user"]}',
                NULL,
                CurlManager::METHOD_GET,
                new Response(200, [], Json::encode($this->createApplicationInstall()->toArray())),
            ),
        );
        $node = $this->getNode();
        $node->setSender($this->mockCurl());
        $dto = new ProcessDto();
        $dto
            ->setJsonData(['org' => 'org', 'repo' => 'rep'])
            ->addHeader('user', 'user');

        $dto = $node->processAction($dto);
        self::assertEquals(['body' => 'ok'], $dto->getJsonData());
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockServer = new MockServer();
        self::getContainer()->set('hbpf.worker-api', $this->mockServer);
    }

    /**
     * @return GitHubRepositoryConnector
     * @throws Exception
     */
    private function getNode(): GitHubRepositoryConnector
    {
        /** @var GitHubRepositoryConnector $node */
        $node = self::getContainer()->get(sprintf('hbpf.connector.%s', GitHubRepositoryConnector::NAME));

        return $node;
    }

    /**
     * @return CurlManagerInterface
     */
    private function mockCurl(): CurlManagerInterface
    {
        $mock = self::createMock(CurlManagerInterface::class);
        $mock->method('send')->willReturnCallback(static function (RequestDto $req) {
            self::assertEquals(CurlManager::METHOD_GET, $req->getMethod());
            self::assertEquals('https://api.github.com/repos/org/rep', $req->getUri(TRUE));

            return new ResponseDto(200, '', '{"body":"ok"}', []);
        });

        return $mock;
    }

    /**
     * @return ApplicationInstall
     * @throws Exception
     */
    private function createApplicationInstall(): ApplicationInstall
    {
        $appInstall = DataProvider::getBasicAppInstall(GitHubApplication::NAME);
        $appInstall
            ->setSettings([
                ApplicationInterface::AUTHORIZATION_FORM => [
                    GitHubApplication::USER  => 'usr',
                    GitHubApplication::TOKEN => 'tkn',
                ],
            ]);

        return $appInstall;
    }

}

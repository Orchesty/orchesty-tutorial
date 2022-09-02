<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Integration\Connector;

use Exception;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\RequestDto;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\ResponseDto;
use Hanaboso\CommonsBundle\Transport\CurlManagerInterface;
use Hanaboso\PipesPhpSdk\Authorization\Provider\OAuth2Provider;
use Pipes\PhpSdk\Application\HubSpotApplication;
use Pipes\PhpSdk\Connector\HubSpotCreateContactConnector;
use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;
use Pipes\PhpSdk\Tests\DataProvider;

/**
 * Class HubSpotCreateContactConnectorTest
 *
 * @package Pipes\PhpSdk\Tests\Integration\Connector
 */
final class HubSpotCreateContactConnectorTest extends DatabaseTestCaseAbstract
{

    /**
     * @return void
     * @throws Exception
     */
    public function testName(): void
    {
        self::assertEquals(HubSpotCreateContactConnector::NAME, $this->getNode()->getName());
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
        $dto = new ProcessDto();
        $dto->addHeader('user', 'user');

        $dto = $node->processAction($dto);
        self::assertEquals(['body' => 'ok'], $dto->getJsonData());
    }

    /**
     * @return HubSpotCreateContactConnector
     * @throws Exception
     */
    private function getNode(): HubSpotCreateContactConnector
    {
        /** @var HubSpotCreateContactConnector $node */
        $node = self::getContainer()->get(sprintf('hbpf.connector.%s', HubSpotCreateContactConnector::NAME));

        return $node;
    }

    /**
     * @return CurlManagerInterface
     */
    private function mockCurl(): CurlManagerInterface
    {
        $mock = self::createMock(CurlManagerInterface::class);
        $mock->method('send')->willReturnCallback(static function (RequestDto $req) {
            self::assertEquals(CurlManager::METHOD_POST, $req->getMethod());
            self::assertEquals(sprintf('%s/crm/v3/objects/contacts', HubSpotApplication::BASE_URL), $req->getUri(TRUE));

            return new ResponseDto(200, '', '{"body":"ok"}', []);
        });

        return $mock;
    }

    /**
     * @throws Exception
     */
    private function createApplicationInstall(): void
    {
        $appInstall = DataProvider::getBasicAppInstall(HubSpotApplication::NAME);
        $appInstall
            ->setSettings([
                HubSpotApplication::AUTHORIZATION_FORM => [
                    HubSpotApplication::TOKEN => [
                        OAuth2Provider::ACCESS_TOKEN => 't.k.n',
                    ],
                ],
            ]);

        $this->pfd($appInstall);
    }

}

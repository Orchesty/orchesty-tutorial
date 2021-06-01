<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Unit\Application;

use Exception;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\PipesPhpSdk\Authorization\Provider\OAuth1Provider;
use Hanaboso\Utils\String\Json;
use Pipes\PhpSdk\Application\SampleOAuth1Application;
use Pipes\PhpSdk\Tests\DataProvider;
use Pipes\PhpSdk\Tests\KernelTestCaseAbstract;

/**
 * Class SampleOAuth1ApplicationTest
 *
 * @package Pipes\PhpSdk\Tests\Unit\Application
 */
final class SampleOAuth1ApplicationTest extends KernelTestCaseAbstract
{

    /**
     * @var SampleOAuth1Application
     */
    private SampleOAuth1Application $app;

    /**
     * @covers \Pipes\PhpSdk\Application\SampleOAuth1Application::getKey
     *
     * @throws Exception
     */
    public function testGetKey(): void
    {
        self::assertEquals('sample-oauth1', $this->app->getKey());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\SampleOAuth1Application::getName
     *
     * @throws Exception
     */
    public function testGetName(): void
    {
        self::assertEquals('SampleOAuth1 Application', $this->app->getName());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\SampleOAuth1Application::getDescription
     *
     * @throws Exception
     */
    public function testGetDescription(): void
    {
        self::assertEquals('Description.', $this->app->getDescription());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\SampleOAuth1Application::getRequestDto
     *
     * @throws Exception
     */
    public function testGetRequestDto(): void
    {
        $dto = $this->app->getRequestDto(
            DataProvider::createApplicationInstall($this->app->getKey()),
            CurlManager::METHOD_POST,
            'foo.bar',
            Json::encode(['foo' => 'bar']),
        );
        self::assertEquals(CurlManager::METHOD_POST, $dto->getMethod());
        self::assertEquals('foo.bar', $dto->getUri(TRUE));
        self::assertEquals(Json::encode(['foo' => 'bar']), $dto->getBody());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\SampleOAuth1Application::getSettingsForm
     *
     * @throws Exception
     */
    public function testGetSettingsForm(): void
    {
        $form = $this->app->getSettingsForm();
        self::assertCount(0, $form->getFields());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\SampleOAuth1Application::authorize
     * @covers \Pipes\PhpSdk\Application\SampleOAuth1Application::getTokenUrl
     * @covers \Pipes\PhpSdk\Application\SampleOAuth1Application::getAuthorizeUrl
     *
     * @throws Exception
     */
    public function testAuthorize(): void
    {
        $this->app->authorize(DataProvider::createApplicationInstall($this->app->getKey()));

        self::assertFake();
    }

    /**
     * @covers \Pipes\PhpSdk\Application\SampleOAuth1Application::setAuthorizationToken
     * @covers \Pipes\PhpSdk\Application\SampleOAuth1Application::getAccessTokenUrl
     *
     * @throws Exception
     */
    public function testSetAuthorizationToken(): void
    {
        $this->app->setAuthorizationToken(
            DataProvider::createApplicationInstall($this->app->getKey()),
            ['token' => 'abc'],
        );

        self::assertFake();
    }


    /**
     * -------------------------------------------- HELPERS ------------------------------------
     */

    /**
     *
     */
    protected function setUp(): void
    {
        parent::setUp();

        $provider = self::createMock(OAuth1Provider::class);
        $provider->method('authorize')->willReturnCallback(static fn(): string => 'redirect/url');
        $provider->method('getAccessToken')->willReturnCallback(static fn(): array => ['token' => 'accessToken']);
        $this->app = new SampleOAuth1Application($provider);
    }

}

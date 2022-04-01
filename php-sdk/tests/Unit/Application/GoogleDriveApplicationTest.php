<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Unit\Application;

use Exception;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\PipesPhpSdk\Authorization\Provider\OAuth2Provider;
use Hanaboso\Utils\String\Json;
use Pipes\PhpSdk\Application\GoogleDriveApplication;
use Pipes\PhpSdk\Tests\DataProvider;
use Pipes\PhpSdk\Tests\KernelTestCaseAbstract;

/**
 * Class GoogleDriveApplicationTest
 *
 * @package Pipes\PhpSdk\Tests\Unit\Application
 */
final class GoogleDriveApplicationTest extends KernelTestCaseAbstract
{

    /**
     * @var GoogleDriveApplication
     */
    private GoogleDriveApplication $app;

    /**
     * @covers \Pipes\PhpSdk\Application\GoogleDriveApplication::getName
     *
     * @throws Exception
     */
    public function testGetName(): void
    {
        self::assertEquals('google-drive', $this->app->getName());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\GoogleDriveApplication::getPublicName
     *
     * @throws Exception
     */
    public function testGetPublicName(): void
    {
        self::assertEquals('GoogleDrive Application', $this->app->getPublicName());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\GoogleDriveApplication::getDescription
     *
     * @throws Exception
     */
    public function testGetDescription(): void
    {
        self::assertEquals('GoogleDrive Application', $this->app->getDescription());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\GoogleDriveApplication::getAuthUrl
     *
     * @throws Exception
     */
    public function testGetAuthUrl(): void
    {
        self::assertEquals(GoogleDriveApplication::AUTH_URL, $this->app->getAuthUrl());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\GoogleDriveApplication::getTokenUrl
     *
     * @throws Exception
     */
    public function testGetTokenUrl(): void
    {
        self::assertEquals(GoogleDriveApplication::TOKEN_URL, $this->app->getTokenUrl());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\GoogleDriveApplication::getRequestDto
     *
     * @throws Exception
     */
    public function testGetRequestDto(): void
    {
        $dto = $this->app->getRequestDto(
            DataProvider::getOauth2AppInstall($this->app->getName()),
            CurlManager::METHOD_POST,
            NULL,
            Json::encode(['foo' => 'bar']),
        );
        self::assertEquals(CurlManager::METHOD_POST, $dto->getMethod());
        self::assertEquals(GoogleDriveApplication::BASE_URL, $dto->getUri(TRUE));
        self::assertEquals(Json::encode(['foo' => 'bar']), $dto->getBody());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\GoogleDriveApplication::getSettingsForm
     *
     * @throws Exception
     */
    public function testGetSettingsForm(): void
    {
        $form = $this->app->getSettingsForm();
        self::assertCount(2, $form->getFields());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\GoogleDriveApplication::authorize
     * @covers \Pipes\PhpSdk\Application\GoogleDriveApplication::getScopes
     *
     * @throws Exception
     */
    public function testAuthorize(): void
    {
        $this->app->authorize(DataProvider::getOauth2AppInstall($this->app->getName()));

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

        $provider = self::createMock(OAuth2Provider::class);
        $provider->method('authorize')->willReturnCallback(static fn(): string => 'redirect/url');
        $this->app = new GoogleDriveApplication($provider);
    }

}

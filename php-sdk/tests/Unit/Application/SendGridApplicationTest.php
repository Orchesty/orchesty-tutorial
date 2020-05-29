<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Unit\Application;

use Exception;
use Hanaboso\CommonsBundle\Enum\ApplicationTypeEnum;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\PipesPhpSdk\Application\Base\ApplicationInterface;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\PipesPhpSdk\Application\Exception\ApplicationInstallException;
use Hanaboso\Utils\String\Json;
use Pipes\PhpSdk\Application\SendGridApplication;
use Pipes\PhpSdk\Tests\KernelTestCaseAbstract;

/**
 * Class SendGridApplicationTest
 *
 * @package Pipes\PhpSdk\Tests\Unit\Application
 */
final class SendGridApplicationTest extends KernelTestCaseAbstract
{

    /**
     * @var SendGridApplication
     */
    private SendGridApplication $app;

    /**
     * @covers \Pipes\PhpSdk\Application\SendGridApplication::getApplicationType
     *
     * @throws Exception
     */
    public function testGetApplicationType(): void
    {
        self::assertEquals(ApplicationTypeEnum::CRON, $this->app->getApplicationType());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\SendGridApplication::getKey
     *
     * @throws Exception
     */
    public function testGetKey(): void
    {
        self::assertEquals('send-grid', $this->app->getKey());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\SendGridApplication::getName
     *
     * @throws Exception
     */
    public function testGetName(): void
    {
        self::assertEquals('SendGrid Application', $this->app->getName());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\SendGridApplication::getDescription
     *
     * @throws Exception
     */
    public function testGetDescription(): void
    {
        self::assertEquals('Send Email With Confidence.', $this->app->getDescription());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\SendGridApplication::isAuthorized
     *
     * @throws Exception
     */
    public function testIsAuthorized(): void
    {
        $appInstall = new ApplicationInstall();
        self::assertFalse($this->app->isAuthorized($appInstall));

        $appInstall->setSettings(
            [ApplicationInterface::AUTHORIZATION_SETTINGS => [SendGridApplication::API_KEY => 'key']]
        );
        self::assertTrue($this->app->isAuthorized($appInstall));
    }

    /**
     * @covers \Pipes\PhpSdk\Application\SendGridApplication::getRequestDto
     * @covers \Pipes\PhpSdk\Application\SendGridApplication::isAuthorized
     *
     * @throws Exception
     */
    public function testGetRequestDto(): void
    {
        $appInstall = new ApplicationInstall();
        $appInstall->setSettings(
            [ApplicationInterface::AUTHORIZATION_SETTINGS => [SendGridApplication::API_KEY => 'key']]
        );

        $dto = $this->app->getRequestDto($appInstall, CurlManager::METHOD_POST, NULL, Json::encode(['foo' => 'bar']));
        self::assertEquals(CurlManager::METHOD_POST, $dto->getMethod());
        self::assertEquals(SendGridApplication::BASE_URL, $dto->getUri(TRUE));
        self::assertEquals(Json::encode(['foo' => 'bar']), $dto->getBody());

        $appInstall = new ApplicationInstall();
        self::expectException(ApplicationInstallException::class);
        $this->app->getRequestDto($appInstall, CurlManager::METHOD_GET);
    }

    /**
     * @covers \Pipes\PhpSdk\Application\SendGridApplication::getSettingsForm
     *
     * @throws Exception
     */
    public function testGetSettingsForm(): void
    {
        $form = $this->app->getSettingsForm();
        self::assertCount(1, $form->getFields());
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

        $this->app = new SendGridApplication();
    }

}

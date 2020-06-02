<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Unit\Application;

use Exception;
use Hanaboso\CommonsBundle\Enum\ApplicationTypeEnum;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\ResponseDto;
use Hanaboso\HbPFAppStore\Model\Webhook\WebhookSubscription;
use Hanaboso\PipesPhpSdk\Application\Base\ApplicationAbstract;
use Hanaboso\PipesPhpSdk\Application\Base\ApplicationInterface;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\PipesPhpSdk\Authorization\Provider\OAuth2Provider;
use Hanaboso\Utils\String\Json;
use Pipes\PhpSdk\Application\HubSpotApplication;
use Pipes\PhpSdk\Tests\KernelTestCaseAbstract;

/**
 * Class HubSpotApplicationTest
 *
 * @package Pipes\PhpSdk\Tests\Unit\Application
 */
final class HubSpotApplicationTest extends KernelTestCaseAbstract
{

    /**
     * @var HubSpotApplication
     */
    private HubSpotApplication $app;

    /**
     * @covers \Pipes\PhpSdk\Application\HubSpotApplication::getApplicationType
     *
     * @throws Exception
     */
    public function testGetApplicationType(): void
    {
        self::assertEquals(ApplicationTypeEnum::WEBHOOK, $this->app->getApplicationType());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\HubSpotApplication::getKey
     *
     * @throws Exception
     */
    public function testGetKey(): void
    {
        self::assertEquals('hub-spot', $this->app->getKey());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\HubSpotApplication::getName
     *
     * @throws Exception
     */
    public function testGetName(): void
    {
        self::assertEquals('HubSpot Application', $this->app->getName());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\HubSpotApplication::getDescription
     *
     * @throws Exception
     */
    public function testGetDescription(): void
    {
        self::assertEquals(
            'HubSpot offers a full stack of software for marketing, sales, and customer service, with a completely free CRM at its core. They’re powerful alone — but even better when used together.',
            $this->app->getDescription()
        );
    }

    /**
     * @covers \Pipes\PhpSdk\Application\HubSpotApplication::getAuthUrl
     *
     * @throws Exception
     */
    public function testGetAuthUrl(): void
    {
        self::assertEquals(HubSpotApplication::HUBSPOT_URL, $this->app->getAuthUrl());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\HubSpotApplication::getTokenUrl
     *
     * @throws Exception
     */
    public function testGetTokenUrl(): void
    {
        self::assertEquals(HubSpotApplication::TOKEN_URL, $this->app->getTokenUrl());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\HubSpotApplication::getRequestDto
     *
     * @throws Exception
     */
    public function testGetRequestDto(): void
    {
        $dto = $this->app->getRequestDto(
            $this->createApplicationInstall(),
            CurlManager::METHOD_POST,
            NULL,
            Json::encode(['foo' => 'bar'])
        );
        self::assertEquals(CurlManager::METHOD_POST, $dto->getMethod());
        self::assertEquals(HubSpotApplication::BASE_URL, $dto->getUri(TRUE));
        self::assertEquals(Json::encode(['foo' => 'bar']), $dto->getBody());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\HubSpotApplication::getSettingsForm
     *
     * @throws Exception
     */
    public function testGetSettingsForm(): void
    {
        $form = $this->app->getSettingsForm();
        self::assertCount(5, $form->getFields());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\HubSpotApplication::getWebhookSubscriptions
     *
     * @throws Exception
     */
    public function testGetWebhookSubscriptions(): void
    {
        self::assertCount(2, $this->app->getWebhookSubscriptions());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\HubSpotApplication::getWebhookSubscribeRequestDto
     *
     * @throws Exception
     */
    public function testGetWebhookSubscribeRequestDto(): void
    {
        $subs = new WebhookSubscription('wh', 'sp', '', ['name' => 'contact.creation']);
        $dto  = $this->app->getWebhookSubscribeRequestDto($this->createApplicationInstall(), $subs, '');
        self::assertEquals('https://api.hubapi.com/webhooks/v1/app_id/subscriptions', $dto->getUriString());
        self::assertEquals(CurlManager::METHOD_POST, $dto->getMethod());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\HubSpotApplication::getWebhookUnsubscribeRequestDto
     *
     * @throws Exception
     */
    public function testGetWebhookUnsubscribeRequestDto(): void
    {
        $dto = $this->app->getWebhookUnsubscribeRequestDto($this->createApplicationInstall(), 'wh_id');
        self::assertEquals('https://api.hubapi.com/webhooks/v1/app_id/subscriptions/wh_id', $dto->getUriString());
        self::assertEquals(CurlManager::METHOD_DELETE, $dto->getMethod());
    }

    /**
     * @covers \Pipes\PhpSdk\Application\HubSpotApplication::processWebhookSubscribeResponse
     *
     * @throws Exception
     */
    public function testProcessWebhookSubscribeResponse(): void
    {
        $dto = new ResponseDto(200, '', Json::encode(['id' => 'wh_id']), []);
        $res = $this->app->processWebhookSubscribeResponse($dto, new ApplicationInstall());

        self::assertEquals('wh_id', $res);
    }

    /**
     * @covers \Pipes\PhpSdk\Application\HubSpotApplication::processWebhookUnsubscribeResponse
     *
     * @throws Exception
     */
    public function testProcessWebhookUnsubscribeResponse(): void
    {
        $dto = new ResponseDto(204, '', '', []);
        self::assertTrue($this->app->processWebhookUnsubscribeResponse($dto));

        $dto = new ResponseDto(400, '', '', []);
        self::assertFalse($this->app->processWebhookUnsubscribeResponse($dto));
    }

    /**
     * @covers \Pipes\PhpSdk\Application\HubSpotApplication::authorize
     * @covers \Pipes\PhpSdk\Application\HubSpotApplication::getScopes
     *
     * @throws Exception
     */
    public function testAuthorize(): void
    {
        $this->app->authorize($this->createApplicationInstall());

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
        $provider->method('authorize')->willReturnCallback(
            static function (): void {
            }
        );
        $this->app = new HubSpotApplication($provider);
    }

    /**
     * @return ApplicationInstall
     */
    private function createApplicationInstall(): ApplicationInstall
    {
        $appInstall = new ApplicationInstall();
        $appInstall
            ->setUser('usr')
            ->setKey($this->app->getKey())
            ->setSettings(
                [
                    ApplicationInterface::AUTHORIZATION_SETTINGS => [ApplicationInterface::TOKEN => [OAuth2Provider::ACCESS_TOKEN => 'tkn']],
                    ApplicationAbstract::FORM                    => [HubSpotApplication::APP_ID => 'app_id'],
                ]
            );

        return $appInstall;
    }

}

<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Application;

use GuzzleHttp\Psr7\Uri;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\CommonsBundle\Process\ProcessDtoAbstract;
use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\RequestDto;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\ResponseDto;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\PipesPhpSdk\Application\Exception\ApplicationInstallException;
use Hanaboso\PipesPhpSdk\Application\Manager\Webhook\WebhookApplicationInterface;
use Hanaboso\PipesPhpSdk\Application\Manager\Webhook\WebhookSubscription;
use Hanaboso\PipesPhpSdk\Application\Model\Form\Field;
use Hanaboso\PipesPhpSdk\Application\Model\Form\Form;
use Hanaboso\PipesPhpSdk\Application\Model\Form\FormStack;
use Hanaboso\PipesPhpSdk\Authorization\Base\OAuth2\OAuth2ApplicationAbstract;
use Hanaboso\PipesPhpSdk\Authorization\Exception\AuthorizationException;
use Hanaboso\Utils\String\Json;

/**
 * Class HubSpotApplication
 *
 * @package Pipes\PhpSdk\Application
 */
final class HubSpotApplication extends OAuth2ApplicationAbstract implements WebhookApplicationInterface
{

    public const BASE_URL = 'https://api.hubapi.com';
    public const NAME     = 'hub-spot';

    private const APPLICATION_ID = 'applicationId';

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return string
     */
    public function getPublicName(): string
    {
        return 'HubSpot';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'HubSpot application with OAuth 2';
    }

    /**
     * @param ProcessDtoAbstract $dto
     * @param ApplicationInstall $applicationInstall
     * @param string             $method
     * @param string|null        $url
     * @param string|null        $data
     *
     * @return RequestDto
     * @throws AuthorizationException
     * @throws CurlException
     * @throws ApplicationInstallException
     */
    public function getRequestDto(
        ProcessDtoAbstract $dto,
        ApplicationInstall $applicationInstall,
        string $method,
        ?string $url = NULL,
        ?string $data = NULL,
    ): RequestDto
    {
        if (!$this->isAuthorized($applicationInstall)) {
            throw new AuthorizationException('Unauthorized');
        }

        return new RequestDto(
            new Uri($url ?? self::BASE_URL),
            $method,
            $dto,
            $data ?? '',
            [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => sprintf('Bearer %s', $this->getAccessToken($applicationInstall)),
            ],
        );
    }

    /**
     * @return FormStack
     */
    public function getFormStack(): FormStack
    {
        $stack    = new FormStack();
        $authForm = new Form(self::AUTHORIZATION_FORM, 'Authorization settings');
        $authForm
            ->addField(new Field(Field::TEXT, self::CLIENT_ID, 'Client Id', NULL, TRUE))
            ->addField(new Field(Field::PASSWORD, self::CLIENT_SECRET, 'Client Secret', NULL, TRUE))
            ->addField(new Field(Field::TEXT, self::APPLICATION_ID, 'Application Id', NULL, TRUE));

        return $stack;
    }

    /**
     * @return string
     */
    public function getAuthUrl(): string
    {
        return 'https://app.hubspot.com/oauth/authorize';
    }

    /**
     * @return string
     */
    public function getTokenUrl(): string
    {
        return 'https://api.hubapi.com/oauth/v1/token';
    }

    /**
     * @return WebhookSubscription[]
     */
    public function getWebhookSubscriptions(): array
    {
        return [
            new WebhookSubscription('Create Contact', 'Webhook', '', ['name' => 'contact.creation']),
            new WebhookSubscription('Delete Contact', 'Webhook', '', ['name' => 'contact.deletion']),
        ];
    }

    /**
     * @param ApplicationInstall  $applicationInstall
     * @param WebhookSubscription $subscription
     * @param string              $url
     *
     * @return RequestDto
     * @throws ApplicationInstallException
     * @throws AuthorizationException
     * @throws CurlException
     */
    public function getWebhookSubscribeRequestDto(
        ApplicationInstall $applicationInstall,
        WebhookSubscription $subscription,
        string $url,
    ): RequestDto
    {
        $hubspotUrl = sprintf(
            '%s/webhooks/v1/%s',
            self::BASE_URL,
            $applicationInstall->getSettings()[self::AUTHORIZATION_FORM][self::APPLICATION_ID] ?? '',
        );

        return $this->getRequestDto(
            new ProcessDto(),
            $applicationInstall,
            CurlManager::METHOD_POST,
            $hubspotUrl,
            Json::encode([
                'webhookUrl'          => $url,
                'subscriptionDetails' => [
                    'subscriptionType' => $subscription->getParameters()['name'],
                    'propertyName'     => 'email',
                ],
                'enabled'             => FALSE,
            ]),
        );
    }

    /**
     * @param ApplicationInstall $applicationInstall
     * @param string             $id
     *
     * @return RequestDto
     */
    public function getWebhookUnsubscribeRequestDto(ApplicationInstall $applicationInstall, string $id): RequestDto
    {
        $url = sprintf(
            '%s/webhooks/v1/%s/subscriptions/%s',
            self::BASE_URL,
            $applicationInstall->getSettings()[self::AUTHORIZATION_FORM][self::APPLICATION_ID] ?? '',
            $id,
        );

        return $this->getRequestDto(
            new ProcessDto(),
            $applicationInstall,
            CurlManager::METHOD_DELETE,
            $url,
        );
    }

    /**
     * @param ResponseDto        $dto
     * @param ApplicationInstall $install
     *
     * @return string
     */
    public function processWebhookSubscribeResponse(ResponseDto $dto, ApplicationInstall $install): string
    {
        $install;

        return $dto->getJsonBody()['id'];
    }

    /**
     * @param ResponseDto $dto
     *
     * @return bool
     */
    public function processWebhookUnsubscribeResponse(ResponseDto $dto): bool
    {
        return $dto->getStatusCode() === 204;
    }

    /**
     * @param ApplicationInstall $applicationInstall
     *
     * @return mixed[]|mixed[]
     */
    protected function getScopes(ApplicationInstall $applicationInstall): array
    {
        $applicationInstall;

        return ['contacts'];
    }

}

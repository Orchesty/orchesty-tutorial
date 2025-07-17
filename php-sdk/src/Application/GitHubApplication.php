<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Application;

use Exception;
use GuzzleHttp\Psr7\Uri;
use Hanaboso\CommonsBundle\Enum\ApplicationTypeEnum;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\CommonsBundle\Process\ProcessDtoAbstract;
use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\RequestDto;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\ResponseDto;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\PipesPhpSdk\Application\Document\Webhook;
use Hanaboso\PipesPhpSdk\Application\Manager\Webhook\WebhookApplicationInterface;
use Hanaboso\PipesPhpSdk\Application\Manager\Webhook\WebhookSubscription;
use Hanaboso\PipesPhpSdk\Application\Model\Form\Field;
use Hanaboso\PipesPhpSdk\Application\Model\Form\Form;
use Hanaboso\PipesPhpSdk\Application\Model\Form\FormStack;
use Hanaboso\PipesPhpSdk\Authorization\Base\Basic\BasicApplicationAbstract;
use Hanaboso\Utils\String\Json;

/**
 * Class GitHubApplication
 *
 * @package Pipes\PhpSdk\Application
 */
final class GitHubApplication extends BasicApplicationAbstract implements WebhookApplicationInterface
{

    public const string NAME = 'git-hub';

    /**
     * @return string
     */
    public function getApplicationType(): string
    {
        return ApplicationTypeEnum::WEBHOOK->value;
    }

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
        return 'Git hub';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Git Hub application';
    }

    /**
     * @param ProcessDtoAbstract $dto
     * @param ApplicationInstall $applicationInstall
     * @param string             $method
     * @param string|null        $url
     * @param string|null        $data
     *
     * @return RequestDto
     * @throws CurlException
     */
    public function getRequestDto(
        ProcessDtoAbstract $dto,
        ApplicationInstall $applicationInstall,
        string $method,
        ?string $url = NULL,
        ?string $data = NULL,
    ): RequestDto
    {
        $form = $applicationInstall->getSettings()[self::AUTHORIZATION_FORM] ?? [];

        return new RequestDto(
            new Uri(sprintf('https://api.github.com%s', $url)),
            $method,
            $dto,
            $data ?? '',
            [
                'Accept'        => 'application/vnd.github+json',
                'Authorization' => sprintf('Bearer %s', $form[self::TOKEN]),
                'Content-Type'  => 'application/json',
            ],
        );
    }

    /**
     * @return FormStack
     */
    public function getFormStack(): FormStack
    {
        $authForm = new Form(self::AUTHORIZATION_FORM, 'Authorization settings');
        $authForm
            ->addField(new Field(Field::TEXT, self::TOKEN, 'Token', NULL, TRUE));

        $stack = new FormStack();
        $stack->addForm($authForm);

        return $stack;
    }

    /**
     * @return WebhookSubscription[]
     */
    public function getWebhookSubscriptions(): array
    {
        return [
            new WebhookSubscription('issues', 'Webhook', '', ['record' => 'record', 'owner' => 'owner']),
            new WebhookSubscription('pull-request', 'Webhook', '', ['record' => 'record', 'owner' => 'owner']),
        ];
    }

    /**
     * @param ApplicationInstall  $applicationInstall
     * @param WebhookSubscription $subscription
     * @param string              $url
     *
     * @return RequestDto
     * @throws CurlException
     */
    public function getWebhookSubscribeRequestDto(
        ApplicationInstall $applicationInstall,
        WebhookSubscription $subscription,
        string $url,
    ): RequestDto
    {
        $request    = new ProcessDto();
        $parameters = $subscription->getParameters();

        return $this->getRequestDto(
            $request,
            $applicationInstall,
            CurlManager::METHOD_POST,
            sprintf('/repos/%s/%s/hooks', $parameters['owner'] ?? '', $parameters['record'] ?? ''),
            Json::encode(
                [
                    'config' => [
                        'content_type' => 'json',
                        'url'          => $url,
                    ],
                    'events' => [$subscription->getName()],
                    'name'   => 'web',
                ],
            ),
        );
    }

    /**
     * @param ApplicationInstall $applicationInstall
     * @param Webhook            $webhook
     *
     * @return RequestDto
     * @throws CurlException
     */
    public function getWebhookUnsubscribeRequestDto(
        ApplicationInstall $applicationInstall,
        Webhook $webhook,
    ): RequestDto
    {
        $request    = new ProcessDto();
        $parameters = array_filter(
            $this->getWebhookSubscriptions(),
            static fn($item) => $item->getName() === $webhook->getName(),
        )[0]->getParameters();

        return $this->getRequestDto(
            $request,
            $applicationInstall,
            CurlManager::METHOD_DELETE,
            sprintf(
                '/repos/%s/%s/hooks/%s',
                $parameters['owner'] ?? '',
                $parameters['record'] ?? '',
                $webhook->getId(),
            ),
        );
    }

    /**
     * @param ResponseDto        $dto
     * @param ApplicationInstall $install
     *
     * @return string
     * @throws Exception
     */
    public function processWebhookSubscribeResponse(ResponseDto $dto, ApplicationInstall $install): string
    {
        $install;

        if ($dto->getStatusCode() !== 201) {
            throw new Exception($dto->getJsonBody()['message']);
        }

        return $dto->getJsonBody()['id'] ?? '';
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

}

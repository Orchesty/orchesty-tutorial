<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests;

use Exception;
use Hanaboso\CommonsBundle\Process\BatchProcessDto;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\ResponseDto;
use Hanaboso\PipesPhpSdk\Application\Base\ApplicationInterface;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\PipesPhpSdk\Application\Manager\Webhook\WebhookSubscription;
use Hanaboso\PipesPhpSdk\Authorization\Base\Basic\BasicApplicationInterface;
use Hanaboso\PipesPhpSdk\Authorization\Base\OAuth2\OAuth2ApplicationInterface;
use Hanaboso\PipesPhpSdk\Authorization\Provider\OAuth2Provider;
use Hanaboso\Utils\System\PipesHeaders;

/**
 * Class DataProvider
 *
 * @package Pipes\PhpSdk\Tests
 */
final class DataProvider
{

    /**
     * @param string $key
     * @param string $user
     * @param string $accessToken
     * @param string $clientId
     * @param string $clientSecret
     *
     * @return ApplicationInstall
     * @throws Exception
     */
    public static function getOauth2AppInstall(
        string $key,
        string $user = 'user',
        string $accessToken = 'token123',
        string $clientId = 'clientId',
        string $clientSecret = 'clientSecret',
    ): ApplicationInstall
    {
        $settings                                                                                                      = [];
        $settings[ApplicationInterface::AUTHORIZATION_FORM][ApplicationInterface::TOKEN][OAuth2Provider::ACCESS_TOKEN] = $accessToken;
        $settings[ApplicationInterface::AUTHORIZATION_FORM][OAuth2ApplicationInterface::CLIENT_ID]                     = $clientId;
        $settings[ApplicationInterface::AUTHORIZATION_FORM][OAuth2ApplicationInterface::CLIENT_SECRET]                 = $clientSecret;

        $applicationInstall = new ApplicationInstall();

        return $applicationInstall
            ->setSettings($settings)
            ->setUser($user)
            ->setKey($key);
    }

    /**
     * @param string $key
     * @param string $user
     * @param string $password
     *
     * @return ApplicationInstall
     * @throws Exception
     */
    public static function getBasicAppInstall(
        string $key,
        string $user = 'user',
        string $password = 'pass123',
    ): ApplicationInstall
    {
        $settings                                                                                = [];
        $settings[ApplicationInterface::AUTHORIZATION_FORM][BasicApplicationInterface::USER]     = $user;
        $settings[ApplicationInterface::AUTHORIZATION_FORM][BasicApplicationInterface::PASSWORD] = $password;

        $applicationInstall = new ApplicationInstall();

        return $applicationInstall
            ->setSettings($settings)
            ->setUser($user)
            ->setKey($key);
    }

    /**
     * @param string $key
     * @param string $user
     * @param string $body
     *
     * @return ProcessDto
     */
    public static function getProcessDto(string $key = '', string $user = 'user', string $body = '{}'): ProcessDto
    {
        $dto = new ProcessDto();
        $dto
            ->setData($body)
            ->setUser($user)
            ->setHeaders(
                [
                    PipesHeaders::USER        => $user,
                    PipesHeaders::APPLICATION => $key,
                ],
            );

        return $dto;
    }

    /**
     * @param string $key
     * @param string $user
     * @param string $body
     *
     * @return BatchProcessDto
     */
    public static function getBatchProcessDto(
        string $key = '',
        string $user = 'user',
        string $body = '',
    ): BatchProcessDto
    {
        $dto = new BatchProcessDto();
        $dto
            ->setBridgeData($body)
            ->setUser($user)
            ->setHeaders(
                [
                    PipesHeaders::USER        => $user,
                    PipesHeaders::APPLICATION => $key,
                ],
            );

        return $dto;
    }

    /**
     * @param string  $key
     * @param string  $user
     * @param mixed[] $settings
     * @param mixed[] $nonEncryptedSettings
     *
     * @return ApplicationInstall
     * @throws Exception
     */
    public static function createApplicationInstall(
        string $key,
        string $user = 'user',
        array $settings = [],
        array $nonEncryptedSettings = [],
    ): ApplicationInstall
    {
        return (new ApplicationInstall())
            ->setKey($key)
            ->setUser($user)
            ->setSettings($settings)
            ->setNonEncryptedSettings($nonEncryptedSettings);
    }

    /**
     * @param string $body
     * @param int    $code
     *
     * @return ResponseDto
     */
    public static function createResponseDto(string $body = '{}', int $code = 200): ResponseDto
    {
        return new ResponseDto($code, '', $body, []);
    }

    /**
     * @param string  $name
     * @param mixed[] $data
     *
     * @return WebhookSubscription
     */
    public static function createWebhookSubscription(string $name, array $data = []): WebhookSubscription
    {
        return new WebhookSubscription($name, 'sp', '', $data);
    }

}

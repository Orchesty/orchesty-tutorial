<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Application;

use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\RequestDto;
use Hanaboso\PipesPhpSdk\Application\Base\ApplicationInterface;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\PipesPhpSdk\Application\Model\Form\Form;
use Hanaboso\PipesPhpSdk\Authorization\Base\OAuth1\OAuth1ApplicationAbstract;
use Hanaboso\PipesPhpSdk\Authorization\Provider\OAuth1Provider;

/**
 * Class SampleOAuth1Application
 *
 * @package Pipes\PhpSdk\Application
 */
final class SampleOAuth1Application extends OAuth1ApplicationAbstract
{

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'sample-oauth1';
    }

    /**
     * @return string
     */
    public function getPublicName(): string
    {
        return 'SampleOAuth1 Application';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Description.';
    }

    /**
     * @param ApplicationInstall $applicationInstall
     * @param string             $method
     * @param string|null        $url
     * @param string|null        $data
     *
     * @return RequestDto
     * @throws CurlException
     */
    public function getRequestDto(
        ApplicationInstall $applicationInstall,
        string $method,
        ?string $url = NULL,
        ?string $data = NULL,
    ): RequestDto
    {
        $token = $applicationInstall->getSettings()
                 [ApplicationInterface::AUTHORIZATION_SETTINGS][ApplicationInterface::TOKEN][OAuth1Provider::OAUTH_TOKEN] ?? '';

        $request = new RequestDto($method, $this->getUri($url));
        $request->setHeaders(
            [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => sprintf('Bearer %s', $token),
            ],
        );

        if (isset($data)) {
            $request->setBody($data);
        }

        return $request;
    }

    /**
     * @return Form
     */
    public function getSettingsForm(): Form
    {
        return new Form();
    }

    /**
     * @return string
     */
    protected function getAuthorizeUrl(): string
    {
        return 'https://app.com/oauth/authorize';
    }

    /**
     * @return string
     */
    protected function getTokenUrl(): string
    {
        return 'https://app.com/oauth/token';
    }

    /**
     * @return string
     */
    protected function getAccessTokenUrl(): string
    {
        return 'https://app.com/oauth/accessToken';
    }

}

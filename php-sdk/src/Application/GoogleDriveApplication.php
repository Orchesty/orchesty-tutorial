<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Application;

use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\RequestDto;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\PipesPhpSdk\Application\Exception\ApplicationInstallException;
use Hanaboso\PipesPhpSdk\Application\Model\Form\Field;
use Hanaboso\PipesPhpSdk\Application\Model\Form\Form;
use Hanaboso\PipesPhpSdk\Authorization\Base\OAuth2\OAuth2ApplicationAbstract;
use Hanaboso\PipesPhpSdk\Authorization\Base\OAuth2\OAuth2ApplicationInterface;

/**
 * Class GoogleDriveApplication
 *
 * @package Pipes\PhpSdk\Application
 */
final class GoogleDriveApplication extends OAuth2ApplicationAbstract
{

    public const    BASE_URL  = 'https://www.googleapis.com';
    public const    AUTH_URL  = 'https://accounts.google.com/o/oauth2/auth';
    public const    TOKEN_URL = 'https://oauth2.googleapis.com/token';

    /**
     * @return string
     */
    public function getKey(): string
    {
        return 'google-drive';
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'GoogleDrive Application';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'GoogleDrive Application';
    }

    /**
     * @return string
     */
    public function getAuthUrl(): string
    {
        return self::AUTH_URL;
    }

    /**
     * @return string
     */
    public function getTokenUrl(): string
    {
        return self::TOKEN_URL;
    }

    /**
     * @param ApplicationInstall $applicationInstall
     * @param string             $method
     * @param string|null        $url
     * @param string|null        $data
     *
     * @return RequestDto
     * @throws CurlException
     * @throws ApplicationInstallException
     */
    public function getRequestDto(
        ApplicationInstall $applicationInstall,
        string $method,
        ?string $url = NULL,
        ?string $data = NULL,
    ): RequestDto
    {
        $request = new RequestDto($method, $this->getUri($url ?? self::BASE_URL));
        $request->setHeaders(
            [
                'Accept'        => 'application/json',
                'Authorization' => sprintf('Bearer %s', $this->getAccessToken($applicationInstall)),
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
        $form = new Form();
        $form
            ->addField(new Field(Field::TEXT, OAuth2ApplicationInterface::CLIENT_ID, 'Client Id', NULL, TRUE))
            ->addField(new Field(Field::TEXT, OAuth2ApplicationInterface::CLIENT_SECRET, 'Client Secret', TRUE));

        return $form;
    }

    /**
     * @param ApplicationInstall $applicationInstall
     *
     * @return mixed[]|string[]
     */
    protected function getScopes(ApplicationInstall $applicationInstall): array
    {
        $applicationInstall;

        return ['https://www.googleapis.com/auth/drive.file'];
    }

}

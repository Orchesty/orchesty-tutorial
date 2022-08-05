<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Application;

use Hanaboso\CommonsBundle\Process\ProcessDtoAbstract;
use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\RequestDto;
use Hanaboso\PipesPhpSdk\Application\Base\ApplicationInterface;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\PipesPhpSdk\Application\Exception\ApplicationInstallException;
use Hanaboso\PipesPhpSdk\Application\Model\Form\Field;
use Hanaboso\PipesPhpSdk\Application\Model\Form\Form;
use Hanaboso\PipesPhpSdk\Application\Model\Form\FormStack;
use Hanaboso\PipesPhpSdk\Authorization\Base\Basic\BasicApplicationAbstract;

/**
 * Class SendGridApplication
 *
 * @package Pipes\PhpSdk\Application
 */
final class SendGridApplication extends BasicApplicationAbstract
{

    public const BASE_URL = 'https://api.sendgrid.com/v3';
    public const API_KEY  = 'api_key';

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'send-grid';
    }

    /**
     * @return string
     */
    public function getPublicName(): string
    {
        return 'SendGrid Application';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Send Email With Confidence.';
    }

    /**
     * @param ApplicationInstall $applicationInstall
     *
     * @return bool
     */
    public function isAuthorized(ApplicationInstall $applicationInstall): bool
    {
        return isset($applicationInstall->getSettings()[ApplicationInterface::AUTHORIZATION_FORM][self::API_KEY]);
    }

    /**
     * @param ProcessDtoAbstract $dto
     * @param ApplicationInstall $applicationInstall
     * @param string             $method
     * @param string|null        $url
     * @param string|null        $data
     *
     * @return RequestDto
     * @throws ApplicationInstallException
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
        if (!$this->isAuthorized($applicationInstall)) {
            throw new ApplicationInstallException('Application SendGrid is not authorized!');
        }

        $settings = $applicationInstall->getSettings();
        $token    = $settings[ApplicationInterface::AUTHORIZATION_FORM][self::API_KEY];
        $request  = new RequestDto(
            $this->getUri($url ?? self::BASE_URL),
            $method,
            $dto,
            '',
            [
                'Content-Type' => 'application/json', 'Authorization' => sprintf('Bearer %s', $token),
            ],
        );

        if ($data) {
            $request->setBody($data);
        }

        return $request;
    }

    /**
     * @return FormStack
     */
    public function getFormStack(): FormStack
    {
        $form  = new Form(ApplicationInterface::AUTHORIZATION_FORM, 'Authorization settings');
        $field = new Field(Field::TEXT, self::API_KEY, 'Api key', NULL, TRUE);
        $form->addField($field);

        return (new FormStack())->addForm($form);
    }

}

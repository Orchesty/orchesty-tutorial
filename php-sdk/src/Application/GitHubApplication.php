<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Application;

use GuzzleHttp\Psr7\Uri;
use Hanaboso\CommonsBundle\Process\ProcessDtoAbstract;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\RequestDto;
use Hanaboso\PipesPhpSdk\Application\Base\ApplicationInterface;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\PipesPhpSdk\Application\Model\Form\Field;
use Hanaboso\PipesPhpSdk\Application\Model\Form\Form;
use Hanaboso\PipesPhpSdk\Application\Model\Form\FormStack;
use Hanaboso\PipesPhpSdk\Authorization\Base\Basic\BasicApplicationAbstract;
use Hanaboso\PipesPhpSdk\Authorization\Base\Basic\BasicApplicationInterface;

/**
 * Class GitHubApplication
 *
 * @package Pipes\PhpSdk\Application
 */
final class GitHubApplication extends BasicApplicationAbstract
{

    public const NAME = 'git-hub';

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
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => base64_encode(
                    sprintf('%s:%s', $form[self::USER] ?? '', $form[self::TOKEN] ?? ''),
                ),
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
            ->addField(new Field(Field::TEXT, self::USER, 'Username', NULL, TRUE))
            ->addField(new Field(Field::TEXT, self::TOKEN, 'Token', NULL, TRUE));

        $stack = new FormStack();
        $stack->addForm($authForm);

        return $stack;
    }

}

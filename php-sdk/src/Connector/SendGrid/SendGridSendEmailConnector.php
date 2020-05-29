<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Connector\SendGrid;

use Doctrine\ODM\MongoDB\DocumentManager;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\PipesPhpSdk\Application\Exception\ApplicationInstallException;
use Hanaboso\PipesPhpSdk\Application\Repository\ApplicationInstallRepository;
use Hanaboso\PipesPhpSdk\Connector\ConnectorAbstract;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\PipesPhpSdk\Connector\Traits\ProcessEventNotSupportedTrait;
use Hanaboso\Utils\Exception\PipesFrameworkException;
use Hanaboso\Utils\String\Json;
use Pipes\PhpSdk\Application\SendGridApplication;

/**
 * Class SendGridSendEmailConnector
 *
 * @package Pipes\PhpSdk\Connector\SendGrid
 */
final class SendGridSendEmailConnector extends ConnectorAbstract
{

    use ProcessEventNotSupportedTrait;

    /**
     * @var CurlManager
     */
    private CurlManager $sender;

    /**
     * @var ApplicationInstallRepository
     */
    private ApplicationInstallRepository $repository;

    /**
     * SendGridSendEmailConnector constructor.
     *
     * @param DocumentManager $dm
     * @param CurlManager     $sender
     */
    public function __construct(DocumentManager $dm, CurlManager $sender)
    {
        $this->sender     = $sender;
        $this->repository = $dm->getRepository(ApplicationInstall::class);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return 'send-grid.send-email';
    }

    /**
     * @param ProcessDto $dto
     *
     * @return ProcessDto
     * @throws ConnectorException
     */
    public function processAction(ProcessDto $dto): ProcessDto
    {
        try {
            $applicationInstall = $this->repository->findUsersAppDefaultHeaders($dto);
        } catch (ApplicationInstallException $e) {
            throw new ConnectorException(
                'ApplicationInstall is not set.',
                ConnectorException::CONNECTOR_FAILED_TO_PROCESS
            );
        }

        $body = [
            'personalizations' => [
                [
                    'to'                    => [
                        [
                            'email' => 'john.doe@example.com',
                            'name'  => 'John Doe',
                        ],
                    ],
                    'dynamic_template_data' => [],
                    'subject'               => 'Hello, World!',
                ],
            ],
            'from'             => [
                'email' => 'noreply@johndoe.com',
                'name'  => 'John Doe',
            ],
            'reply_to'         => [
                'email' => 'noreply@johndoe.com',
                'name'  => 'John Doe',
            ],
            'template_id'      => '1',
        ];

        $url     = sprintf('%s/mail/send', SendGridApplication::BASE_URL);
        $request = $this->getApplication()
            ->getRequestDto($applicationInstall, CurlManager::METHOD_POST, $url, Json::encode($body))
            ->setDebugInfo($dto);

        try {
            $response = $this->sender->send($request);

            if (!$this->evaluateStatusCode($response->getStatusCode(), $dto)) {
                return $dto;
            }
        } catch (CurlException|PipesFrameworkException $e) {
            throw new ConnectorException($e->getMessage(), $e->getCode(), $e);
        }

        return $dto->setData($response->getBody());
    }

}

<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Connector\HubSpot;

use Doctrine\ODM\MongoDB\DocumentManager;
use Hanaboso\CommonsBundle\Exception\OnRepeatException;
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
use Pipes\PhpSdk\Application\HubSpotApplication;

/**
 * Class HubSpotCreateContactConnector
 *
 * @package Pipes\PhpSdk\Connector\HubSpot
 */
final class HubSpotCreateContactConnector extends ConnectorAbstract
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
     * HubSpotCreateContactConnector constructor.
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
        return 'hub-spot.create-contact';
    }

    /**
     * @param ProcessDto $dto
     *
     * @return ProcessDto
     * @throws ApplicationInstallException
     * @throws PipesFrameworkException
     * @throws OnRepeatException
     * @throws ConnectorException
     */
    public function processAction(ProcessDto $dto): ProcessDto
    {
        $applicationInstall = $this->repository->findUsersAppDefaultHeaders($dto);
        $data               = $this->getJsonContent($dto);
        if (!isset($data['name'], $data['email'], $data['phone'])) {
            throw new ConnectorException('Some data is missing. Keys [name, email, phone] is required.');
        }

        $name = explode(' ', $data['name']);
        $body = [
            'properties' => [
                [
                    'property' => 'email',
                    'value'    => $data['email'],
                ],
                [
                    'property' => 'firstname',
                    'value'    => $name[0],
                ],
                [
                    'property' => 'lastname',
                    'value'    => $name[1] ?? '',
                ],
                [
                    'property' => 'phone',
                    'value'    => $data['phone'],
                ],
            ],
        ];

        try {
            $response = $this->sender->send(
                $this->getApplication()->getRequestDto(
                    $applicationInstall,
                    CurlManager::METHOD_POST,
                    sprintf('%s/contacts/v1/contact', HubspotApplication::BASE_URL),
                    Json::encode($body)
                )
            );
            $message  = $response->getJsonBody()['validationResults'][0]['message'] ?? NULL;
            $this->evaluateStatusCode($response->getStatusCode(), $dto, $message);

            $dto->setData($response->getBody());
        } catch (CurlException | ConnectorException $e) {
            throw new OnRepeatException($dto, $e->getMessage(), $e->getCode(), $e);
        }

        return $dto;
    }

}

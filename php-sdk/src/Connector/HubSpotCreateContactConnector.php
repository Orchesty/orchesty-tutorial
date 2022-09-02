<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Connector;

use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\PipesPhpSdk\Application\Exception\ApplicationInstallException;
use Hanaboso\PipesPhpSdk\Connector\ConnectorAbstract;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\PipesPhpSdk\CustomNode\Exception\CustomNodeException;
use Pipes\PhpSdk\Application\HubSpotApplication;

/**
 * Class HubSpotCreateContactConnector
 *
 * @package Pipes\PhpSdk\Connector
 */
final class HubSpotCreateContactConnector extends ConnectorAbstract
{

    public const NAME = 'hub-spot-create-contact';

    /**
     * @return string
     */
    function getName(): string
    {
        return self::NAME;
    }

    /**
     * @param ProcessDto $dto
     *
     * @return ProcessDto
     * @throws CurlException
     * @throws ApplicationInstallException
     * @throws ConnectorException
     * @throws CustomNodeException
     */
    function processAction(ProcessDto $dto): ProcessDto
    {
        $appInstall = $this->getApplicationInstallFromProcess($dto);
        $request    = $this->getApplication()->getRequestDto(
            $dto,
            $appInstall,
            CurlManager::METHOD_POST,
            sprintf('%s/crm/v3/objects/contacts', HubSpotApplication::BASE_URL),
            $dto->getData(),
        );

        $response = $this->getSender()->send($request);
        $dto->setData($response->getBody());

        return $dto;
    }

}

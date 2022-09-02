<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Connector;

use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\CommonsBundle\Process\ProcessDtoAbstract;
use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\PipesPhpSdk\Application\Exception\ApplicationInstallException;
use Hanaboso\PipesPhpSdk\Connector\ConnectorAbstract;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\PipesPhpSdk\CustomNode\Exception\CustomNodeException;
use Hanaboso\Utils\Exception\PipesFrameworkException;

/**
 * Class GitHubRepositoryConnector
 *
 * @package Pipes\PhpSdk\Connector
 */
final class GitHubRepositoryConnector extends ConnectorAbstract
{

    public const NAME = 'git-hub-get-repository';

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
     * @throws ApplicationInstallException
     * @throws CustomNodeException
     * @throws PipesFrameworkException
     * @throws CurlException
     * @throws ConnectorException
     */
    function processAction(ProcessDto $dto): ProcessDto
    {
        $data       = $dto->getJsonData();
        $appInstall = $this->getApplicationInstallFromProcess($dto);

        if (!isset($data['user']) || !isset($data['repo'])) {
            return $dto->setStopProcess(ProcessDtoAbstract::STOP_AND_FAILED, 'Missing required data [user, repo]');
        }

        $request  = $this->getApplication()->getRequestDto(
            $dto,
            $appInstall,
            CurlManager::METHOD_GET,
            sprintf('/repos/%s/%s', $data['user'], $data['repo']),
        );
        $response = $this->getSender()->send($request);
        $dto->setData($response->getBody());

        return $dto;
    }

}

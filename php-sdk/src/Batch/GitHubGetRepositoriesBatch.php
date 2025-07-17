<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Batch;

use GuzzleHttp\Exception\GuzzleException;
use Hanaboso\CommonsBundle\Process\BatchProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\PipesPhpSdk\Application\Exception\ApplicationInstallException;
use Hanaboso\PipesPhpSdk\Batch\BatchAbstract;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\PipesPhpSdk\CustomNode\Exception\CustomNodeException;

/**
 * Class GitHubGetRepositoriesBatch
 *
 * @package Pipes\PhpSdk\Batch
 */
final class GitHubGetRepositoriesBatch extends BatchAbstract
{

    public const string NAME = 'git-hub-repositories-batch';

    private const int PAGE_ITEMS = 5;

    /**
     * @return string
     */
    function getName(): string
    {
        return self::NAME;
    }

    /**
     * @param BatchProcessDto $dto
     *
     * @return BatchProcessDto
     * @throws CurlException
     * @throws ApplicationInstallException
     * @throws ConnectorException
     * @throws CustomNodeException
     * @throws GuzzleException
     */
    function processAction(BatchProcessDto $dto): BatchProcessDto
    {
        $currentPage = intval($dto->getBatchCursor('1'));
        $org         = $dto->getJsonData()['org'] ?? '';
        $appInstall  = $this->getApplicationInstallFromProcess($dto);

        $request = $this->getApplication()->getRequestDto(
            $dto,
            $appInstall,
            CurlManager::METHOD_GET,
            sprintf('/orgs/%s/repos?per_page=%s&page=%s', $org, self::PAGE_ITEMS, $currentPage),
        );
        $result  = $this->getSender()->send($request)->getJsonBody();
        $dto->setItemList($result);
        if (count($result) >= self::PAGE_ITEMS) {
            $dto->setBatchCursor((string) ($currentPage + 1));
        }

        return $dto;
    }

}

<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Batch;

use Doctrine\ODM\MongoDB\MongoDBException;
use Hanaboso\CommonsBundle\Process\BatchProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\PipesPhpSdk\Application\Exception\ApplicationInstallException;
use Hanaboso\PipesPhpSdk\Batch\BatchAbstract;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\PipesPhpSdk\CustomNode\Exception\CustomNodeException;
use Hanaboso\PipesPhpSdk\Storage\DataStorage\DataStorageManager;
use Hanaboso\Utils\String\Json;
use Hanaboso\Utils\System\PipesHeaders;

/**
 * Class GitHubStoreRepositoriesBatch
 *
 * @package Pipes\PhpSdk\Batch
 */
final class GitHubStoreRepositoriesBatch extends BatchAbstract
{

    public const NAME = 'git-hub-store-repositories-batch';

    private const PAGE_ITEMS = 5;

    /**
     * GitHubStoreRepositoriesBatch constructor.
     *
     * @param DataStorageManager $dataStorageManager
     */
    public function __construct(private readonly DataStorageManager $dataStorageManager)
    {
    }

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
     * @throws ApplicationInstallException
     * @throws ConnectorException
     * @throws CurlException
     * @throws CustomNodeException
     * @throws MongoDBException
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

        $processId = $dto->getHeader(PipesHeaders::PROCESS_ID);
        $this->dataStorageManager->store($processId, [Json::encode($result[0])]);

        if (count($result) >= self::PAGE_ITEMS) {
            $dto->setBatchCursor((string) ($currentPage + 1));
        } else {
            $dto->addItem($processId);
        }

        return $dto;
    }

}

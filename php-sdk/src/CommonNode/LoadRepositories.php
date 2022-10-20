<?php declare(strict_types=1);

namespace Pipes\PhpSdk\CommonNode;

use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\PipesPhpSdk\CustomNode\CommonNodeAbstract;
use Hanaboso\PipesPhpSdk\Storage\DataStorage\DataStorageManager;

/**
 * Class LoadRepositories
 *
 * @package Pipes\PhpSdk\CommonNode
 */
final class LoadRepositories extends CommonNodeAbstract
{

    public const NAME = 'load-repositories';

    /**
     * LoadRepositories constructor.
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
     * @param ProcessDto $dto
     *
     * @return ProcessDto
     */
    function processAction(ProcessDto $dto): ProcessDto
    {
        $data  = $dto->getJsonData();
        $repos = $this->dataStorageManager->load(id: $data['collection'], toArray: TRUE);

        return $dto->setJsonData($repos ?? []);
    }

}

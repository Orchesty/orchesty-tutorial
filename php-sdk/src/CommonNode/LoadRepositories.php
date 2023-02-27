<?php declare(strict_types=1);

namespace Pipes\PhpSdk\CommonNode;

use Exception;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\PipesPhpSdk\Application\Repository\ApplicationInstallRepository;
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
     * @param ApplicationInstallRepository $repository
     * @param DataStorageManager           $dataStorageManager
     */
    public function __construct(
        ApplicationInstallRepository $repository,
        private readonly DataStorageManager $dataStorageManager,
    )
    {
        parent::__construct($repository);
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
     * @throws Exception
     */
    function processAction(ProcessDto $dto): ProcessDto
    {
        $data  = $dto->getJsonData();
        $repos = $this->dataStorageManager->load(id: $data['collection']);

        $res = [];
        foreach ($repos as $repo){
            $res[] = $repo->toArray();
        }

        return $dto->setJsonData($res);
    }

}

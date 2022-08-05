<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Batch\Splitter;

use Hanaboso\CommonsBundle\Process\BatchProcessDto;
use Hanaboso\PipesPhpSdk\Batch\BatchAbstract;

/**
 * Class UsersBatchSplitter
 *
 * @package Pipes\PhpSdk\Batch\Splitter
 */
final class UsersBatchSplitter extends BatchAbstract
{

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'user-batch-splitter';
    }

    /**
     * @param BatchProcessDto $dto
     *
     * @return BatchProcessDto
     */
    public function processAction(BatchProcessDto $dto): BatchProcessDto
    {
        $dto->setItemList($dto->getJsonData());

        return $dto;
    }

}

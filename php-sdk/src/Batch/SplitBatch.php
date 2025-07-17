<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Batch;

use Hanaboso\CommonsBundle\Process\BatchProcessDto;
use Hanaboso\PipesPhpSdk\Batch\BatchAbstract;

/**
 * Class SplitBatch
 *
 * @package Pipes\PhpSdk\Batch
 */
final class SplitBatch extends BatchAbstract
{

    public const string NAME = 'split-batch';

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
     */
    function processAction(BatchProcessDto $dto): BatchProcessDto
    {
        return $dto->setItemList([['id' => 1], ['id' => 2], ['id' => 3]]);
    }

}

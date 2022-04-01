<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Batch\Splitter;

use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\PipesPhpSdk\Connector\ConnectorAbstract;

/**
 * Class UsersBatchSplitter
 *
 * @package Pipes\PhpSdk\Batch\Splitter
 */
final class UsersBatchSplitter extends ConnectorAbstract
{

    /**
     * @return string
     */
    public function getId(): string
    {
        return 'user-batch-splitter';
    }

    /**
     * @param ProcessDto $dto
     *
     * @return ProcessDto
     */
    public function processAction(ProcessDto $dto): ProcessDto
    {
        return $dto;
    }

}

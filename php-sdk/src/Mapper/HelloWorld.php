<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Mapper;

use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\PipesPhpSdk\CustomNode\CommonNodeAbstract;

/**
 * Class HelloWorld
 *
 * @package Pipes\PhpSdk\Mapper
 */
final class HelloWorld extends CommonNodeAbstract
{

    public const string NAME = 'hello-world';

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
        return $dto->setJsonData(['message' => 'Hello world']);
    }

}

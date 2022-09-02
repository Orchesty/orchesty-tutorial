<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Connector;

use GuzzleHttp\Psr7\Uri;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\RequestDto;
use Hanaboso\PipesPhpSdk\Connector\ConnectorAbstract;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;

/**
 * Class GetUsersConnector
 *
 * @package Pipes\PhpSdk\Connector
 */
final class GetUsersConnector extends ConnectorAbstract
{

    public const NAME = 'jsonplaceholder-get-users';

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
     * @throws ConnectorException
     */
    function processAction(ProcessDto $dto): ProcessDto
    {
        $request = new RequestDto(
            new Uri('https://jsonplaceholder.typicode.com/users'),
            CurlManager::METHOD_GET,
            $dto,
        );

        $response = $this->getSender()->send($request);
        $dto->setData($response->getBody());

        return $dto;
    }

}

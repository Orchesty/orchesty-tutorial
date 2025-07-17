<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Connector;

use GuzzleHttp\Psr7\Uri;
use Hanaboso\CommonsBundle\Exception\OnRepeatException;
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

    public const string NAME = 'jsonplaceholder-get-users';

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
     * @throws ConnectorException
     * @throws CurlException
     * @throws OnRepeatException
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
        if ($response->getStatusCode() >= 300) {
            throw new OnRepeatException($dto, $response->getBody(), $response->getStatusCode());
        }

        return $dto;
    }

}

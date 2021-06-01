<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Connector\Users;

use GuzzleHttp\Psr7\Uri;
use Hanaboso\CommonsBundle\Exception\OnRepeatException;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\RequestDto;
use Hanaboso\PipesPhpSdk\Connector\ConnectorAbstract;
use Hanaboso\PipesPhpSdk\Connector\Traits\ProcessEventNotSupportedTrait;
use Hanaboso\Utils\Exception\PipesFrameworkException;

/**
 * Class GetUsersConnector
 *
 * @package Pipes\PhpSdk\Connector\Users
 */
final class GetUsersConnector extends ConnectorAbstract
{

    use ProcessEventNotSupportedTrait;

    /**
     * GetUsersConnector constructor.
     *
     * @param CurlManager $sender
     */
    public function __construct(private CurlManager $sender)
    {
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return 'get-users';
    }

    /**
     * @param ProcessDto $dto
     *
     * @return ProcessDto
     * @throws OnRepeatException
     */
    public function processAction(ProcessDto $dto): ProcessDto
    {
        try {
            $request = new RequestDto(
                CurlManager::METHOD_GET,
                new Uri('https://jsonplaceholder.typicode.com/users'),
            );
            $request->setDebugInfo($dto);
            $response = $this->sender->send($request);

            // If status code from response is 500 it will throw an exception to start the Repeater
            if ($response->getStatusCode() === 500) {
                throw new CurlException('Service is a unreachable!');
            }

            // If status code from response is not 200 or 201 process will be stopped as failed
            $this->evaluateStatusCode(
                $response->getStatusCode(),
                $dto,
                sprintf('Status code is not valid %s!', $response->getStatusCode()),
            );

            $dto->setData($response->getBody());
        } catch (CurlException | PipesFrameworkException $e) {
            $repeat = new OnRepeatException($dto, $e->getMessage());
            $repeat
                ->setInterval(60_000)
                ->setMaxHops(3);

            throw $repeat;
        }

        return $dto;
    }

}

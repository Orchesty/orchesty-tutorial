<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Connector\HubSpot;

use Hanaboso\CommonsBundle\Exception\OnRepeatException;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\PipesPhpSdk\Application\Exception\ApplicationInstallException;
use Hanaboso\PipesPhpSdk\Connector\ConnectorAbstract;
use Hanaboso\Utils\Exception\PipesFrameworkException;
use Hanaboso\Utils\System\PipesHeaders;
use Pipes\PhpSdk\Application\HubSpotApplication;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class HubSpotCreateContactAbstract
 *
 * @package Pipes\PhpSdk\Connector\HubSpot
 */
abstract class HubSpotCreateContactAbstract extends ConnectorAbstract implements LoggerAwareInterface
{

    /**
     * @var string
     */
    protected string $contactUrl;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * HubSpotCreateContactAbstract constructor.
     */
    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param ProcessDto $dto
     *
     * @return ProcessDto
     * @throws ApplicationInstallException
     * @throws PipesFrameworkException
     * @throws OnRepeatException
     */
    public function processAction(ProcessDto $dto): ProcessDto
    {
        $applicationInstall = $this->getApplicationInstallFromProcess($dto);

        try {
            $response = $this->getSender()->send(
                $this->getApplication()->getRequestDto(
                    $dto,
                    $applicationInstall,
                    CurlManager::METHOD_POST,
                    sprintf('%s/%s', HubspotApplication::BASE_URL, $this->contactUrl),
                    $dto->getData(),
                ),
            );

            if ($response->getStatusCode() === 202) {
                return $dto->setData($response->getBody());
            }

            if ($response->getStatusCode() === 403){
                $this->logger->error(
                    'Token does not have proper permissions!',
                    array_merge(
                        ['response' => $response->getBody(), PipesHeaders::debugInfo($dto->getHeaders())],
                    ),
                );
            }

            $message = $response->getJsonBody()['validationResults'][0]['message'] ?? '';
            $this->evaluateStatusCode($response->getStatusCode(), $dto, $message);

            if ($response->getStatusCode() === 409) {
                $parsed = $response->getJsonBody();
                $this->logger->error(
                    sprintf('Contact "%s" already exist.', $parsed['identityProfile']['identity'][0]['value'] ?? ''),
                    array_merge(
                        ['response' => $response->getBody(), PipesHeaders::debugInfo($dto->getHeaders())],
                    ),
                );
            }

            $dto->setData($response->getBody());
        } catch (CurlException $e) {
            throw new OnRepeatException($dto, $e->getMessage(), $e->getCode(), $e);
        }

        return $dto;
    }

}

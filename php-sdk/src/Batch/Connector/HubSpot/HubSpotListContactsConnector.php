<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Batch\Connector\HubSpot;

use Exception;
use GuzzleHttp\Psr7\Uri;
use Hanaboso\CommonsBundle\Process\BatchProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\RequestDto;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\PipesPhpSdk\Application\Exception\ApplicationInstallException;
use Hanaboso\PipesPhpSdk\Batch\BatchAbstract;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\Utils\String\Json;
use Hanaboso\Utils\System\PipesHeaders;
use Pipes\PhpSdk\Application\HubSpotApplication;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class HubSpotListContactsConnector
 *
 * @package Pipes\PhpSdk\Batch\Connector\HubSpot
 */
final class HubSpotListContactsConnector extends BatchAbstract implements LoggerAwareInterface
{

    public const ITEMS_PER_PAGE = 90;
    public const URL            = '%s/contacts/v1/lists/all/contacts/all';

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * HubSpotListContactsConnector constructor.
     */
    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'hub-spot.list-contacts';
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param BatchProcessDto $dto
     *
     * @return BatchProcessDto
     * @throws ApplicationInstallException
     * @throws ConnectorException
     * @throws Exception
     */
    public function processAction(BatchProcessDto $dto): BatchProcessDto
    {
        $applicationInstall = $this->getApplicationInstallFromProcess($dto);
        $requestDto         = $this->getApplication()->getRequestDto(
            $dto,
            $applicationInstall,
            CurlManager::METHOD_GET,
            sprintf(self::URL, HubspotApplication::BASE_URL),
        );

        return $this->doPageLoop($requestDto, $applicationInstall, $dto);
    }

    /**
     * -------------------------------------------- HELPERS ------------------------------------
     */

    /**
     * @param RequestDto         $dto
     * @param ApplicationInstall $install
     * @param BatchProcessDto    $processDto
     *
     * @return BatchProcessDto
     * @throws Exception
     */
    private function doPageLoop(
        RequestDto $dto,
        ApplicationInstall $install,
        BatchProcessDto $processDto,
    ): BatchProcessDto
    {
        $offset = (int) $processDto->getBatchCursor('0');
        $uri    = $this->getUri($dto, $offset);

        $response = $this->getSender()->send(RequestDto::from($dto, $processDto, $uri));

        $body    = $response->getBody();
        $data    = empty($body) ? [] : Json::decode($body);
        $respDto = $this->createSuccessMessage($processDto, $install, $data, $processDto->getHeaders());

        if ($data['has-more'] ?? FALSE) {
            $respDto->setBatchCursor((string) ++$offset);
        }

        return $respDto;
    }

    /**
     * @param RequestDto $dto
     * @param int        $offset
     *
     * @return Uri
     */
    private function getUri(RequestDto $dto, int $offset): Uri
    {
        return new Uri(
            sprintf(
                '%s?count=%s&vidOffset=%s',
                urldecode($dto->getUriString()),
                self::ITEMS_PER_PAGE,
                $offset,
            ),
        );
    }

    /**
     * @param BatchProcessDto    $dto
     * @param ApplicationInstall $install
     * @param mixed[]            $data
     * @param mixed[]            $headers
     *
     * @return BatchProcessDto
     * @throws Exception
     */
    private function createSuccessMessage(
        BatchProcessDto $dto,
        ApplicationInstall $install,
        array $data,
        array $headers,
    ): BatchProcessDto
    {
        if (array_key_exists('contacts', $data)) {
            return $dto->setItemList($data['contacts']);
        } else {
            throw $this->batchConnectorError(
                new ConnectorException('Bad response data from HubSpot response. Missing "contacts".'),
                $install,
                ['data' => $data],
                $headers,
            );
        }
    }

    /**
     * @param Exception          $e
     * @param ApplicationInstall $install
     * @param mixed[]            $context
     * @param mixed[]            $headers
     *
     * @return Exception
     * @throws Exception
     */
    private function batchConnectorError(
        Exception $e,
        ApplicationInstall $install,
        array $context = [],
        array $headers = [],
    ): Exception
    {
        $context = array_merge(
            [
                'app_install' => $install->getId(),
                'user'        => $install->getUser(),
                'key'         => $install->getKey(),
            ],
            $context,
        );

        $this->logger->error($e->getMessage(), array_merge($context, PipesHeaders::debugInfo($headers)));

        return $e;
    }

}

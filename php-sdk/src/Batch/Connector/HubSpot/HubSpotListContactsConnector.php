<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Batch\Connector\HubSpot;

use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use GuzzleHttp\Psr7\Uri;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\RequestDto;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\PipesPhpSdk\Application\Exception\ApplicationInstallException;
use Hanaboso\PipesPhpSdk\Application\Repository\ApplicationInstallRepository;
use Hanaboso\PipesPhpSdk\Connector\ConnectorAbstract;
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
final class HubSpotListContactsConnector extends ConnectorAbstract implements LoggerAwareInterface
{

    public const ITEMS_PER_PAGE = 90;
    public const URL            = '%s/contacts/v1/lists/all/contacts/all';

    /**
     * @var ApplicationInstallRepository
     */
    private ApplicationInstallRepository $repository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * HubSpotListContactsConnector constructor.
     *
     * @param DocumentManager $dm
     * @param CurlManager     $sender
     */
    public function __construct(DocumentManager $dm, private CurlManager $sender)
    {
        $this->repository = $dm->getRepository(ApplicationInstall::class);
        $this->logger     = new NullLogger();
    }

    /**
     * @return string
     */
    public function getId(): string
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
     * @param ProcessDto $dto
     *
     * @return ProcessDto
     * @throws ApplicationInstallException
     * @throws ConnectorException
     * @throws Exception
     */
    public function processAction(ProcessDto $dto): ProcessDto
    {
        $dto->addHeader(PipesHeaders::createKey(PipesHeaders::APPLICATION), $this->getApplicationKey() ?? '');
        $applicationInstall = $this->repository->findUserAppByHeaders($dto);
        $requestDto         = $this->getApplication()->getRequestDto(
            $applicationInstall,
            CurlManager::METHOD_GET,
            sprintf(self::URL, HubspotApplication::BASE_URL),
        );
        $requestDto->setDebugInfo($dto);

        return $this->doPageLoop($requestDto, $applicationInstall, $dto);
    }

    /**
     * -------------------------------------------- HELPERS ------------------------------------
     */

    /**
     * @param RequestDto         $dto
     * @param ApplicationInstall $install
     * @param ProcessDto         $processDto
     *
     * @return ProcessDto
     * @throws Exception
     */
    private function doPageLoop(RequestDto $dto, ApplicationInstall $install, ProcessDto $processDto): ProcessDto
    {
        $offset = (int) $processDto->getBatchCursor('0');
        $uri    = $this->getUri($dto, $offset);

        $response = $this->sender->send(RequestDto::from($dto, $uri));

        $body    = $response->getBody();
        $data    = empty($body) ? [] : Json::decode($body);
        $respDto = $this->createSuccessMessage($install, $data, $processDto->getHeaders());

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
     * @param ApplicationInstall $install
     * @param mixed[]            $data
     * @param mixed[]            $headers
     *
     * @return ProcessDto
     * @throws Exception
     */
    private function createSuccessMessage(ApplicationInstall $install, array $data, array $headers = []): ProcessDto
    {
        if (array_key_exists('contacts', $data)) {
            return (new ProcessDto())->setData(Json::encode($data['contacts']));
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

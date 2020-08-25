<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Batch\Connector\HubSpot;

use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Uri;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\RequestDto;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\PipesPhpSdk\Application\Exception\ApplicationInstallException;
use Hanaboso\PipesPhpSdk\Application\Repository\ApplicationInstallRepository;
use Hanaboso\PipesPhpSdk\Connector\ConnectorAbstract;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\PipesPhpSdk\Connector\Traits\ProcessActionNotSupportedTrait;
use Hanaboso\PipesPhpSdk\Connector\Traits\ProcessEventNotSupportedTrait;
use Hanaboso\PipesPhpSdk\RabbitMq\Impl\Batch\BatchInterface;
use Hanaboso\PipesPhpSdk\RabbitMq\Impl\Batch\BatchTrait;
use Hanaboso\PipesPhpSdk\RabbitMq\Impl\Batch\SuccessMessage;
use Hanaboso\Utils\String\Json;
use Hanaboso\Utils\System\PipesHeaders;
use Pipes\PhpSdk\Application\HubSpotApplication;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class HubSpotListContactsConnector
 *
 * @package Pipes\PhpSdk\Batch\Connector\HubSpot
 */
final class HubSpotListContactsConnector extends ConnectorAbstract implements BatchInterface, LoggerAwareInterface
{

    use ProcessActionNotSupportedTrait;
    use ProcessEventNotSupportedTrait;
    use BatchTrait;

    public const ITEMS_PER_PAGE = 90;
    public const URL            = '%s/contacts/v1/lists/all/contacts/all';

    /**
     * @var ApplicationInstallRepository
     */
    private ApplicationInstallRepository $repository;

    /**
     * @var CurlManager
     */
    private CurlManager $sender;

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
    public function __construct(DocumentManager $dm, CurlManager $sender)
    {
        $this->repository = $dm->getRepository(ApplicationInstall::class);
        $this->sender     = $sender;
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
     *
     * @return HubSpotListContactsConnector
     */
    public function setLogger(LoggerInterface $logger): HubSpotListContactsConnector
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param ProcessDto $dto
     * @param callable   $callbackItem
     *
     * @return PromiseInterface
     * @throws CurlException
     * @throws ApplicationInstallException
     * @throws ConnectorException
     */
    public function processBatch(ProcessDto $dto, callable $callbackItem): PromiseInterface
    {
        $dto->addHeader(PipesHeaders::createKey(PipesHeaders::APPLICATION), $this->getApplicationKey() ?? '');
        $applicationInstall = $this->repository->findUserAppByHeaders($dto);
        $requestDto         = $this->getApplication()->getRequestDto(
            $applicationInstall,
            CurlManager::METHOD_GET,
            sprintf(self::URL, HubspotApplication::BASE_URL)
        );
        $requestDto->setDebugInfo($dto);

        return $this->doPageLoop($callbackItem, $requestDto, $applicationInstall, $dto);
    }

    /**
     * -------------------------------------------- HELPERS ------------------------------------
     */

    /**
     * @param callable           $callbackItem
     * @param RequestDto         $dto
     * @param ApplicationInstall $install
     * @param ProcessDto         $processDto
     * @param int                $page
     * @param int                $offset
     *
     * @return PromiseInterface
     * @throws CurlException
     */
    private function doPageLoop(
        callable $callbackItem,
        RequestDto $dto,
        ApplicationInstall $install,
        ProcessDto $processDto,
        int $page = 0,
        int $offset = 0
    ): PromiseInterface
    {
        $uri = $this->getUri($dto, $offset);

        return $this->sender->sendAsync(RequestDto::from($dto, $uri))
            ->then(
                function (ResponseInterface $response) use (
                    $dto,
                    $callbackItem,
                    $page,
                    $install,
                    $processDto
                ): PromiseInterface {
                    $body = $response->getBody()->getContents();
                    $data = empty($body) ? [] : Json::decode($body);
                    $this->createSuccessMessage($install, $callbackItem, $data, ++$page, $processDto->getHeaders());

                    if ($data['has-more'] ?? FALSE) {
                        return $this->doPageLoop(
                            $callbackItem,
                            $dto,
                            $install,
                            $processDto,
                            ++$page,
                            $data['vid-offset'] ?? 0
                        );
                    } else {
                        unset($body, $data);

                        return $this->createPromise();
                    }
                },
                fn(Exception $e) => $callbackItem(
                    $this->batchConnectorError($e, $install, [], $processDto->getHeaders())
                )
            );
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
            )
        );
    }

    /**
     * @param ApplicationInstall $install
     * @param callable           $callbackItem
     * @param mixed[]            $data
     * @param int                $page
     * @param mixed[]            $headers
     *
     * @throws Exception
     */
    private function createSuccessMessage(
        ApplicationInstall $install,
        callable $callbackItem,
        array $data,
        int $page,
        array $headers = []
    ): void
    {
        if (array_key_exists('contacts', $data)) {
            $contacts = $data['contacts'];
            $i        = $page * self::ITEMS_PER_PAGE;
            foreach ($contacts as $contact) {
                $successMessage = new SuccessMessage($i);
                $successMessage->setData(Json::encode($contact));
                $callbackItem($successMessage);
                $i++;
            }

            unset($data, $contacts, $i, $successMessage);
        } else {
            $this->batchConnectorError(
                new ConnectorException('Bad response data from HubSpot response. Missing "contacts".'),
                $install,
                ['data' => $data],
                $headers
            );
        }
    }

    /**
     * @param Exception          $e
     * @param ApplicationInstall $install
     * @param mixed[]            $context
     * @param mixed[]            $headers
     *
     * @return SuccessMessage
     * @throws Exception
     */
    private function batchConnectorError(
        Exception $e,
        ApplicationInstall $install,
        array $context = [],
        array $headers = []
    ): SuccessMessage
    {
        $context = array_merge(
            [
                'app_install' => $install->getId(),
                'user'        => $install->getUser(),
                'key'         => $install->getKey(),
            ],
            $context
        );

        $this->logger->error($e->getMessage(), array_merge($context, PipesHeaders::debugInfo($headers)));
        unset($context);

        throw $e;
    }

}

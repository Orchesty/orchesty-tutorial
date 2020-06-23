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
use Hanaboso\HbPFConnectors\Model\Application\Impl\Hubspot\HubspotApplication;
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
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class HubSpotListContactsConnector
 *
 * @package Pipes\PhpSdk\Batch\Connector\HubSpot
 */
final class HubSpotListContactsConnector extends ConnectorAbstract implements BatchInterface
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
        $applicationInstall = $this->repository->findUsersAppDefaultHeaders($dto);
        $requestDto         = $this->getApplication()->getRequestDto(
            $applicationInstall,
            CurlManager::METHOD_GET,
            sprintf(self::URL, HubspotApplication::BASE_URL)
        );
        $requestDto->setDebugInfo($dto);

        return $this->doPageLoop($callbackItem, $requestDto, $applicationInstall);
    }

    /**
     * -------------------------------------------- HELPERS ------------------------------------
     */

    /**
     * @param callable           $callbackItem
     * @param RequestDto         $dto
     * @param ApplicationInstall $install
     * @param int                $page
     * @param int|null           $offset
     *
     * @return PromiseInterface
     * @throws CurlException
     */
    protected function doPageLoop(
        callable $callbackItem,
        RequestDto $dto,
        ApplicationInstall $install,
        int $page = 0,
        ?int $offset = NULL
    ): PromiseInterface
    {
        $uri = $this->getUri($dto, $page, $offset);

        return $this->fetchData(RequestDto::from($dto, $uri))
            ->then(
                function (ResponseInterface $response) use ($dto, $callbackItem, $page, $install): PromiseInterface {
                    $body    = $response->getBody()->getContents();
                    $data    = empty($body) ? [] : Json::decode($body);
                    $promise = $callbackItem($this->createSuccessMessage($data, $page));

                    if ($data['has-more'] ?? FALSE) {
                        return $this->doPageLoop($callbackItem, $dto, $install, $page + 1, $data['vid-offset'] ?? NULL);
                    } else {
                        unset($body, $data);

                        return $promise;
                    }
                },
                fn(Exception $e) => $callbackItem($this->batchConnectorError($e, $install))
            );
    }

    /**
     * @param RequestDto $request
     *
     * @return PromiseInterface
     */
    protected function fetchData(RequestDto $request): PromiseInterface
    {
        return $this->sender->sendAsync($request);
    }

    /**
     * @param Exception          $e
     * @param ApplicationInstall $install
     *
     * @return SuccessMessage
     * @throws Exception
     */
    protected function batchConnectorError(Exception $e, ApplicationInstall $install): SuccessMessage
    {
        $this->logger->error(
            $e->getMessage(),
            [
                'AppInstall' => $install->getId(),
                'User'       => $install->getUser(),
                'Key'        => $install->getKey(),
            ]
        );

        throw $e;
    }

    /**
     * @param RequestDto $dto
     * @param int        $page
     * @param int|null   $offset
     *
     * @return Uri
     */
    protected function getUri(RequestDto $dto, int $page, ?int $offset = NULL): Uri
    {
        if (!$offset) {
            $offset = $page * self::ITEMS_PER_PAGE;
        }

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
     * @param mixed $data
     * @param int   $i
     *
     * @return SuccessMessage
     * @throws ConnectorException
     */
    protected function createSuccessMessage($data, int $i): SuccessMessage
    {
        if (array_key_exists('contacts', $data)) {
            $successMessage = new SuccessMessage($i);
            $data           = $data['contacts'];

            $successMessage->setData(Json::encode($data));
            unset($data);

            return $successMessage;
        } else {
            throw new ConnectorException(
                'Bad response data from HubSpot response.',
            );
        }
    }

}

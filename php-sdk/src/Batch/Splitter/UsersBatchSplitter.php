<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Batch\Splitter;

use GuzzleHttp\Promise\PromiseInterface;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\PipesPhpSdk\Connector\ConnectorAbstract;
use Hanaboso\PipesPhpSdk\Connector\Traits\ProcessActionNotSupportedTrait;
use Hanaboso\PipesPhpSdk\Connector\Traits\ProcessEventNotSupportedTrait;
use Hanaboso\PipesPhpSdk\RabbitMq\Impl\Batch\BatchInterface;
use Hanaboso\PipesPhpSdk\RabbitMq\Impl\Batch\BatchTrait;
use Hanaboso\PipesPhpSdk\RabbitMq\Impl\Batch\SuccessMessage;
use Hanaboso\Utils\String\Json;

/**
 * Class UsersBatchSplitter
 *
 * @package Pipes\PhpSdk\Batch\Splitter
 */
final class UsersBatchSplitter extends ConnectorAbstract implements BatchInterface
{

    use ProcessActionNotSupportedTrait;
    use ProcessEventNotSupportedTrait;
    use BatchTrait;

    /**
     * @return string
     */
    public function getId(): string
    {
        return 'user-batch-splitter';
    }

    /**
     * @param ProcessDto $dto
     * @param callable   $callbackItem
     *
     * @return PromiseInterface
     */
    public function processBatch(ProcessDto $dto, callable $callbackItem): PromiseInterface
    {
        $users = $this->getJsonContent($dto);

        for ($i = 0; $i < count($users); $i++) {
            $message = new SuccessMessage($i);
            $message->setData(Json::encode($users[$i]));

            $callbackItem($message);
        }

        return $this->createPromise();
    }

}

<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Batch\Splitter;

use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\PipesPhpSdk\Connector\ConnectorAbstract;
use Hanaboso\PipesPhpSdk\Connector\Traits\ProcessActionNotSupportedTrait;
use Hanaboso\PipesPhpSdk\Connector\Traits\ProcessEventNotSupportedTrait;
use Hanaboso\PipesPhpSdk\RabbitMq\Impl\Batch\BatchInterface;
use Hanaboso\PipesPhpSdk\RabbitMq\Impl\Batch\SuccessMessage;
use Hanaboso\Utils\String\Json;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

/**
 * Class UsersBatchSplitter
 *
 * @package Pipes\PhpSdk\Batch\Splitter
 */
final class UsersBatchSplitter extends ConnectorAbstract implements BatchInterface
{

    use ProcessActionNotSupportedTrait;
    use ProcessEventNotSupportedTrait;

    /**
     * @return string
     */
    public function getId(): string
    {
        return 'user-batch-splitter';
    }

    /**
     * @param ProcessDto    $dto
     * @param LoopInterface $loop
     * @param callable      $callbackItem
     *
     * @return PromiseInterface
     */
    public function processBatch(ProcessDto $dto, LoopInterface $loop, callable $callbackItem): PromiseInterface
    {
        $loop;
        $users = $this->getJsonContent($dto);

        for ($i = 0; $i < count($users); $i++) {
            $message = new SuccessMessage($i);
            $message->setData(Json::encode($users[$i]));

            $callbackItem($message);
        }

        return resolve();
    }

}

<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Unit\Batch;

use Exception;
use Hanaboso\CommonsBundle\Process\BatchProcessDto;
use Hanaboso\Utils\String\Json;
use Pipes\PhpSdk\Batch\SplitBatch;
use Pipes\PhpSdk\Tests\KernelTestCaseAbstract;

/**
 * Class SplitBatchTest
 *
 * @package Pipes\PhpSdk\Tests\Unit\Batch
 */
final class SplitBatchTest extends KernelTestCaseAbstract
{

    /**
     * @return void
     * @throws Exception
     */
    public function testName(): void
    {
        self::assertSame(SplitBatch::NAME, $this->getNode()->getName());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testProcess(): void
    {
        $node   = $this->getNode();
        $result = $node->processAction(new BatchProcessDto())->getBridgeData();
        $data   = Json::decode($result);

        self::assertCount(3, $data);
    }

    /**
     * @return SplitBatch
     * @throws Exception
     */
    private function getNode(): SplitBatch
    {
        /** @var SplitBatch $node */
        $node = self::getContainer()->get(sprintf('hbpf.batch.%s', SplitBatch::NAME));

        return $node;
    }

}

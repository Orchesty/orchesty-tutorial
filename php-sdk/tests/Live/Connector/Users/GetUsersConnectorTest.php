<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Live\Connector\Users;

use Exception;
use Hanaboso\Utils\String\Json;
use Pipes\PhpSdk\Connector\Users\GetUsersConnector;
use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;
use Pipes\PhpSdk\Tests\DataProvider;

/**
 * Class GetUsersConnectorTest
 *
 * @package Pipes\PhpSdk\Tests\Live\Connector\Users
 */
final class GetUsersConnectorTest extends DatabaseTestCaseAbstract
{

    /**
     * @covers \Pipes\PhpSdk\Connector\Users\GetUsersConnector::processAction
     *
     * @group  live
     *
     * @throws Exception
     */
    public function testProcessAction(): void
    {
        $curl      = self::getContainer()->get('hbpf.transport.curl_manager');
        $connector = new GetUsersConnector();
        $connector->setSender($curl);

        $resp = $connector->processAction(DataProvider::getProcessDto());
        self::assertNotEmpty($resp->getData());
        $data = Json::decode($resp->getData());
        self::assertCount(10, $data);
    }

}

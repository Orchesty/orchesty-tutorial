<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Live\Connector\Users;

use Exception;
use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\Utils\String\Json;
use Pipes\PhpSdk\Connector\Users\GetUsersConnector;
use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;

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
     * @throws Exception
     */
    public function testProcessAction(): void
    {
        $curl      = self::$container->get('hbpf.transport.curl_manager');
        $connector = new GetUsersConnector($curl);

        $resp = $connector->processAction(new ProcessDto());
        self::assertNotEmpty($resp->getData());
        $data = Json::decode($resp->getData());
        self::assertCount(10, $data);
    }

}

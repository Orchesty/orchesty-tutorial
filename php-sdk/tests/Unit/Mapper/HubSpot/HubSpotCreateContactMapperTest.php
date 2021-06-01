<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Unit\Mapper\HubSpot;

use Exception;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\Utils\String\Json;
use Pipes\PhpSdk\Mapper\HubSpot\HubSpotCreateContactMapper;
use Pipes\PhpSdk\Tests\DataProvider;
use Pipes\PhpSdk\Tests\KernelTestCaseAbstract;

/**
 * Class HubSpotCreateContactMapperTest
 *
 * @package Pipes\PhpSdk\Tests\Unit\Mapper\HubSpot
 */
final class HubSpotCreateContactMapperTest extends KernelTestCaseAbstract
{

    /**
     * @covers \Pipes\PhpSdk\Mapper\HubSpot\HubSpotCreateContactMapper::process
     *
     * @throws Exception
     */
    public function testProcess(): void
    {
        $mapper = new HubSpotCreateContactMapper();
        $dto    = DataProvider::getProcessDto(
            'key',
            'usr',
            Json::encode(
                [
                    'name' => 'John Doe', 'email' => 'noreply@johndoe.com', 'phone' => '555-555',
                ],
            ),
        );

        $res = Json::decode($mapper->process($dto)->getData())['properties'];
        self::assertCount(4, $res);
    }

    /**
     * @covers \Pipes\PhpSdk\Mapper\HubSpot\HubSpotCreateContactMapper::process
     *
     * @throws Exception
     */
    public function testProcessDataError(): void
    {
        $mapper = new HubSpotCreateContactMapper();
        $dto    = DataProvider::getProcessDto();

        self::expectException(ConnectorException::class);
        $mapper->process($dto);
    }

}

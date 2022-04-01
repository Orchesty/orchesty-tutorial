<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Unit\Mapper\HubSpot;

use Exception;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\Utils\String\Json;
use Pipes\PhpSdk\Mapper\HubSpot\HubSpotCreateMultipleContactsMapper;
use Pipes\PhpSdk\Tests\DataProvider;
use Pipes\PhpSdk\Tests\KernelTestCaseAbstract;

/**
 * Class HubSpotCreateMultipleContactsMapperTest
 *
 * @package Pipes\PhpSdk\Tests\Unit\Mapper\HubSpot
 */
final class HubSpotCreateMultipleContactsMapperTest extends KernelTestCaseAbstract
{

    /**
     * @covers \Pipes\PhpSdk\Mapper\HubSpot\HubSpotCreateMultipleContactsMapper::process
     *
     * @throws Exception
     */
    public function testProcess(): void
    {
        $mapper = new HubSpotCreateMultipleContactsMapper();
        $dto    = DataProvider::getProcessDto(
            'key',
            'usr',
            Json::encode(
                [
                    [
                        'name' => 'John Doe', 'email' => 'noreply@johndoe.com', 'phone' => '555-555',
                    ],
                ],
            ),
        );

        $res = Json::decode($mapper->processAction($dto)->getData());
        self::assertCount(3, $res[0]['properties']);
        self::assertArrayHasKey('email', $res[0]);
    }

    /**
     * @covers \Pipes\PhpSdk\Mapper\HubSpot\HubSpotCreateMultipleContactsMapper::process
     *
     * @throws Exception
     */
    public function testProcessDataError(): void
    {
        $mapper = new HubSpotCreateMultipleContactsMapper();
        $dto    = DataProvider::getProcessDto('', '', Json::encode([[]]));

        self::expectException(ConnectorException::class);
        $mapper->processAction($dto);
    }

}

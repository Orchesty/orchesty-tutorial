<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Mapper\HubSpot;

use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\PipesPhpSdk\CustomNode\CustomNodeAbstract;
use Hanaboso\Utils\String\Json;

/**
 * Class HubSpotCreateContactMapper
 *
 * @package Pipes\PhpSdk\Mapper\HubSpot
 */
final class HubSpotCreateContactMapper extends CustomNodeAbstract
{

    /**
     * @param ProcessDto $dto
     *
     * @return ProcessDto
     * @throws ConnectorException
     */
    public function processAction(ProcessDto $dto): ProcessDto
    {
        $data = Json::decode($dto->getData());
        if (!isset($data['name'], $data['email'], $data['phone'])) {
            throw new ConnectorException('Some data is missing. Keys [name, email, phone] is required.');
        }

        $name = explode(' ', $data['name']);
        $body = [
            'properties' => [
                [
                    'property' => 'email',
                    'value'    => $data['email'],
                ],
                [
                    'property' => 'firstname',
                    'value'    => $name[0],
                ],
                [
                    'property' => 'lastname',
                    'value'    => $name[1] ?? '',
                ],
                [
                    'property' => 'phone',
                    'value'    => $data['phone'],
                ],
            ],
        ];

        return $dto->setData(Json::encode($body));
    }

}

<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Mapper\HubSpot;

use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\PipesPhpSdk\CustomNode\CustomNodeAbstract;
use Hanaboso\Utils\String\Json;

/**
 * Class HubSpotCreateMultipleContactsMapper
 *
 * @package Pipes\PhpSdk\Mapper\HubSpot
 */
final class HubSpotCreateMultipleContactsMapper extends CustomNodeAbstract
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
        $body = [];

        foreach ($data as $user) {
            if (!isset($user['name'], $user['email'], $user['phone'])) {
                throw new ConnectorException('Some data is missing. Keys [name, email, phone] is required.');
            }

            $name   = explode(' ', $user['name']);
            $body[] = [
                'email'      => $user['email'],
                'properties' => [
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
                        'value'    => $user['phone'],
                    ],
                ],
            ];
        }

        return $dto->setData(Json::encode($body));
    }

}

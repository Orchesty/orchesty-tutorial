<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Connector\HubSpot;

use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\PipesPhpSdk\Connector\ConnectorAbstract;
use Hanaboso\PipesPhpSdk\Connector\Traits\ProcessActionNotSupportedTrait;

/**
 * Class HubSpotContactCreatedConnector
 *
 * @package Pipes\PhpSdk\Connector\HubSpot
 */
final class HubSpotContactCreatedConnector extends ConnectorAbstract
{

    use ProcessActionNotSupportedTrait;

    /**
     * @return string
     */
    public function getId(): string
    {
        return 'hub-spot.contact-created';
    }

    /**
     * @param ProcessDto $dto
     *
     * @return ProcessDto
     */
    public function processEvent(ProcessDto $dto): ProcessDto
    {
        return $dto;
    }

}

<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Connector\HubSpot;

/**
 * Class HubSpotCreateContactConnector
 *
 * @package Pipes\PhpSdk\Connector\HubSpot
 */
final class HubSpotCreateContactConnector extends HubSpotCreateContactAbstract
{

    /**
     * @var string
     */
    protected string $contactUrl = 'contacts/v1/contact/batch/';

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'hub-spot.create-contact';
    }

}

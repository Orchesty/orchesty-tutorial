<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Connector\HubSpot;

/**
 * Class HubSpotCreateMultipleContactsConnector
 *
 * @package Pipes\PhpSdk\Connector\HubSpot
 */
final class HubSpotCreateMultipleContactsConnector extends HubSpotCreateContactAbstract
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
        return 'hub-spot.create-multiple-contacts';
    }

}

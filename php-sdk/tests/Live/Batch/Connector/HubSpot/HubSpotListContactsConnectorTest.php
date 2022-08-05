<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Live\Batch\Connector\HubSpot;

use Exception;
use Hanaboso\PipesPhpSdk\Application\Base\ApplicationInterface;
use Hanaboso\PipesPhpSdk\Authorization\Base\OAuth2\OAuth2ApplicationInterface;
use Hanaboso\PipesPhpSdk\Authorization\Provider\OAuth2Provider;
use Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector;
use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;
use Pipes\PhpSdk\Tests\DataProvider;

/**
 * Class HubSpotListContactsConnectorTest
 *
 * @package Pipes\PhpSdk\Tests\Live\Batch\Connector\HubSpot
 */
final class HubSpotListContactsConnectorTest extends DatabaseTestCaseAbstract
{

    /**
     * @covers \Pipes\PhpSdk\Batch\Connector\HubSpot\HubSpotListContactsConnector::processBatch
     *
     * @group  live
     *
     * @throws Exception
     */
    public function testProcessBatch(): void
    {
        $settings     = self::getContainer()->get('hbpf.commons.crypt.crypt_manager')->decrypt('001_aaa');
        $token        = $settings[ApplicationInterface::AUTHORIZATION_FORM][ApplicationInterface::TOKEN][OAuth2Provider::ACCESS_TOKEN];
        $clientId     = $settings[ApplicationInterface::AUTHORIZATION_FORM][OAuth2ApplicationInterface::CLIENT_ID];
        $clientSecret = $settings[ApplicationInterface::AUTHORIZATION_FORM][OAuth2ApplicationInterface::CLIENT_SECRET];
        $app          = self::getContainer()->get('hbpf.application.hub-spot');
        $curl         = self::getContainer()->get('hbpf.transport.curl_manager');
        $connector    = new HubSpotListContactsConnector();
        $connector
            ->setApplication($app)
            ->setSender($curl)
            ->setDb($this->dm);

        $appInstall = DataProvider::getOauth2AppInstall(
            $app->getName(),
            'user',
            $token,
            $clientId,
            $clientSecret,
        );
        $this->pfd($appInstall);
        $this->dm->clear();

        $dto = DataProvider::getBatchProcessDto($app->getName());
        self::assertBatch($connector, $dto, [DataProvider::getBatchProcessDto()]);
    }

}

<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Live\Connector\HubSpot;

use Exception;
use Hanaboso\PipesPhpSdk\Application\Base\ApplicationInterface;
use Hanaboso\PipesPhpSdk\Authorization\Base\OAuth2\OAuth2ApplicationAbstract;
use Hanaboso\PipesPhpSdk\Authorization\Base\OAuth2\OAuth2ApplicationInterface;
use Hanaboso\PipesPhpSdk\Authorization\Provider\OAuth2Provider;
use Hanaboso\Utils\String\Json;
use Pipes\PhpSdk\Connector\HubSpot\HubSpotCreateContactConnector;
use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;
use Pipes\PhpSdk\Tests\DataProvider;

/**
 * Class HubSpotCreateContactConnectorTest
 *
 * @package Pipes\PhpSdk\Tests\Live\Connector\HubSpot
 */
final class HubSpotCreateContactConnectorTest extends DatabaseTestCaseAbstract
{

    /**
     * @covers \Pipes\PhpSdk\Connector\HubSpot\HubSpotCreateContactConnector::processAction
     *
     * @group  live
     *
     * @throws Exception
     */
    public function testProcessAction(): void
    {
        $settings     = self::getContainer()->get('hbpf.commons.crypt.crypt_manager')->decrypt('001_aaa');
        $token        = $settings[ApplicationInterface::AUTHORIZATION_SETTINGS][ApplicationInterface::TOKEN][OAuth2Provider::ACCESS_TOKEN];
        $clientId     = $settings[ApplicationInterface::AUTHORIZATION_SETTINGS][OAuth2ApplicationInterface::CLIENT_ID];
        $clientSecret = $settings[ApplicationInterface::AUTHORIZATION_SETTINGS][OAuth2ApplicationInterface::CLIENT_SECRET];
        $app          = self::getContainer()->get('hbpf.application.hub-spot');
        $curl         = self::getContainer()->get('hbpf.transport.curl_manager');
        $connector    = new HubSpotCreateContactConnector($this->dm, $curl);
        $connector->setApplication($app);

        $appInstall = DataProvider::getOauth2AppInstall(
            $app->getKey(),
            'user',
            $token,
            $clientId,
            $clientSecret,
        );
        $this->pfd($appInstall);
        $this->dm->clear();

        $dto = DataProvider::getProcessDto(
            $app->getKey(),
            'user',
            Json::encode(
                [
                    'name' => 'first last', 'email' => 'first@last.com', 'phone' => '555-555',
                ],
            ),
        );

        $resp = $connector->processAction($dto);
        self::assertNotEmpty($resp->getData());
    }

}

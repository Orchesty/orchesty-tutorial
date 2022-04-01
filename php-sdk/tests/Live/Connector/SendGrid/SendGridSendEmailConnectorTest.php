<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Live\Connector\SendGrid;

use Exception;
use Hanaboso\PipesPhpSdk\Application\Base\ApplicationInterface;
use Hanaboso\Utils\String\Json;
use Pipes\PhpSdk\Application\SendGridApplication;
use Pipes\PhpSdk\Connector\SendGrid\SendGridSendEmailConnector;
use Pipes\PhpSdk\Tests\DatabaseTestCaseAbstract;
use Pipes\PhpSdk\Tests\DataProvider;

/**
 * Class SendGridSendEmailConnectorTest
 *
 * @package Pipes\PhpSdk\Tests\Live\Connector\SendGrid
 */
final class SendGridSendEmailConnectorTest extends DatabaseTestCaseAbstract
{

    /**
     * @covers \Pipes\PhpSdk\Connector\SendGrid\SendGridSendEmailConnector::processAction
     *
     * @group  live
     *
     * @throws Exception
     */
    public function testProcessAction(): void
    {
        $app       = self::getContainer()->get('hbpf.application.send-grid');
        $curl      = self::getContainer()->get('hbpf.transport.curl_manager');
        $connector = new SendGridSendEmailConnector($this->dm, $curl);
        $connector->setApplication($app);

        $appInstall = DataProvider::getBasicAppInstall($app->getName());
        $appInstall->setSettings(
            [ApplicationInterface::AUTHORIZATION_SETTINGS => [SendGridApplication::API_KEY => 'key']],
        );
        $this->pfd($appInstall);
        $this->dm->clear();

        $data = Json::encode(['email' => 'noreply@johndoe.com', 'name' => 'John Doe', 'subject' => 'Hello, World!']);
        $resp = $connector->processAction(DataProvider::getProcessDto($app->getName(), 'user', $data));
        self::assertNotEmpty($resp->getData());
    }

}

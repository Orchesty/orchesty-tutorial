<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\Unit\Application;

use Exception;
use Hanaboso\CommonsBundle\Transport\Curl\Dto\ResponseDto;
use Hanaboso\PipesPhpSdk\Application\Document\ApplicationInstall;
use Hanaboso\PipesPhpSdk\Application\Manager\Webhook\WebhookSubscription;
use Pipes\PhpSdk\Application\GitHubApplication;
use Pipes\PhpSdk\Tests\KernelTestCaseAbstract;

/**
 * Class GitHubApplicationTest
 *
 * @package Pipes\PhpSdk\Tests\Unit\Application
 */
final class GitHubApplicationTest extends KernelTestCaseAbstract
{

    /**
     * @return void
     */
    public function testGetWebhookSubscriptions(): void
    {
        $gitHubApplication = new GitHubApplication();
        self::assertEquals(
            [
                new WebhookSubscription('issues', 'Webhook', '', ['record' => 'record', 'owner' => 'owner']),
                new WebhookSubscription('pull-request', 'Webhook', '', ['record' => 'record', 'owner' => 'owner']),
            ],
            $gitHubApplication->getWebhookSubscriptions(),
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testProcessWebhookSubscribeResponse(): void
    {
        $gitHubApplication = new GitHubApplication();
        self::assertEquals(
            '1',
            $gitHubApplication->processWebhookSubscribeResponse(
                new ResponseDto(201, '', '{"id": "1"}', []),
                new ApplicationInstall(),
            ),
        );
    }

    /**
     * @return void
     */
    public function testProcessWebhookUnsubscribeResponse(): void
    {
        $gitHubApplication = new GitHubApplication();
        self::assertTrue(
            $gitHubApplication->processWebhookUnsubscribeResponse(
                new ResponseDto(204, '', '', []),
            ),
        );
    }

}

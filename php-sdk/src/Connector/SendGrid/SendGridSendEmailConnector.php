<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Connector\SendGrid;

use Hanaboso\CommonsBundle\Process\ProcessDto;
use Hanaboso\CommonsBundle\Transport\Curl\CurlException;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\PipesPhpSdk\Application\Exception\ApplicationInstallException;
use Hanaboso\PipesPhpSdk\Connector\ConnectorAbstract;
use Hanaboso\PipesPhpSdk\Connector\Exception\ConnectorException;
use Hanaboso\Utils\Exception\PipesFrameworkException;
use Hanaboso\Utils\String\Json;
use Pipes\PhpSdk\Application\SendGridApplication;

/**
 * Class SendGridSendEmailConnector
 *
 * @package Pipes\PhpSdk\Connector\SendGrid
 */
final class SendGridSendEmailConnector extends ConnectorAbstract
{

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'send-grid.send-email';
    }

    /**
     * @param ProcessDto $dto
     *
     * @return ProcessDto
     * @throws ConnectorException
     * @throws ApplicationInstallException
     */
    public function processAction(ProcessDto $dto): ProcessDto
    {
        $applicationInstall = $this->getApplicationInstallFromProcess($dto);
        $data               = $dto->getJsonData();
        if (!isset($data['email'], $data['name'], $data['subject'])) {
            throw new ConnectorException('Some data is missing. Keys [email, name, subject] is required.');
        }

        $body = [
            'personalizations' => [
                [
                    'to'      => [
                        [
                            'email' => $data['email'],
                            'name'  => $data['name'],
                        ],
                    ],
                    'subject' => $data['subject'],
                ],
            ],
            'from'             => [
                'email' => 'noreply@johndoe.com',
                'name'  => 'John Doe',
            ],
            'reply_to'         => [
                'email' => 'noreply@johndoe.com',
                'name'  => 'John Doe',
            ],
            'template_id'      => '1',
        ];

        $url     = sprintf('%s/mail/send', SendGridApplication::BASE_URL);
        $request = $this->getApplication()
            ->getRequestDto($dto, $applicationInstall, CurlManager::METHOD_POST, $url, Json::encode($body));

        try {
            $response = $this->getSender()->send($request);

            if (!$this->evaluateStatusCode($response->getStatusCode(), $dto, '')) {
                return $dto;
            }
        } catch (CurlException|PipesFrameworkException $e) {
            throw new ConnectorException($e->getMessage(), $e->getCode(), $e);
        }

        return $dto->setData($response->getBody());
    }

}

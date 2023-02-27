<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\MockServer;

use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Hanaboso\CommonsBundle\WorkerApi\ClientInterface;
use Hanaboso\Utils\String\Json;
use Monolog\LogRecord;
use Psr\Http\Message\ResponseInterface;

/**
 * Class MockServer
 *
 * @package Pipes\PhpSdk\Tests\MockServer
 */
final class MockServer implements ClientInterface
{

    /**
     * @var Mock[] $mocks
     */
    private array $mocks = [];

    /**
     * @param string                 $uri
     * @param mixed[]|LogRecord|null $data
     * @param string                 $method
     *
     * @return ResponseInterface
     * @throws MockServerException
     */
    public function send(
        string $uri,
        array|LogRecord|null $data = NULL,
        string $method = CurlManager::METHOD_POST,
    ): ResponseInterface
    {
        $mock = $this->popMock();
        if (empty($mock)) {
            throw new MockServerException('No requests are mocked!');
        }
        if (!$this->compareMock($uri, $data, $method, $mock)) {
            throw new MockServerException(
                sprintf(
                    'Expected mock not found! Expected: uri=%s, data=%s, method=%s',
                    $uri,
                    Json::encode(
                        $data,
                    ),
                    $method,
                ),
            );
        }

        return $mock->response;
    }

    /**
     * @param Mock $mock
     *
     * @return void
     */
    public function addMock(Mock $mock): void
    {
        $this->mocks[] = $mock;
    }

    /**
     * @return Mock|null
     */
    private function popMock(): Mock|null
    {
        return array_shift($this->mocks) ?? NULL;
    }

    /**
     * @param string                 $uri
     * @param mixed[]|LogRecord|null $data
     * @param string                 $method
     * @param Mock                   $mock
     *
     * @return bool
     */
    private function compareMock(string $uri, array|LogRecord|null $data, string $method, Mock $mock,): bool
    {
        if (!empty($mock->replaceFields) && is_array($data)) {
            foreach ($mock->replaceFields as $key => $value) {
                if ($data) {
                    foreach (array_keys($data) as $index) {
                        $data[$index][$key] = $value;
                    }
                }
            }
        }

        return $uri === $mock->uri && $data === $mock->data && $method === $mock->method;
    }

}

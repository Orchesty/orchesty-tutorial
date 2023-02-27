<?php declare(strict_types=1);

namespace Pipes\PhpSdk\Tests\MockServer;

use GuzzleHttp\Psr7\Response;
use Hanaboso\CommonsBundle\Transport\Curl\CurlManager;
use Monolog\LogRecord;

/**
 * Class Mock
 *
 * @package Pipes\PhpSdk\Tests\MockServer
 */
final class Mock
{

    /**
     * Mock constructor.
     *
     * @param string                 $uri
     * @param mixed[]|LogRecord|null $data
     * @param string                 $method
     * @param Response               $response
     * @param mixed[]                $replaceFields
     */
    public function __construct(
        public string $uri,
        public array|LogRecord|null $data = NULL,
        public string $method = CurlManager::METHOD_POST,
        public Response $response = new Response(),
        public array $replaceFields = [],
    )
    {
    }

}

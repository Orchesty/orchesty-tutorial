<?php declare(strict_types=1);

use Pipes\PhpSdk\Kernel;

require __DIR__ . '/../vendor/autoload.php';

$kernel = new Kernel(
    (string) (filter_input(INPUT_SERVER, 'APP_ENV') ?? 'test'),
    (bool) (filter_input(INPUT_SERVER, 'APP_DEBUG') ?? TRUE)
);
$kernel->boot();

return $kernel->getContainer()->get('doctrine_mongodb.odm.document_manager');

<?php

require_once '../vendor/autoload.php';

use \SplitIO\ThinSdk\Factory;

$factory = Factory::withConfig([
    'transfer' => [
        'address' => '../../splitd/splitd.sock',
        'type'    => 'unix-stream',
    ],
    'logging' => [
        'level' => \Psr\Log\LogLevel::INFO,
    ],
]);

$client = $factory->client();
echo $client->track("key", "user", "checkin", 0.123, ['age' => 22]);

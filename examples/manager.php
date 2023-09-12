<?php

require_once '../vendor/autoload.php';

use \SplitIO\ThinSdk\Factory;

$factory = Factory::withConfig([
    'transfer' => [
        'address' => '../../splitd/splitd.sock',
        'type'    => 'unix-stream',
    ],
    'logging' => [
        'level' => \Psr\Log\LogLevel::DEBUG,
    ],
]);

$manager = $factory->manager();
$names = $manager->splitNames();
print_r($names);
var_dump($manager->split($names[0]));
var_dump($manager->splits());

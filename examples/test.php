<?php

require_once '../vendor/autoload.php';

use \SplitIO\ThinClient\Factory;

$factory = Factory::withConfig([
	'transfer' => [
		'address' => '../../splitd/splitd.sock',
		'type'    => 'unix-seqpacket',
	]
]);

$client = $factory->client();

echo $client->getTreatment("key2", null, "tinchotest", null) . PHP_EOL;
echo $client->getTreatment("key1", "", "test_DOS", ['age' => 64]) . PHP_EOL;

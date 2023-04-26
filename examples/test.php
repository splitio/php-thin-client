<?php

require_once '../vendor/autoload.php';

use \SplitIO\ThinClient\Factory;
use \SplitIO\ThinClient\Utils\ImpressionListener;
use \SplitIO\ThinClient\Models\Impression;

class CustomListener implements ImpressionListener
{
    public function accept(Impression $i)
    {
        echo "recibi impression para key=".$i->getKey()
            ." feat=".$i->getFeature()
            ." treatment=".$i->getTreatment()
            ." label=".$i->getLabel()."\n";
    }
}

$factory = Factory::withConfig([
    'transfer' => [
        'address' => '../../splitd/splitd.sock',
        'type'    => 'unix-stream',
    ],
    'logging' => [
        'level' => \Psr\Log\LogLevel::INFO,
    ],
    'utils' => [
        'impressionListener' => new CustomListener(),
    ],
]);

$client = $factory->client();

while (true) {
    echo $client->getTreatment("test_82", null, "PHP_8_SPLITD_changeTrafficAllocationAndTargetingRule", null) . PHP_EOL;
    sleep(1);
}

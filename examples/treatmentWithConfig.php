<?php

require_once '../vendor/autoload.php';

use \SplitIO\ThinSdk\Factory;
use \SplitIO\ThinSdk\Utils\ImpressionListener;
use \SplitIO\ThinSdk\Models\Impression;

class CustomListener implements ImpressionListener
{
    public function accept(Impression $i, ?array $a)
    {
        echo "got an impression for: key=" . $i->getKey()
            . " feat=" . $i->getFeature()
            . " treatment=" . $i->getTreatment()
            . " label=" . $i->getLabel()
            . " cn=" . $i->getChangeNumber()
            . " #attrs=" . (($a == null) ? 0 : count($a)) . "\n";
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
print_r($client->getTreatmentWithConfig("key", null, "feature1", ['age' => 22]));
print_r($client->getTreatmentsWithConfig("key", null, ["feature1", "feature2"], ['age' => 22]));

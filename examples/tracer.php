<?php

require_once '../vendor/autoload.php';

use \SplitIO\ThinSdk\Factory;
use \SplitIO\ThinSdk\Utils\Tracing\Tracer;
use \SplitIO\ThinSdk\Utils\Tracing\TracerHook;

class CustomTracer implements TracerHook
{

    private $events = [];

    public function on(array $event)
    {
        // assume we only care about getTreatment() calls...
        if ($event['method'] != Tracer::METHOD_GET_TREATMENT) {
            return;
        }

        switch ($event['event']) {
            case Tracer::EVENT_START:
                array_push($this->events, "start (" . json_encode($event['arguments']) . ") -- " . microtime(true));
                break;
            case Tracer::EVENT_RPC_START:
                array_push($this->events, "about to send rpc -- " . microtime(true));
                break;
            case Tracer::EVENT_RPC_END:
                array_push($this->events, "rpc completed -- " . microtime(true));
                break;
            case Tracer::EVENT_EXCEPTION:
                array_push($this->events, "exception occured -- " . microtime(true));
                break;
            case Tracer::EVENT_END:
                array_push($this->events, "end -- " . microtime(true));
                break;
        }
    }

    public function getEvents(): array
    {
        return $this->events;
    }
}

$ct = new CustomTracer();

$factory = Factory::withConfig([
    'transfer' => [
        'address' => '../../splitd.sock',
        'type'    => 'unix-stream',
    ],
    'logging' => [
        'level' => \Psr\Log\LogLevel::INFO,
    ],
    'utils' => [
        'tracer' => [
            'hook' => $ct,
            'forwardArgs' => true,
        ]
    ],

]);

$manager = $factory->manager();
$client = $factory->client();
echo $client->getTreatment("key", null, $manager->splitNames()[0], ['age' => 22]);
print_r($ct->getEvents());

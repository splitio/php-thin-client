<?php

require_once '../vendor/autoload.php';

use \SplitIO\ThinSdk\Factory;
use \SplitIO\ThinSdk\Utils\Tracing\Tracer;
use \SplitIO\ThinSdk\Utils\Tracing\TracerHook;

class CustomTracer implements TracerHook
{

    private $traces = [];

    // assume we only care about getTreatment() calls...
    public function on(array $event)
    {

        if ($event['method'] != Tracer::METHOD_GET_TREATMENT) {
            return;
        }

        $trace = $this->traces[$event['id']] ?? [];

        switch ($event['event']) {
            case Tracer::EVENT_START:
                $trace['start'] = microtime(true);
                $trace['args'] = $event['arguments'];
                break;
            case Tracer::EVENT_RPC_START:
                $trace['rpc_start'] = microtime(true);
                break;
            case Tracer::EVENT_RPC_END:
                $trace['rpc_end'] = microtime(true);
                break;
            case Tracer::EVENT_EXCEPTION:
                $trace['exception'] = $event['exception'];
                break;
            case Tracer::EVENT_END:
                $trace['end'] = microtime(true);
                break;
        }

        $this->traces[$event['id']] =  $trace;
    }

    public function getTraces(): array
    {
        return $this->traces;
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
var_dump($ct->getTraces());

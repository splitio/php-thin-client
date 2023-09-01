<?php

namespace SplitIO\ThinSdk\Link\Consumer;

use \SplitIO\ThinSdk\Link\Protocol;
use \SplitIO\ThinSdk\Link\Protocol\V1\RPC;
use \SplitIO\ThinSdk\Link\Transfer;
use \SplitIO\ThinSdk\Link\Serialization;

use \SplitIO\ThinSdk\Config\Utils as UtilsConfig;


class V1Manager implements Manager
{
    private /*Transfer\RawConnection*/ $conn;
    private /*Serialization\Serializer*/ $serializer;
    private /*ConnectionFactory*/ $connFactory;
    private /*UtilsConfig*/ $utilsConfig;
    private /*string*/ $id;
    private /*LoggerInterface*/ $logger;


    public function __construct(
        Transfer\ConnectionFactory $connFactory,
        Serialization\SerializerFactory $serializerFactory,
        UtilsConfig $utilsConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        // save these 2 for future reconnects
        $this->connFactory = $connFactory;
        $this->utilsConfig = $utilsConfig;
        $this->logger = $logger;

        $this->id = 'someId'; /*TODO*/

        $this->serializer = $serializerFactory->create();
        $this->conn = $this->connFactory->create();
        $this->register($this->id, $utilsConfig->impressionListener() != null);
    }

    public function getTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes): array
    {
        $result = Protocol\V1\TreatmentResponse::fromRaw(
            $this->rpcWithReconnect(RPC::forTreatment($key, $bucketingKey, $feature, $attributes))
        )->getEvaluationResult();

        return [$result->getTreatment(), $result->getImpressionListenerData(), $result->getConfig()];
    }

    public function getTreatments(string $key, ?string $bucketingKey, array $features, ?array $attributes): array
    {
        $response = Protocol\V1\TreatmentsResponse::fromRaw(
            $this->rpcWithReconnect(RPC::forTreatments($key, $bucketingKey, $features, $attributes))
        );

        $results = [];
        foreach ($features as $idx => $feature) {
            $result = $response->getEvaluationResult($idx);
            $results[$feature] = $result == null
                ? ["control", null, null]
                : [$result->getTreatment(), $result->getImpressionListenerdata(), $result->getConfig()];
        }

        return $results;
    }

    public function track(string $key, string $trafficType, string $eventType, ?float $value, ?array $properties): bool
    {
        return Protocol\V1\TrackResponse::fromRaw(
            $this->rpcWithReconnect(RPC::forTrack($key, $trafficType, $eventType, $value, $properties))
        )->getSuccess();
    }


    private function register(string $id, bool $impressionFeedback)
    {
        // this is performed without retries to avoid an endless loop,
        // since register should occur only once per connection. if it fails,
        // it's not worth retrying for this single evaluation, and probably better off to just return 'control'.
        return $this->performRPC(RPC::forRegister($id, new Protocol\V1\RegisterFlags($impressionFeedback)));
    }

    private function rpcWithReconnect(RPC $rpc): array
    {
        try {
            return $this->performRPC($rpc);
        } catch (Transfer\ConnectionException $exc) {
            $this->logger->error("an error occurred while performing an RPC");
            $this->logger->error($exc);
        }

        // TODO(mredolatti): shutdown current conn?
        $this->conn = $this->connFactory->create();
        $this->register($this->id, $this->utilsConfig->impressionListener() != null);
        return $this->performRPC($rpc);
    }

    private function performRPC(RPC $rpc): array
    {
        $this->conn->sendMessage($this->serializer->serialize($rpc));
        return $this->serializer->deserialize($this->conn->readMessage());
    }
};

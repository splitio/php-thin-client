<?php

namespace SplitIO\ThinClient\Link\Consumer;

use \SplitIO\ThinClient\Link\Protocol;
use \SplitIO\ThinClient\Link\Protocol\V1\RPC;
use \SplitIO\ThinClient\Link\Transfer;
use \SplitIO\ThinClient\Link\Serialization;

use \SplitIO\ThinClient\Config\Transfer as TransferConfig;
use \SplitIO\ThinClient\Config\Serialization as SerializationConfig;
use \SplitIO\ThinClient\Config\Utils as UtilsConfig;


class V1Manager implements Manager
{
    private /*Transfer\RawConnection*/ $conn;
    private /*Serialization\Serializer*/ $serializer;
    private /*TransferConfig*/ $transferConfig;
    private /*UtilsConfig*/ $utilsConfig;
    private /*string*/ $id;
    private /*LoggerInterface*/ $logger;


    public function __construct(
        TransferConfig $transferConfig,
        SerializationConfig $serializationConfig,
        UtilsConfig $utilsConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        // save these 2 for future reconnects
        $this->transferConfig = $transferConfig;
        $this->utilsConfig = $utilsConfig;
        $this->logger = $logger;

        $this->id = 'someId'; /*TODO*/

        $this->serializer = Serialization\Initializer::setup($serializationConfig);
        $this->conn = Transfer\Initializer::setup($this->transferConfig);
        $this->register($this->id, $utilsConfig->impressionListener() != null);
    }

    public function getTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes): Protocol\V1\TreatmentResponse
    {
        return Protocol\V1\TreatmentResponse::fromRaw(
            $this->rpcWithReconnect(RPC::forTreatment($key, $bucketingKey, $feature, $attributes))
        );
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
        $this->conn = Transfer\Initializer::setup($this->transferConfig);
        $this->register($this->id, $this->utilsConfig->impressionListener() != null);
        return $this->performRPC($rpc);
    }

    private function performRPC(RPC $rpc): array
    {
        $this->conn->sendMessage($this->serializer->serialize($rpc));
        return $this->serializer->deserialize($this->conn->readMessage());
    }
};

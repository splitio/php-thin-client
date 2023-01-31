<?php

namespace SplitIO\ThinClient\Link;

use \SplitIO\ThinClient\Link\Protocol\RPC;
use \SplitIO\ThinClient\Config\Transfer as TransferConfig;
use \SplitIO\ThinClient\Config\Serialization as SerializationConfig;


class Manager
{
	private Transfer\RawConnection $conn;
	private Serialization\Serializer $serializer;

	private TransferConfig $transferConfig;
	private string $id;


	public function __construct(TransferConfig $transferConfig, SerializationConfig $serializationConfig)
	{
		// save these 2 for future reconnects
		$this->transferConfig = $transferConfig;
		$this->id = 'someId';

		$this->serializer = Serialization\Initializer::setup($serializationConfig);
		$this->conn = Transfer\Initializer::setup($this->transferConfig);
		$this->register($this->id);
	}

	public function getTreatment(string $key, ?string $bucketingKey, string $feature, ?array $attributes)
	{
		return $this->rpcWithReconnect(RPC::forTreatment($key, $bucketingKey, $feature, $attributes));
	}

	private function register(string $id)
	{
		return $this->rpcWithReconnect(RPC::forRegister($id));
	}

	private function rpcWithReconnect(RPC $rpc): array
	{
		try {
			return $this->performRPC($rpc);
		} catch (Transfer\ConnectionException) {
			// TODO(mredolatti): log
		} /*catch (Protocol\RemoteException) {
			// TODO(mredolatti): log
			// Should this be retried as well?
		}*/

		if (!$this->conn->isHealthy()) {
			// TODO(mredolatti): shutdown current conn?
			$this->conn = Transfer\Initializer::setup($this->transferConfig);
			$this->register($this->id);
		}
		return $this->performRPC($rpc);
	}

	private function performRPC(RPC $rpc): array
	{
		$this->conn->sendMessage($this->serializer->serialize($rpc));
		$parsed = $this->serializer->deserialize($this->conn->readMessage());
		if ($parsed['Status'] != Protocol\Result::Ok->value) {
			throw new Protocol\RemoteException("error in daemon when executing rpc");
		}
		return $parsed['Payload'];
	}
};

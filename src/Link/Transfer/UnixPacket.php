<?php

namespace SplitIO\ThinClient\Link\Transfer;

class UnixPacket implements RawConnection
{
	private string $targetSockFN;
	private \Socket $sock;

	public function __construct(string $targetSockFN)
	{
		$this->targetSockFN = $targetSockFN;

		if (!$this->sock = socket_create(AF_UNIX, SOCK_SEQPACKET, 0)) {
			throw new \Exception("failed to create a socket");
		}

		if (!socket_connect($this->sock, $this->targetSockFN)) {
			throw new \Exception("failed to connect to remote socket $this->targetSockFN");
		}
	}

	public function sendMessage(string $message)
	{
		if (socket_send($this->sock, $message, strlen($message), 0) != strlen($message)) {
			throw new \Exception("error writing to socket");
		}
	}

	public function readMessage(): string
	{
		$buffer = "";
		$n = socket_recv($this->sock, $buffer, 1024, 0);
		if (!$n) {
			throw new \Exception("error reading");
		}
		return substr($buffer, 0, $n);
	}

	public function isHealthy(): bool
	{
		$status = socket_get_status($this->sock);
		return (!$status['timed_out'] && !$status['eof']);
	}
}

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
			throw new ConnectionException("failed to create a socket: "
				. Helpers::getSocketError($this->sock));
		}

		if (!socket_connect($this->sock, $this->targetSockFN)) {
			throw new ConnectionException("failed to connect to remote socket $this->targetSockFN"
				. Helpers::getSocketError($this->sock));
		}
	}

	public function sendMessage(string $message)
	{
		if (socket_send($this->sock, $message, strlen($message), 0) != strlen($message)) {
			throw new ConnectionException("error writing to socket: "
				. Helpers::getSocketError($this->sock));
		}
	}

	public function readMessage(): string
	{
		$buffer = "";
		$n = socket_recv($this->sock, $buffer, 1024, 0);
		if (!$n) {
			throw new ConnectionException("error reading from socket: "
				. Helpers::getSocketError($this->sock));
		}
		return substr($buffer, 0, $n);
	}

	public function isHealthy(): bool
	{
		$status = socket_get_status($this->sock);
		return (!$status['timed_out'] && !$status['eof']);
	}
}

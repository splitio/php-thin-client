<?php

namespace SplitIO\ThinSdk\Link\Transfer;

class ConnectionFactory
{

    private /*array*/ $sockOpts;
    private /*string*/ $sockAddr;
    private /*string*/ $sockType;

    public function __construct(\SplitIO\ThinSdk\Config\Transfer $options)
    {
        $this->sockType = $options->connType();
        $this->sockAddr = $options->sockFN();
        $this->sockOpts = array_filter([
            'timeout' => self::formatTimeout($options->timeout()),
            'sendBufferSize' => $options->bufferSize(),
            'recvBufferSize' => $options->bufferSize(),
        ]);

    }

    public function create(): RawConnection
    {
        switch ($this->sockType) {
            case 'unix-seqpacket':
                return new UnixPacket($this->sockAddr, $this->sockOpts);
            case 'unix-stream':
                return new UnixStream($this->sockAddr, $this->sockOpts);
        }
        throw new \Exception("invalid connection type " . $this->sockType);
    }

    private static function formatTimeout($timeout)/*: ?int */
    {
	if (is_array($timeout)) {
            // assume it's a properly formatted unix-like timeout (including 'sec' & 'usec')
	    if (!array_key_exists('sec', $timeout) || !array_key_exists('usec', $timeout)) {
                throw new \Exception("timeout must either be an int (milliseconds) or an array with keys 'sec' & 'usec'");
            }
            return $timeout;
	}

        if (!is_null($timeout) && !is_int($timeout)) {
            throw new \Exception("timeout must either be an int (milliseconds) or an array with keys 'sec' & 'usec'");
        }

        if ($timeout == null || $timeout == 0) {
            $timeout = 1000;
        }
        return [
            'sec' => floor($timeout / 1000),
            'usec' => ($timeout % 1000) * 1000,
        ];
    }
}

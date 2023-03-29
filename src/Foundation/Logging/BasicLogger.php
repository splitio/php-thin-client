<?php

namespace SplitIO\ThinClient\Foundation\Logging;

use \Psr\Log\LoggerInterface;
use \Psr\Log\AbstractLogger;
use \Psr\Log\LogLevel;

class BasicLogger extends AbstractLogger implements LoggerInterface
{

    private Sink $sink;
    private int $level;

    public static function default(string $level = LogLevel::INFO): BasicLogger
    {
        return new BasicLogger(new StdoutSink(), $level);
    }

    public function __construct(Sink $sink, string $level)
    {
        $this->sink = $sink;
        $this->level = self::normalizeLogLevel($level);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        if (self::normalizeLogLevel($level) > $this->level) {
            return;
        }

        $now = date_create()->format(\DateTimeInterface::ATOM);
        $message = count($context) == 0 ? $message : self::interpolate($message, $context);
        $this->sink->write("$now [{$level}]\t" . $message);
    }

    static private function interpolate(string|\Stringable $message, array $context = [])
    {
        // implementation based on: https://www.php-fig.org/psr/psr-3/
        $replace = [];
        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || $val instanceof \Stringable)) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        return strtr($message, $replace);
    }

    static private function normalizeLogLevel(string $level): int
    {
        switch ($level) {
            case LogLevel::EMERGENCY:
                return 1;
            case LogLevel::ALERT:
                return 2;
            case LogLevel::CRITICAL:
                return 3;
            case LogLevel::ERROR:
                return 4;
            case LogLevel::WARNING:
                return 5;
            case LogLevel::NOTICE:
                return 6;
            case LogLevel::INFO:
                return 7;
            case LogLevel::DEBUG:
                return 8;
            default:
                return 1000; // never log
        }
    }
}

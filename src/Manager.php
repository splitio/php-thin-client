<?php

namespace SplitIO\ThinSdk;

use SplitIO\ThinSdk\ManagerInterface;
use SplitIO\ThinSdk\SplitView;
use SplitIO\ThinSdk\Link\Consumer\Manager as LinkManager;
use Psr\Log\LoggerInterface;

class Manager implements ManagerInterface
{

    private /*LoggerInterface*/ $logger;
    private /*LinkManager*/ $link;

    public function __construct(LinkManager $lm, LoggerInterface $logger)
    {
        $this->link = $lm;
        $this->logger = $logger;
    }
    public function splitNames(): array
    {
        try {
            return $this->link->splitNames();
        } catch (\Exception $exc) {
            $this->logger->error("failed to fetch split names: " . $exc->getMessage());
        }
        return [];
    }

    public function split(string $name): ?SplitView
    {
        try {
            return $this->link->split($name);
        } catch (\Exception $exc) {
            $this->logger->error("failed to fetch split information: " . $exc->getMessage());
        }
        return null;
    }

    public function splits(): array
    {
        try {
            return $this->link->splits();
        } catch (\Exception $exc) {
            $this->logger->error("failed to fetch all splits information: " . $exc->getMessage());
            $this->logger->debug("full trace:" . $exc);
        }
        return [];
    }
}

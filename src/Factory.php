<?php

namespace SplitIO\ThinSdk;

use SplitIO\ThinSdk\Foundation\Logging;
use SplitIO\ThinSdk\Utils\EvalCache;

class Factory implements FactoryInterface
{
    private /*Config\Main*/ $config;
    private /*Link\Consumer\Manager*/ $linkManager;
    private /*\Psr\Log\LoggerInterface*/ $logger;

    private function __construct(Config\Main $config)
    {
        $this->config = $config;
        $this->logger = Logging\Helpers::getLogger($config->logging());
        $this->linkManager = Link\Consumer\Initializer::setup(
            Link\Protocol\Version::V1(),
            $config->transfer(),
            $config->serialization(),
            $config->utils(),
            $this->logger,
        );
    }

    public static function default(): Factory
    {
        return new Factory(Config\Main::default());
    }

    public static function withConfig(array $config): FactoryInterface
    {
        try {
            return new Factory(Config\Main::fromArray($config));
        } catch (\Exception $e) {

            try {
                $parsedConfig = Config\Main::fromArray($config);
                if ($parsedConfig->fallback()->disable()) { // fallback disabled, re-throw
                    throw new Fallback\FallbackDisabledException($e);
                }

                $logger = Logging\Helpers::getLogger($parsedConfig->logging());
                $logger->error(sprintf("error instantiating a factory with supplied config (%s). will return a fallback.", $e->getMessage()));
                $logger->debug($e);
                return new Fallback\GenericFallbackFactory($parsedConfig->fallback()->client(), $parsedConfig->fallback()->manager());
            } catch (Fallback\FallbackDisabledException $e) {
                // client wants to handle exception himself. re-throw it;
                throw $e->wrapped();
            } catch (\Exception $e) {
                // This branch is virtually unreachable (hence untestable) unless we introduce a bug.
                // it's basically a safeguard to prevent the customer app from crashing if we do.
                $logger = Logging\Helpers::getLogger(Config\Logging::default());
                $logger->error(sprintf("error parsing supplied factory config config (%s). will return a fallback.", $e->getMessage()));
                return new Fallback\GenericFallbackFactory(new Fallback\AlwaysControlClient(), new Fallback\AlwaysEmptyManager());
            }
        }
    }

    public function client(): ClientInterface
    {
        $uc = $this->config->utils();
        return new Client($this->linkManager, $this->logger, $uc->impressionListener(), EvalCache\Helpers::getCache($uc, $this->logger));
    }

    public function manager(): ManagerInterface
    {
        return new Manager($this->linkManager, $this->logger);
    }
};

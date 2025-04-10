<?php

namespace JustBetter\Sentry\Plugin;

use JustBetter\Sentry\Helper\Data;
use JustBetter\Sentry\Model\SentryLog;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Logger\Monolog;
use Monolog\JsonSerializableDateTimeImmutable;
use Monolog\Level;

class MonologPlugin extends Monolog
{
    /**
     * @psalm-param array<callable(array): array> $processors
     *
     * @param string $name The logging channel, a simple descriptive name that is attached to all log records
     * @param Data $sentryHelper
     * @param SentryLog $sentryLog
     * @param DeploymentConfig $deploymentConfig
     * @param \Monolog\Handler\HandlerInterface[] $handlers Optional stack of handlers, the first one in the array is
     *     called first, etc.
     * @param callable[] $processors Optional array of processors
     */
    public function __construct(
        $name,
        protected Data $sentryHelper,
        protected SentryLog $sentryLog,
        protected DeploymentConfig $deploymentConfig,
        array $handlers = [],
        array $processors = []
    ) {
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * Adds a log record to Sentry.
     *
     * @param int|Level $level
     * @param string $message
     * @param array $context
     * @param JsonSerializableDateTimeImmutable|null $datetime
     *
     * @return bool
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function addRecord(
        int|Level $level,
        string $message,
        array $context = [],
        JsonSerializableDateTimeImmutable|null $datetime = null
    ): bool {
        if ($this->deploymentConfig->isAvailable() && $this->sentryHelper->isActive()) {
            $this->sentryLog->send($message, $level, $context);
        }

        // @phpstan-ignore argument.type
        return parent::addRecord($level, $message, $context, $datetime);
    }
}

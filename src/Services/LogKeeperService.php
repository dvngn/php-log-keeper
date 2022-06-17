<?php

namespace Devengine\LogKeeper\Services;

use Carbon\CarbonImmutable;
use Devengine\LogKeeper\Exceptions\LogUtilException;
use Devengine\LogKeeper\Repositories\LogFileRepository;
use Devengine\LogKeeper\Support\LogUtil;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LogKeeperService implements LoggerAwareInterface
{
    public function __construct(protected readonly array $config,
                                protected readonly LogFileRepository $repository,
                                protected LoggerInterface $logger = new NullLogger())
    {
    }

    /**
     * @throws LogUtilException
     */
    public function work(): void
    {
        $today = CarbonImmutable::today();

        $this->logger->info("Log retention days: {$this->getRetentionDays()}");

        foreach ($this->repository as $filename) {
            $fileDate = LogUtil::parseDate($filename);

            if ($today->diffInDays($fileDate) > $this->getRetentionDays()) {
                $this->performFileArchiving($filename);

                $this->logger->info("Archived the log file.", [
                    'filename' => $filename,
                ]);
            }
        }

        $this->logger->info("Log archiving is finished.");
    }

    private function performFileArchiving(string $filename): void
    {
        $this->repository->compress($filename);
        $this->repository->delete($filename);
    }

    private function getRetentionDays(): int
    {
        return $this->config['days'] ?? 30;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
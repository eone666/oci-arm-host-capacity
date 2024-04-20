<?php

namespace Hitrov;

use Hitrov\Interfaces\TooManyRequestsWaiterInterface;

class TooManyRequestsWaiter implements TooManyRequestsWaiterInterface
{
    private const WAITER_FILENAME = 'too_many_requests_waiter.txt';
    private int $waitSeconds;
    private \Hitrov\Interfaces\NotifierInterface $notifier;
    public function __construct(int $waitTime)
    {
        $this->waitSeconds = $waitTime;
        $this->notifier = (function (): \Hitrov\Interfaces\NotifierInterface{
            /*
             * if you have own https://core.telegram.org/bots
             * and set TELEGRAM_BOT_API_KEY and your TELEGRAM_USER_ID in .env
             *
             * then you can get notified when script will succeed.
             * otherwise - don't mind OR develop you own NotifierInterface
             * to e.g. send SMS or email.
             */
            return new \Hitrov\Notification\Telegram();
        })();

        if ($this->fileExists()) {
            return;
        }

        file_put_contents($this->getFilename(), '');
    }

    public function isTooEarly(): bool
    {
        if (!$this->fileExists()) {
            return false;
        }

        return time() < (int) file_get_contents($this->getFilename());
    }

    public function isConfigured(): bool
    {
        return $this->waitSeconds > 0;
    }

    public function enable(): void
    {
        if ($this->notifier->isSupported()) {
            $this->notifier->notify('Too many requests! Wait ' . $this->waitSeconds . ' seconds!', true);
        }

        file_put_contents($this->getFilename(), time() + $this->waitSeconds);
    }

    public function remove(): void
    {
        if ($this->fileExists()) {
            unlink($this->getFilename());
        }
    }

    public function secondsRemaining(): int
    {
        if (!$this->fileExists()) {
            return 0;
        }

        return (int) file_get_contents($this->getFilename()) - time();
    }

    private function getFilename(): string
    {
        return sprintf('%s/%s', getcwd(), self::WAITER_FILENAME);
    }

    private function fileExists(): bool
    {
        return file_exists($this->getFilename());
    }
}

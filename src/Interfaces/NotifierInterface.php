<?php


namespace Hitrov\Interfaces;


interface NotifierInterface
{
    public function notify(string $message, bool $silent): array;
    public function isSupported(): bool;
}

<?php declare(strict_types=1);

namespace BenRowan\PhotoTool\Trait;

trait EventHookTrait
{
    private array $eventHooks = [];

    public function addHook(string $event, callable $observer): void
    {
        if (!isset($this->eventHooks[$event])) {
            $this->eventHooks[$event] = [];
        }

        $this->eventHooks[$event][] = $observer;
    }

    private function callHooks(string $event, array $context=[]): void
    {
        if (!isset($this->eventHooks[$event])) {
            return;
        }

        foreach ($this->eventHooks[$event] as $observer) {
            $observer($context);
        }
    }
}
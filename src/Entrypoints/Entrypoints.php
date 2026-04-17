<?php

namespace HeimrichHannot\EncoreBundle\Entrypoints;

class Entrypoints
{
    private array $entrypoints = [];
    private array $active;

    public function add(Entrypoint $entrypoint)
    {
        $this->entrypoints[$entrypoint->name] = $entrypoint;
        if ($entrypoint->active) {
            $this->active[$entrypoint->name] = $entrypoint;
        } else {
            unset($this->active[$entrypoint->name]);
        }
    }

    public function all(): array
    {
        return $this->entrypoints;
    }

    public function allActive(): array
    {
        return $this->active;
    }
}
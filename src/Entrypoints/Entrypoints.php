<?php

namespace HeimrichHannot\EncoreBundle\Entrypoints;

class Entrypoints
{
    /**
     * @var Entrypoint[]
     */
    private array $entrypoints = [];
    /**
     * @var Entrypoint[]
     */
    private array $active = [];

    public function add(Entrypoint $entrypoint)
    {
        $this->entrypoints[$entrypoint->name] = $entrypoint;
        if ($entrypoint->active) {
            $this->active[$entrypoint->name] = $entrypoint;
        } else {
            unset($this->active[$entrypoint->name]);
        }
    }

    /**
     * @return Entrypoint[]
     */
    public function all(): array
    {
        return $this->entrypoints;
    }

    /**
     * @return Entrypoint[]
     */
    public function allActive(): array
    {
        return $this->active;
    }
}
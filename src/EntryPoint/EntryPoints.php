<?php

namespace HeimrichHannot\EncoreBundle\EntryPoint;

class EntryPoints
{
    /**
     * @var EntryPoint[]
     */
    private array $entryPoints = [];
    /**
     * @var EntryPoint[]
     */
    private array $active = [];

    public function add(EntryPoint $entryPoint): void
    {
        $this->entryPoints[$entryPoint->name] = $entryPoint;
        if ($entryPoint->active) {
            $this->active[$entryPoint->name] = $entryPoint;
        } else {
            unset($this->active[$entryPoint->name]);
        }
    }

    /**
     * @return EntryPoint[]
     */
    public function all(): array
    {
        return $this->entryPoints;
    }

    /**
     * @return EntryPoint[]
     */
    public function allActive(): array
    {
        return $this->active;
    }
}

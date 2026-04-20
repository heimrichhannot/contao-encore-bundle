<?php

namespace HeimrichHannot\EncoreBundle\Dca;

class EncoreEntriesSelectField
{
    public const NAME_DEFAULT = 'encoreEntries';

    protected static array $tables = [];

    /**
     * Register a dca to have an author field and update logic added.
     */
    public static function register(string $table): EncoreEntriesSelectFieldOptions
    {
        $config = new EncoreEntriesSelectFieldOptions($table);

        static::$tables[$table] = $config;

        return $config;
    }

    /**
     * @return array<EncoreEntriesSelectFieldOptions>
     */
    public static function getRegistrations(): array
    {
        return static::$tables;
    }
}

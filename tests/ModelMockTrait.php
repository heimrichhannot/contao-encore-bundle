<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\EncoreBundle\Test;

use PHPUnit\Framework\MockObject\MockObject;

trait ModelMockTrait
{
    /**
     * Mocks a class with magic properties.
     */
    protected function mockModelObject(string $class, array $properties = []): MockObject
    {
        $mock = $this->createMock($class);
        $mock
            ->method('__get')
            ->willReturnCallback(
                static function (string $key) use (&$properties) {
                    return $properties[$key] ?? null;
                }
            )
        ;

        if (\in_array('__set', get_class_methods($class), true)) {
            $mock
                ->method('__set')
                ->willReturnCallback(
                    static function (string $key, $value) use (&$properties) {
                        $properties[$key] = $value;
                    }
                )
            ;
        }

        if (\in_array('__isset', get_class_methods($class), true)) {
            $mock
                ->method('__isset')
                ->willReturnCallback(
                    static function (string $key) use (&$properties) {
                        return isset($properties[$key]);
                    }
                )
            ;
        }

        if (\in_array('row', get_class_methods($class), true)) {
            $mock
                ->method('row')
                ->willReturnCallback(
                    static function () use (&$properties) {
                        return $properties;
                    }
                )
            ;
        }

        return $mock;
    }
}

<?php

/**
 * The yundun admin v3 project.
 *
 * @author Qingshan Luo <shanshan.lqs@gmail.com>
 */

namespace Service;

use RuntimeException;

class Factory
{
    /**
     * The service instances.
     *
     * @var array
     */
    protected static $services = [];

    /**
     * The service aliases.
     *
     * @var array
     */
    protected static $aliases = [];

    /**
     * Build a service instance.
     *
     * @param  string             $name The service alias or class name.
     * @throws RuntimeException
     * @return Service\Service
     */
    public static function build($name)
    {
        $abstract = isset(static::$aliases[$name]) ? static::$aliases[$name] : $name;

        if (!isset(static::$services[$abstract])) {
            if (!class_exists($abstract, true)) {
                throw RuntimeException("The service {$abstract} does not exist.");
            }

            static::$services[$abstract] = new $abstract();
        }

        return static::$services[$abstract];
    }

    /**
     * Bind service alias.
     *
     * @param  string $abstract The service class name.
     * @param  string $alias    The service alias.
     * @return void
     */
    public static function alias($abstract, $alias)
    {
        static::$aliases[$alias] = $abstract;
    }
}

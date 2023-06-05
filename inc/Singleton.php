<?php

namespace WpsyncWebspark\Inc;

abstract class Singleton
{
    static protected ?self $_instance = null;

    protected function __construct()
    {

    }

    public static function get_instance(): static
    {
        if (!self::$_instance) {
            self::$_instance = new static();
        }

        return self::$_instance;
    }
}
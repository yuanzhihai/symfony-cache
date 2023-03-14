<?php
declare( strict_types = 1 );

namespace yzh52521\cache;

/**
 * @method static get( $key,$default = null )
 * @method static set( $key,$value,$ttl = null )
 * @method static delete( string $key )
 * @method static clear()
 * @method static setMultiple( $values,$ttl = null )
 * @method static deleteMultiple( $keys )
 * @method static has( $key )
 * @method static store( $name = null )
 *
 */
class Cache
{
    protected static $_instance = null;

    public static function instance()
    {
        if (!static::$_instance) {
            static::$_instance = new CacheManager();
        }
        return static::$_instance;
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($method,$arguments)
    {
        return static::instance()->{$method}( ... $arguments );
    }
}
<?php
declare( strict_types = 1 );

namespace yzh52521\cache;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\DoctrineDbalAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Contracts\Cache\CacheInterface;

class CacheManager
{
    protected $config;
    /**
     * The array of resolved cache stores.
     *
     * @var array
     */
    protected $stores = [];

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];


    public function __construct()
    {
        $this->config = config( 'cache' );
    }

    public function store($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();
        return $this->stores[$name] = $this->getStoreName( $name );
    }

    public function driver($driver = null)
    {
        return $this->store( $driver );
    }

    protected function getStoreName($name)
    {
        return $this->stores[$name] ?? $this->resolve( $name );
    }

    protected function getConfig($name)
    {
        if (!is_null( $name ) && $name !== 'null') {
            return $this->config['stores'][$name];
        }
        return ['driver' => 'null'];
    }

    public function getDefaultDriver()
    {
        return config( 'cache.default' );
    }

    protected function resolve($name)
    {
        $config = $this->getConfig( $name );

        if (is_null( $config )) {
            throw new \Exception( "Cache store [{$name}] is not defined." );
        }

        if (isset( $this->customCreators[$config['driver']] )) {
            return $this->callCustomCreator( $config );
        } else {
            $driverMethod = 'create'.ucfirst( $config['driver'] ).'Driver';

            if (method_exists( $this,$driverMethod )) {
                return $this->{$driverMethod}( $config );
            } else {
                throw new \Exception( "Driver [{$config['driver']}] is not supported." );
            }
        }
    }

    public function forgetDriver($name = null)
    {
        $name ??= $this->getDefaultDriver();

        foreach ( (array)$name as $cacheName ) {
            if (isset( $this->stores[$cacheName] )) {
                unset( $this->stores[$cacheName] );
            }
        }

        return $this;
    }

    /**
     * Create an instance of the Apcu cache driver.
     *
     * @param array $config
     * @return Psr16Cache
     * @throws \Symfony\Component\Cache\Exception\CacheException
     */
    protected function createApcuDriver(array $config): Psr16Cache
    {
        return $this->repository( new ApcuAdapter( $config['namespace'],$config['default_lifetime'] ?? 0,$config['version'] ) );
    }

    /**
     * Create an instance of the file cache driver.
     *
     * @param array $config
     * @return Psr16Cache
     */
    protected function createFileDriver(array $config): Psr16Cache
    {
        return $this->repository( new FilesystemAdapter( $config['namespace'],$config['default_lifetime'] ?? 0,$config['path'] ) );
    }

    /**
     * Create an instance of the Memcached cache driver.
     *
     * @param array $config
     * @return Psr16Cache
     * @throws \Symfony\Component\Cache\Exception\CacheException
     */
    protected function createMemcachedDriver(array $config): Psr16Cache
    {
        $client = MemcachedAdapter::createConnection(
            $config['servers'],$config['options'] ?? []
        );
        return $this->repository( new MemcachedAdapter( $client,$config['namespace'],$config['default_lifetime'] ?? 0 ) );
    }

    /**
     * Create an instance of the Redis cache driver.
     *
     * @param array $config
     * @return Psr16Cache
     */
    protected function createRedisDriver(array $config): Psr16Cache
    {
        return $this->repository( new RedisAdapter( $config['connection'],$config['namespace'],$config['default_lifetime'] ?? 0 ) );
    }

    /**
     *  Create a instance of the Array cache driver
     * @param array $config
     * @return Psr16Cache
     */
    protected function createArrayDriver(array $config): Psr16Cache
    {
        return $this->repository( new ArrayAdapter( $config['default_lifetime'],$config['serialized'],$config['max_lifetime'],$config['max_items'] ) );
    }

    /**
     * Create a instance of the Database cache driver
     * @param array $config
     * @return Psr16Cache
     * @throws \Doctrine\DBAL\Exception
     */
    protected function createDatabaseDriver(array $config): Psr16Cache
    {
        if ($config['connection'] !== null) {
            $dsnParser        = new DsnParser();
            $connectionParams = $dsnParser->parse( $config['connection'] );
            $conn             = DriverManager::getConnection( $connectionParams );
        } else {
            $dba              = config( 'database.connections.mysql' );
            $connectionParams = [
                'dbname'   => $dba['database'],
                'user'     => $dba['username'],
                'password' => $dba['password'],
                'host'     => $dba['host'],
                'driver'   => 'pdo_mysql',
            ];
            $conn             = DriverManager::getConnection( $connectionParams );
        }
        return $this->repository( new DoctrineDbalAdapter( $conn,$config['namespace'],$config['default_lifetime'] ?? 0,$config['options'] ) );
    }


    public function purge($name = null)
    {
        $name = $name ?? $this->getDefaultDriver();

        unset( $this->stores[$name] );
    }

    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]( $this,$config );
    }

    /**
     * @param AbstractAdapter|CacheInterface $store
     * @return Psr16Cache
     */
    public function repository($store): Psr16Cache
    {
        return new Psr16Cache( $store );
    }


    public function __call(string $method,array $parameters)
    {
        return $this->store()->$method( ...$parameters );
    }
}
<?php
declare ( strict_types = 1 );

namespace yzh52521\cache;

class Install
{
    const WEBMAN_PLUGIN = true;

    /**
     * @var array
     */
    protected static $pathRelation = [];

    /**
     * Install
     * @return void
     */
    public static function install()
    {
        $cacche_file = config_path().'/cache.php';
        if (!is_file( $cacche_file )) {
            copy( __DIR__.'/config.php',$cacche_file );
        }
        static::installByRelation();
    }

    /**
     * Uninstall
     * @return void
     */
    public static function uninstall()
    {
        $cacche_file = config_path().'/cache.php';
        if (is_file( $cacche_file )) {
            unlink( $cacche_file );
        }
        self::uninstallByRelation();
    }

    /**
     * installByRelation
     * @return void
     */
    public static function installByRelation()
    {
        foreach ( static::$pathRelation as $source => $dest ) {
            if ($pos = strrpos( $dest,'/' )) {
                $parent_dir = base_path().'/'.substr( $dest,0,$pos );
                if (!is_dir( $parent_dir )) {
                    mkdir( $parent_dir,0777,true );
                }
            }
            copy_dir( __DIR__."/$source",base_path()."/$dest" );
            echo "Create $dest";
        }
    }

    /**
     * uninstallByRelation
     * @return void
     */
    public static function uninstallByRelation()
    {
        foreach ( static::$pathRelation as $source => $dest ) {
            $path = base_path()."/$dest";
            if (!is_dir( $path ) && !is_file( $path )) {
                continue;
            }
            echo "Remove $dest";
            if (is_file( $path ) || is_link( $path )) {
                unlink( $path );
                continue;
            }
            remove_dir( $path );
        }
    }

}

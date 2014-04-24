<?php

namespace Jasny\DBVC\VCS;

/**
 * Don't use a VCS for DBVC
 */
class None
{
    /**
     * Never use this by default
     * 
     * @return boolean
     */
    public static function isUsed()
    {
        return false;
    }
    
    /**
     * Get a list with update files in alpabatic order.
     * 
     * @param string $dir
     * @return array
     */
    public static function getUpdateFiles($dir)
    {
        if (!file_exists($dir)) return array();
        
        $updates = array();
        $files = scandir($dir, SCANDIR_SORT_NONE);
        foreach ($files as $file) {
            if ($file[0] === '.' || !is_file($dir . DIRECTORY_SEPARATOR . $file)) continue;
            $updates[pathinfo($file, PATHINFO_FILENAME)] = $file;
        }
        
        ksort($updates, SORT_NATURAL);
        return $updates;
    }
}

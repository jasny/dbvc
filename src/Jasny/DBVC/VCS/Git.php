<?php

namespace Jasny\DBVC\VCS;

/**
 * Description of Git
 */
class Git
{
    /**
     * Path to git binary
     * @var string
     */
    public static $cmd = 'git';

    
    /**
     * Execute a git command using the binary
     * 
     * @param string $command
     * @param array  $args
     * @return string
     */
    protected static function git($command, array $args = array())
    {
        foreach ($args as $key=>$value) {
            $command = str_replace('$' . $key, escapeshellarg($value), $command);
        }
        
        $command = escapeshellcmd(self::$cmd) . " $command";
        
        $process = proc_open($command, [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);
        if (!is_resource($process)) throw new \Exception("Git command failed");
        
        fclose($pipes[0]);
        
        $out = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);

        $return_value = proc_close($process);
        if ($return_value > 0) throw new \Exception("Git command failed. " . trim($err));
        
        return trim($out);
    }
    
    /**
     * Get project base dir
     * 
     * @return string
     */
    public static function isUsed()
    {
        try {
            static::git('rev-parse');
        } catch (\Exception $e) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * Get a list with update files in the correct order.
     * 
     * @param string $dir
     * @return array
     */
    public static function getUpdateFiles($dir)
    {
        if (!file_exists($dir)) return [];
        
        $out = static::git('log -c --no-merges --pretty="format:" --name-status -p $DIR', ['DIR'=>$dir]);
        
        $updates = array();
        foreach (explode("\n", $out) as $line) {
            list($action, $basename) = explode("\t", $line);
            $update = pathinfo($basename, PATHINFO_FILENAME);
            
            if ($action === 'A') {
                $file = $dir . DIRECTORY_SEPARATOR . $basename;
                if (is_file($file) && !in_array($update, $updates)) $updates[$update] = $file;
            } elseif ($action === 'D') {
                $key = array_search($update, $updates);
                if ($key !== false) unset($updates[$key]);
            }
        }
        
        return $updates;
    }
}

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
     * Check if GIT is used as VCS for this project
     * 
     * @return boolean
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
            list($action, $file) = explode("\t", $line) + [null, null];
            $update = pathinfo($file, PATHINFO_FILENAME);
            
            if ($action === 'A') {
                if (is_file($file) && !in_array($update, $updates)) $updates[$update] = basename($file);
            } elseif ($action === 'D') {
                $key = array_search($update, $updates);
                if ($key !== false) unset($updates[$key]);
            }
        }
        
        return array_reverse($updates);
    }
}

<?php
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

/**
 * Cache
 *
 * @author Brett O'Donnell <cornernote@gmail.com>
 * @copyright 2013 Mr PHP
 * @link https://github.com/cornernote/php-cache
 * @license http://www.gnu.org/copyleft/gpl.html
 */
class Cache
{

    /**
     * @var string
     */
    static public $path = '/var/cache';

    /**
     * @var string
     */
    static private $_prefix;

    /**
     * @var array
     */
    static private $_cache;

    /**
     * Get a cached element
     *
     * @param $key
     * @param $expires
     * @return mixed
     */
    static public function get($key, $expires = null)
    {
        if (self::$_cache[$key])
            return self::$_cache[$key];

        $file = self::getFilename($key);
        if (!file_exists($file))
            return null;
        $result = unserialize(file_get_contents($file));
        if (strtotime($expires) && !empty($result['time']) && $result['time'] < strtotime($expires)) {
            @unlink($file);
            return null;
        }
        if (!isset($result['data']))
            return null;
        return self::$_cache[$key] = $result['data'];
    }

    /**
     * Set a cached element
     *
     * @param $key
     * @param $value
     * @param $expires
     */
    static public function set($key, $value, $expires = null)
    {
        $file = self::getFilename($key);
        if (!file_exists(dirname($file)))
            mkdir(dirname($file), 0700, true);
        file_put_contents($file, serialize(array('data' => $value, 'time' => strtotime($expires))));
        return $value;
    }

    /**
     * Clear cache for this model
     */
    public function clear()
    {
        self::getPrefix(true);
    }

    /**
     * @param $key
     * @return string
     */
    private function getFilename($key)
    {
        $md5 = md5($key);
        $path = self::$path . DS . self::getPrefix();
        $path .= DS . substr($md5, 0, 1) . DS . substr($md5, 0, 2) . DS . substr($md5, 0, 3);
        return $path . DS . $key;
    }

    /**
     * Get the cache prefix for this model
     *
     * @param bool $removeOldKey
     * @return string
     */
    private function getPrefix($removeOldKey = false)
    {
        if (self::$_prefix)
            return self::$_prefix;

        if ($removeOldKey) {
            $prefix = time();
            file_put_contents(self::$path . DS . 'prefix', $prefix);
        }
        else {
            $prefix = file_get_contents(self::$path . DS . 'prefix');
        }
        return self::$_prefix = $prefix . '.';
    }

}
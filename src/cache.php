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
    public $path = '/var/cache';

    /**
     * @var string
     */
    private $_prefix;

    /**
     * @var array
     */
    private $_cache;

    /**
     * Get a cached element
     *
     * @param $key
     * @param $expires
     * @return mixed
     */
    public function get($key, $expires = null)
    {
        if ($this->_cache[$key])
            return $this->_cache[$key];

        $file = $this->getFilename($key);
        if (!file_exists($file))
            return null;
        $result = unserialize(file_get_contents($file));
        if (strtotime($expires) && !empty($result['time']) && $result['time'] < strtotime($expires)) {
            @unlink($file);
            return null;
        }
        if (!isset($result['data']))
            return null;
        return $this->_cache[$key] = $result['data'];
    }

    /**
     * Set a cached element
     *
     * @param $key
     * @param $value
     * @param $expires
     */
    public function set($key, $value, $expires = null)
    {
        $file = $this->getFilename($key);
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
        $this->getPrefix(true);
    }

    /**
     * @param $key
     * @return string
     */
    private function getFilename($key)
    {
        $md5 = md5($key);
        $path = $this->path . DS . $this->getPrefix();
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
        if ($this->_prefix)
            return $this->_prefix;

        if ($removeOldKey) {
            $prefix = time();
            file_put_contents($this->path . DS . 'prefix', $prefix);
        }
        else {
            $prefix = file_get_contents($this->path . DS . 'prefix');
        }
        return $this->_prefix = $prefix . '.';
    }

}
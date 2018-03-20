<?php namespace XoopsModules\Newbb;

/*
     You may not change or alter any portion of this comment or credits
     of supporting developers from this source code or any supporting source code
     which is considered copyrighted (c) material of the original comment or credit authors.

     This program is distributed in the hope that it will be useful,
     but WITHOUT ANY WARRANTY; without even the implied warranty of
     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
    */
/**
 * NewBB module for xoops
 *
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         GPL 2.0 or later
 * @package         newbb
 * @since           5.0.0
 * @author          XOOPS Development Team <name@site.com> - <https://xoops.org>
 */

//defined('XOOPS_ROOT_PATH') || die('Restricted access');

/**
 * Class Helper
 */
class Helper extends \Xmf\Module\Helper
{
    public $debug;

    /**
     * @internal param $debug
     * @param bool $debug
     */
    protected function __construct($debug = false)
    {
        $this->debug   = $debug;
        parent::__construct(basename(dirname(__DIR__)));
    }

    /**
     * @param bool $debug
     *
     * @return \XoopsModules\Newbb\Helper
     */
    public static function getInstance($debug = false)
    {
        static $instance;
        if (null === $instance) {
            $instance = new static($debug);
        }

        return $instance;
    }

    /**
     * @param null|string $name
     * @param null|string $value
     *
     * @return mixed
     */
    public function setConfig($name = null, $value = null)
    {
        if (null === $this->configs) {
            $this->initConfig();
        }
        $this->configs[$name] = $value;
        $this->addLog("Setting config '{$name}' : " . $this->configs[$name]);

        return $this->configs[$name];
    }

    /**
     * @return string
     */
    public function getDirname()
    {
        return $this->dirname;
    }

    /**
     * Get an Object Handler
     *
     * @param string $name name of handler to load
     *
     * @return bool|\XoopsObjectHandler|\XoopsPersistableObjectHandler
     */
    public function getHandler($name)
    {
        $ret   = false;
        $db    = \XoopsDatabaseFactory::getDatabaseConnection();
        $class = '\\XoopsModules\\' . ucfirst(strtolower(basename(dirname(__DIR__)))) . '\\' . $name . 'Handler';
        $ret   = new $class($db);
        return $ret;
    }
}

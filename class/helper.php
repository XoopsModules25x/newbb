<?php

//namespace Xoopsmodules\Newbb;

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
 * @copyright       XOOPS Project (http://xoops.org)
 * @license         GPL 2.0 or later
 * @package         newbb
 * @since           5.0.0
 * @author          XOOPS Development Team <name@site.com> - <http://xoops.org>
 */

//defined('XOOPS_ROOT_PATH') || die('Restricted access');

/**
 * Class Helper
 */
class Newbb extends \Xmf\Module\Helper
{
    public $debugArray = [];

    /**
     * @internal param $debug
     */
    protected function __construct()
    {
        //        $this->debug   = $debug;
        $this->dirname = basename(dirname(__DIR__));
    }

    /**
     * @param bool $debug
     *
     * @return Publisher
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

}

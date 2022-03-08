<?php declare(strict_types=1);

namespace XoopsModules\Newbb;

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
 * @license         GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @since           5.0.0
 * @author          XOOPS Development Team <https://xoops.org>
 */

/**
 * Class Helper
 */
class Helper extends \Xmf\Module\Helper
{
    public $debug;

    /**
     * @param bool $debug
     */
    public function __construct($debug = false)
    {
        $this->debug   = $debug;
        $moduleDirName = \basename(\dirname(__DIR__));
        parent::__construct($moduleDirName);
    }

    /**
     * @param bool $debug
     *
     * @return \XoopsModules\Newbb\Helper
     */
    public static function getInstance(bool $debug = false): self
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
     * @return string|null
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
        $ret = false;

        $class = __NAMESPACE__ . '\\' . \ucfirst($name) . 'Handler';
        if (!\class_exists($class)) {
            throw new \RuntimeException("Class '$class' not found");
        }
        /** @var \XoopsMySQLDatabase $db */
        $db     = \XoopsDatabaseFactory::getDatabaseConnection();
        $helper = self::getInstance();
        $ret    = new $class($db, $helper);
        $this->addLog("Getting handler '{$name}'");

        return $ret;
    }
}

<?php namespace XoopsModules\Newbb;

use XoopsModules\Newbb;
use XoopsModules\Newbb\Common;

/**
 * Class Utility
 */
class Utility
{
    use Common\VersionChecks; //checkVerXoops, checkVerPhp Traits

    use Common\ServerStats; // getServerStats Trait

    use Common\FilesManagement; // Files Management Trait

    //--------------- Custom module methods -----------------------------

    /**
     * Verify that a mysql table exists
     *
     * @package       News
     * @author        Hervé Thouzard (http://www.herve-thouzard.com)
     * @copyright (c) Hervé Thouzard
     * @param $tablename
     * @return bool
     */
    public function tableExists($tablename)
    {
        global $xoopsDB;
        /** @var \XoopsMySQLDatabase $xoopsDB */
        $result = $xoopsDB->queryF("SHOW TABLES LIKE '$tablename'");

        return ($xoopsDB->getRowsNum($result) > 0);
    }

    /**
     * Verify that a field exists inside a mysql table
     *
     * @package       News
     * @author        Hervé Thouzard (http://www.herve-thouzard.com)
     * @copyright (c) Hervé Thouzard
     * @param $fieldname
     * @param $table
     * @return bool
     */
    public function fieldExists($fieldname, $table)
    {
        global $xoopsDB;
        $result = $xoopsDB->queryF("SHOW COLUMNS FROM   $table LIKE '$fieldname'");

        return ($xoopsDB->getRowsNum($result) > 0);
    }

    /**
     * Add a field to a mysql table
     *
     * @package       News
     * @author        Hervé Thouzard (http://www.herve-thouzard.com)
     * @copyright (c) Hervé Thouzard
     * @param $field
     * @param $table
     * @return bool|\mysqli_result
     */
    public function addField($field, $table)
    {
        global $xoopsDB;
        $result = $xoopsDB->queryF('ALTER TABLE ' . $table . " ADD $field");

        return $result;
    }

    /**
     * Function responsible for checking if a directory exists, we can also write in and create an index.html file
     *
     * @param string $folder Le chemin complet du répertoire à vérifier
     *
     * @return void
     */
    public static function prepareFolder($folder)
    {
        try {
            if (!@mkdir($folder) && !is_dir($folder)) {
                throw new \RuntimeException(sprintf('Unable to create the %s directory', $folder));
            } else {
                file_put_contents($folder . '/index.html', '<script>history.go(-1);</script>');
            }
        } catch (\Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n", '<br>';
        }
    }

    public static function cleanCache()
    {
        $cacheHelper = new \Xmf\Module\Helper\Cache('newbb');
        if (method_exists($cacheHelper, 'clear')) {
            $cacheHelper->clear();
            return;
        }
        // for 2.5 systems, clear everything
        require_once XOOPS_ROOT_PATH . '/modules/system/class/maintenance.php';
        $maintenance = new \SystemMaintenance();
        $cacheList   = [
            3, // xoops_cache
        ];
        $maintenance->CleanCache($cacheList);
        xoops_setActiveModules();
    }

    /**
     * Checks if a user is admin of NewBB
     *
     * @return boolean
     */
    public static function userIsAdmin()
    {
        $helper = Newbb\Helper::getInstance();

        static $newbbIsAdmin;

        if (isset($newbbIsAdmin)) {
            return $newbbIsAdmin;
        }

        if (!$GLOBALS['xoopsUser']) {
            $newbbIsAdmin = false;
        } else {
            $newbbIsAdmin = $GLOBALS['xoopsUser']->isAdmin($helper->getModule()->getVar('mid'));
        }

        return $newbbIsAdmin;
    }
}

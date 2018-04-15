<?php
/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright    XOOPS Project https://xoops.org/
 * @license      GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package
 * @since
 * @author       XOOPS Development Team
 */

use XoopsModules\Newbb;

//require_once __DIR__ . '/setup.php';

/**
 *
 * Prepares system prior to attempting to install module
 * @param XoopsModule $module {@link XoopsModule}
 *
 * @return bool true if ready to install, false if not
 */
function xoops_module_pre_install_newbb(\XoopsModule $module)
{
    include  dirname(__DIR__) . '/preloads/autoloader.php';
    /** @var Newbb\Utility $utility */
    $utility      = new \XoopsModules\Newbb\Utility();
    $xoopsSuccess = $utility::checkVerXoops($module);
    $phpSuccess   = $utility::checkVerPhp($module);

    if (false !== $xoopsSuccess && false !== $phpSuccess) {
        $moduleTables =& $module->getInfo('tables');
        foreach ($moduleTables as $table) {
            $GLOBALS['xoopsDB']->queryF('DROP TABLE IF EXISTS ' . $GLOBALS['xoopsDB']->prefix($table) . ';');
        }
    }

    return $xoopsSuccess && $phpSuccess;
}

/**
 *
 * Performs tasks required during installation of the module
 * @param XoopsModule $module {@link XoopsModule}
 *
 * @return bool true if installation successful, false if not
 */
function xoops_module_install_newbb(\XoopsModule $module)
{
    require_once  dirname(dirname(dirname(__DIR__))) . '/mainfile.php';
    require_once  dirname(__DIR__) . '/include/config.php';

    $moduleDirName = basename(dirname(__DIR__));

    $helper       = Newbb\Helper::getInstance();
    $utility      = new Newbb\Utility();
    $configurator = new Newbb\Common\Configurator();
    // Load language files
    $helper->loadLanguage('admin');
    $helper->loadLanguage('modinfo');

    // default Permission Settings ----------------------
    global $xoopsModule;
    $moduleId     = $xoopsModule->getVar('mid');
    $moduleId2    = $helper->getModule()->mid();
    $grouppermHandler = xoops_getHandler('groupperm');
    // access rights ------------------------------------------
    $grouppermHandler->addRight($moduleDirName . '_approve', 1, XOOPS_GROUP_ADMIN, $moduleId);
    $grouppermHandler->addRight($moduleDirName . '_submit', 1, XOOPS_GROUP_ADMIN, $moduleId);
    $grouppermHandler->addRight($moduleDirName . '_view', 1, XOOPS_GROUP_ADMIN, $moduleId);
    $grouppermHandler->addRight($moduleDirName . '_view', 1, XOOPS_GROUP_USERS, $moduleId);
    $grouppermHandler->addRight($moduleDirName . '_view', 1, XOOPS_GROUP_ANONYMOUS, $moduleId);

    //  ---  CREATE FOLDERS ---------------
    if (count($configurator->uploadFolders) > 0) {
        //    foreach (array_keys($GLOBALS['uploadFolders']) as $i) {
        foreach (array_keys($configurator->uploadFolders) as $i) {
            $utility::createFolder($configurator->uploadFolders[$i]);
        }
    }

    //  ---  COPY blank.png FILES ---------------
    if (count($configurator->copyBlankFiles) > 0) {
        $file =  dirname(__DIR__) . '/assets/images/blank.png';
        foreach (array_keys($configurator->copyBlankFiles) as $i) {
            $dest = $configurator->copyBlankFiles[$i] . '/blank.png';
            $utility::copyFile($file, $dest);
        }
    }
    //delete .html entries from the tpl table
    $sql = 'DELETE FROM ' . $xoopsDB->prefix('tplfile') . " WHERE `tpl_module` = '" . $xoopsModule->getVar('dirname', 'n') . "' AND `tpl_file` LIKE '%.html%'";
    $xoopsDB->queryF($sql);

    return true;
}

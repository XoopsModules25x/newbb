<?php declare(strict_types=1);

/**
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 *
 * @category        Module
 * @author          XOOPS Development Team
 * @copyright       XOOPS Project
 * @link            https://xoops.org
 * @license         GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 */

use Xmf\Module\Admin;
use Xmf\Request;
use XoopsModules\Newbb\{
    Common\Blocksadmin,
    Helper
};

/** @var Admin $adminObject */
/** @var Helper $helper */

require_once __DIR__ . '/admin_header.php';
xoops_cp_header();

$moduleDirName      = $helper->getDirname();
$moduleDirNameUpper = \mb_strtoupper($moduleDirName);

/** @var \XoopsMySQLDatabase $xoopsDB */
$xoopsDB     = \XoopsDatabaseFactory::getDatabaseConnection();
$blocksadmin = new Blocksadmin($xoopsDB, $helper);

$xoopsModule = XoopsModule::getByDirname($moduleDirName);

if (!is_object($GLOBALS['xoopsUser']) || !is_object($xoopsModule)
    || !$GLOBALS['xoopsUser']->isAdmin($xoopsModule->mid())) {
    exit(constant('CO_' . $moduleDirNameUpper . '_' . 'ERROR403'));
}
if ($GLOBALS['xoopsUser']->isAdmin($xoopsModule->mid())) {
    require_once XOOPS_ROOT_PATH . '/class/xoopsblock.php';

    $op = Request::getCmd('op', 'list');
    if (!empty($_POST)) {
        $ok             = Request::getInt('ok', 0, 'POST');
        $confirm_submit = Request::getCmd('confirm_submit', '', 'POST');
        $submit         = Request::getString('submit', '', 'POST');
        $bside          = Request::getString('bside', '0', 'POST');
        $bweight        = Request::getString('bweight', '0', 'POST');
        $bvisible       = Request::getString('bvisible', '0', 'POST');
        $bmodule        = Request::getArray('bmodule', [], 'POST');
        $btitle         = Request::getString('btitle', '', 'POST');
        $bcachetime     = Request::getString('bcachetime', '0', 'POST');
        $groups         = Request::getArray('groups', [], 'POST');
        $options        = Request::getArray('options', [], 'POST');
        $submitblock    = Request::getString('submitblock', '', 'POST');
        $fct            = Request::getString('fct', '', 'POST');
        $title          = Request::getString('title', '', 'POST');
        $side           = Request::getString('side', '0', 'POST');
        $weight         = Request::getString('weight', '0', 'POST');
        $visible        = Request::getString('visible', '0', 'POST');
    }

    if ('list' === $op) {
        //        xoops_cp_header();
        $blocksadmin->listBlocks();
        require_once __DIR__ . '/admin_footer.php';
        exit();
    }

    if (\in_array($op, ['edit', 'edit_ok', 'delete', 'delete_ok', 'clone', 'clone_ok'])) {
        $bid = Request::getInt('bid', 0);
        $ok  = Request::getInt('ok', 0);

        if ('clone' === $op) {
            $blocksadmin->cloneBlock($bid);
        }

        if ('delete' === $op) {
            if (1 === $ok) {
                //            if (!$GLOBALS['xoopsSecurity']->check()) {
                //                redirect_header($helper->url('admin/blocksadmin.php'), 3, implode(',', $GLOBALS['xoopsSecurity']->getErrors()));
                //            }
                $blocksadmin->deleteBlock($bid);
            } else {
                //            xoops_cp_header();
                xoops_confirm(['ok' => 1, 'op' => 'delete', 'bid' => $bid], 'blocksadmin.php', constant('CO_' . $moduleDirNameUpper . '_' . 'DELETE_BLOCK_CONFIRM'), constant('CO_' . $moduleDirNameUpper . '_' . 'CONFIRM'), true);
                xoops_cp_footer();
            }
        }

        if ('edit' === $op) {
            $blocksadmin->editBlock($bid);
        }

        if ('edit_ok' === $op) {
            $blocksadmin->updateBlock($bid, $btitle, $bside, $bweight, $bvisible, $bcachetime, $bmodule, $options, $groups);
        }

        if ('clone_ok' === $op) {
            $blocksadmin->isBlockCloned($bid, $bside, $bweight, $bvisible, $bcachetime, $bmodule, $options, $groups);
        }
    }

    if ('order' === $op) {
        $bid = Request::getArray('bid', []);

        $title      = Request::getArray('title', [], 'POST');
        $side       = Request::getArray('side', [], 'POST');
        $weight     = Request::getArray('weight', [], 'POST');
        $visible    = Request::getArray('visible', [], 'POST');
        $bcachetime = Request::getArray('bcachetime', [], 'POST');
        $bmodule    = Request::getArray('bmodule', [], 'POST');//mb

        $oldtitle      = Request::getArray('oldtitle', [], 'POST');
        $oldside       = Request::getArray('oldside', [], 'POST');
        $oldweight     = Request::getArray('oldweight', [], 'POST');
        $oldvisible    = Request::getArray('oldvisible', [], 'POST');
        $oldgroups     = Request::getArray('oldgroups', [], 'POST');
        $oldbcachetime = Request::getArray('oldcachetime', [], 'POST');
        $oldbmodule    = Request::getArray('oldbmodule', [], 'POST');//mb

        $blocksadmin->orderBlock(
            $bid,
            $oldtitle,
            $oldside,
            $oldweight,
            $oldvisible,
            $oldgroups,
            $oldbcachetime,
            $oldbmodule,
            $title,
            $weight,
            $visible,
            $side,
            $bcachetime,
            $groups,
            $bmodule
        );
    }
} else {
    echo constant('CO_' . $moduleDirNameUpper . '_' . 'ERROR403');
}

require_once __DIR__ . '/admin_footer.php';

<?php declare(strict_types=1);
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
 * @license      GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author       Kazumi Ono (AKA onokazu) https://www.myweb.ne.jp/, https://xoops.org/, https://jp.xoops.org/
 * @author       XOOPS Development Team
 */

use Xmf\Module\Helper\Cache;
use Xmf\Request;
use XoopsModules\Newbb\{
    Helper,
    Utility,
    CategoryHandler
};

/** @var Helper $helper */
/** @var CategoryHandler $categoryHandler */
require_once __DIR__ . '/admin_header.php';
require_once \dirname(__DIR__) . '/include/functions.render.php';

xoops_cp_header();

$op     = Request::getCmd('op', Request::getCmd('op', '', 'POST'), 'GET'); //!empty($_GET['op'])? $_GET['op'] : (!empty($_POST['op'])?$_POST['op']:"");
$cat_id = Request::getInt('cat_id', Request::getInt('cat_id', 0, 'POST'), 'GET'); // (int)( !empty($_GET['cat_id']) ? $_GET['cat_id'] : @$_POST['cat_id'] );

//$categoryHandler = \XoopsModules\Newbb\Helper::getInstance()->getHandler('Category');

/**
 * newCategory()
 */
function newCategory(): void
{
    editCategory();
}

/**
 * editCategory()
 *
 * @param null|\XoopsObject $categoryObject
 * @internal param int $catid
 */
function editCategory(\XoopsObject $categoryObject = null): void
{
    global $xoopsModule;
    $categoryHandler = Helper::getInstance()->getHandler('Category');
    if (null === $categoryObject) {
        $categoryObject = $categoryHandler->create();
    }
    $groups_cat_access = null;
    require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');

    if ($categoryObject->isNew()) {
        $sform = new \XoopsThemeForm(_AM_NEWBB_CREATENEWCATEGORY, 'op', xoops_getenv('SCRIPT_NAME'));
        $categoryObject->setVar('cat_title', '');
        $categoryObject->setVar('cat_image', '');
        $categoryObject->setVar('cat_description', '');
        $categoryObject->setVar('cat_order', 0);
        $categoryObject->setVar('cat_url', 'https://xoops.org/modules/newbb/ newBB Support');
    } else {
        $sform = new \XoopsThemeForm(_AM_NEWBB_EDITCATEGORY . ' ' . $categoryObject->getVar('cat_title'), 'op', xoops_getenv('SCRIPT_NAME'));
    }

    $sform->addElement(new \XoopsFormText(_AM_NEWBB_SETCATEGORYORDER, 'cat_order', 5, 10, $categoryObject->getVar('cat_order')), false);
    $sform->addElement(new \XoopsFormText(_AM_NEWBB_CATEGORY, 'title', 50, 80, $categoryObject->getVar('cat_title', 'E')), true);
    $sform->addElement(new \XoopsFormDhtmlTextArea(_AM_NEWBB_CATEGORYDESC, 'cat_description', $categoryObject->getVar('cat_description', 'E'), 10, 60), false);

    $imgdir      = '/modules/' . $xoopsModule->getVar('dirname') . '/assets/images/category';
    $cat_image   = $categoryObject->getVar('cat_image');
    $cat_image   = empty($cat_image) ? 'assets/images/category/blank.gif' : $cat_image;
    $graph_array = \XoopsLists::getImgListAsArray(XOOPS_ROOT_PATH . $imgdir . '/');
    array_unshift($graph_array, _NONE);
    $cat_image_select = new \XoopsFormSelect('', 'cat_image', $categoryObject->getVar('cat_image'));
    $cat_image_select->addOptionArray($graph_array);
    $cat_image_select->setExtra("onchange=\"showImgSelected('img', 'cat_image', '/" . $imgdir . "/', '', '" . XOOPS_URL . "')\"");
    $cat_image_tray = new \XoopsFormElementTray(_AM_NEWBB_IMAGE, '&nbsp;');
    $cat_image_tray->addElement($cat_image_select);
    $cat_image_tray->addElement(new \XoopsFormLabel('', "<br><img src='" . XOOPS_URL . $imgdir . '/' . $cat_image . " 'name='img' id='img' alt='' >"));
    $sform->addElement($cat_image_tray);

    $sform->addElement(new \XoopsFormText(_AM_NEWBB_SPONSORLINK, 'cat_url', 50, 80, $categoryObject->getVar('cat_url', 'E')), false);
    $sform->addElement(new \XoopsFormHidden('cat_id', $categoryObject->getVar('cat_id')));

    $buttonTray = new \XoopsFormElementTray('', '');
    $buttonTray->addElement(new \XoopsFormHidden('op', 'save'));

    $butt_save = new \XoopsFormButton('', '', _SUBMIT, 'submit');
    $butt_save->setExtra('onclick="this.form.elements.op.value=\'save\'"');
    $buttonTray->addElement($butt_save);
    if ($categoryObject->getVar('cat_id')) {
        $butt_delete = new \XoopsFormButton('', '', _CANCEL, 'submit');
        $butt_delete->setExtra('onclick="this.form.elements.op.value=\'default\'"');
        $buttonTray->addElement($butt_delete);
    }
    $sform->addElement($buttonTray);
    $sform->display();
}

switch ($op) {
    case 'mod':
        $categoryObject = ($cat_id > 0) ? $categoryHandler->get($cat_id) : $categoryHandler->create();
        //        if (!$newXoopsModuleGui) {
        //            //loadModuleAdminMenu(1, ( $cat_id > 0) ? _AM_NEWBB_EDITCATEGORY . $categoryObject->getVar('cat_title') : _AM_NEWBB_CREATENEWCATEGORY);
        //            echo "<legend style='font-weight: bold; color: #900;'>" . _AM_NEWBB_EDITCATEGORY . '</legend>';
        //        } else {
        $adminObject->displayNavigation(basename(__FILE__));
        //        }
        echo '<br>';
        editCategory($categoryObject);
        break;
    case 'del':
        if (!Request::getBool('confirm', '', 'POST')) {
            xoops_confirm(['op' => 'del', 'cat_id' => Request::getInt('cat_id', 0, 'GET'), 'confirm' => 1], 'admin_cat_manager.php', _AM_NEWBB_WAYSYWTDTTAL);
            break;
        }
        $categoryObject = $categoryHandler->create(false);
        $categoryObject->setVar('cat_id', Request::getInt('cat_id', 0, 'POST'));
        $categoryHandler->delete($categoryObject);

        redirect_header('admin_cat_manager.php', 2, _AM_NEWBB_CATEGORYDELETED);

        break;
    case 'save':
        $cacheHelper = new Cache('newbb');
        $cacheHelper->delete('permission_category');
        if ($cat_id) {
            $categoryObject = $categoryHandler->get($cat_id);
            $message        = _AM_NEWBB_CATEGORYUPDATED;
        } else {
            $categoryObject = $categoryHandler->create();
            $message        = _AM_NEWBB_CATEGORYCREATED;
        }

        $categoryObject->setVar('cat_title', Request::getString('title', '', 'POST'));
        $categoryObject->setVar('cat_image', Request::getString('cat_image', '', 'POST'));
        $categoryObject->setVar('cat_order', Request::getInt('cat_order', 0, 'POST'));
        $categoryObject->setVar('cat_description', Request::getText('cat_description', '', 'POST'));
        $categoryObject->setVar('cat_url', Request::getString('cat_url', '', 'POST'));

        $cat_isNew = $categoryObject->isNew();
        if (!$categoryHandler->insert($categoryObject)) {
            $message = _AM_NEWBB_DATABASEERROR;
        }
        if ($cat_isNew && ($cat_id == $categoryObject->getVar('cat_id'))) {
            $categoryHandler->applyPermissionTemplate($categoryObject);
        }
        redirect_header('admin_cat_manager.php', 2, $message);
        break;
    default:
        if (!$categories = $categoryHandler->getByPermission('all')) {
            $adminObject->addItemButton(_AM_NEWBB_CREATENEWCATEGORY, 'admin_cat_manager.php?op=mod', $icon = 'add');
            $adminObject->displayButton('left');

            echo '<br>';
            newCategory();
            break;
        }
        $adminObject->displayNavigation(basename(__FILE__));
        $adminObject->addItemButton(_AM_NEWBB_CREATENEWCATEGORY, 'admin_cat_manager.php?op=mod', $icon = 'add');
        $adminObject->displayButton('left');

        echo "<table width='100%' border='0' cellspacing='1' class='outer'>" . "<tr><td class='odd'>";
        echo "<table border='0' cellpadding='4' cellspacing='1' width='100%' class='outer'>";
        echo "<tr align='center'>";
        echo "<th align='left' class='bg3'>" . _AM_NEWBB_CATEGORY1 . '</th>';
        echo "<th class='bg3' width='10%'>" . _AM_NEWBB_EDIT . '</th>';
        echo "<th class='bg3' width='10%'>" . _AM_NEWBB_DELETE . '</th>';
        echo '</tr>';

        /** @var XoopsModules\Newbb\Category $onecat */
        foreach ($categories as $key => $onecat) {
            $cat_edit_link  = '<a href="admin_cat_manager.php?op=mod&cat_id=' . $onecat->getVar('cat_id') . '">' . newbbDisplayImage('admin_edit', _EDIT) . '</a>';
            $cat_del_link   = '<a href="admin_cat_manager.php?op=del&cat_id=' . $onecat->getVar('cat_id') . '">' . newbbDisplayImage('admin_delete', _DELETE) . '</a>';
            $cat_title_link = '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/index.php?cat=' . $onecat->getVar('cat_id') . '">' . $onecat->getVar('cat_title') . '</a>';

            echo "<tr class='odd' align='left'>";
            echo '<td>' . $cat_title_link . '</td>';
            echo "<td align='center'>" . $cat_edit_link . '</td>';
            echo "<td align='center'>" . $cat_del_link . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</td></tr></table>';
        echo '<fieldset>';
        echo '<legend>&nbsp;' . _MI_NEWBB_ADMENU_CATEGORY . '&nbsp;</legend>';
        echo _AM_NEWBB_HELP_CATEGORY_TAB;
        echo '<br>' . newbbDisplayImage('admin_edit', _EDIT) . '&nbsp;-&nbsp;' . _EDIT;
        echo '<br>' . newbbDisplayImage('admin_delete', _DELETE) . '&nbsp;-&nbsp;' . _DELETE;
        echo '</fieldset>';
        break;
}

$cacheHelper = Utility::cleanCache();
//$cacheHelper->delete('permission_category');

require_once __DIR__ . '/admin_footer.php';

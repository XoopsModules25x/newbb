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
 * @copyright    XOOPS Project (https://xoops.org)/
 * @license      GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author       XOOPS Development Team
 */

use Xmf\Request;
use XoopsModules\Newbb\{
    CategoryHandler,
    ForumHandler,
    Helper,
    Utility,
    GroupPermForm,
    PermissionHandler
};

/** @var Helper $helper */
/** @var CategoryHandler $categoryHandler */
/** @var ForumHandler $forumHandler */
/** @var PermissionHandler $permissionHandler */
require_once __DIR__ . '/admin_header.php';
require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
if (!class_exists('XoopsGroupPermForm')) {
    require_once $GLOBALS['xoops']->path('class/xoopsform/grouppermform.php');
}

/**
 * TODO: synchronize cascade permissions for multi-level
 */

/**
 * Add category navigation to forum casscade structure
 * <ol>Special points:
 *    <li> Use negative values for category IDs to avoid conflict between category and forum
 *    <li> Disabled checkbox for categories to avoid unnecessary permission items for categories in forum permission table
 * </ol>
 *
 * Note: this is a __patchy__ solution. We should have a more extensible and flexible group permission management: not only for data architecture but also for management interface
 */

//$action = isset($_REQUEST['action']) ? strtolower($_REQUEST['action']) : "";
$action            = \mb_strtolower(Request::getCmd('action', ''));
$module_id         = $xoopsModule->getVar('mid');
$permissionHandler = Helper::getInstance()->getHandler('Permission');
$perms             = $permissionHandler->getValidForumPerms();

switch ($action) {
    case 'template':
        xoops_cp_header();
        $adminObject->displayNavigation(basename(__FILE__));
        echo "<legend style='font-weight: bold; color: #900;'>" . _AM_NEWBB_PERM_ACTION . '</legend>';
        $opform    = new \XoopsSimpleForm(_AM_NEWBB_PERM_ACTION_HELP_TEMPLAT, 'actionform', 'admin_permissions.php', 'get');
        $op_select = new \XoopsFormSelect('', 'action');
        $op_select->setExtra('onchange="document.forms.actionform.submit()"');
        $op_select->addOptionArray(
            [
                'no'       => _SELECT,
                'template' => _AM_NEWBB_PERM_TEMPLATE,
                'apply'    => _AM_NEWBB_PERM_TEMPLATEAPP,
                'default'  => _AM_NEWBB_PERM_SETBYGROUP,
            ]
        );
        $opform->addElement($op_select);
        $opform->display();
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = xoops_getHandler('member');
        $glist         = $memberHandler->getGroupList();
        $elements      = [];
        $perm_template = $permissionHandler->getTemplate();
        foreach (array_keys($glist) as $i) {
            $selected   = !empty($perm_template[$i]) ? array_keys($perm_template[$i]) : [];
            $ret_ele    = '<tr align="left" valign="top"><td class="head">' . $glist[$i] . '</td>';
            $ret_ele    .= '<td class="even">';
            $ret_ele    .= '<table class="outer"><tr><td class="odd"><table><tr>';
            $ii         = 0;
            $option_ids = [];
            foreach ($perms as $perm) {
                ++$ii;
                if (0 == $ii % 5) {
                    $ret_ele .= '</tr><tr>';
                }
                $checked      = in_array('forum_' . $perm, $selected, true) ? ' checked' : '';
                $option_id    = $perm . '_' . $i;
                $option_ids[] = $option_id;
                $ret_ele      .= '<td><input name="perms[' . $i . '][' . 'forum_' . $perm . ']" id="' . $option_id . '" onclick="" value="1" type="checkbox"' . $checked . '>' . constant('_AM_NEWBB_CAN_' . \mb_strtoupper($perm)) . '<br></td>';
            }
            $ret_ele    .= '</tr></table></td><td class="even">';
            $ret_ele    .= _ALL . ' <input id="checkall[' . $i . ']" type="checkbox" value="" onclick="var optionids = new Array(' . implode(', ', $option_ids) . '); xoopsCheckAllElements(optionids, \'checkall[' . $i . ']\')" >';
            $ret_ele    .= '</td></tr></table>';
            $ret_ele    .= '</td></tr>';
            $elements[] = $ret_ele;
        }
        $tray = new \XoopsFormElementTray('');
        $tray->addElement(new \XoopsFormHidden('action', 'template_save'));
        $tray->addElement(new \XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
        $tray->addElement(new \XoopsFormButton('', 'reset', _CANCEL, 'reset'));
        $ret = '<br><strong>' . _AM_NEWBB_PERM_TEMPLATE . '</strong><br>' . _AM_NEWBB_PERM_TEMPLATE_DESC . '<br>';
        $ret .= "<form name='template' id='template' method='post'>\n<table width='100%' class='outer' cellspacing='1'>\n";
        $ret .= implode("\n", $elements);
        $ret .= '<tr align="left" valign="top"><td class="head"></td><td class="even" style="text-align:center;">';
        $ret .= $tray->render();
        $ret .= '</td></tr>';
        $ret .= '</table></form>';
        echo $ret;
        require_once __DIR__ . '/admin_footer.php';
        break;
    case 'template_save':
        //        $res = $permissionHandler->setTemplate($_POST['perms'], $groupid = 0);
        $res = $permissionHandler->setTemplate(Request::getArray('perms', '', 'POST'), $groupid = 0);
        if ($res) {
            redirect_header('admin_permissions.php', 2, _AM_NEWBB_PERM_TEMPLATE_CREATED);
        } else {
            redirect_header('admin_permissions.php?action=template', 2, _AM_NEWBB_PERM_TEMPLATE_ERROR);
        }
        break;
    //        exit();

    case 'apply':
        $perm_template = $permissionHandler->getTemplate();
        if (null === $perm_template) {
            redirect_header('admin_permissions.php?action=template', 2, _AM_NEWBB_PERM_TEMPLATE);
        }
        xoops_cp_header();
        $adminObject->displayNavigation(basename(__FILE__));
        echo "<legend style='font-weight: bold; color: #900;'>" . _AM_NEWBB_PERM_ACTION . '</legend>';
        $opform    = new \XoopsSimpleForm(_AM_NEWBB_PERM_ACTION_HELP_APPLY, 'actionform', 'admin_permissions.php', 'get');
        $op_select = new \XoopsFormSelect('', 'action');
        $op_select->setExtra('onchange="document.forms.actionform.submit()"');
        $op_select->addOptionArray(
            [
                'no'       => _SELECT,
                'template' => _AM_NEWBB_PERM_TEMPLATE,
                'apply'    => _AM_NEWBB_PERM_TEMPLATEAPP,
            ]
        );
        $opform->addElement($op_select);
        $opform->display();

        $categoryHandler  = Helper::getInstance()->getHandler('Category');
        $criteriaCategory = new \CriteriaCompo(new \Criteria('cat_id'));
        $criteriaCategory->setSort('cat_order');
        $categories = $categoryHandler->getList($criteriaCategory);

        $forumHandler = Helper::getInstance()->getHandler('Forum');
        $forums       = $forumHandler->getTree(array_keys($categories), 0, 'all');
        foreach (array_keys($forums) as $c) {
            $fm_options[-1 * $c - 1000] = ' ';
            $fm_options[-1 * $c]        = '[' . $categories[$c] . ']';
            foreach (array_keys($forums[$c]) as $f) {
                $fm_options[$f] = $forums[$c][$f]['prefix'] . $forums[$c][$f]['forum_name'];
            }
        }
        unset($forums, $categories);

        $fmform    = new \XoopsThemeForm(_AM_NEWBB_PERM_TEMPLATEAPP, 'fmform', 'admin_permissions.php', 'post', true);
        $fm_select = new \XoopsFormSelect(_AM_NEWBB_PERM_FORUMS, 'forums', null, 10, true);
        $fm_select->addOptionArray($fm_options);
        $fmform->addElement($fm_select);
        $tray = new \XoopsFormElementTray('');
        $tray->addElement(new \XoopsFormHidden('action', 'apply_save'));
        $tray->addElement(new \XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
        $tray->addElement(new \XoopsFormButton('', 'reset', _CANCEL, 'reset'));
        $fmform->addElement($tray);
        $fmform->display();
        require_once __DIR__ . '/admin_footer.php';
        break;
    case 'apply_save':
        if (!Request::getArray('forums', '', 'POST')) {
            break;
        }
        foreach (Request::getArray('forums', '', 'POST') as $forum) {
            if ($forum < 1) {
                continue;
            }
            $permissionHandler->applyTemplate($forum, $module_id);
        }
        //$cacheHelper->delete('permission');
        Utility::cleanCache();
        redirect_header('admin_permissions.php', 2, _AM_NEWBB_PERM_TEMPLATE_APPLIED);
        break;
    default:
        xoops_cp_header();

        $categoryHandler  = Helper::getInstance()->getHandler('Category');
        $criteriaCategory = new \CriteriaCompo(new \Criteria('cat_id'));
        $criteriaCategory->setSort('cat_order');
        $categories = $categoryHandler->getList($criteriaCategory);

        if (0 === count($categories)) {
            redirect_header('admin_cat_manager.php', 2, _AM_NEWBB_CREATENEWCATEGORY);
        }

        $forumHandler = Helper::getInstance()->getHandler('Forum');
        $forums       = $forumHandler->getTree(array_keys($categories), 0, 'all');

        if (0 === count($forums)) {
            redirect_header('admin_forum_manager.php', 2, _AM_NEWBB_CREATENEWFORUM);
        }

        $adminObject->displayNavigation(basename(__FILE__));
        echo "<legend style='font-weight: bold; color: #900;'>" . _AM_NEWBB_PERM_ACTION . '</legend>';
        $opform    = new \XoopsSimpleForm(_AM_NEWBB_PERM_ACTION_HELP, 'actionform', 'admin_permissions.php', 'get');
        $op_select = new \XoopsFormSelect('', 'action');
        $op_select->setExtra('onchange="document.forms.actionform.submit()"');
        $op_select->addOptionArray(
            [
                'no'       => _SELECT,
                'template' => _AM_NEWBB_PERM_TEMPLATE,
                'apply'    => _AM_NEWBB_PERM_TEMPLATEAPP,
                'default'  => _AM_NEWBB_PERM_SETBYGROUP,
            ]
        );
        $opform->addElement($op_select);
        $opform->display();

        $op_options = ['category' => _AM_NEWBB_CAT_ACCESS];
        $fm_options = [
            'category' => [
                'title'     => _AM_NEWBB_CAT_ACCESS,
                'item'      => 'category_access',
                'desc'      => '',
                'anonymous' => true,
            ],
        ];
        foreach ($perms as $perm) {
            $op_options[$perm] = constant('_AM_NEWBB_CAN_' . \mb_strtoupper($perm));
            $fm_options[$perm] = [
                'title'     => constant('_AM_NEWBB_CAN_' . \mb_strtoupper($perm)),
                'item'      => 'forum_' . $perm,
                'desc'      => '',
                'anonymous' => true,
            ];
        }

        $op_keys = array_keys($op_options);
        $op      = \mb_strtolower(Request::getCmd('op', Request::getCmd('op', '', 'COOKIE'), 'GET'));
        if (empty($op)) {
            $op = $op_keys[0];
            setcookie('op', $op_keys[1] ?? '');
        } elseif (false !== ($key = array_search($op, $op_keys, true))) {
            setcookie('op', $op_keys[$key + 1] ?? '');
        }

        $opform    = new \XoopsSimpleForm('', 'opform', 'admin_permissions.php', 'get');
        $op_select = new \XoopsFormSelect('', 'op', $op);
        $op_select->setExtra('onchange="document.forms.opform.submit()"');
        $op_select->addOptionArray($op_options);
        $opform->addElement($op_select);
        $opform->display();

        $perm_desc = '';

        $form = new GroupPermForm($fm_options[$op]['title'], $module_id, $fm_options[$op]['item'], $fm_options[$op]['desc'], 'admin/admin_permissions.php', $fm_options[$op]['anonymous']);

        $categoryHandler  = Helper::getInstance()->getHandler('Category');
        $criteriaCategory = new \CriteriaCompo(new \Criteria('cat_id'));
        $criteriaCategory->setSort('cat_order');
        $categories = $categoryHandler->getList($criteriaCategory);
        if ('category' === $op) {
            foreach (array_keys($categories) as $key) {
                $form->addItem($key, $categories[$key]);
            }
            unset($categories);
        } else {
            $forumHandler = Helper::getInstance()->getHandler('Forum');
            $forums       = $forumHandler->getTree(array_keys($categories), 0, 'all');
            if (count($forums) > 0) {
                foreach (array_keys($forums) as $c) {
                    $key_c = -1 * $c;
                    $form->addItem($key_c, '<strong>[' . $categories[$c] . ']</strong>');
                    foreach (array_keys($forums[$c]) as $f) {
                        $pid = $forums[$c][$f]['parent_forum'] ?: $key_c;
                        $form->addItem($f, $forums[$c][$f]['prefix'] . $forums[$c][$f]['forum_name'], $pid);
                    }
                }
            }
            unset($forums, $categories);
        }
        $form->display();
        echo '<fieldset>';
        echo '<legend>&nbsp;' . _MI_NEWBB_ADMENU_PERMISSION . '&nbsp;</legend>';
        echo _AM_NEWBB_HELP_PERMISSION_TAB;
        echo '</fieldset>';
        // Since we can not control the permission update, a trick is used here
        /** var Newbb\PermissionHandler $permissionHandler */
        $permissionHandler = Helper::getInstance()->getHandler('Permission');
        $permissionHandler->createPermData();
        //$cacheHelper->delete('permission');
        Utility::cleanCache();

        require_once __DIR__ . '/admin_footer.php';
        break;
}

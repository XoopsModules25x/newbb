<?php
/**
 * Topic type management for newbb
 *
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <php_pp@hotmail.com>
 * @since          4.00
 * @package        module::newbb
 */

use Xmf\Request;

require_once __DIR__ . '/admin_header.php';
xoops_cp_header();
echo '<br>';
require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
$adminObject->displayNavigation(basename(__FILE__));

/*
 * The 'op' could be
 * <ol>
 *    <li>'save_type': saving for batch edit or add</li>
 *    <li>'delete': batch delete</li>
 *    <li>'template': set type setting template</li>
 *    <li>'apply': apply template to forums</li>
 *    <li>'forum': type setting per forum</li>
 *    <li>'add': batch add</li>
 *    <li>default: list of existing types</li>
 * </ol>
 */
$op       = Request::getCmd('op', '');
$validOps = [
    'save_type',
    'delete',
    'template',
    'save_template',
    'apply',
    'save_apply',
    'forum',
    'edit_forum',
    'save_forum',
    'add'
];
if (!in_array($op, $validOps, true)) {
    $op = '';
}

///** @var Newbb\TypeHandler $typeHandler */
//$typeHandler = Newbb\Helper::getInstance()->getHandler('Type');
$cacheHelper = new \Xmf\Module\Helper\Cache('newbb');

switch ($op) {
    case 'save_type':
        $type_names0 = $_POST['type_name'];
        $type_names  = Request::getArray('type_name', null, 'POST');
        $type_del    = [];
        foreach (array_keys($type_names) as $key) {
            if (Request::getBool('isnew', '', 'POST')) {
                $typeObject = $typeHandler->create();
            } elseif (!$typeObject = $typeHandler->get($key)) {
                continue;
            }

            //            if (Request::getArray("type_del[$key]", '', 'POST')) {
            $temp = Request::getArray('type_del', '', 'POST');
            if ($temp[$key]) {
                $type_del[] = $key;
                continue;
            } else {
                foreach (['type_name', 'type_color', 'type_description'] as $var) {
                    //                    if ($typeObject->getVar($var) != @$_POST[$var][$key]) {
                    //                        $typeObject->setVar($var, @$_POST[$var][$key]);
                    //                    }
                    $temp = Request::getArray($var, '', 'POST');
                    if ($typeObject->getVar($var) != $temp[$key]) {
                        $typeObject->setVar($var, $temp[$key]);
                    }

                    //                    $typeObject->setVar($var, Request::getArray($var, '', 'POST')[$key]);
                }
                $typeHandler->insert($typeObject);
                unset($typeObject);
            }
        }
        if (count($type_del) > 0) {
            $type_list = $typeHandler->getList(new \Criteria('type_id', '(' . implode(', ', $type_del) . ')', 'IN'));
            xoops_confirm(['op' => 'delete', 'type_del' => serialize($type_del)], xoops_getenv('PHP_SELF'), sprintf(_AM_NEWBB_TODEL_TYPE, implode(', ', array_values($type_list))), '', false);
        } else {
            redirect_header(xoops_getenv('PHP_SELF'), 2, _MD_NEWBB_DBUPDATED);
        }
        break;

    case 'delete':
        $type_dels = @unserialize(Request::getString('type_del', '', 'POST'));
        foreach ($type_dels as $key) {
            if (!$typeObject = $typeHandler->get($key)) {
                continue;
            }
            $typeHandler->delete($typeObject);
            unset($typeObject);
        }
        redirect_header(xoops_getenv('PHP_SELF'), 2, _MD_NEWBB_DBUPDATED);
        break;

    case 'template':
        $typesObject = $typeHandler->getAll();
        if (0 === count($typesObject)) {
            redirect_header(xoops_getenv('PHP_SELF'), 2, _AM_NEWBB_TYPE_ADD_ERR);
        }

        $adminObject->addItemButton(_AM_NEWBB_TYPE_ADD, 'admin_type_manager.php?op=add', $icon = 'add');
        $adminObject->addItemButton(_AM_NEWBB_TYPE_TEMPLATE, 'admin_type_manager.php?op=template', $icon = 'add');
        $adminObject->addItemButton(_AM_NEWBB_TYPE_TEMPLATE_APPLY, 'admin_type_manager.php?op=apply', $icon = 'add');
        $adminObject->addItemButton(_AM_NEWBB_TYPE_FORUM, 'admin_type_manager.php?op=forum', $icon = 'add');
        $adminObject->displayButton('left');
        echo '<legend style="font-weight: bold; color: #900;">' . _AM_NEWBB_TYPE_ORDER_DESC . '</legend>';
        echo "<table width='100%' border='0' cellspacing='1' class='outer'>" . "<tr><td class='odd'>";
        echo "<form name='template' method='post' action='" . xoops_getenv('PHP_SELF') . "'>";
        echo "<table border='0' cellpadding='4' cellspacing='1' width='100%' class='outer'>";
        echo "<tr align='center'>";
        echo "<th class='bg3' width='20%'>" . _AM_NEWBB_TYPE_ORDER . '</th>';
        echo "<th class='bg3' width='20%'>" . _AM_NEWBB_TYPE_NAME . '</th>';
        echo "<th class='bg3'>" . _AM_NEWBB_TYPE_DESCRIPTION . '</th>';
        echo '</tr>';

        if ($templates = $cacheHelper->read('type_template')) {
            arsort($templates);
            foreach ($templates as $order => $key) {
                if (!isset($typesObject[$key])) {
                    continue;
                }
                $typeObject = $typesObject[$key];
                echo "<tr class='even' align='left'>";
                echo "<td><input type='text' name='type_order[{$key}]' value='" . $order . "' size='10' /></td>";
                echo "<td><em style='color:" . $typeObject->getVar('type_color') . ";'>" . $typeObject->getVar('type_name') . '</em></td>';
                echo '<td>' . $typeObject->getVar('type_description') . '</td>';
                echo '</tr>';
                unset($typesObject[$key]);
            }
            echo "<tr><td colspan='3' height='5px'></td></tr>";
        }
        foreach ($typesObject as $key => $typeObject) {
            echo "<tr class='odd' align='left'>";
            echo "<td><input type='text' name='type_order[{$key}]' value='0' size='10' /></td>";
            echo "<td><em style='color:" . $typeObject->getVar('type_color') . ";'>" . $typeObject->getVar('type_name') . '</em></td>';
            echo '<td>' . $typeObject->getVar('type_description') . '</td>';
            echo '</tr>';
        }

        echo "<tr><td colspan='3' style='text-align:center;'>";
        echo "<input type='hidden' name='op' value='save_template' />";
        echo "<input type='submit' name='submit' value='" . _SUBMIT . "' /> ";
        echo "<input type='reset' value='" . _CANCEL . "' />";
        echo '</td></tr></table>';
        echo '</form>';
        echo '</td></tr></table>';
        break;

    case 'save_template':
        $templates = array_flip(array_filter(Request::getArray('type_order', [], 'POST')));
        $cacheHelper->write('type_template', $templates);
        redirect_header(xoops_getenv('PHP_SELF') . '?op=template', 2, _MD_NEWBB_DBUPDATED);
        break;

    case 'apply':
        if (!$templates = $cacheHelper->read('type_template')) {
            redirect_header(xoops_getenv('PHP_SELF') . '?op=template', 2, _AM_NEWBB_TYPE_TEMPLATE_ERR);
        }

        //        $categoryHandler  = Newbb\Helper::getInstance()->getHandler('Category');
        $criteriaCategory = new \CriteriaCompo(new \Criteria('1', 1));
        $criteriaCategory->setSort('cat_order');
        $categories = $categoryHandler->getList($criteriaCategory);
        //        $forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
        $forums = $forumHandler->getTree(array_keys($categories), 0, 'all');
        foreach (array_keys($forums) as $c) {
            $fm_options[-1 * $c] = '[' . $categories[$c] . ']';
            foreach (array_keys($forums[$c]) as $f) {
                $fm_options[$f] = $forums[$c][$f]['prefix'] . $forums[$c][$f]['forum_name'];
            }
        }
        unset($forums, $categories);
        $fmform    = new \XoopsThemeForm(_AM_NEWBB_TYPE_TEMPLATE_APPLY, 'fmform', xoops_getenv('PHP_SELF'), 'post', true);
        $fm_select = new \XoopsFormSelect(_AM_NEWBB_PERM_FORUMS, 'forums', null, 10, true);
        $fm_select->addOptionArray($fm_options);
        $fmform->addElement($fm_select);
        $tray = new \XoopsFormElementTray('');
        $tray->addElement(new \XoopsFormHidden('op', 'save_apply'));
        $tray->addElement(new \XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
        $tray->addElement(new \XoopsFormButton('', 'reset', _CANCEL, 'reset'));
        $fmform->addElement($tray);

        //loadModuleAdminMenu(11, _AM_NEWBB_TYPE_TEMPLATE_APPLY);
        $adminObject->addItemButton(_AM_NEWBB_TYPE_ADD, 'admin_type_manager.php?op=add', $icon = 'add');
        $adminObject->addItemButton(_AM_NEWBB_TYPE_TEMPLATE, 'admin_type_manager.php?op=template', $icon = 'add');
        $adminObject->addItemButton(_AM_NEWBB_TYPE_TEMPLATE_APPLY, 'admin_type_manager.php?op=apply', $icon = 'add');
        $adminObject->addItemButton(_AM_NEWBB_TYPE_FORUM, 'admin_type_manager.php?op=forum', $icon = 'add');
        $adminObject->displayButton('left');

        echo "<table width='100%' border='0' cellspacing='1' class='outer'>" . "<tr><td class='odd'>";
        echo "<table border='0' cellpadding='4' cellspacing='1' width='100%' class='outer'>";
        echo "<tr align='center'>";
        echo "<th class='bg3' width='20%'>" . _AM_NEWBB_TYPE_NAME . '</th>';
        echo "<th class='bg3' width='20%'>" . _AM_NEWBB_TYPE_ORDER . '</th>';
        echo "<th class='bg3'>" . _AM_NEWBB_TYPE_DESCRIPTION . '</th>';
        echo '</tr>';

        $typesObject = $typeHandler->getAll(new \Criteria('type_id', '(' . implode(', ', array_values($templates)) . ')', 'IN'));
        arsort($templates);
        foreach ($templates as $order => $key) {
            if (!isset($typesObject[$key])) {
                continue;
            }
            $typeObject = $typesObject[$key];
            echo "<tr class='even' align='left'>";
            echo "<td><em style='color:" . $typeObject->getVar('type_color') . ";'>" . $typeObject->getVar('type_name') . '</em></td>';
            echo '<td>' . $order . '</td>';
            echo '<td>' . $typeObject->getVar('type_description') . '</td>';
            echo '</tr>';
            unset($typesObject[$key]);
        }
        echo '</table>';
        echo '</td></tr></table>';
        echo '<br>';
        $fmform->display();
        break;

    case 'save_apply':
        if (!$templates = $cacheHelper->read('type_template')) {
            redirect_header(xoops_getenv('PHP_SELF') . '?op=template', 2, _AM_NEWBB_TYPE_TEMPLATE);
        }
        foreach (Request::getArray('forums', [], 'POST') as $forum) {
            if ($forum < 1) {
                continue;
            }
            $typeHandler->updateByForum($forum, array_flip($templates));
        }
        redirect_header(xoops_getenv('PHP_SELF'), 2, _MD_NEWBB_DBUPDATED);
        break;

    case 'forum':
        //        $categoryHandler  = Newbb\Helper::getInstance()->getHandler('Category');
        $criteriaCategory = new \CriteriaCompo(new \Criteria('1', 1));
        $criteriaCategory->setSort('cat_order');
        $categories = $categoryHandler->getList($criteriaCategory);
        if (empty($categories)) {
            redirect_header('admin_cat_manager.php', 2, _AM_NEWBB_CREATENEWCATEGORY);
        }
        //        $forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
        $forums = $forumHandler->getTree(array_keys($categories));
        if (empty($forums)) {
            redirect_header('admin_forum_manager.php', 2, _AM_NEWBB_CREATENEWFORUM);
        }

        foreach (array_keys($forums) as $c) {
            $fm_options[-1 * $c] = '[' . $categories[$c] . ']';
            foreach (array_keys($forums[$c]) as $f) {
                $fm_options[$f] = $forums[$c][$f]['prefix'] . $forums[$c][$f]['forum_name'];
            }
        }
        unset($forums, $categories);
        $fmform    = new \XoopsThemeForm(_AM_NEWBB_TYPE_FORUM, 'fmform', xoops_getenv('PHP_SELF'), 'post', true);
        $fm_select = new \XoopsFormSelect(_AM_NEWBB_PERM_FORUMS, 'forum', null, 5, false);
        $fm_select->addOptionArray($fm_options);
        $fmform->addElement($fm_select);
        $tray = new \XoopsFormElementTray('');
        $tray->addElement(new \XoopsFormHidden('op', 'edit_forum'));
        $tray->addElement(new \XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
        $tray->addElement(new \XoopsFormButton('', 'reset', _CANCEL, 'reset'));
        $fmform->addElement($tray);

        //loadModuleAdminMenu(11, _AM_NEWBB_TYPE_FORUM);
        $adminObject->addItemButton(_AM_NEWBB_TYPE_ADD, 'admin_type_manager.php?op=add', $icon = 'add');
        $adminObject->addItemButton(_AM_NEWBB_TYPE_TEMPLATE, 'admin_type_manager.php?op=template', $icon = 'add');
        $adminObject->addItemButton(_AM_NEWBB_TYPE_FORUM, 'admin_type_manager.php?op=forum', $icon = 'add');
        $adminObject->displayButton('left');

        $fmform->display();
        break;

    case 'edit_forum':
        if (!Request::getInt('forum', 0, 'POST') || Request::getInt('forum', 0, 'POST') < 1) {
            redirect_header(xoops_getenv('PHP_SELF') . '?op=forum', 2, _AM_NEWBB_TYPE_FORUM_ERR);
        }

        //        $forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
        if (!$forumObject = $forumHandler->get(Request::getInt('forum', 0, 'POST'))) {
            redirect_header(xoops_getenv('PHP_SELF') . '?op=forum', 2, _AM_NEWBB_TYPE_FORUM_ERR);
        }

        $typesObject = $typeHandler->getAll();
        if (0 === count($typesObject)) {
            redirect_header(xoops_getenv('PHP_SELF'), 2, _AM_NEWBB_TYPE_ADD_ERR);
        }

        $adminObject->addItemButton(_AM_NEWBB_TYPE_ADD, 'admin_type_manager.php?op=add', $icon = 'add');
        $adminObject->addItemButton(_AM_NEWBB_TYPE_TEMPLATE, 'admin_type_manager.php?op=template', $icon = 'add');
        $adminObject->addItemButton(_AM_NEWBB_TYPE_FORUM, 'admin_type_manager.php?op=forum', $icon = 'add');
        $adminObject->displayButton('left');
        echo '<legend style="font-weight: bold; color: #900;">' . _AM_NEWBB_TYPE_ORDER_DESC . '</legend>';
        echo "<table width='100%' border='0' cellspacing='1' class='outer'>" . "<tr><td class='odd'>";
        echo "<form name='template' method='post' action='" . xoops_getenv('PHP_SELF') . "'>";
        echo "<table border='0' cellpadding='4' cellspacing='1' width='100%' class='outer'>";
        echo "<tr align='center'>";
        echo "<th class='bg3' width='20%'>" . _AM_NEWBB_TYPE_ORDER . '</th>';
        echo "<th class='bg3' width='20%'>" . _AM_NEWBB_TYPE_NAME . '</th>';
        echo "<th class='bg3'>" . _AM_NEWBB_TYPE_DESCRIPTION . '</th>';
        echo '</tr>';

        $types       = $typeHandler->getByForum(Request::getInt('forum', 0, 'POST'));
        $types_order = [];
        foreach ($types as $key => $type) {
            $types_order[] = $type['type_order'];
        }
        array_multisort($types_order, $types);
        foreach ($types as $key => $type) {
            if (!isset($typesObject[$type['type_id']])) {
                continue;
            }
            $typeObject = $typesObject[$type['type_id']];
            echo "<tr class='even' align='left'>";
            echo "<td><input type='text' name='type_order[" . $type['type_id'] . "]' value='" . $type['type_order'] . "' size='10' /></td>";
            echo "<td><em style='color:" . $typeObject->getVar('type_color') . ";'>" . $typeObject->getVar('type_name') . '</em></td>';
            echo '<td>' . $typeObject->getVar('type_description') . '</td>';
            echo '</tr>';
            unset($typesObject[$type['type_id']]);
        }
        echo "<tr><td colspan='3' height='5px'></td></tr>";
        foreach ($typesObject as $key => $typeObject) {
            echo "<tr class='odd' align='left'>";
            echo "<td><input type='text' name='type_order[{$key}]' value='0' size='10' /></td>";
            echo "<td><em style='color:" . $typeObject->getVar('type_color') . ";'>" . $typeObject->getVar('type_name') . '</em></td>';
            echo '<td>' . $typeObject->getVar('type_description') . '</td>';
            echo '</tr>';
        }

        echo "<tr><td colspan='3' style='text-align:center;'>";
        echo '<legend style="font-weight: bold; color: #900;">' . _AM_NEWBB_TYPE_EDITFORUM_DESC . '</legend>';
        echo "<input type='hidden' name='forum' value='" . Request::getInt('forum', 0, 'POST') . "' />";
        echo "<input type='hidden' name='op' value='save_forum' />";
        echo "<input type='submit' name='submit' value='" . _SUBMIT . "' /> ";
        echo "<input type='reset' value='" . _CANCEL . "' />";
        echo '</td></tr></table>';
        echo '</form>';
        echo '</td></tr></table>';
        break;

    case 'save_forum':
        if (!Request::getInt('forum', 0, 'POST') || Request::getInt('forum', 0, 'POST') < 1) {
            redirect_header(xoops_getenv('PHP_SELF') . '?op=forum', 2, _AM_NEWBB_TYPE_FORUM);
        }
        $typeHandler->updateByForum(Request::getInt('forum', 0, 'POST'), Request::getArray('type_order', null, 'POST'));
        redirect_header(xoops_getenv('PHP_SELF') . '?op=forum', 2, _MD_NEWBB_DBUPDATED);
        break;

    case 'add':
    default:
        $typesObject = $typeHandler->getAll();
        if (0 === count($typesObject)) {
            $op    = 'add';
            $title = _AM_NEWBB_TYPE_ADD;
        } else {
            $title = _AM_NEWBB_TYPE_LIST;
        }

        if ('add' !== $op) {
            $adminObject->addItemButton(_AM_NEWBB_TYPE_ADD, 'admin_type_manager.php?op=add', $icon = 'add');
            $adminObject->addItemButton(_AM_NEWBB_TYPE_TEMPLATE, 'admin_type_manager.php?op=template', $icon = 'add');
            $adminObject->addItemButton(_AM_NEWBB_TYPE_FORUM, 'admin_type_manager.php?op=forum', $icon = 'add');
            $adminObject->displayButton('left');
        }
        echo _AM_NEWBB_TYPE_HELP;
        echo "<table width='100%' border='0' cellspacing='1' class='outer'>" . "<tr><td class='odd'>";
        echo "<form name='list' method='post' action='" . xoops_getenv('PHP_SELF') . "'>";
        echo "<table border='0' cellpadding='4' cellspacing='1' width='100%' class='outer'>";
        echo "<tr align='center'>";
        if ('add' !== $op) {
            echo "<th class='bg3' width='5%'>" . _DELETE . '</th>';
        }
        echo "<th align='left' class='bg3' width='20%'>" . _AM_NEWBB_TYPE_NAME . '</th>';
        echo "<th class='bg3' width='15%'>" . _AM_NEWBB_TYPE_COLOR . '</th>';
        echo "<th align='left' class='bg3'>" . _AM_NEWBB_TYPE_DESCRIPTION . '</th>';
        echo '</tr>';

        $isColorpicker = require_once $GLOBALS['xoops']->path('class/xoopsform/formcolorpicker.php');

        if ('add' !== $op) {
            foreach ($typesObject as $key => $typeObject) {
                echo "<tr class='odd' align='left'>";
                echo "<td><input type='checkbox' name='type_del[{$key}]' /></td>";
                echo "<td><input type='text' name='type_name[{$key}]' value='" . $typeObject->getVar('type_name') . "' size='10' /></td>";
                if ($isColorpicker) {
                    $form_colorpicker = new \XoopsFormColorPicker('', "type_color[{$key}]", $typeObject->getVar('type_color'));
                    echo '<td>' . $form_colorpicker->render() . '</td>';
                } else {
                    echo "<td><input type='text' name='type_color[{$key}]' value='" . $typeObject->getVar('type_color') . "' size='10' /></td>";
                }
                echo "<td><input type='text' name='type_description[{$key}]' value='" . $typeObject->getVar('type_description') . "' size='30' /></td>";
                echo '</tr>';
            }
            echo "<tr><td colspan='4' style='text-align:center;'>";
        } else {
            $adminObject->addItemButton(_AM_NEWBB_TYPE_TEMPLATE, 'admin_type_manager.php?op=template', $icon = 'add');
            $adminObject->addItemButton(_AM_NEWBB_TYPE_FORUM, 'admin_type_manager.php?op=forum', $icon = 'add');
            $adminObject->displayButton('left');
            for ($i = 0; $i < 10; ++$i) {
                echo "<tr class='odd' align='left'>";
                echo "<td><input type='text' name='type_name[{$i}]' value='' size='10' /></td>";
                if ($isColorpicker) {
                    $form_colorpicker = new \XoopsFormColorPicker('', "type_color[{$i}]", '');
                    echo '<td>' . $form_colorpicker->render() . '</td>';
                } else {
                    echo "<td><input type='text' name='type_color[{$i}]' value='' size='10' /></td>";
                }
                echo "<td><input type='text' name='type_description[{$i}]' value='' size='40' /></td>";
                echo '</tr>';
            }
            echo "<tr><td colspan='3' style='text-align:center;'>";
            echo "<input type='hidden' name='isnew' value='1' />";
        }
        echo "<input type='hidden' name='op' value='save_type' />";
        echo "<input type='submit' name='submit' value='" . _SUBMIT . "' /> ";
        echo "<input type='reset' value='" . _CANCEL . "' />";
        echo '</td></tr></table>';
        echo '</form>';
        echo '</td></tr></table>';
        echo '<fieldset>';
        echo '<legend>&nbsp;' . _MI_NEWBB_ADMENU_TYPE . '&nbsp;</legend>';
        echo _AM_NEWBB_HELP_TYPE_TAB;
        echo '</fieldset>';
        break;
}

require_once __DIR__ . '/admin_footer.php';

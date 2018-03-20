<?php
//
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System                      //
// Copyright (c) 2000-2016 XOOPS.org                           //
// <https://xoops.org/>                             //
// ------------------------------------------------------------------------ //
// This program is free software; you can redistribute it and/or modify     //
// it under the terms of the GNU General Public License as published by     //
// the Free Software Foundation; either version 2 of the License, or        //
// (at your option) any later version.                                      //
// //
// You may not change or alter any portion of this comment or credits       //
// of supporting developers from this source code or any supporting         //
// source code which is considered copyrighted (c) material of the          //
// original comment or credit authors.                                      //
// //
// This program is distributed in the hope that it will be useful,          //
// but WITHOUT ANY WARRANTY; without even the implied warranty of           //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
// GNU General Public License for more details.                             //
// //
// You should have received a copy of the GNU General Public License        //
// along with this program; if not, write to the Free Software              //
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------ //
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, https://xoops.org/, http://jp.xoops.org/ //
// Project: XOOPS Project                                                    //
// ------------------------------------------------------------------------- //

use Xmf\Request;
use XoopsModules\Newbb;

require_once __DIR__ . '/admin_header.php';
include $GLOBALS['xoops']->path('class/xoopstree.php');
require_once $GLOBALS['xoops']->path('class/pagenav.php');
require_once __DIR__ . '/../include/functions.forum.php';
require_once __DIR__ . '/../include/functions.render.php';

$cacheHelper = Newbb\Utility::cleanCache();

xoops_cp_header();

$op       = Request::getCmd('op', Request::getCmd('op', '', 'POST'), 'GET'); // !empty($_GET['op'])? $_GET['op'] : (!empty($_POST['op'])?$_POST['op']:"");
$forum_id = Request::getInt('forum', Request::getInt('forum', 0, 'POST'), 'GET'); //(int)( !empty($_GET['forum'])? $_GET['forum'] : (!empty($_POST['forum'])?$_POST['forum']:0) );

///** @var Newbb\ForumHandler $forumHandler */
//$forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
switch ($op) {
    case 'moveforum':
        //if (!$newXoopsModuleGui) loadModuleAdminMenu(2, "");

        if (Request::getInt('dest_forum', 0, 'POST')) {
            $dest = Request::getInt('dest_forum', 0, 'POST');
            if ($dest > 0) {
                $pid        = $dest;
                $forum_dest = $forumHandler->get($pid);
                $cid        = $forum_dest->getVar('cat_id');
                unset($forum_dest);
            } else {
                $cid = abs($dest);
                $pid = 0;
            }
            $forumObject = $forumHandler->get($forum_id);
            $forumObject->setVar('cat_id', $cid);
            $forumObject->setVar('parent_forum', $pid);
            $forumHandler->insert($forumObject);
            if ($forumHandler->insert($forumObject)) {
                if ($cid !== $forumObject->getVar('cat_id') && $subforums = newbbGetSubForum($forum_id)) {
                    $forums = array_map('intval', array_values($subforums));
                    $forumHandler->updateAll('cat_id', $cid, new \Criteria('forum_id', '(' . implode(', ', $forums) . ')', 'IN'));
                }

                //$cacheHelper->delete('forum');
                redirect_header('admin_forum_manager.php?op=manage', 2, _AM_NEWBB_MSG_FORUM_MOVED);
            } else {
                redirect_header('admin_forum_manager.php?op=manage', 2, _AM_NEWBB_MSG_ERR_FORUM_MOVED);
            }
        } else {
            $box = '<select name="dest_forum">';
            $box .= '<option value=0 selected>' . _SELECT . '</option>';
            $box .= newbbForumSelectBox($forum_id, 'all', true, true);
            $box .= '</select>';

            echo "<table width='100%' border='0' cellspacing='1' class='outer'>" . "<tr><td class='odd'>";
            echo '<form action="./admin_forum_manager.php" method="post" name="forummove" id="forummove">';
            echo '<input type="hidden" name="op" value="moveforum" />';
            echo '<input type="hidden" name="forum" value=' . $forum_id . ' />';
            echo '<table border="0" cellpadding="1" cellspacing="0" align="center" valign="top" width="95%"><tr>';
            echo '<td class="bg2" align="center"><strong>' . _AM_NEWBB_MOVETHISFORUM . '</strong></td>';
            echo '</tr>';
            echo '<tr><td class="bg1" align="center">' . $box . '</td></tr>';
            echo '<tr><td align="center"><input type="submit" name="save" value=' . _GO . ' class="button" /></td></tr>';
            echo '</table></form>';
            echo '</td></tr></table>';
        }
        break;

    case 'mergeforum':
        //if (!$newXoopsModuleGui) loadModuleAdminMenu(2, "");

        if (Request::getString('dest_forum', '', 'POST')) {
            $forum_dest = $forumHandler->get(Request::getString('dest_forum', '', 'POST'));
            if (is_object($forum_dest)) {
                $cid         = $forum_dest->getVar('cat_id');
                $sql         = '    UPDATE ' . $GLOBALS['xoopsDB']->prefix('newbb_posts') . '    SET forum_id=' . Request::getInt('dest_forum', 0, 'POST') . "    WHERE forum_id=$forum_id";
                $result_post = $GLOBALS['xoopsDB']->queryF($sql);

                $sql          = '    UPDATE ' . $GLOBALS['xoopsDB']->prefix('newbb_topics') . '    SET forum_id=' . Request::getInt('dest_forum', 0, 'POST') . "    WHERE forum_id=$forum_id";
                $result_topic = $GLOBALS['xoopsDB']->queryF($sql);

                $forumObject = $forumHandler->get($forum_id);
                $forumHandler->updateAll('parent_forum', Request::getInt('dest_forum', 0, 'POST'), new \Criteria('parent_forum', $forum_id));
                if ($cid !== $forumObject->getVar('cat_id') && $subforums = newbbGetSubForum($forum_id)) {
                    $forums = array_map('intval', array_values($subforums));
                    $forumHandler->updateAll('cat_id', $cid, new \Criteria('forum_id', '(' . implode(', ', $forums) . ')', 'IN'));
                }

                $forumHandler->delete($forumObject);

                $forumHandler->synchronization($forum_dest);
                unset($forum_dest);
                $cacheHelper->delete('forum');

                redirect_header('admin_forum_manager.php?op=manage', 2, _AM_NEWBB_MSG_FORUM_MERGED);
            } else {
                redirect_header('admin_forum_manager.php?op=manage', 2, _AM_NEWBB_MSG_ERR_FORUM_MOVED);
            }
        } else {
            $box = '<select name="dest_forum">';
            $box .= '<option value=0 selected>' . _SELECT . '</option>';
            $box .= newbbForumSelectBox($forum_id, 'all');
            $box .= '</select>';
            echo "<table width='100%' border='0' cellspacing='1' class='outer'>" . "<tr><td class='odd'>";
            echo '<form action="' . xoops_getenv('PHP_SELF') . '" method="post" name="forummove" id="forummove">';
            echo '<input type="hidden" name="op" value="mergeforum" />';
            echo '<input type="hidden" name="forum" value=' . $forum_id . ' />';
            echo '<table border="0" cellpadding="1" cellspacing="0" align="center" valign="top" width="95%"><tr>';
            echo '<td class="bg2" align="center"><strong>' . _AM_NEWBB_MERGETHISFORUM . '</strong></td>';
            echo '</tr>';
            echo '<tr><td class="bg1" align="center">' . _AM_NEWBB_MERGETO_FORUM . '</td></tr>';
            echo '<tr><td class="bg1" align="center">' . $box . '</td></tr>';
            echo '<tr><td align="center"><input type="submit" name="save" value=' . _GO . ' class="button" /></td></tr>';
            echo '</form></table>';
            echo '</td></tr></table>';
        }
        break;

    case 'save':

        if ($forum_id) {
            $forumObject = $forumHandler->get($forum_id);
            $message     = _AM_NEWBB_FORUMUPDATE;
        } else {
            $forumObject = $forumHandler->create();
            $message     = _AM_NEWBB_FORUMCREATED;
        }

        $forumObject->setVar('forum_name', Request::getString('forum_name', '', 'POST'));
        $forumObject->setVar('forum_desc', Request::getString('forum_desc', '', 'POST'));
        $forumObject->setVar('forum_order', Request::getInt('forum_order', 0, 'POST'));
        $forumObject->setVar('forum_moderator', Request::getArray('forum_moderator', [], 'POST'));
        $forumObject->setVar('parent_forum', Request::getInt('parent_forum', 0, 'POST'));
        $forumObject->setVar('attach_maxkb', Request::getInt('attach_maxkb', 0, 'POST'));
        $forumObject->setVar('attach_ext', Request::getString('attach_ext', '', 'POST'));
        $forumObject->setVar('hot_threshold', Request::getInt('hot_threshold', 0, 'POST'));
        if (Request::getInt('parent_forum', 0, 'POST')) {
            $parentObject    = $forumHandler->get(Request::getInt('parent_forum', 0, 'POST'), ['cat_id']);
            $_POST['cat_id'] = $parentObject->getVar('cat_id');
        }
        $forumObject->setVar('cat_id', Request::getInt('cat_id', 0, 'POST'));

        if ($forumHandler->insert($forumObject)) {
            //$cacheHelper->delete('forum');
            if (Request::getInt('perm_template', 0, 'POST')) {
                //                /** var Newbb\PermissionHandler $permHandler */
                //                $permHandler = Newbb\Helper::getInstance()->getHandler('Permission');
                $perm_template = $permHandler->getTemplate();
                /** @var \XoopsMemberHandler $memberHandler */
                $memberHandler = xoops_getHandler('member');
                $glist         = $memberHandler->getGroupList();
                $perms         = $permHandler->getValidForumPerms(true);
                foreach (array_keys($glist) as $group) {
                    foreach ($perms as $perm) {
                        $ids = $permHandler->getItemIds($perm, $group, $xoopsModule->getVar('mid'));
                        if (!in_array($forumObject->getVar('forum_id'), $ids)) {
                            if (empty($perm_template[$group][$perm])) {
                                $permHandler->deleteRight($perm, $forumObject->getVar('forum_id'), $group, $xoopsModule->getVar('mid'));
                            } else {
                                $permHandler->addRight($perm, $forumObject->getVar('forum_id'), $group, $xoopsModule->getVar('mid'));
                            }
                        }
                    }
                }
            }
            redirect_header('admin_forum_manager.php', 2, $message);
        } else {
            redirect_header('admin_forum_manager.php?op=mod&amp;forum=' . $forumObject->getVar('forum_id') . '', 2, _AM_NEWBB_FORUM_ERROR);
        }
        break;

    case 'mod':
        $forumObject = $forumHandler->get($forum_id);
        include $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname') . '/include/form.forum.php');
        break;

    case 'del':
        if (1 !== Request::getInt('confirm', 0, 'POST')) {
            xoops_confirm(['op' => 'del', 'forum' => Request::getInt('forum', 0, 'GET'), 'confirm' => 1], 'admin_forum_manager.php', _AM_NEWBB_TWDAFAP);
            break;
        } else {
            $forumObject = $forumHandler->get(Request::getInt('forum', 0, 'POST'));
            $forumHandler->delete($forumObject);
            //$cacheHelper->delete('forum');
            redirect_header('admin_forum_manager.php?op=manage', 1, _AM_NEWBB_FORUMREMOVED);
        }
        break;

    case 'addforum':
        echo '<br>';
        $parent_forum = Request::getInt('forum', 0, 'GET');
        $cat_id       = Request::getInt('cat_id', 0, 'GET');
        if (!$parent_forum && !$cat_id) {
            break;
        }
        $forumObject = $forumHandler->create();
        $forumObject->setVar('parent_forum', $parent_forum);
        $forumObject->setVar('cat_id', $cat_id);
        include $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname') . '/include/form.forum.php');
        break;

    default:

        //        /** @var Newbb\CategoryHandler $categoryHandler */
        //        $categoryHandler  = Newbb\Helper::getInstance()->getHandler('Category');
        $criteriaCategory = new \CriteriaCompo(new \Criteria('1', 1));
        $criteriaCategory->setSort('cat_order');
        $categories = $categoryHandler->getList($criteriaCategory);
        if (0 === count($categories)) {
            redirect_header('admin_cat_manager.php', 2, _AM_NEWBB_CREATENEWCATEGORY);
        }

        $echo = $adminObject->displayNavigation(basename(__FILE__));
        $echo .= "<table width='100%' border='0' cellspacing='1' class='outer'>" . "<tr><td class='odd'>";
        $echo .= "<table border='0' cellpadding='4' cellspacing='1' width='100%' class='outer'>";
        $echo .= "<tr align='center'>";
        $echo .= "<th class='bg3' colspan='2'>" . _AM_NEWBB_NAME . '</th>';
        $echo .= "<th class='bg3'>" . _AM_NEWBB_EDIT . '</th>';
        $echo .= "<th class='bg3'>" . _AM_NEWBB_DELETE . '</th>';
        $echo .= "<th class='bg3'>" . _AM_NEWBB_ADD . '</th>';
        $echo .= "<th class='bg3'>" . _AM_NEWBB_MOVE . '</th>';
        $echo .= "<th class='bg3'>" . _AM_NEWBB_MERGE . '</th>';
        $echo .= '</tr>';

        //        $categoryHandler  = Newbb\Helper::getInstance()->getHandler('Category');
        $criteriaCategory = new \CriteriaCompo(new \Criteria('1', 1));
        $criteriaCategory->setSort('cat_order');
        $categories = $categoryHandler->getList($criteriaCategory);
        $forums     = $forumHandler->getTree(array_keys($categories), 0, 'all');
        foreach (array_keys($categories) as $c) {
            $category       = $categories[$c];
            $cat_id         = $c;
            $cat_link       = '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/index.php?viewcat=' . $cat_id . '">' . $category . '</a>';
            $cat_edit_link  = '<a href="admin_cat_manager.php?op=mod&amp;cat_id=' . $cat_id . '">' . newbbDisplayImage('admin_edit', _EDIT) . '</a>';
            $cat_del_link   = '<a href="admin_cat_manager.php?op=del&amp;cat_id=' . $cat_id . '">' . newbbDisplayImage('admin_delete', _DELETE) . '</a>';
            $forum_add_link = '<a href="admin_forum_manager.php?op=addforum&amp;cat_id=' . $cat_id . '">' . newbbDisplayImage('new_forum') . '</a>';
            $echo           .= "<tr class='even' align='left'>";
            $echo           .= "<td width='100%' colspan='2'><strong>" . $cat_link . '</strong></td>';
            $echo           .= "<td align='center'>" . $cat_edit_link . '</td>';
            $echo           .= "<td align='center'>" . $cat_del_link . '</td>';
            $echo           .= "<td align='center'>" . $forum_add_link . '</td>';
            $echo           .= '<td></td>';
            $echo           .= '<td></td>';
            $echo           .= '</tr>';
            if (!isset($forums[$c])) {
                continue;
            }
            $i = 0;
            foreach (array_keys($forums[$c]) as $f) {
                $forum        = $forums[$c][$f];
                $f_link       = $forum['prefix'] . '<a href="' . XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname', 'n') . '/viewforum.php?forum=' . $f . '">' . $forum['forum_name'] . '</a>';
                $f_edit_link  = '<a href="admin_forum_manager.php?op=mod&amp;forum=' . $f . '">' . newbbDisplayImage('admin_edit', _AM_NEWBB_EDIT) . '</a>';
                $f_del_link   = '<a href="admin_forum_manager.php?op=del&amp;forum=' . $f . '">' . newbbDisplayImage('admin_delete', _AM_NEWBB_DELETE) . '</a>';
                $sf_add_link  = '<a href="admin_forum_manager.php?op=addforum&amp;cat_id=' . $c . '&forum=' . $f . '">' . newbbDisplayImage('new_forum', _AM_NEWBB_CREATEFORUM) . '</a>';
                $f_move_link  = '<a href="admin_forum_manager.php?op=moveforum&amp;forum=' . $f . '">' . newbbDisplayImage('admin_move', _AM_NEWBB_MOVE) . '</a>';
                $f_merge_link = '<a href="admin_forum_manager.php?op=mergeforum&amp;forum=' . $f . '">' . newbbDisplayImage('admin_merge', _AM_NEWBB_MERGE) . '</a>';

                $class = (($i++) % 2) ? 'odd' : 'even';
                $echo  .= "<tr class='" . $class . "' align='left'><td></td>";
                $echo  .= '<td><strong>' . $f_link . '</strong></td>';
                $echo  .= "<td align='center'>" . $f_edit_link . '</td>';
                $echo  .= "<td align='center'>" . $f_del_link . '</td>';
                $echo  .= "<td align='center'>" . $sf_add_link . '</td>';
                $echo  .= "<td align='center'>" . $f_move_link . '</td>';
                $echo  .= "<td align='center'>" . $f_merge_link . '</td>';
                $echo  .= '</tr>';
            }
        }
        unset($forums, $categories);

        echo $echo;
        echo '</table>';
        echo '</td></tr></table>';
        echo '<fieldset>';
        echo '<legend>&nbsp;' . _MI_NEWBB_ADMENU_FORUM . '&nbsp;</legend>';
        echo _AM_NEWBB_HELP_FORUM_TAB;
        echo '<br>' . newbbDisplayImage('admin_edit', _EDIT) . '&nbsp;-&nbsp;' . _EDIT;
        echo '<br>' . newbbDisplayImage('admin_delete', _DELETE) . '&nbsp;-&nbsp;' . _DELETE;
        echo '<br>' . newbbDisplayImage('new_forum', _AM_NEWBB_CREATEFORUM) . '&nbsp;-&nbsp;' . _AM_NEWBB_CREATEFORUM;
        echo '<br>' . newbbDisplayImage('admin_move', _AM_NEWBB_MOVE) . '&nbsp;-&nbsp;' . _AM_NEWBB_MOVE;
        echo '<br>' . newbbDisplayImage('admin_merge', _AM_NEWBB_MERGE) . '&nbsp;-&nbsp;' . _AM_NEWBB_MERGE;
        echo '</fieldset>';
        break;
}
require_once __DIR__ . '/admin_footer.php';

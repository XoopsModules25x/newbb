<?php
// 
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System                      //
// Copyright (c) 2000-2016 XOOPS.org                           //
// <http://xoops.org/>                             //
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
// URL: http://www.myweb.ne.jp/, http://xoops.org/, http://jp.xoops.org/ //
// Project: XOOPS Project                                                    //
// ------------------------------------------------------------------------- //
include_once __DIR__ . '/admin_header.php';
include $GLOBALS['xoops']->path('class/xoopstree.php');
include_once $GLOBALS['xoops']->path('class/pagenav.php');
mod_loadFunctions('forum', 'newbb');
mod_loadFunctions('render', 'newbb');
load_functions('cache');

xoops_cp_header();

$op       = XoopsRequest::getCmd('op', XoopsRequest::getCmd('op', '', 'POST'), 'GET'); // !empty($_GET['op'])? $_GET['op'] : (!empty($_POST['op'])?$_POST['op']:"");
$forum_id = XoopsRequest::getInt('forum', XoopsRequest::getInt('forum', 0, 'POST'), 'GET'); //(int)( !empty($_GET['forum'])? $_GET['forum'] : (!empty($_POST['forum'])?$_POST['forum']:0) );

$forumHandler = xoops_getModuleHandler('forum', 'newbb');
switch ($op) {
    case 'moveforum':
        //if (!$newXoopsModuleGui) loadModuleAdminMenu(2, "");

        if (XoopsRequest::getInt('dest_forum', 0, 'POST')) {
            $dest = XoopsRequest::getInt('dest_forum', 0, 'POST');
            if ($dest > 0) {
                $pid        = $dest;
                $forum_dest = $forumHandler->get($pid);
                $cid        = $forum_dest->getVar('cat_id');
                unset($forum_dest);
            } else {
                $cid = abs($dest);
                $pid = 0;
            }
            $forum_obj = $forumHandler->get($forum_id);
            $forum_obj->setVar('cat_id', $cid);
            $forum_obj->setVar('parent_forum', $pid);
            $forumHandler->insert($forum_obj);
            if ($forumHandler->insert($forum_obj)) {
                if ($cid !== $forum_obj->getVar('cat_id') && $subforums = newbb_getSubForum($forum_id)) {
                    $forums = array_map('intval', array_values($subforums));
                    $forumHandler->updateAll('cat_id', $cid, new Criteria('forum_id', '(' . implode(', ', $forums) . ')', 'IN'));
                }

                mod_clearCacheFile('forum', 'newbb');
                redirect_header('admin_forum_manager.php?op=manage', 2, _AM_NEWBB_MSG_FORUM_MOVED);
            } else {
                redirect_header('admin_forum_manager.php?op=manage', 2, _AM_NEWBB_MSG_ERR_FORUM_MOVED);
            }
        } else {
            $box = '<select name="dest_forum">';
            $box .= '<option value=0 selected>' . _SELECT . '</option>';
            $box .= newbb_forumSelectBox($forum_id, 'all', true, true);
            $box .= '</select>';

            echo '<form action="./admin_forum_manager.php" method="post" name="forummove" id="forummove">';
            echo '<input type="hidden" name="op" value="moveforum" />';
            echo '<input type="hidden" name="forum" value=' . $forum_id . ' />';
            echo '<table border="0" cellpadding="1" cellspacing="0" align="center" valign="top" width="95%"><tr>';
            echo '<td class="bg2" align="center"><strong>' . _AM_NEWBB_MOVETHISFORUM . '</strong></td>';
            echo '</tr>';
            echo '<tr><td class="bg1" align="center">' . $box . '</td></tr>';
            echo '<tr><td align="center"><input type="submit" name="save" value=' . _GO . ' class="button" /></td></tr>';
            echo '</table></form>';
        }
        break;

    case 'mergeforum':
        //if (!$newXoopsModuleGui) loadModuleAdminMenu(2, "");

        if (XoopsRequest::getString('dest_forum', '', 'POST')) {
            $forum_dest = $forumHandler->get(XoopsRequest::getString('dest_forum', '', 'POST'));
            if (is_object($forum_dest)) {
                $cid         = $forum_dest->getVar('cat_id');
                $sql         = '    UPDATE ' . $GLOBALS['xoopsDB']->prefix('bb_posts') . '    SET forum_id=' . XoopsRequest::getInt('dest_forum', 0, 'POST') . "    WHERE forum_id=$forum_id";
                $result_post = $GLOBALS['xoopsDB']->queryF($sql);

                $sql          = '    UPDATE ' . $GLOBALS['xoopsDB']->prefix('bb_topics') . '    SET forum_id=' . XoopsRequest::getInt('dest_forum', 0, 'POST') . "    WHERE forum_id=$forum_id";
                $result_topic = $GLOBALS['xoopsDB']->queryF($sql);

                $forum_obj = $forumHandler->get($forum_id);
                $forumHandler->updateAll('parent_forum', XoopsRequest::getInt('dest_forum', 0, 'POST'), new Criteria('parent_forum', $forum_id));
                if ($cid !== $forum_obj->getVar('cat_id') && $subforums = newbb_getSubForum($forum_id)) {
                    $forums = array_map('intval', array_values($subforums));
                    $forumHandler->updateAll('cat_id', $cid, new Criteria('forum_id', '(' . implode(', ', $forums) . ')', 'IN'));
                }

                $forumHandler->delete($forum_obj);

                //mod_clearCacheFile("forum", "newbb");
                $forumHandler->synchronization($forum_dest);
                unset($forum_dest);
                mod_clearCacheFile('forum', 'newbb');

                redirect_header('admin_forum_manager.php?op=manage', 2, _AM_NEWBB_MSG_FORUM_MERGED);
            } else {
                redirect_header('admin_forum_manager.php?op=manage', 2, _AM_NEWBB_MSG_ERR_FORUM_MOVED);
            }
        } else {
            $box = '<select name="dest_forum">';
            $box .= '<option value=0 selected>' . _SELECT . '</option>';
            $box .= newbb_forumSelectBox($forum_id, 'all');
            $box .= '</select>';

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
        }
        break;

    case 'save':

        if ($forum_id) {
            $forum_obj = $forumHandler->get($forum_id);
            $message   = _AM_NEWBB_FORUMUPDATE;
        } else {
            $forum_obj = $forumHandler->create();
            $message   = _AM_NEWBB_FORUMCREATED;
        }

        $forum_obj->setVar('forum_name', XoopsRequest::getString('forum_name', '', 'POST'));
        $forum_obj->setVar('forum_desc', XoopsRequest::getString('forum_desc', '', 'POST'));
        $forum_obj->setVar('forum_order', XoopsRequest::getInt('forum_order', 0, 'POST'));
        $forum_obj->setVar('forum_moderator', XoopsRequest::getArray('forum_moderator', [], 'POST'));
        $forum_obj->setVar('parent_forum', XoopsRequest::getInt('parent_forum', 0, 'POST'));
        $forum_obj->setVar('attach_maxkb', XoopsRequest::getInt('attach_maxkb', 0, 'POST'));
        $forum_obj->setVar('attach_ext', XoopsRequest::getString('attach_ext', '', 'POST'));
        $forum_obj->setVar('hot_threshold', XoopsRequest::getInt('hot_threshold', 0, 'POST'));
        if (XoopsRequest::getInt('parent_forum', 0, 'POST')) {
            $parent_obj      = $forumHandler->get(XoopsRequest::getInt('parent_forum', 0, 'POST'), ['cat_id']);
            $_POST['cat_id'] = $parent_obj->getVar('cat_id');
        }
        $forum_obj->setVar('cat_id', XoopsRequest::getInt('cat_id', 0, 'POST'));

        if ($forumHandler->insert($forum_obj)) {
            mod_clearCacheFile('forum', 'newbb');
            if (XoopsRequest::getInt('perm_template', 0, 'POST')) {
                $grouppermHandler = xoops_getModuleHandler('permission', $xoopsModule->getVar('dirname'));
                $perm_template    = $grouppermHandler->getTemplate();
                $memberHandler    = xoops_getHandler('member');
                $glist            = $memberHandler->getGroupList();
                $perms            = $grouppermHandler->getValidForumPerms(true);
                foreach (array_keys($glist) as $group) {
                    foreach ($perms as $perm) {
                        $ids = $grouppermHandler->getItemIds($perm, $group, $xoopsModule->getVar('mid'));
                        if (!in_array($forum_obj->getVar('forum_id'), $ids)) {
                            if (empty($perm_template[$group][$perm])) {
                                $grouppermHandler->deleteRight($perm, $forum_obj->getVar('forum_id'), $group, $xoopsModule->getVar('mid'));
                            } else {
                                $grouppermHandler->addRight($perm, $forum_obj->getVar('forum_id'), $group, $xoopsModule->getVar('mid'));
                            }
                        }
                    }
                }
            }
            redirect_header('admin_forum_manager.php', 2, $message);
        } else {
            redirect_header('admin_forum_manager.php?op=mod&amp;forum=' . $forum_obj->getVar('forum_id') . '', 2, _AM_NEWBB_FORUM_ERROR);
        }
        break;

    case 'mod':
        $forum_obj = $forumHandler->get($forum_id);
        //if (!$newXoopsModuleGui) loadModuleAdminMenu(2, _AM_NEWBB_EDITTHISFORUM . $forum_obj->getVar('forum_name'));
        echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_NEWBB_EDITTHISFORUM . '</legend>';

        include $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname') . '/include/form.forum.php');

        echo '</fieldset>';
        break;

    case 'del':
        if (1 !== XoopsRequest::getInt('confirm', 0, 'POST')) {
            xoops_confirm(['op' => 'del', 'forum' => XoopsRequest::getInt('forum', 0, 'GET'), 'confirm' => 1], 'admin_forum_manager.php', _AM_NEWBB_TWDAFAP);
            break;
        } else {
            $forum_obj = $forumHandler->get(XoopsRequest::getInt('forum', 0, 'POST'));
            $forumHandler->delete($forum_obj);
            mod_clearCacheFile('forum', 'newbb');
            redirect_header('admin_forum_manager.php?op=manage', 1, _AM_NEWBB_FORUMREMOVED);
        }
        break;

    case 'addforum':
        //if (!$newXoopsModuleGui) loadModuleAdminMenu(2, _AM_NEWBB_CREATENEWFORUM);
        echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_NEWBB_CREATENEWFORUM . '</legend>';
        echo '<br>';
        $parent_forum = XoopsRequest::getInt('forum', 0, 'GET');
        $cat_id       = XoopsRequest::getInt('cat_id', 0, 'GET');
        if (!$parent_forum && !$cat_id) {
            break;
        }
        $forum_obj = $forumHandler->create();
        $forum_obj->setVar('parent_forum', $parent_forum);
        $forum_obj->setVar('cat_id', $cat_id);
        include $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname') . '/include/form.forum.php');

        echo '</fieldset>';

        break;

    default:

        $categoryHandler  = xoops_getModuleHandler('category', 'newbb');
        $criteriaCategory = new CriteriaCompo(new criteria('1', 1));
        $criteriaCategory->setSort('cat_order');
        $categories = $categoryHandler->getList($criteriaCategory);
        if (0 === count($categories)) {
            redirect_header('admin_cat_manager.php', 2, _AM_NEWBB_CREATENEWCATEGORY);
        }

        $echo = $indexAdmin->addNavigation(basename(__FILE__));
        $echo .= '<fieldset>';

        $echo .= "<table border='0' cellpadding='4' cellspacing='1' width='100%' class='outer'>";
        $echo .= "<tr align='center'>";
        $echo .= "<th class='bg3' colspan='2'>" . _AM_NEWBB_NAME . '</th>';
        $echo .= "<th class='bg3'>" . _AM_NEWBB_EDIT . '</th>';
        $echo .= "<th class='bg3'>" . _AM_NEWBB_DELETE . '</th>';
        $echo .= "<th class='bg3'>" . _AM_NEWBB_ADD . '</th>';
        $echo .= "<th class='bg3'>" . _AM_NEWBB_MOVE . '</th>';
        $echo .= "<th class='bg3'>" . _AM_NEWBB_MERGE . '</th>';
        $echo .= '</tr>';

        $categoryHandler  = xoops_getModuleHandler('category', 'newbb');
        $criteriaCategory = new CriteriaCompo(new criteria('1', 1));
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
            $echo .= "<tr class='even' align='left'>";
            $echo .= "<td width='100%' colspan='2'><strong>" . $cat_link . '</strong></td>';
            $echo .= "<td align='center'>" . $cat_edit_link . '</td>';
            $echo .= "<td align='center'>" . $cat_del_link . '</td>';
            $echo .= "<td align='center'>" . $forum_add_link . '</td>';
            $echo .= '<td></td>';
            $echo .= '<td></td>';
            $echo .= '</tr>';
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
                $echo .= "<tr class='" . $class . "' align='left'><td></td>";
                $echo .= '<td><strong>' . $f_link . '</strong></td>';
                $echo .= "<td align='center'>" . $f_edit_link . '</td>';
                $echo .= "<td align='center'>" . $f_del_link . '</td>';
                $echo .= "<td align='center'>" . $sf_add_link . '</td>';
                $echo .= "<td align='center'>" . $f_move_link . '</td>';
                $echo .= "<td align='center'>" . $f_merge_link . '</td>';
                $echo .= '</tr>';
            }
        }
        unset($forums, $categories);

        echo $echo;
        echo '</table>';
        echo '</fieldset>';
        break;
}
include_once __DIR__ . '/admin_footer.php';

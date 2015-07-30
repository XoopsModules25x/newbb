<?php
// $Id: admin_groupmod.php,v 4.0 2010/01/06 16:43:32 dhcst$
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System                      //
// Copyright (c) 2000 XOOPS.org                           //
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
// Author: Dirk Herrmann (AKA alfred)                                          //
// URL: http://www.mymyxoops.org/, http://simple-xoops.de/ //
// Project: XOOPS Project                                                    //
// ------------------------------------------------------------------------- //
include_once __DIR__ . '/admin_header.php';
xoops_cp_header();
echo '<fieldset>';
include_once $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname') . '/class/xoopsformloader.php');
if (!$newXoopsModuleGui) {
    //loadModuleAdminMenu(10,_AM_NEWBB_GROUPMOD_TITLE);
    echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_NEWBB_GROUPMOD_TITLE . '</legend>';
} else {
    echo $indexAdmin->addNavigation('admin_groupmod.php');
}
$member_handler =&xoops_gethandler('member');
$forumHandler  = &xoops_getmodulehandler('forum', 'newbb');
if (XoopsRequest::getString('submit', '', 'POST')) {
    $fgroups = XoopsRequest::getArray('group', '', 'POST');// !empty($_POST['group']) ? $_POST['group'] : '';
    $fforum  = XoopsRequest::getInt('forenid', 0, 'POST');// (int)($_POST['forenid']);
    $fuser   = array();
    if ($fforum !== 0) {
        if ($fgroups !== '') {
            foreach ($fgroups as $k) {
                $gg = &$member_handler->getUsersByGroup($k, false);
                foreach ($gg as $f) {
                    if (!in_array($f, $fuser)) {
                        $fuser[] = $f;
                    }
                }
            }
        }
        if ($fforum === -1) { // alle Foren
            $sql = 'UPDATE ' . $GLOBALS['xoopsDB']->prefix('bb_forums') . " SET forum_moderator='" . serialize($fuser) . "'";
        } else {
            $sql = 'UPDATE ' . $GLOBALS['xoopsDB']->prefix('bb_forums') . " SET forum_moderator='" . serialize($fuser) . "' WHERE forum_id =" . $fforum;
        }
        if (is_array($fuser) && $GLOBALS['xoopsDB']->queryF($sql)) {
            $mess = _AM_NEWBB_GROUPMOD_ADDMOD;
        } else {
            $mess = _AM_NEWBB_GROUPMOD_ERRMOD . '<br /><small>( ' . $sql . ' )</small>';
        }
        redirect_header('admin_groupmod.php', 1, $mess);
//        echo '<div class="confirmMsg">' . $mess . '</div><br /><br />';
    }
}

echo _AM_NEWBB_GROUPMOD_TITLEDESC;
echo "<br /><br /><table width='100%' border='0' cellspacing='1' class='outer'>"
     . "<tr><td class='odd'>";
echo "<form name='reorder' method='post'>";
$categoryHandler  = &xoops_getmodulehandler('category', 'newbb');
$criteriaCategory = new CriteriaCompo(new criteria('1', 1));
$criteriaCategory->setSort('cat_order');
$categories = $categoryHandler->getAll($criteriaCategory, array('cat_id', 'cat_order', 'cat_title'));
$forums     = $forumHandler->getTree(array_keys($categories), 0, 'all', '&nbsp;&nbsp;&nbsp;&nbsp;');
echo '<select name="forenid">';
echo '<option value="-1">-- ' . _AM_NEWBB_GROUPMOD_ALLFORUMS . ' --</option>';
foreach (array_keys($categories) as $c) {
    if (!isset($forums[$c])) {
        continue;
    }
    $i = 0;
    foreach ($forums[$c] as $key => $forum) {
        echo '<option value="' . $forum['forum_id'] . '"> ' . $categories[$c]->getVar('cat_title') . '::' . $forum['forum_name'] . '</option>';
    }
}
echo '</select>';
echo "</td><tr><tr><td class='even'>";
$groups =& $member_handler->getGroups();
foreach ($groups as $value) {
    echo '<input type="checkbox" name="group[]" value="' . $value->getVar('groupid') . '" /> ' . $value->getVar('name') . '<br />';
}
echo "</td><tr><tr><td class='odd'>";

echo '<input type="submit" value="' . _SUBMIT . '" name="submit" />';
echo '</td></tr></table>';
echo '</form></fieldset>';
echo '</fieldset>';
xoops_cp_footer();

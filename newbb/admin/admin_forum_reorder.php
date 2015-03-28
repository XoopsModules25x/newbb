<?php
// $Id: admin_forum_reorder.php 62 2012-08-17 10:15:26Z alfred $
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System                      //
// Copyright (c) 2000 XOOPS.org                           //
// <http://www.xoops.org/>                             //
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
// URL: http://www.myweb.ne.jp/, http://www.xoops.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //
include_once __DIR__ . '/admin_header.php';

if (XoopsRequest::getInt('cat_orders', 0, 'POST')) $cat_orders = XoopsRequest::getInt('cat_orders', 0, 'POST');
if (XoopsRequest::getInt('orders', 0, 'POST')) $orders = XoopsRequest::getInt('orders', 0, 'POST');
if (XoopsRequest::getInt('cat', 0, 'POST')) $cat = XoopsRequest::getInt('cat', 0, 'POST');
if (XoopsRequest::getInt('forum', 0, 'POST')) $forum = XoopsRequest::getInt('forum', 0, 'POST');

if (XoopsRequest::getString('submit', '', 'POST')) {
    $catOrdersCount = count($cat_orders);
    for ($i = 0; $i < $catOrdersCount; ++$i) {
        $sql = "update " . $xoopsDB->prefix("bb_categories") . " set cat_order = " . $cat_orders[$i] . " WHERE cat_id=$cat[$i]";
        if (!$result = $xoopsDB->query($sql)) {
            redirect_header("admin_forum_reorder.php", 1, _AM_NEWBB_FORUM_ERROR);
        }
    }
    $ordersCount = count($orders);
    for ($i = 0; $i < $ordersCount; ++$i) {
        $sql = "update " . $xoopsDB->prefix("bb_forums") . " set forum_order = " . $orders[$i] . " WHERE forum_id=" . $forum[$i];
        if (!$result = $xoopsDB->query($sql)) {
            redirect_header("admin_forum_reorder.php", 1, _AM_NEWBB_FORUM_ERROR);
        }
    }
    redirect_header("admin_forum_reorder.php", 1, _AM_NEWBB_BOARDREORDER);
} else {
    include_once $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar("dirname") . "/class/xoopsformloader.php");
    $orders     = array();
    $cat_orders = array();
    $forum      = array();
    $cat        = array();

    xoops_cp_header();
    echo "<fieldset>";

    if (!$newXoopsModuleGui) {
        //loadModuleAdminMenu(4, _AM_NEWBB_SETFORUMORDER);
        echo "<fieldset>";
        echo "<legend style='font-weight: bold; color: #900;'>" . _AM_NEWBB_SETFORUMORDER . "</legend>";
    } else echo $indexAdmin->addNavigation('admin_forum_reorder.php');

    echo "<table width='100%' border='0' cellspacing='1' class='outer'>"
         . "<tr><td class='odd'>";
    $tform = new XoopsThemeForm(_AM_NEWBB_SETFORUMORDER, "", "");
    $tform->display();
    echo "<form name='reorder' method='post'>";
    echo "<table border='0' width='100%' cellpadding='2' cellspacing='1' class='outer'>";
    echo "<tr>";
    echo "<td class='head' align='left' width='60%'><strong>" . _AM_NEWBB_REORDERTITLE . "</strong></td>";
    echo "<td class='head' align='center'><strong>" . _AM_NEWBB_REORDERWEIGHT . "</strong></td>";
    echo "</tr>";

    $forum_handler     = &xoops_getmodulehandler('forum', 'newbb');
    $category_handler  = &xoops_getmodulehandler('category', 'newbb');
    $criteria_category = new CriteriaCompo(new criteria('1', 1));
    $criteria_category->setSort('cat_order');
    $categories = $category_handler->getAll($criteria_category, array("cat_id", "cat_order", "cat_title"));
    $forums     = $forum_handler->getTree(array_keys($categories), 0, 'all', "&nbsp;&nbsp;&nbsp;&nbsp;");
    foreach (array_keys($categories) as $c) {
        echo "<tr>";
        echo "<td align='left' nowrap='nowrap' class='head' >" . $categories[$c]->getVar("cat_title") . "</td>";
        echo "<td align='right' class='head'>";
        echo "<input type='text' name='cat_orders[]' value='" . $categories[$c]->getVar('cat_order') . "' size='5' maxlength='5' />";
        echo "<input type='hidden' name='cat[]' value='" . $c . "' />";
        echo "</td>";
        echo "</tr>";

        if (!isset($forums[$c])) continue;
        $i = 0;
        foreach ($forums[$c] as $key => $forum) {
            echo "<tr>";
            $class = ((++$i) % 2) ? "odd" : "even";
            echo "<td align='left' nowrap='nowrap' class='" . $class . "'>" . $forum['prefix'] . $forum['forum_name'] . "</td>";
            echo "<td align='left' class='" . $class . "'>";
            echo $forum['prefix'] . "<input type='text' name='orders[]' value='" . $forum['forum_order'] . "' size='5' maxlength='5' />";
            echo "<input type='hidden' name='forum[]' value='" . $key . "' />";
            echo "</td>";
            echo "</tr>";
        }
    }
    echo "<tr><td class='even' align='center' colspan='6'>";

    echo "<input type='submit' name='submit' value='" . _SUBMIT . "' />";
    echo "</td></tr>";
    echo "</table>";
    echo "</form>";
    echo "</td></tr></table>";
    echo "</fieldset>";
    if (!$newXoopsModuleGui) echo "</fieldset>";
}
xoops_cp_footer();

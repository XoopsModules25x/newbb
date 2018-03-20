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

require_once __DIR__ . '/admin_header.php';

$cat_orders = Request::getArray('cat_orders', null, 'POST');
$orders     = Request::getArray('orders', null, 'POST');
$cat        = Request::getArray('cat', null, 'POST');
$forum      = Request::getArray('forum', null, 'POST');

if (Request::getString('submit', '', 'POST')) {
    $catOrdersCount = count($cat_orders);
    for ($i = 0; $i < $catOrdersCount; ++$i) {
        $sql = 'update ' . $GLOBALS['xoopsDB']->prefix('newbb_categories') . ' set cat_order = ' . $cat_orders[$i] . " WHERE cat_id=$cat[$i]";
        if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
            redirect_header('admin_forum_reorder.php', 1, _AM_NEWBB_FORUM_ERROR);
        }
    }
    $ordersCount = count($orders);
    for ($i = 0; $i < $ordersCount; ++$i) {
        $sql = 'update ' . $GLOBALS['xoopsDB']->prefix('newbb_forums') . ' set forum_order = ' . $orders[$i] . ' WHERE forum_id=' . $forum[$i];
        if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
            redirect_header('admin_forum_reorder.php', 1, _AM_NEWBB_FORUM_ERROR);
        }
    }
    redirect_header('admin_forum_reorder.php', 1, _AM_NEWBB_BOARDREORDER);
} else {
    require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
    $orders     = [];
    $cat_orders = [];
    $forum      = [];
    $cat        = [];

    xoops_cp_header();

    $adminObject->displayNavigation(basename(__FILE__));

    echo "<table width='100%' border='0' cellspacing='1' class='outer'>" . "<tr><td class='odd'>";
    $tform = new \XoopsThemeForm(_AM_NEWBB_SETFORUMORDER, '', '');
    $tform->display();
    echo "<form name='reorder' method='post'>";
    echo "<table border='0' width='100%' cellpadding='2' cellspacing='1' class='outer'>";
    echo '<tr>';
    echo "<td class='head' align='left' width='60%'><strong>" . _AM_NEWBB_REORDERTITLE . '</strong></td>';
    echo "<td class='head' align='center'><strong>" . _AM_NEWBB_REORDERWEIGHT . '</strong></td>';
    echo '</tr>';

    //    /** @var Newbb\ForumHandler $forumHandler */
    //    $forumHandler     = Newbb\Helper::getInstance()->getHandler('Forum');
    //    /** @var Newbb\CategoryHandler $categoryHandler */
    //    $categoryHandler  = Newbb\Helper::getInstance()->getHandler('Category');
    $criteriaCategory = new \CriteriaCompo(new \Criteria('1', 1));
    $criteriaCategory->setSort('cat_order');
    $categories = $categoryHandler->getAll($criteriaCategory, ['cat_id', 'cat_order', 'cat_title']);
    $forums     = $forumHandler->getTree(array_keys($categories), 0, 'all', '&nbsp;&nbsp;&nbsp;&nbsp;');
    foreach (array_keys($categories) as $c) {
        echo '<tr>';
        echo "<td align='left' nowrap='nowrap' class='head' >" . $categories[$c]->getVar('cat_title') . '</td>';
        echo "<td align='right' class='head'>";
        echo "<input type='text' name='cat_orders[]' value='" . $categories[$c]->getVar('cat_order') . "' size='5' maxlength='5' />";
        echo "<input type='hidden' name='cat[]' value='" . $c . "' />";
        echo '</td>';
        echo '</tr>';

        if (!isset($forums[$c])) {
            continue;
        }
        $i = 0;
        foreach ($forums[$c] as $key => $forum) {
            echo '<tr>';
            $class = ((++$i) % 2) ? 'odd' : 'even';
            echo "<td align='left' nowrap='nowrap' class='" . $class . "'>" . $forum['prefix'] . $forum['forum_name'] . '</td>';
            echo "<td align='left' class='" . $class . "'>";
            echo $forum['prefix'] . "<input type='text' name='orders[]' value='" . $forum['forum_order'] . "' size='5' maxlength='5' />";
            echo "<input type='hidden' name='forum[]' value='" . $key . "' />";
            echo '</td>';
            echo '</tr>';
        }
    }
    echo "<tr><td class='even' align='center' colspan='6'>";

    echo "<input type='submit' name='submit' value='" . _SUBMIT . "' />";
    echo '</td></tr>';
    echo '</table>';
    echo '</form>';
    echo '</td></tr></table>';
    echo '<fieldset>';
    echo '<legend>&nbsp;' . _MI_NEWBB_ADMENU_ORDER . '&nbsp;</legend>';
    echo _AM_NEWBB_HELP_ORDER_TAB;
    echo '</fieldset>';
}
require_once __DIR__ . '/admin_footer.php';

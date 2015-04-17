<?php
// $Id: about.php 62 2012-08-17 10:15:26Z alfred $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, http://www.xoops.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //

include_once __DIR__ . '/admin_header.php';
xoops_cp_header();

if ($newXoopsModuleGui) {
    $module_info =& $module_handler->get($xoopsModule->getVar("mid"));
    $aboutAdmin  = new ModuleAdmin();
    echo $aboutAdmin->addNavigation('about.php');
    echo $aboutAdmin->renderabout('PBQZ7D6LT6UBC', true);
} else {
    $module_handler =& xoops_gethandler('module');
    $versioninfo    =& $module_handler->get($xoopsModule->getVar('mid'));

    //loadModuleAdminMenu(13, _AM_NEWBB_ABOUT . " " . $versioninfo->getInfo('name'));

    echo "<fieldset>";
    // Left headings...
    echo "<img src='" . XOOPS_URL . "/modules/" . $xoopsModule->dirname() . "/" . $versioninfo->getInfo('image') . "' alt='' hspace='0' vspace='0' align='left' style='margin-right: 10px;' /></a>";
    echo "<div style='margin-top: 10px; color: #33538e; margin-bottom: 4px; font-size: 18px; line-height: 18px; font-weight: bold; display: block;'>" . $versioninfo->getInfo('name') . " version " . $versioninfo->getInfo('version') . " (" . $versioninfo->getInfo('status_version') . ")</div>";

    if ($versioninfo->getInfo('author_realname') !== '') {
        $author_name = $versioninfo->getInfo('author') . " (" . $versioninfo->getInfo('author_realname') . ")";
    } else {
        $author_name = $versioninfo->getInfo('author');
    }

    echo "<div style = 'line-height: 16px; font-weight: bold; display: block;'>" . _AM_NEWBB_BY . " " . $author_name;
    echo "</div>";
    echo "<div style = 'line-height: 16px; display: block;'>" . $versioninfo->getInfo('license') . "</div><br /><br /></>\n";

    // Author Information
    echo "<table width='100%' cellspacing='1' cellpadding='3' border='0' class='outer'>";
    echo "<tr>";
    echo "<td colspan='2' class='bg3' align='left'><strong>" . _AM_NEWBB_AUTHOR_INFO . "</strong></td>";
    echo "</tr>";

    if ($versioninfo->getInfo('$author_name') !== '') {
        echo "<tr>";
        echo "<td class='head' width='150px' align='left'>" . _AM_NEWBB_AUTHOR_NAME . "</td>";
        echo "<td class='even' align='left'>" . $author_name . "</td>";
        echo "</tr>";
    }
    if ($versioninfo->getInfo('author_website_url') !== '') {
        echo "<tr>";
        echo "<td class='head' width='150px' align='left'>" . _AM_NEWBB_AUTHOR_WEBSITE . "</td>";
        echo "<td class='even' align='left'><a href='" . $versioninfo->getInfo('author_website_url') . "' target='_blank'>" . $versioninfo->getInfo('author_website_name') . "</a></td>";
        echo "</tr>";
    }
    if ($versioninfo->getInfo('author_email') !== '') {
        echo "<tr>";
        echo "<td class='head' width='150px' align='left'>" . _AM_NEWBB_AUTHOR_EMAIL . "</td>";
        echo "<td class='even' align='left'><a href='mailto:" . $versioninfo->getInfo('author_email') . "'>" . $versioninfo->getInfo('author_email') . "</a></td>";
        echo "</tr>";
    }
    if ($versioninfo->getInfo('credits') !== '') {
        echo "<tr>";
        echo "<td class='head' width='150px' align='left'>" . _AM_NEWBB_AUTHOR_CREDITS . "</td>";
        echo "<td class='even' align='left'>" . $versioninfo->getInfo('credits') . "</td>";
        echo "</tr>";
    }

    echo "</table>";
    echo "<br />\n";

    // Module Developpment information
    echo "<table width='100%' cellspacing='1' cellpadding='3' border='0' class='outer'>";
    echo "<tr>";
    echo "<td colspan='2' class='bg3' align='left'><strong>" . _AM_NEWBB_MODULE_INFO . "</strong></td>";
    echo "</tr>";

    if ($versioninfo->getInfo('status') !== '') {
        echo "<tr>";
        echo "<td class='head' width='200' align='left'>" . _AM_NEWBB_MODULE_STATUS . "</td>";
        echo "<td class='even' align='left'>" . $versioninfo->getInfo('status') . "</td>";
        echo "</tr>";
    }

    if ($versioninfo->getInfo('demo_site_url') !== '') {
        echo "<tr>";
        echo "<td class='head' align='left'>" . _AM_NEWBB_MODULE_DEMO . "</td>";
        echo "<td class='even' align='left'><a href='" . $versioninfo->getInfo('demo_site_url') . "' target='blank'>" . $versioninfo->getInfo('demo_site_name') . "</a></td>";
        echo "</tr>";
    }

    if ($versioninfo->getInfo('support_site_url') !== '') {
        echo "<tr>";
        echo "<td class='head' align='left'>" . _AM_NEWBB_MODULE_SUPPORT . "</td>";
        echo "<td class='even' align='left'><a href='" . $versioninfo->getInfo('support_site_url') . "' target='blank'>" . $versioninfo->getInfo('support_site_name') . "</a></td>";
        echo "</tr>";
    }

    if ($versioninfo->getInfo('submit_bug') !== '') {
        echo "<tr>";
        echo "<td class='head' align='left'>" . _AM_NEWBB_MODULE_BUG . "</td>";
        echo "<td class='even' align='left'><a href='" . $versioninfo->getInfo('submit_bug') . "' target='blank'>" . "Submit a Bug in newBB Bug Tracker" . "</a></td>";
        echo "</tr>";
    }
    if ($versioninfo->getInfo('submit_feature') !== '') {
        echo "<tr>";
        echo "<td class='head' align='left'>" . _AM_NEWBB_MODULE_FEATURE . "</td>";
        echo "<td class='even' align='left'><a href='" . $versioninfo->getInfo('submit_feature') . "' target='_blank'>" . "Request a feature in the newBB Feature Tracker" . "</a></td>";
        echo "</tr>";
    }

    echo "</table>";

    // Warning
    if ($versioninfo->getInfo('warning') !== '') {
        echo "<br />\n";
        echo "<table width='100%' cellspacing='1' cellpadding='3' border='0' class='outer'>";
        echo "<tr>";
        echo "<td class='bg3' align='left'><strong>" . _AM_NEWBB_MODULE_DISCLAIMER . "</strong></td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td class='even' align='left'>" . $versioninfo->getInfo('warning') . "</td>";
        echo "</tr>";
        echo "</table>";
    }

    // Author's note
    if ($versioninfo->getInfo('author_word') !== '') {
        echo "<br />\n";
        echo "<table width='100%' cellspacing='1' cellpadding='3' border='0' class='outer'>";
        echo "<tr>";
        echo "<td class='bg3' align='left'><strong>" . _AM_NEWBB_AUTHOR_WORD . "</strong></td>";
        echo "</tr>";

        $extra = (defined('_AM_NEWBB_AUTHOR_WORD_EXTRA')) ? _AM_NEWBB_AUTHOR_WORD_EXTRA : '';
        echo "<tr>";
        echo "<td class='even' align='left'>" . $versioninfo->getInfo('author_word') . "</td>";
        echo "</tr>";
        echo "</table>";
    }
    echo "</fieldset>";
}

xoops_cp_footer();

<?php
//
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                  Copyright (c) 2000-2016 XOOPS.org                        //
//                       <https://xoops.org/>                             //
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
// URL: http://www.myweb.ne.jp/, https://xoops.org/, http://jp.xoops.org/ //
// Project: XOOPS Project                                                    //
// ------------------------------------------------------------------------- //

use Xmf\Request;

require_once __DIR__ . '/admin_header.php';
require_once $GLOBALS['xoops']->path('class/pagenav.php');

$op   = Request::getCmd('op', Request::getCmd('op', 'default', 'POST'), 'GET'); // !empty($_GET['op'])? $_GET['op'] : (!empty($_POST['op'])?$_POST['op']:"default");
$item = Request::getString('op', Request::getInt('item', 'process', 'POST'), 'GET'); //!empty($_GET['op'])? $_GET['item'] : (!empty($_POST['item'])?$_POST['item']:"process");

$start = Request::getInt('start', 0, 'GET');
//$reportHandler = Newbb\Helper::getInstance()->getHandler('Report');

xoops_cp_header();
switch ($op) {
    case 'delete':
        $digest_ids = Request::getArray('digest_id', [], 'POST');
        //        /** @var Newbb\DigestHandler $digestHandler */
        //        $digestHandler = Newbb\Helper::getInstance()->getHandler('Digest');
        foreach ($digest_ids as $did => $value) {
            $digest = $digestHandler->get($did);
            $digestHandler->delete($digest);
        }
        redirect_header('admin_digest.php', 1);
        break;

    case 'digest':
        xoops_confirm(['op' => 'digestconfirmed'], 'admin_digest.php', _AM_NEWBB_DIGEST_CONFIRM);
        break;
    case 'digestconfirmed':
        $message = '';
        if ('POST' === Request::getMethod()) {
            //            $digestHandler = Newbb\Helper::getInstance()->getHandler('Digest');

            switch ($digestHandler->process(true)) {
                case 0:
                    $message = _AM_NEWBB_DIGEST_SENT;
                    break;
                case 4:
                    $message = _AM_NEWBB_DIGEST_NOT_SENT;
                    break;
                default:
                    $message = _AM_NEWBB_DIGEST_FAILED;
                    break;
            }
        }
        redirect_header('admin_digest.php', 1, $message);
        break;

    default:
        require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');

        $limit = 5;
        $adminObject->displayNavigation(basename(__FILE__));

        $adminObject->addItemButton(_AM_NEWBB_DIGEST, 'admin_digest.php?op=digest', $icon = 'add');
        $adminObject->displayButton('left');

        //if (!$newXoopsModuleGui) loadModuleAdminMenu(7,_AM_NEWBB_DIGESTADMIN);
        //    else $adminObject->displayNavigation(basename(__FILE__));
        echo '<ul><li>' . _AM_NEWBB_DIGEST_HELP_1 . '</li>';
        echo '<li>' . _AM_NEWBB_DIGEST_HELP_2 . '</li>';
        echo '<li>' . _AM_NEWBB_DIGEST_HELP_3 . '</li>';
        echo '<li>' . _AM_NEWBB_DIGEST_HELP_4 . '</li></ul>';
        echo "<table width='100%' border='0' cellspacing='1' class='outer'>" . "<tr><td class='odd'>";
        echo '<form action="' . xoops_getenv('PHP_SELF') . '" method="post">';
        echo "<table border='0' cellpadding='4' cellspacing='1' width='100%' class='outer'>";
        echo "<tr align='center'>";
        echo "<th class='bg3' width='2%'>" . _DELETE . '</th>';
        echo "<th class='bg3'>" . _AM_NEWBB_DIGESTCONTENT . '</th>';
        echo '</tr>';

        $digests = [];
        //        /** @var Newbb\DigestHandler $digestHandler */
        //        $digestHandler = Newbb\Helper::getInstance()->getHandler('Digest');
        $digests = $digestHandler->getAllDigests($start, $limit);
        foreach ($digests as $digest) {
            echo "<tr class='odd' align='left'>";
            echo "<td align='center' ><input type='checkbox' name='digest_id[" . $digest['digest_id'] . "]' value='1' /></td>";
            echo '<td><strong>#' . $digest['digest_id'] . ' @ ' . formatTimestamp($digest['digest_time']) . '</strong><br>' . str_replace("\n", '<br>', $digest['digest_content']) . '</td>';
            echo '</tr>';
            echo "<tr colspan='2'><td height='2'></td></tr>";
        }
        $submit = new \XoopsFormButton('', 'submit', _SUBMIT, 'submit');
        echo "<tr><td colspan='2' align='center'>" . $submit->render() . '</td></tr>';
        $hidden = new \XoopsFormHidden('op', 'delete');
        echo $hidden->render();
        $hidden = new \XoopsFormHidden('item', $item);
        echo $hidden->render() . '</form>';

        echo '</table>';
        echo '</td></tr></table>';
        echo '<fieldset>';
        echo '<legend>&nbsp;' . _AM_NEWBB_PREFERENCES . '&nbsp;-&nbsp;' . _MI_NEWBB_ADMENU_DIGEST . '&nbsp;</legend>';
        echo _AM_NEWBB_DIGEST_HELP_AUTO_DIGEST;
        echo '</fieldset>';
        $nav = new \XoopsPageNav($digestHandler->getDigestCount(), $limit, $start, 'start');
        echo $nav->renderNav(4);

        break;
}
require_once __DIR__ . '/admin_footer.php';

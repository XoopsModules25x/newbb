<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright       The XUUPS Project http://sourceforge.net/projects/xuups/
 * @license         http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package         NEWBB
 * @since           4.3
 * @author          irmtfan <irmtfan@yahoo.com>
 * @author          The Persian Xoops Support Site<www.xoops.ir>
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

global $xoTheme;

include_once $GLOBALS['xoops']->path('modules/newbb/include/functions.render.php');
$iconHandler = newbbGetIconHandler();
//  get css rel path from setted language
$css_rel_path = $iconHandler->getPath('language/' . $GLOBALS['xoopsConfig']['language'], 'newbb', 'language/english', 'css');
// add local stylesheet
$xoTheme->addStylesheet($css_rel_path . '/style.css');

//  get js rel path from setted language
$js_rel_path = $iconHandler->getPath('language/' . $GLOBALS['xoopsConfig']['language'], 'newbb', 'language/english', 'js');
// add all local js files inside js directory
xoops_load('XoopsLists');
$allfiles = XoopsLists::getFileListAsArray($GLOBALS['xoops']->path($js_rel_path));
foreach ($allfiles as $jsfile) {
    if (strtolower(pathinfo($jsfile, PATHINFO_EXTENSION)) === 'js') {
        $xoTheme->addScript($js_rel_path . '/' . $jsfile);
    }
}
global $forumCookie;  // for $forumCookie["prefix"] revert last change - use global instead of include_once
// add toggle script
//$toggle_script = "var toggle_cookie=\"" . $forumCookie['prefix'] . 'G' . '\';';
$toggle_script = "var toggle_cookie=\"" . $forumCookie['prefix'] . 'G' . '";';
$xoTheme->addScript(null, array('type' => 'text/javascript'), $toggle_script);

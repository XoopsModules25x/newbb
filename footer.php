<?php declare(strict_types=1);
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @since           4.3
 * @author          irmtfan <irmtfan@yahoo.com>
 * @author          The Persian Xoops Support Site<www.xoops.ir>
 */
global $xoTheme;

require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.render.php');
$iconHandler = newbbGetIconHandler();
//  get css rel path from setted language
$css_rel_path = $iconHandler->getPath('language/' . $GLOBALS['xoopsConfig']['language'], 'newbb', 'language/english', 'css');
// add local stylesheet
/** @var xos_opal_Theme $xoTheme */
$xoTheme->addStylesheet($css_rel_path . '/style.css');

//  get js rel path from setted language
$js_rel_path = $iconHandler->getPath('language/' . $GLOBALS['xoopsConfig']['language'], 'newbb', 'language/english', 'js');
// add all local js files inside js directory
xoops_load('XoopsLists');
$allfiles = \XoopsLists::getFileListAsArray($GLOBALS['xoops']->path($js_rel_path));
foreach ($allfiles as $jsfile) {
    if ('js' === \mb_strtolower(pathinfo($jsfile, PATHINFO_EXTENSION))) {
        $xoTheme->addScript($js_rel_path . '/' . $jsfile);
    }
}
global $forumCookie;  // for $forumCookie["prefix"] revert last change - use global instead of include
// add toggle script
//$toggle_script = "var toggle_cookie=\"" . $forumCookie['prefix'] . 'G' . '\';';
$toggle_script = 'var toggle_cookie="' . (isset($forumCookie['prefix'])?:'') . 'G";';
$xoTheme->addScript(null, ['type' => 'text/javascript'], $toggle_script);

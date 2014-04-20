<?php
/**
 * CBB 4.0, or newbb, the forum module for XOOPS project
 *
 * @copyright	The XOOPS Project http://xoops.sf.net
 * @license		http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author		Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since		4.00
 * @version		$Id $
 * @package		module::newbb
 */

if (!defined('XOOPS_ROOT_PATH')) { exit(); }

defined("NEWBB_FUNCTIONS_INI") || include_once dirname(__FILE__)."/functions.ini.php";
define("NEWBB_FUNCTIONS_LANGUAGE_LOADED", TRUE);


if (!defined("NEWBB_FUNCTIONS_LANGUAGE")):
define("NEWBB_FUNCTIONS_LANGUAGE", 1);

function newbb_load_language($page, $dirname = "newbb")
{
	global $xoopsConfig;
	$page = str_replace("..", "", $page);
	if (!@include_once XOOPS_ROOT_PATH."/modules/{$dirname}/{$xoopsConfig['language']}/{$language}.php") {
		if (!@include_once XOOPS_ROOT_PATH."/modules/{$dirname}/language/{$language}.php") {
			return false;
		}
	}
	
	return true;
}

ENDIF;
?>
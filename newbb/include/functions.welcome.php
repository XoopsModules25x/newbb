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
define("NEWBB_FUNCTIONS_WELCOME_LOADED", TRUE);


if (!defined("NEWBB_FUNCTIONS_WELCOME")):
define("NEWBB_FUNCTIONS_WELCOME", true);

function newbb_welcome()
{
	global $xoopsModule, $xoopsModuleConfig, $myts, $xoopsUser, $forum_obj;
	//$xoopsModuleConfig["welcome_forum"] = 1;
	$forum_handler =& xoops_getmodulehandler('forum', 'newbb');
	$forum_obj = $forum_handler->get($xoopsModuleConfig["welcome_forum"]);
	if (!$forum_handler->getPermission($forum_obj)) {
		unset($forum_obj);
		return false;
	}
	
	include dirname(__FILE__)."/functions.welcome.inc.php";
	unset($forum_obj);
	return $ret;
}
newbb_welcome();
endif;
?>
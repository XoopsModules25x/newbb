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
define("NEWBB_FUNCTIONS_CONFIG_LOADED", TRUE);


IF (!defined("NEWBB_FUNCTIONS_CONFIG")):
define("NEWBB_FUNCTIONS_CONFIG", 1);

function newbb_load_config($category = "", $dirname = "newbb")
{
	global $xoopsModuleConfig;
	static $configs;
	
	if ( isset($configs[""]) || isset($configs[$category]) ) return true;
	$config_handler = xoops_getmodulehandler("config", $dirname);
	if ($configs_data = $config_handler->getByCategory($category)) {
		$GLOBALS["xoopsModuleConfig"] = array_merge($GLOBALS["xoopsModuleConfig"], $configs_data);
	}
	$configs[$category] = 1;
	
	return true;
}

ENDIF;
?>
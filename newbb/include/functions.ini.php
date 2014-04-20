<?php
// $Id: functions.ini.php 62 2012-08-17 10:15:26Z alfred $
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
//  Author: phppp (D.J., infomax@gmail.com)                                  //
//  URL: http://xoopsforge.com, http://xoops.org.cn                          //
//  Project: Article Project                                                 //
//  ------------------------------------------------------------------------ //
if (!defined('XOOPS_ROOT_PATH')) { exit(); }


if (defined("NEWBB_FUNCTIONS_INI")) return; 
define("NEWBB_FUNCTIONS_INI",1);

include_once(XOOPS_ROOT_PATH."/Frameworks/art/functions.ini.php");

function newbb_load_object()
{
	return load_object();
}

function &newbb_load_config()
{
	static $moduleConfig;
	if (isset($moduleConfig)) {
		return $moduleConfig;
	}
	
	load_functions("config");
	$moduleConfig = mod_loadConfig("newbb");
	// irmtfan - change the read_mode = 2 (db) to read_mode = 1 (cookie) for anonymous users
	if (!is_object($GLOBALS["xoopsUser"]) && $moduleConfig["read_mode_db_to_cookie_for_anon"] && 2 == $moduleConfig["read_mode"]) {
		$moduleConfig["read_mode"] = 1;
	}
    return $moduleConfig;
}


// Backword compatible
function newbb_load_lang_file( $filename, $module = '', $default = 'english' )
{
	if (function_exists("xoops_load_lang_file")) {
		return xoops_load_lang_file($filename, $module, $default);
	}
	
	$lang = $GLOBALS['xoopsConfig']['language'];
	$path = XOOPS_ROOT_PATH . ( empty($module) ? '/' : "/modules/$module/" ) . 'language';
	if ( !( $ret = @include_once( "$path/$lang/$filename.php" ) ) ) {
		$ret = @include_once( "$path/$default/$filename.php" );
	}
	return $ret;
}

?>
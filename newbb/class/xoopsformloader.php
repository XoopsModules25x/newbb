<?php
// $Id: xoopsformloader.php 62 2012-08-17 10:15:26Z alfred $
if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}

if (!@include_once XOOPS_ROOT_PATH."/Frameworks/compat/class/xoopsformloader.php") {
	include_once XOOPS_ROOT_PATH."/class/xoopsformloader.php";
}
?>
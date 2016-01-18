<?php
// $Id: xoopsformloader.php 62 2012-08-17 10:15:26Z alfred $
// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

if (!@include_once $GLOBALS['xoops']->path('Frameworks/compat/class/xoopsformloader.php')) {
    include_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
}

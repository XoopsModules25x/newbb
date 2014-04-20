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
 
// Why the skip-DB-security check defined only for XMLRPC? We also need it!!! ~_*
if (!defined('XOOPS_XMLRPC')) define('XOOPS_XMLRPC', 1);
ob_start();
include_once("header.php");
if ($xoopsModuleConfig['email_digest'] ==0) {
	echo "<br />Not set";
	return false;
}
$digest_handler =& xoops_getmodulehandler('digest', 'newbb');
$msg = $digest_handler->process();
$msg .= ob_get_contents();
ob_end_clean();
echo "<br />".$msg;
?>
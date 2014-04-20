<?php
/**
 * Tag blocks for CBB 4.0+
 *
 * @copyright	The XOOPS project http://www.xoops.org/
 * @license		http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author		Taiwen Jiang (phppp or D.J.) <php_pp@hotmail.com>
 * @since		4.00
 * @version		$Id: newbb_block_tag.php 62 2012-08-17 10:15:26Z alfred $
 * @package		module::newbb/tag
 */

if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}

/**#@+
 * Function to display tag cloud
 */
function newbb_tag_block_cloud_show($options) 
{
	if (!@include_once XOOPS_ROOT_PATH."/modules/tag/blocks/block.php") {
		return null; 
	} 
	$block_content = tag_block_cloud_show($options, "newbb");
	return $block_content;
}

function newbb_tag_block_cloud_edit($options) 
{
	if (!@include_once XOOPS_ROOT_PATH."/modules/tag/blocks/block.php") {
		return null; 
	}
	$form = tag_block_cloud_edit($options);
	return $form;
}

/**#@+
 * Function to display top tag list
 */
function newbb_tag_block_top_show($options) 
{
	if (!@include_once XOOPS_ROOT_PATH."/modules/tag/blocks/block.php") {
		return null; 
	}
	$block_content = tag_block_top_show($options, "newbb");
	return $block_content;
}

function newbb_tag_block_top_edit($options) {
	if (!@include_once XOOPS_ROOT_PATH."/modules/tag/blocks/block.php") {
		return null; 
	} 
	$form = tag_block_top_edit($options);
	return $form;
}
?>
<?php
/**
 * Tag blocks for NewBB 4.0+
 *
 * @copyright    XOOPS Project (http://xoops.org)
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author        Taiwen Jiang (phppp or D.J.) <php_pp@hotmail.com>
 * @since        4.00
 * @version        $Id: newbb_block_tag.php 62 2012-08-17 10:15:26Z alfred $
 * @package        module::newbb/tag
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

/**#@+
 * Function to display tag cloud
 * @param $options
 * @return array|null
 */
function newbb_tag_block_cloud_show($options)
{
    if (!@include_once $GLOBALS['xoops']->path('modules/tag/blocks/block.php')) {
        return null;
    }
    $block_content = tag_block_cloud_show($options, "newbb");

    return $block_content;
}

/**
 * @param $options
 * @return null|string
 */
function newbb_tag_block_cloud_edit($options)
{
    if (!@include_once $GLOBALS['xoops']->path('modules/tag/blocks/block.php')) {
        return null;
    }
    $form = tag_block_cloud_edit($options);

    return $form;
}

/**#@+
 * Function to display top tag list
 * @param $options
 * @return array|null
 */
function newbb_tag_block_top_show($options)
{
    if (!@include_once $GLOBALS['xoops']->path('modules/tag/blocks/block.php')) {
        return null;
    }
    $block_content = tag_block_top_show($options, "newbb");

    return $block_content;
}

/**
 * @param $options
 * @return null|string
 */
function newbb_tag_block_top_edit($options)
{
    if (!@include_once $GLOBALS['xoops']->path('modules/tag/blocks/block.php')) {
        return null;
    }
    $form = tag_block_top_edit($options);

    return $form;
}

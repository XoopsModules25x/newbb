<?php
/**
 * Tag blocks for NewBB 4.0+
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <php_pp@hotmail.com>
 * @since          4.00
 * @package        module::newbb/tag
 */

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

/**#@+
 * Function to display tag cloud
 * @param $options
 * @return array|null
 */
function newbb_tag_block_cloud_show($options)
{
    if (!@require_once $GLOBALS['xoops']->path('modules/tag/blocks/block.php')) {
        return null;
    }
    $block_content = tag_block_cloud_show($options, 'newbb');

    return $block_content;
}

/**
 * @param $options
 * @return null|string
 */
function newbb_tag_block_cloud_edit($options)
{
    if (!@require_once $GLOBALS['xoops']->path('modules/tag/blocks/block.php')) {
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
    if (!@require_once $GLOBALS['xoops']->path('modules/tag/blocks/block.php')) {
        return null;
    }
    $block_content = tag_block_top_show($options, 'newbb');

    return $block_content;
}

/**
 * @param $options
 * @return null|string
 */
function newbb_tag_block_top_edit($options)
{
    if (!@require_once $GLOBALS['xoops']->path('modules/tag/blocks/block.php')) {
        return null;
    }
    $form = tag_block_top_edit($options);

    return $form;
}

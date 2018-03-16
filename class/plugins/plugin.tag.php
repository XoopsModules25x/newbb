<?php namespace XoopsModules\Newbb;

/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */

use XoopsModules\Newbb;
use XoopsModules\Tag;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

/**
 * Get item fields:
 * title
 * content
 * time
 * link
 * uid
 * uname
 * tags
 *
 * @var array $items associative array of items: [modid][catid][itemid]
 *
 * @return boolean
 *
 */
function newbb_tag_iteminfo(&$items)
{
    if (0 === count($items) || !is_array($items)) {
        return false;
    }

    $items_id = [];
    foreach (array_keys($items) as $cat_id) {
        // Some handling here to build the link upon catid
        // catid is not used in newbb, so just skip it
        foreach (array_keys($items[$cat_id]) as $item_id) {
            // In newbb, the item_id is "topic_id"
            $items_id[] = (int)$item_id;
        }
    }
    /** @var TopicHandler $itemHandler */
    $itemHandler = Newbb\Helper::getInstance()->getHandler('Topic');
    /** @var \XoopsObject $itemsObject */
    $itemsObject = $itemHandler->getObjects(new \Criteria('topic_id', '(' . implode(', ', $items_id) . ')', 'IN'), true);

    foreach (array_keys($items) as $cat_id) {
        foreach (array_keys($items[$cat_id]) as $item_id) {
            /** @var \XoopsObject $itemObject */
            if (!$itemObject = $itemsObject[$item_id]) {
                continue;
            }
            $items[$cat_id][$item_id] = [
                'title'   => $itemObject->getVar('topic_title'),
                'uid'     => $itemObject->getVar('topic_poster'),
                'link'    => "viewtopic.php?topic_id={$item_id}",
                'time'    => $itemObject->getVar('topic_time'),
                'tags'    => tag_parse_tag($itemObject->getVar('topic_tags', 'n')),
                'content' => ''
            ];
        }
    }
    unset($itemsObject);

    return true;
}

/**
 * Remove orphan tag-item links
 *
 * @param $mid
 */
function newbb_tag_synchronization($mid)
{
    /** @var TopicHandler $itemHandler */
    $itemHandler = Newbb\Helper::getInstance()->getHandler('Topic');
    /** @var \XoopsPersistableObjectHandler $linkHandler */
    $linkHandler = Tag\Helper::getInstance()->getHandler('Link');

    /* clear tag-item links */
    $sql = "    DELETE FROM {$linkHandler->table}"
           . '    WHERE '
           . "        tag_modid = {$mid}"
           . '        AND '
           . '        ( tag_itemid NOT IN '
           . "            ( SELECT DISTINCT {$itemHandler->keyName} "
           . "                FROM {$itemHandler->table} "
           . "                WHERE {$itemHandler->table}.approved > 0"
           . '            ) '
           . '        )';
    $linkHandler->db->queryF($sql);
}

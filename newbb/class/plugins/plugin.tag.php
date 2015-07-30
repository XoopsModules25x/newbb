<?php
/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright    XOOPS Project (http://xoops.org)
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author        Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since        4.00
 * @version        $Id $
 * @package        module::newbb
 */
// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

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
 * @var        array $items associative array of items: [modid][catid][itemid]
 *
 * @return    boolean
 *
 */
function newbb_tag_iteminfo(&$items)
{
    if (0 === count($items) || !is_array($items)) {
        return false;
    }

    $items_id = array();
    foreach (array_keys($items) as $cat_id) {
        // Some handling here to build the link upon catid
        // catid is not used in newbb, so just skip it
        foreach (array_keys($items[$cat_id]) as $item_id) {
            // In newbb, the item_id is "topic_id"
            $items_id[] = (int)($item_id);
        }
    }
    $item_handler =& xoops_getmodulehandler('topic', 'newbb');
    $items_obj    = $item_handler->getObjects(new Criteria("topic_id", "(" . implode(", ", $items_id) . ")", "IN"), true);

    foreach (array_keys($items) as $cat_id) {
        foreach (array_keys($items[$cat_id]) as $item_id) {
            if (!$item_obj =& $items_obj[$item_id]) {
                continue;
            }
            $items[$cat_id][$item_id] = array(
                "title"   => $item_obj->getVar("topic_title"),
                "uid"     => $item_obj->getVar("topic_poster"),
                "link"    => "viewtopic.php?topic_id={$item_id}",
                "time"    => $item_obj->getVar("topic_time"),
                "tags"    => tag_parse_tag($item_obj->getVar("topic_tags", "n")),
                "content" => ""
            );
        }
    }
    unset($items_obj);
}

/**
 * Remove orphan tag-item links
 *
 * @param $mid
 * @return bool
 */
function newbb_tag_synchronization($mid)
{
    $item_handler =& xoops_getmodulehandler("topic", "newbb");
    $link_handler =& xoops_getmodulehandler("link", "tag");

    /* clear tag-item links */
    if ($link_handler->mysql_major_version() >= 4) {
        $sql = "    DELETE FROM {$link_handler->table}" .
               "    WHERE " .
               "        tag_modid = {$mid}" .
               "        AND " .
               "        ( tag_itemid NOT IN " .
               "            ( SELECT DISTINCT {$item_handler->keyName} " .
               "                FROM {$item_handler->table} " .
               "                WHERE {$item_handler->table}.approved > 0" .
               "            ) " .
               "        )";
    } else {
        $sql = "    DELETE {$link_handler->table} FROM {$link_handler->table}" .
               "    LEFT JOIN {$item_handler->table} AS aa ON {$link_handler->table}.tag_itemid = aa.{$item_handler->keyName} " .
               "    WHERE " .
               "        tag_modid = {$mid}" .
               "        AND " .
               "        ( aa.{$item_handler->keyName} IS NULL" .
               "            OR aa.approved < 1" .
               "        )";
    }
    if (!$result = $link_handler->db->queryF($sql)) {
        //xoops_error($link_handler->db->error());
    }
}

<?php
/**
 * CBB 4.0, or newbb, the forum module for XOOPS project
 *
 * @copyright    The XOOPS Project http://xoops.sf.net
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author        Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since        4.00
 * @version        $Id $
 * @package        module::newbb
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

defined("NEWBB_FUNCTIONS_INI") || include __DIR__ . "/functions.ini.php";
define("NEWBB_FUNCTIONS_TOPIC_LOADED", true);

if (!defined("NEWBB_FUNCTIONS_TOPIC")) {
    define("NEWBB_FUNCTIONS_TOPIC", 1);

    /**
     * Create full title of a topic
     *
     * the title is composed of [type_name] if type_id is greater than 0 plus topic_title
     * @param $topic_title
     * @param null $prefix_name
     * @param null $prefix_color
     * @return string
     */
    function newbb_getTopicTitle($topic_title, $prefix_name = null, $prefix_color = null)
    {
        return getTopicTitle($topic_title, $prefix_name = null, $prefix_color = null);
    }

    /**
     * @param $topic_title
     * @param null $prefix_name
     * @param null $prefix_color
     * @return string
     */
    function getTopicTitle($topic_title, $prefix_name = null, $prefix_color = null)
    {
        if (empty($prefix_name)) {
            return $topic_title;
        }
        $topic_prefix = $prefix_color ? "<em style=\"font-style: normal; color: " . $prefix_color . ";\">[" . $prefix_name . "]</em> " : "[" . $prefix_name . "] ";

        return $topic_prefix . $topic_title;
    }
}

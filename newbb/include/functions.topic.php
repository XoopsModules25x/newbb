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

defined("NEWBB_FUNCTIONS_INI") || include __DIR__ . "/functions.ini.php";
define("NEWBB_FUNCTIONS_TOPIC_LOADED", true);

if (!defined("NEWBB_FUNCTIONS_TOPIC")) {
    define("NEWBB_FUNCTIONS_TOPIC", 1);

    /**
     * Create full title of a topic
     *
     * the title is composed of [type_name] if type_id is greater than 0 plus topic Title
     * @param $topicTitle
     * @param null $prefixName
     * @param null $prefixColor
     * @return string
     */
    function newbb_getTopicTitle($topicTitle, $prefixName = null, $prefixColor = null)
    {
        return getTopicTitle($topicTitle, $prefixName = null, $prefixColor = null);
    }

    /**
     * @param $topicTitle
     * @param null $prefixName
     * @param null $prefixColor
     * @return string
     */
    function getTopicTitle($topicTitle, $prefixName = null, $prefixColor = null)
    {
        if (empty($prefixName)) {
            return $topicTitle;
        }
        $topicPrefix = $prefixColor ? "<em style=\"font-style: normal; color: " . $prefixColor . ";\">[" . $prefixName . "]</em> " : "[" . $prefixName . "] ";

        return $topicPrefix . $topicTitle;
    }
}

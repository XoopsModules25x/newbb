<?php
/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */
require_once __DIR__ . '/header.php';

if (!is_object($GLOBALS['xoopsUser']) || !$GLOBALS['xoopsUser']->isAdmin()) {
    exit(_NOPERM);
}

if ($xoopsModule->getVar('version') >= 401) {
    exit('Version not valid');
}

if (empty($GLOBALS['xoopsModuleConfig']['subject_prefix'])) {
    exit('No need for update');
}

$GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('newbb_type_tmp'));
$GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('newbb_type_forum_tmp'));

if (!$GLOBALS['xoopsDB']->queryF('
        CREATE TABLE ' . $GLOBALS['xoopsDB']->prefix('newbb_type_tmp') . " (
          `type_id`             SMALLINT(4)         UNSIGNED NOT NULL AUTO_INCREMENT,
          `type_name`             VARCHAR(64)         NOT NULL DEFAULT '',
          `type_color`             VARCHAR(10)         NOT NULL DEFAULT '',
          `type_description`     VARCHAR(255)         NOT NULL DEFAULT '',

          PRIMARY KEY              (`type_id`)
        ) ENGINE=MyISAM;
    ")) {
    exit('Can not create tmp table for `bb_type_tmp`');
}

if (!$GLOBALS['xoopsDB']->queryF('
        CREATE TABLE ' . $GLOBALS['xoopsDB']->prefix('newbb_type_forum_tmp') . " (
          `tf_id`                 MEDIUMINT(4)         UNSIGNED NOT NULL AUTO_INCREMENT,
          `type_id`             SMALLINT(4)         UNSIGNED NOT NULL DEFAULT '0',
          `forum_id`             SMALLINT(4)         UNSIGNED NOT NULL DEFAULT '0',
          `type_order`             SMALLINT(4)         UNSIGNED NOT NULL DEFAULT '99',

          PRIMARY KEY              (`tf_id`),
          KEY `forum_id`        (`forum_id`),
          KEY `type_order`        (`type_order`)
        ) ENGINE=MyISAM;
    ")) {
    $GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('newbb_type_tmp'));
    exit('Can not create tmp table for `bb_type_forum_tmp`');
}

//$typeHandler = Newbb\Helper::getInstance()->getHandler('Type');
$subjectpres = array_filter(array_map('trim', explode(',', $GLOBALS['xoopsModuleConfig']['subject_prefix'])));
$types       = [];
$order       = 1;
foreach ($subjectpres as $subjectpre) {
    if (preg_match("/<[^#]*color=[\"'](#[^'\"\s]*)[^>]>[\[]?([^<\]]*)[\]]?/is", $subjectpre, $matches)) {
        if (!$GLOBALS['xoopsDB']->queryF('
                INSERT INTO ' . $GLOBALS['xoopsDB']->prefix('newbb_type_tmp') . '
                    (`type_name`, `type_color`)
                VALUES
                    (' . $GLOBALS['xoopsDB']->quoteString($matches[2]) . ', ' . $GLOBALS['xoopsDB']->quoteString($matches[1]) . ')
            ')) {
            xoops_error("Can not add type of `{$matches[2]}`");
            continue;
        }
        $types[$GLOBALS['xoopsDB']->getInsertId()] = $order++;
    }
}
if (0 === count($types)) {
    $GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('newbb_type_tmp'));
    $GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('newbb_type_forum_tmp'));
    exit('No type item created');
}

///** @var Newbb\ForumHandler $forumHandler */
//$forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
if ($forums_type = $forumHandler->getIds(new \Criteria('allow_subject_prefix', 1))) {
    foreach ($forums_type as $forum_id) {
        $type_query = [];
        foreach ($types as $key => $order) {
            $type_query[] = "({$key}, {$forum_id}, {$order})";
        }

        $sql = 'INSERT INTO ' . $GLOBALS['xoopsDB']->prefix('newbb_type_forum_tmp') . ' (type_id, forum_id, type_order) ' . ' VALUES ' . implode(', ', $type_query);
        if (false === ($result = $GLOBALS['xoopsDB']->queryF($sql))) {
            xoops_error($GLOBALS['xoopsDB']->error());
        }
    }
} else {
    $GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('newbb_type_tmp'));
    $GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('newbb_type_forum_tmp'));
    exit('No type item to update');
}

die('update succeeded');

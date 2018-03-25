<?php
/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (http://xoops.org)
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */
include_once __DIR__ . '/header.php';

if (!is_object($GLOBALS['xoopsUser']) || !$GLOBALS['xoopsUser']->isAdmin()) {
    exit(_NOPERM);
}

if ($xoopsModule->getVar('version') >= 401) {
    exit('Version not valid');
}

if (empty($GLOBALS['xoopsModuleConfig']['subject_prefix'])) {
    exit('No need for update');
}

$GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_type_tmp'));
$GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_type_forum_tmp'));

if (!$GLOBALS['xoopsDB']->queryF('
        CREATE TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_type_tmp') . " (
          `type_id`             smallint(4)         unsigned NOT NULL auto_increment,
          `type_name`             varchar(64)         NOT NULL default '',
          `type_color`             varchar(10)         NOT NULL default '',
          `type_description`     varchar(255)         NOT NULL default '',

          PRIMARY KEY              (`type_id`)
        ) ENGINE=MyISAM;
    ")
) {
    exit('Can not create tmp table for `bb_type_tmp`');
}

if (!$GLOBALS['xoopsDB']->queryF('
        CREATE TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_type_forum_tmp') . " (
          `tf_id`                 mediumint(4)         unsigned NOT NULL auto_increment,
          `type_id`             smallint(4)         unsigned NOT NULL default '0',
          `forum_id`             smallint(4)         unsigned NOT NULL default '0',
          `type_order`             smallint(4)         unsigned NOT NULL default '99',

          PRIMARY KEY              (`tf_id`),
          KEY `forum_id`        (`forum_id`),
          KEY `type_order`        (`type_order`)
        ) ENGINE=MyISAM;
    ")
) {
    $GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_type_tmp'));
    exit('Can not create tmp table for `bb_type_forum_tmp`');
}

$typeHandler = xoops_getModuleHandler('type', 'newbb');
$subjectpres = array_filter(array_map('trim', explode(',', $GLOBALS['xoopsModuleConfig']['subject_prefix'])));
$types       = [];
$order       = 1;
foreach ($subjectpres as $subjectpre) {
    if (preg_match("/<[^#]*color=[\"'](#[^'\"\s]*)[^>]>[\[]?([^<\]]*)[\]]?/is", $subjectpre, $matches)) {
        if (!$GLOBALS['xoopsDB']->queryF('
                INSERT INTO ' . $GLOBALS['xoopsDB']->prefix('bb_type_tmp') . '
                    (`type_name`, `type_color`)
                VALUES
                    (' . $GLOBALS['xoopsDB']->quoteString($matches[2]) . ', ' . $GLOBALS['xoopsDB']->quoteString($matches[1]) . ')
            ')
        ) {
            xoops_error("Can not add type of `{$matches[2]}`");
            continue;
        }
        $types[$GLOBALS['xoopsDB']->getInsertId()] = $order++;
    }
}
if (0 === count($types)) {
    $GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_type_tmp'));
    $GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_type_forum_tmp'));
    exit('No type item created');
}

$forumHandler = xoops_getModuleHandler('forum', 'newbb');
if ($forums_type = $forumHandler->getIds(new Criteria('allow_subject_prefix', 1))) {
    foreach ($forums_type as $forum_id) {
        $type_query = [];
        foreach ($types as $key => $order) {
            $type_query[] = "({$key}, {$forum_id}, {$order})";
        }

        $sql = 'INSERT INTO ' . $GLOBALS['xoopsDB']->prefix('bb_type_forum_tmp') . ' (type_id, forum_id, type_order) ' . ' VALUES ' . implode(', ', $type_query);
        if (($result = $GLOBALS['xoopsDB']->queryF($sql)) === false) {
            xoops_error($GLOBALS['xoopsDB']->error());
        }
    }
} else {
    $GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_type_tmp'));
    $GLOBALS['xoopsDB']->queryF('DROP TABLE ' . $GLOBALS['xoopsDB']->prefix('bb_type_forum_tmp'));
    exit('No type item to update');
}

die('update succeeded');

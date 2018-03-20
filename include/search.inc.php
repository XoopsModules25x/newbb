<?php
/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>, irmtfan <irmtfan@users.sourceforge.net>
 * @since          4.3
 * @package        module::newbb
 */

use XoopsModules\Newbb;

// completely rewrite by irmtfan - remove hardcode database access, solve order issues, add post_text & topic_id, add highlight and reduce queries
// defined('XOOPS_ROOT_PATH') || die('Restricted access');
require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

/**
 * @param                $queryarray
 * @param                $andor
 * @param                $limit
 * @param                $offset
 * @param                $userid
 * @param  int           $forums
 * @param  int           $sortby
 * @param  string        $searchin
 * @param  CriteriaCompo $criteriaExtra
 * @return array
 */
function newbb_search(
    $queryarray,
    $andor,
    $limit,
    $offset,
    $userid,
    $forums = 0,
    $sortby = 0,
    $searchin = 'both',
    CriteriaCompo $criteriaExtra = null
) {
    global $myts, $xoopsDB;
    // irmtfan - in XOOPSCORE/search.php $GLOBALS['xoopsModuleConfig'] is not set
    if (!isset($GLOBALS['xoopsModuleConfig'])) {
        $GLOBALS['xoopsModuleConfig'] = newbbLoadConfig();
    }
    // irmtfan - in XOOPSCORE/search.php $xoopsModule is not set
    if (!is_object($GLOBALS['xoopsModule']) && is_object($GLOBALS['module'])
        && 'newbb' === $GLOBALS['module']->getVar('dirname')) {
        $GLOBALS['xoopsModule'] = $GLOBALS['module'];
    }
    /** @var Newbb\ForumHandler $forumHandler */
    $forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
    $validForums  = $forumHandler->getIdsByValues($forums); // can we use view permission? $forumHandler->getIdsByValues($forums, "view")

    $criteriaPost = new \CriteriaCompo();
    $criteriaPost->add(new \Criteria('p.approved', 1), 'AND'); // only active posts

    $forum_list = [];// get forum lists just for forum names
    if (count($validForums) > 0) {
        $criteriaPermissions = new \CriteriaCompo();
        $criteriaPermissions->add(new \Criteria('p.forum_id', '(' . implode(',', $validForums) . ')', 'IN'), 'AND');
        $forum_list = $forumHandler->getAll(new \Criteria('forum_id', '(' . implode(', ', $validForums) . ')', 'IN'), 'forum_name', false);
    }

    if (is_numeric($userid) && 0 !== $userid) {
        $criteriaUser = new \CriteriaCompo();
        $criteriaUser->add(new \Criteria('p.uid', $userid), 'OR');
    } elseif (is_array($userid) && count($userid) > 0) {
        $userid       = array_map('intval', $userid);
        $criteriaUser = new \CriteriaCompo();
        $criteriaUser->add(new \Criteria('p.uid', '(' . implode(',', $userid) . ')', 'IN'), 'OR');
    }

    $count = 0;
    if (is_array($queryarray)) {
        $count = count($queryarray);
    }
    $highlightKey = '';
    if ($count > 0) {
        $criteriaKeywords = new \CriteriaCompo();
        foreach ($queryarray as $queryTerm) {
            $termCriteria  = new \CriteriaCompo();
            $queryTermLike = '%' . $xoopsDB->escape($queryTerm) . '%';
            if ('title' === $searchin || 'both' === $searchin) {
                $termCriteria->add(new \Criteria('p.subject', $queryTermLike, 'LIKE'), 'OR');
            }
            if ('text' === $searchin || 'both' === $searchin) {
                $termCriteria->add(new \Criteria('t.post_text', $queryTermLike, 'LIKE'), 'OR');
            }
            $criteriaKeywords->add($termCriteria, $andor);
        }
        // add highlight keywords to post links
        $highlightKey = '&amp;keywords=' . implode(' ', $queryarray);
        $highlightKey = str_replace(' ', '+', $highlightKey);
    }
    $criteria = new \CriteriaCompo();
    $criteria->add($criteriaPost, 'AND');
    if (isset($criteriaPermissions)) {
        $criteria->add($criteriaPermissions, 'AND');
    }
    if (isset($criteriaUser)) {
        $criteria->add($criteriaUser, 'AND');
    }
    if (isset($criteriaKeywords)) {
        $criteria->add($criteriaKeywords, 'AND');
    }
    if (isset($criteriaExtra)) {
        $criteria->add($criteriaExtra, 'AND');
    }
    //$criteria->setLimit($limit); // no need for this
    //$criteria->setStart($offset); // no need for this

    if (empty($sortby)) {
        $sortby = 'p.post_time';
    }
    $criteria->setSort($sortby);
    $order = 'ASC';
    if ('p.post_time' === $sortby) {
        $order = 'DESC';
    }
    $criteria->setOrder($order);

    /** @var Newbb\PostHandler $postHandler */
    $postHandler = Newbb\Helper::getInstance()->getHandler('Post');
    $posts       = $postHandler->getPostsByLimit($criteria, $limit, $offset);

    $ret = [];
    $i   = 0;
    foreach (array_keys($posts) as $id) {
        /** @var Newbb\Post $post */
        $post                  = $posts[$id];
        $post_data             = $post->getPostBody();
        $ret[$i]['topic_id']   = $post->getVar('topic_id');
        $ret[$i]['link']       = XOOPS_URL . '/modules/newbb/viewtopic.php?post_id=' . $post->getVar('post_id') . $highlightKey; // add highlight key
        $ret[$i]['title']      = $post_data['subject'];
        $ret[$i]['time']       = $post_data['date'];
        $ret[$i]['forum_name'] = $myts->htmlSpecialChars($forum_list[$post->getVar('forum_id')]['forum_name']);
        $ret[$i]['forum_link'] = XOOPS_URL . '/modules/newbb/viewforum.php?forum=' . $post->getVar('forum_id');
        $ret[$i]['post_text']  = $post_data['text'];
        $ret[$i]['uid']        = $post->getVar('uid');
        $ret[$i]['poster']     = $post->getVar('uid') ? '<a href="' . XOOPS_URL . '/userinfo.php?uid=' . $ret[$i]['uid'] . '">' . $post_data['author'] . '</a>' : $post_data['author'];
        ++$i;
    }

    return $ret;
}

<?php namespace XoopsModules\Newbb;

/**
 * Newbb module
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package         newbb
 * @since           4.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 */

use XoopsModules\Newbb;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

/**
 * Class ForumHandler
 */
class ForumHandler extends \XoopsPersistableObjectHandler
{
    /**
     * @param null|\XoopsDatabase $db
     */
    public function __construct(\XoopsDatabase $db)
    {
        parent::__construct($db, 'newbb_forums', Forum::class, 'forum_id', 'forum_name');
    }

    /**
     * @param \XoopsObject $object
     * @param  bool        $force
     * @return bool
     * @internal param \XoopsObject $forum
     */

    public function insert(\XoopsObject $object, $force = true) //insert($forum)
    {
        $forum = $object;
        if (!parent::insert($forum, true)) {
            return false;
        }

        if ($forum->isNew()) {
            $this->applyPermissionTemplate($forum);
        }

        return $forum->getVar('forum_id');
    }

    /**
     * @param \XoopsObject $forum
     * @param  bool        $force
     * @return bool
     */
    public function delete(\XoopsObject $forum, $force = false) //delete(&$forum)
    {
        global $xoopsModule;
        // RMV-NOTIFY
        xoops_notification_deletebyitem($xoopsModule->getVar('mid'), 'forum', $forum->getVar('forum_id'));
        // Get list of all topics in forum, to delete them too
        /** @var Newbb\TopicHandler $topicHandler */
        $topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
        $topicHandler->deleteAll(new \Criteria('forum_id', $forum->getVar('forum_id')), true, true);
        $this->updateAll('parent_forum', $forum->getVar('parent_forum'), new \Criteria('parent_forum', $forum->getVar('forum_id')));
        $this->deletePermission($forum);

        return parent::delete($forum);
    }

    /**
     * @param  string $perm
     * @return mixed
     */
    public function getIdsByPermission($perm = 'access')
    {
        /** var Newbb\PermissionHandler $permHandler */
        $permHandler = Newbb\Helper::getInstance()->getHandler('Permission');
        return $permHandler->getForums($perm);
    }

    /**
     * @param  int    $cat
     * @param  string $permission
     * @param  null   $tags
     * @param  bool   $asObject
     * @return array
     */
    public function &getByPermission($cat = 0, $permission = 'access', $tags = null, $asObject = true)
    {
        $_cachedForums = [];
        if (!$valid_ids = $this->getIdsByPermission($permission)) {
            return $_cachedForums;
        }

        $criteria = new \CriteriaCompo(new \Criteria('forum_id', '(' . implode(', ', $valid_ids) . ')', 'IN'));
        if (is_numeric($cat) && $cat > 0) {
            $criteria->add(new \Criteria('cat_id', (int)$cat));
        } elseif (is_array($cat) && count($cat) > 0) {
            $criteria->add(new \Criteria('cat_id', '(' . implode(', ', $cat) . ')', 'IN'));
        }
        $criteria->setSort('forum_order');
        $criteria->setOrder('ASC');
        $_cachedForums = $this->getAll($criteria, $tags, $asObject);

        return $_cachedForums;
    }

    /**
     * @param  int    $categoryid
     * @param  string $permission
     * @param  bool   $asObject
     * @param  null   $tags
     * @return array
     */
    public function &getForumsByCategory($categoryid = 0, $permission = '', $asObject = true, $tags = null)
    {
        $forums = $this->getByPermission($categoryid, $permission, $tags);
        if ($asObject) {
            return $forums;
        }

        $forums_array = [];
        $array_cat    = [];
        $array_forum  = [];
        if (!is_array($forums)) {
            return [];
        }
        foreach (array_keys($forums) as $forumid) {
            $forum                                                  = $forums[$forumid];
            $forums_array[$forum->getVar('parent_forum')][$forumid] = [
                'cid'   => $forum->getVar('cat_id'),
                'title' => $forum->getVar('forum_name')
            ];
        }
        if (!isset($forums_array[0])) {
            $ret = [];

            return $ret;
        }
        foreach ($forums_array[0] as $key => $forum) {
            if (isset($forums_array[$key])) {
                $forum['sub'] = $forums_array[$key];
            }
            $array_forum[$forum['cid']][$key] = $forum;
        }
        ksort($array_forum);
        unset($forums, $forums_array);

        return $array_forum;
    }

    /**
     * @param        $forum
     * @param  null  $criteria
     * @return array
     */
    public function getAllTopics(&$forum, $criteria = null)
    {
        global $myts, $viewAllForums;
        $startdate = '';

        require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.render.php');
        require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.session.php');
        require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.time.php');
        require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.read.php');
        require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.topic.php');

        $criteria_vars = ['startdate', 'start', 'sort', 'order', 'type', 'status', 'excerpt'];
        foreach ($criteria_vars as $var) {
            ${$var} = $criteria[$var];
        }

        $topic_lastread = newbbGetCookie('LT', true);
        $criteria_forum = '';
        if (is_object($forum)) {
            $criteria_forum = ' AND t.forum_id = ' . $forum->getVar('forum_id');
            $hot_threshold  = $forum->getVar('hot_threshold');
        } else {
            $hot_threshold = 10;
            if (is_array($forum) && count($forum) > 0) {
                $criteria_forum = ' AND t.forum_id IN (' . implode(',', array_keys($forum)) . ')';
            } elseif (!empty($forum)) {
                $criteria_forum = ' AND t.forum_id =' . (int)$forum;
            }
        }

        $criteria_post    = $startdate ? ' p.post_time > ' . $startdate : ' 1 = 1 ';
        $criteria_topic   = empty($type) ? '' : " AND t.type_id={$type}";
        $criteria_extra   = '';
        $criteria_approve = ' AND t.approved = 1';
        $post_on          = ' p.post_id = t.topic_last_post_id';
        $leftjoin         = ' LEFT JOIN ' . $this->db->prefix('newbb_posts') . ' p ON p.post_id = t.topic_last_post_id';
        $sort_array       = [];
        switch ($status) {
            case 'digest':
                $criteria_extra = ' AND t.topic_digest = 1';
                break;

            case 'unreplied':
                $criteria_extra = ' AND t.topic_replies < 1';
                break;

            case 'unread':
                if (empty($GLOBALS['xoopsModuleConfig']['read_mode'])) {
                } elseif (2 == $GLOBALS['xoopsModuleConfig']['read_mode']) {
                    // START irmtfan use read_uid to find the unread posts when the user is logged in
                    $read_uid = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
                    if (!empty($read_uid)) {
                        $leftjoin      .= ' LEFT JOIN ' . $this->db->prefix('newbb_reads_topic') . ' r ON r.read_item = t.topic_id AND r.uid = ' . $read_uid . ' ';
                        $criteria_post .= ' AND (r.read_id IS NULL OR r.post_id < t.topic_last_post_id)';
                    } else {
                    }
                    // END irmtfan use read_uid to find the unread posts when the user is logged in
                } elseif (1 == $GLOBALS['xoopsModuleConfig']['read_mode']) {
                    // START irmtfan fix read_mode = 1 bugs - for all users (member and anon)
                    if ($time_criterion = max($GLOBALS['last_visit'], $startdate)) {
                        $criteria_post  = ' p.post_time > ' . $time_criterion; // for all users
                        $topics         = [];
                        $topic_lastread = newbbGetCookie('LT', true);
                        if (count($topic_lastread) > 0) {
                            foreach ($topic_lastread as $id => $time) {
                                if ($time > $time_criterion) {
                                    $topics[] = $id;
                                }
                            }
                        }
                        if (count($topics) > 0) {
                            $criteria_extra = ' AND t.topic_id NOT IN (' . implode(',', $topics) . ')';
                        }
                    }
                    // END irmtfan fix read_mode = 1 bugs - for all users (member and anon)
                }
                break;
            case 'pending':
                $post_on          = ' p.topic_id = t.topic_id';
                $criteria_post    .= ' AND p.pid = 0';
                $criteria_approve = ' AND t.approved = 0';
                break;

            case 'deleted':
                $criteria_approve = ' AND t.approved = -1';
                break;

            case 'all': // For viewall.php; do not display sticky topics at first
            case 'active': // same as "all"
                break;

            default:
                if ($startdate > 0) {
                    $criteria_post = ' (p.post_time > ' . $startdate . ' OR t.topic_sticky=1)';
                }
                $sort_array[] = 't.topic_sticky DESC';
                break;
        }

        $select = 't.*, ' . ' p.post_time as last_post_time, p.poster_name as last_poster_name, p.icon, p.post_id, p.uid';
        $from   = $this->db->prefix('newbb_topics') . ' t ' . $leftjoin;
        $where  = $criteria_post . $criteria_topic . $criteria_forum . $criteria_extra . $criteria_approve;

        if ($excerpt) {
            $select .= ', p.post_karma, p.require_reply, pt.post_text';
            $from   .= ' LEFT JOIN ' . $this->db->prefix('newbb_posts_text') . ' pt ON pt.post_id = t.topic_last_post_id';
        }
        if ('u.uname' === $sort) {
            $sort = 't.topic_poster';
        }

        $sort_array[] = trim($sort . ' ' . $order);
        $sortby       = implode(', ', array_filter($sort_array));
        if (empty($sortby)) {
            $sortby = 't.topic_last_post_id DESC';
        }

        $sql = 'SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . $where . ' ORDER BY ' . $sortby;

        if (!$result = $this->db->query($sql, $GLOBALS['xoopsModuleConfig']['topics_per_page'], $start)) {
            redirect_header('index.php', 2, _MD_NEWBB_ERROROCCURED);
        }

        $sticky  = 0;
        $topics  = [];
        $posters = [];
        $reads   = [];
        $types   = [];

        /** @var Newbb\TypeHandler $typeHandler */
        $typeHandler = Newbb\Helper::getInstance()->getHandler('Type');
        $typen       = $typeHandler->getByForum($forum->getVar('forum_id'));
        while (false !== ($myrow = $this->db->fetchArray($result))) {
            if ($myrow['topic_sticky']) {
                ++$sticky;
            }

            // ------------------------------------------------------
            // topic_icon: priority: sticky -> digest -> regular

            if ($myrow['topic_haspoll']) {
                if ($myrow['topic_sticky']) {
                    $topic_icon = newbbDisplayImage('topic_sticky', _MD_NEWBB_TOPICSTICKY) . '<br>' . newbbDisplayImage('poll', _MD_NEWBB_TOPICHASPOLL);
                } else {
                    $topic_icon = newbbDisplayImage('poll', _MD_NEWBB_TOPICHASPOLL);
                }
            } elseif ($myrow['topic_sticky']) {
                $topic_icon = newbbDisplayImage('topic_sticky', _MD_NEWBB_TOPICSTICKY);
            } elseif (!empty($myrow['icon'])) {
                $topic_icon = '<img src="' . XOOPS_URL . '/images/subject/' . htmlspecialchars($myrow['icon']) . '" alt="" />';
            } else {
                $topic_icon = '<img src="' . XOOPS_URL . '/images/icons/no_posticon.gif" alt="" />';
            }

            // ------------------------------------------------------
            // rating_img
            $rating = number_format($myrow['rating'] / 2, 0);
            // irmtfan - add alt key for rating
            if ($rating < 1) {
                $rating_img = newbbDisplayImage('blank');
            } else {
                $rating_img = newbbDisplayImage('rate' . $rating, constant('_MD_NEWBB_RATE' . $rating));
            }
            // ------------------------------------------------------
            // topic_page_jump
            $topic_page_jump      = '';
            $topic_page_jump_icon = '';
            $totalpages           = ceil(($myrow['topic_replies'] + 1) / $GLOBALS['xoopsModuleConfig']['posts_per_page']);
            if ($totalpages > 1) {
                $topic_page_jump .= '&nbsp;&nbsp;';
                $append          = false;
                for ($i = 1; $i <= $totalpages; ++$i) {
                    if ($i > 3 && $i < $totalpages) {
                        if (!$append) {
                            $topic_page_jump .= '...';
                            $append          = true;
                        }
                    } else {
                        $topic_page_jump .= '[<a href="' . XOOPS_URL . '/modules/newbb/viewtopic.php?topic_id=' . $myrow['topic_id'] . '&amp;start=' . (($i - 1) * $GLOBALS['xoopsModuleConfig']['posts_per_page']) . '">' . $i . '</a>]';
                        // irmtfan remove here and move
                        //$topic_page_jump_icon = "<a href='" . XOOPS_URL . "/modules/newbb/viewtopic.php?post_id=" . $myrow['post_id'] . "&amp;start=" . (($i - 1) * $GLOBALS['xoopsModuleConfig']['posts_per_page']) . "'>" . newbbDisplayImage('lastposticon',_MD_NEWBB_GOTOLASTPOST) . '</a>';
                    }
                }
            }
            // irmtfan - move here for both topics with and without pages
            $topic_page_jump_icon = "<a href='" . XOOPS_URL . '/modules/newbb/viewtopic.php?post_id=' . $myrow['post_id'] . "'>" . newbbDisplayImage('lastposticon', _MD_NEWBB_GOTOLASTPOST) . '</a>';

            // ------------------------------------------------------
            // => topic array
            $forum_link = '';
            if (!empty($viewAllForums[$myrow['forum_id']])) {
                $forum_link = '<a href="' . XOOPS_URL . '/modules/newbb/viewforum.php?forum=' . $myrow['forum_id'] . '">' . $viewAllForums[$myrow['forum_id']]['forum_name'] . '</a>';
            }

            $topic_title = $myts->htmlSpecialChars($myrow['topic_title']);
            // irmtfan remove here and move to for loop
            //if ($myrow['type_id'] > 0) {
            //$topic_title = '<span style="color:'.$typen[$myrow["type_id"]]["type_color"].'">['.$typen[$myrow["type_id"]]["type_name"].']</span> '.$topic_title.'';
            //}
            if ($myrow['topic_digest']) {
                $topic_title = "<span class='digest'>" . $topic_title . '</span>';
            }

            if (0 == $excerpt) {
                $topic_excerpt = '';
            } elseif (($myrow['post_karma'] > 0 || $myrow['require_reply'] > 0) && !newbbIsAdmin($forum)) {
                $topic_excerpt = '';
            } else {
                $topic_excerpt = xoops_substr(newbbHtml2text($myts->displayTarea($myrow['post_text'])), 0, $excerpt);
                $topic_excerpt = str_replace('[', '&#91;', $myts->htmlSpecialChars($topic_excerpt));
            }
            // START irmtfan move here
            $topics[$myrow['topic_id']] = [
                'topic_id'             => $myrow['topic_id'],
                'topic_icon'           => $topic_icon,
                'type_id'              => $myrow['type_id'],
                //'type_text'                 => $topic_prefix,/*irmtfan remove here and move to for loop*/
                'topic_title'          => $topic_title,
                //'topic_link'                => XOOPS_URL . '/modules/newbb/viewtopic.php?topic_id=' . $myrow['topic_id'],
                'topic_link'           => 'viewtopic.php?topic_id=' . $myrow['topic_id'],
                'rating_img'           => $rating_img,
                'topic_page_jump'      => $topic_page_jump,
                'topic_page_jump_icon' => $topic_page_jump_icon,
                'topic_replies'        => $myrow['topic_replies'],

                'topic_digest' => $myrow['topic_digest'],
                //mb

                'topic_poster_uid'       => $myrow['topic_poster'],
                'topic_poster_name'      => $myts->htmlSpecialChars($myrow['poster_name'] ?: $GLOBALS['xoopsConfig']['anonymous']),
                'topic_views'            => $myrow['topic_views'],
                'topic_time'             => newbbFormatTimestamp($myrow['topic_time']),
                'topic_last_posttime'    => newbbFormatTimestamp($myrow['last_post_time']),
                'topic_last_poster_uid'  => $myrow['uid'],
                'topic_last_poster_name' => $myts->htmlSpecialChars($myrow['last_poster_name'] ?: $GLOBALS['xoopsConfig']['anonymous']),
                'topic_forum_link'       => $forum_link,
                'topic_excerpt'          => $topic_excerpt,
                'stick'                  => empty($myrow['topic_sticky']),
                'stats'                  => [
                    $myrow['topic_status'],
                    $myrow['topic_digest'],
                    $myrow['topic_replies']
                ],
                /* irmtfan uncomment use ib the for loop*/
                //"topic_poster"              => $topic_poster,/*irmtfan remove here and move to for loop*/
                //"topic_last_poster"         => $topic_last_poster,/*irmtfan remove here and move to for loop*/
                //"topic_folder"              => newbbDisplayImage($topic_folder,$topic_folder_text),/*irmtfan remove here and move to for loop*/
            ];
            // END irmtfan move here
            /* users */
            $posters[$myrow['topic_poster']] = 1;
            $posters[$myrow['uid']]          = 1;
            // reads
            if (!empty($GLOBALS['xoopsModuleConfig']['read_mode'])) {
                $reads[$myrow['topic_id']] = (1 == $GLOBALS['xoopsModuleConfig']['read_mode']) ? $myrow['last_post_time'] : $myrow['topic_last_post_id'];
            }
        }// irmtfan while end
        // START irmtfan move to a for loop
        $posters_name = newbbGetUnameFromIds(array_keys($posters), $GLOBALS['xoopsModuleConfig']['show_realname'], true);
        //$topic_poster = newbbGetUnameFromId($myrow['topic_poster'], $GLOBALS['xoopsModuleConfig']['show_realname'], true);
        //$topic_last_poster = newbbGetUnameFromId($myrow['uid'], $GLOBALS['xoopsModuleConfig']['show_realname'], true);
        $topic_isRead = newbbIsRead('topic', $reads);
        foreach (array_keys($topics) as $id) {
            $topics[$id]['topic_read'] = empty($topic_isRead[$id]) ? 0 : 1; // add topic-read/topic-new smarty variable
            if (!empty($topics[$id]['type_id']) && isset($typen[$topics[$id]['type_id']])) {
                $topics[$id]['topic_title'] = getTopicTitle($topics[$id]['topic_title'], $typen[$topics[$id]['type_id']]['type_name'], $typen[$topics[$id]['type_id']]['type_color']);
            }
            //$topic_prefix =  (!empty($typen[$myrow['type_id']])) ? getTopicTitle("", $typen[$myrow['type_id']]["type_name"], $typen[$myrow['type_id']]["type_color"]) : "";
            $topics[$id]['topic_poster']      = !empty($posters_name[$topics[$id]['topic_poster_uid']]) ? $posters_name[$topics[$id]['topic_poster_uid']] : $topics[$id]['topic_poster_name'];
            $topics[$id]['topic_last_poster'] = !empty($posters_name[$topics[$id]['topic_last_poster_uid']]) ? $posters_name[$topics[$id]['topic_last_poster_uid']] : $topics[$id]['topic_last_poster_name'];

            // ------------------------------------------------------
            // topic_folder: priority: newhot -> hot/new -> regular
            list($topic_status, $topic_digest, $topic_replies) = $topics[$id]['stats'];
            if (1 == $topic_status) {
                $topic_folder      = 'topic_locked';
                $topic_folder_text = _MD_NEWBB_TOPICLOCKED;
            } else {
                if ($topic_digest) {
                    $topic_folder      = 'topic_digest';
                    $topic_folder_text = _MD_NEWBB_TOPICDIGEST;
                } elseif ($topic_replies >= $hot_threshold) {
                    $topic_folder      = empty($topic_isRead[$id]) ? 'topic_hot_new' : 'topic_hot';
                    $topic_folder_text = empty($topic_isRead[$id]) ? _MD_NEWBB_MORETHAN : _MD_NEWBB_MORETHAN2;
                } else {
                    $topic_folder      = empty($topic_isRead[$id]) ? 'topic_new' : 'topic';
                    $topic_folder_text = empty($topic_isRead[$id]) ? _MD_NEWBB_NEWPOSTS : _MD_NEWBB_NONEWPOSTS;
                }
            }
            $topics[$id]['topic_folder'] = newbbDisplayImage($topic_folder, $topic_folder_text);
            unset($topics[$id]['topic_poster_name'], $topics[$id]['topic_last_poster_name'], $topics[$id]['stats']);
        } // irmtfan end for loop
        // END irmtfan move to a for loop
        if (count($topics) > 0) {
            $sql = ' SELECT DISTINCT topic_id FROM ' . $this->db->prefix('newbb_posts') . " WHERE attachment != ''" . ' AND topic_id IN (' . implode(',', array_keys($topics)) . ')';
            if ($result = $this->db->query($sql)) {
                while (false !== (list($topic_id) = $this->db->fetchRow($result))) {
                    $topics[$topic_id]['attachment'] = '&nbsp;' . newbbDisplayImage('attachment', _MD_NEWBB_TOPICSHASATT);
                }
            }
        }

        return [$topics, $sticky];
    }

    /**
     * @param $forum
     * @param $startdate
     * @param $type
     * @return null|int
     */
    public function getTopicCount(&$forum, $startdate, $type)
    {
        require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.session.php');

        $criteria_extra   = '';
        $criteria_approve = ' AND t.approved = 1'; // any others?
        $leftjoin         = ' LEFT JOIN ' . $this->db->prefix('newbb_posts') . ' p ON p.post_id = t.topic_last_post_id';
        $criteria_post    = ' p.post_time > ' . $startdate;
        switch ($type) {
            case 'digest':
                $criteria_extra = ' AND topic_digest = 1';
                break;
            case 'unreplied':
                $criteria_extra = ' AND topic_replies < 1';
                break;
            case 'unread':
                if (empty($GLOBALS['xoopsModuleConfig']['read_mode'])) {
                } elseif (2 == $GLOBALS['xoopsModuleConfig']['read_mode']) {
                    // START irmtfan use read_uid to find the unread posts when the user is logged in

                    $read_uid = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0;
                    if (!empty($read_uid)) {
                        $leftjoin      .= ' LEFT JOIN ' . $this->db->prefix('newbb_reads_topic') . ' r ON r.read_item = t.topic_id AND r.uid = ' . $read_uid . ' ';
                        $criteria_post .= ' AND (r.read_id IS NULL OR r.post_id < t.topic_last_post_id)';
                    } else {
                    }
                    // END irmtfan use read_uid to find the unread posts when the user is logged in
                } elseif (1 == $GLOBALS['xoopsModuleConfig']['read_mode']) {
                    // START irmtfan fix read_mode = 1 bugs - for all users (member and anon)
                    if ($time_criterion = max($GLOBALS['last_visit'], $startdate)) {
                        $criteria_post  = ' p.post_time > ' . $time_criterion; // for all users
                        $topics         = [];
                        $topic_lastread = newbbGetCookie('LT', true);
                        if (count($topic_lastread) > 0) {
                            foreach ($topic_lastread as $id => $time) {
                                if ($time > $time_criterion) {
                                    $topics[] = $id;
                                }
                            }
                        }
                        if (count($topics) > 0) {
                            $criteria_extra = ' AND t.topic_id NOT IN (' . implode(',', $topics) . ')';
                        }
                    }
                    // END irmtfan fix read_mode = 1 bugs - for all users (member and anon)
                }
                break;
            case 'pending':
                $criteria_approve = ' AND t.approved = 0';
                break;
            case 'deleted':
                $criteria_approve = ' AND t.approved = -1';
                break;
            case 'all':
                break;
            default:
                $criteria_post = ' (p.post_time > ' . $startdate . ' OR t.topic_sticky=1)';
                break;
        }
        $criteria_forum = '';
        if (is_object($forum)) {
            $criteria_forum = ' AND t.forum_id = ' . $forum->getVar('forum_id');
        } else {
            if (is_array($forum) && count($forum) > 0) {
                $criteria_forum = ' AND t.forum_id IN (' . implode(',', array_keys($forum)) . ')';
            } elseif (!empty($forum)) {
                $criteria_forum = ' AND t.forum_id =' . (int)$forum;
            }
        }

        $sql = 'SELECT COUNT(*) AS count FROM ' . $this->db->prefix('newbb_topics') . ' t ' . $leftjoin;
        $sql .= ' WHERE ' . $criteria_post . $criteria_forum . $criteria_extra . $criteria_approve;
        if (!$result = $this->db->query($sql)) {
            //xoops_error($this->db->error().'<br>'.$sql);
            return null;
        }
        $myrow = $this->db->fetchArray($result);
        $count = $myrow['count'];

        return $count;
    }

    // get permission

    /**
     * @param         $forum
     * @param  string $type
     * @param  bool   $checkCategory
     * @return bool
     */
    public function getPermission($forum, $type = 'access', $checkCategory = true)
    {
        global $xoopsModule;
        static $_cachedPerms;

        if ('all' === $type) {
            return true;
        }

        require_once __DIR__ . '/../include/functions.user.php';
        if (newbbIsAdmin($forum)) {
            return true;
        }
        //if ($GLOBALS["xoopsUserIsAdmin"] && $xoopsModule->getVar("dirname") === "newbb") {
        //return true;
        //}

        if (!is_object($forum)) {
            $forum = $this->get($forum);
        }

        if (!empty($checkCategory)) {
            /** @var Newbb\CategoryHandler $categoryHandler */
            $categoryHandler = Newbb\Helper::getInstance()->getHandler('Category');
            $categoryPerm    = $categoryHandler->getPermission($forum->getVar('cat_id'));
            if (!$categoryPerm) {
                return false;
            }
        }

        $type = strtolower($type);
        // START irmtfan commented and removed
        //if ('moderate' === $type) {
        //require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.user.php');
        //$permission = newbbIsModerator($forum);
        //} else {
        $forum_id = $forum->getVar('forum_id');
        /** var Newbb\PermissionHandler $permHandler */
        $permHandler = Newbb\Helper::getInstance()->getHandler('Permission');
        $permission  = $permHandler->getPermission('forum', $type, $forum_id);
        //}
        // END irmtfan commented and removed
        return $permission;
    }

    /**
     * @param $forum
     * @return mixed
     */
    public function deletePermission(&$forum)
    {
        /** var Newbb\PermissionHandler $permHandler */
        $permHandler = Newbb\Helper::getInstance()->getHandler('Permission');

        return $permHandler->deleteByForum($forum->getVar('forum_id'));
    }

    /**
     * @param $forum
     * @return mixed
     */
    public function applyPermissionTemplate(&$forum)
    {
        /** var Newbb\PermissionHandler $permHandler */
        $permHandler = Newbb\Helper::getInstance()->getHandler('Permission');

        return $permHandler->applyTemplate($forum->getVar('forum_id'));
    }

    /*
    function isForum($forum)
    {
        $count = false;
        $sql = 'SELECT COUNT(*) as count FROM ' . $this->db->prefix("newbb_forums");
        $sql .= ' WHERE forum_id=' . $forum ;
        if ($result = $this->db->query($sql)) {
            $myrow = $this->db->fetchArray($result);
            $count = $myrow['count'];
        }

        return $count;
    }
    */

    /**
     * clean orphan forums from database
     * @param  string $table_link
     * @param  string $field_link
     * @param  string $field_object
     * @param  array  $forum_ids forum IDs
     * @return bool   true on success
     */
    // START irmtfan rewrite forum cleanOrphan function. add parent_forum and cat_id orphan check
    //    public function cleanOrphan(array $forum_ids = array())
    public function cleanOrphan($table_link = '', $field_link = '', $field_object = '', $forum_ids = [])
    {
        // check parent_forum orphan forums
        if (empty($forum_ids)) {
            $forum_ids = $this->getIds();
        }
        if (empty($forum_ids)) {
            return false;
        }
        /*
            $sql =    "    UPDATE ".$GLOBALS['xoopsDB']->prefix("newbb_forums").
                    "    SET parent_forum = 0".
                    "    WHERE (parent_forum NOT IN ( ".$forum_ids."))".
                    "        OR parent_forum = forum_id";
        */
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('parent_forum', '(' . implode(', ', $forum_ids) . ')', 'NOT IN'), 'AND');
        $criteria->add(new \Criteria('parent_forum', '`forum_id`', '='), 'OR');
        $b1 = $this->updateAll('parent_forum', 0, $criteria, true);
        // check cat_id orphan forums
        $categoryHandler = Newbb\Helper::getInstance()->getHandler('Category');
        $cat_ids         = $categoryHandler->getIds();
        if (empty($cat_ids)) {
            return false;
        }
        $criteria = new \CriteriaCompo();
        $criteria->add(new \Criteria('cat_id', '(' . implode(', ', $cat_ids) . ')', 'NOT IN'), 'AND');
        $b2 = $this->updateAll('cat_id', $cat_ids[0], $criteria, true);

        return ($b1 && $b2);
    }
    // END irmtfan rewrite forum cleanOrphan function. add parent_forum and cat_id orphan check

    /**
     * forum data synchronization
     *
     * @param  mixed $object null for all forums; integer for forum_id; object for forum object
     * @return bool
     * @internal param int $mode 1 for stats only; 2 for forum index data only; 0 for both
     *
     */
    public function synchronization($object = null)
    {
        if (empty($object)) {
            $forums = $this->getIds();
            $this->cleanOrphan('', '', '', $forums); // irmtfan - move cleanOrphan to synchronization function
            foreach ($forums as $id) {
                $this->synchronization($id);
            }

            return true;
        }

        if (!is_object($object)) {
            $object = $this->get((int)$object);
        }

        if (!$object->getVar('forum_id')) {
            return false;
        }
        $sql = 'SELECT MAX(post_id) AS last_post, COUNT(*) AS total FROM ' . $this->db->prefix('newbb_posts') . ' AS p LEFT JOIN  ' . $this->db->prefix('newbb_topics') . ' AS t ON p.topic_id=t.topic_id WHERE p.approved=1 AND t.approved=1 AND p.forum_id = ' . $object->getVar('forum_id');

        if ($result = $this->db->query($sql)) {
            $last_post = 0;
            $posts     = 0;
            if ($row = $this->db->fetchArray($result)) {
                $last_post = (int)$row['last_post'];
                $posts     = (int)$row['total'];
            }
            if ($object->getVar('forum_last_post_id') !== $last_post) {
                $object->setVar('forum_last_post_id', $last_post);
            }
            if ($object->getVar('forum_posts') !== $posts) {
                $object->setVar('forum_posts', $posts);
            }
        }

        $sql = 'SELECT COUNT(*) AS total FROM ' . $this->db->prefix('newbb_topics') . ' WHERE approved=1 AND forum_id = ' . $object->getVar('forum_id');
        if ($result = $this->db->query($sql)) {
            if ($row = $this->db->fetchArray($result)) {
                if ($object->getVar('forum_topics') !== $row['total']) {
                    $object->setVar('forum_topics', $row['total']);
                }
            }
        }
        $object->setDirty();

        return $this->insert($object, true);
    }

    /**
     * @param  null $passedSubForums
     * @return array
     */
    public function getSubforumStats($passedSubForums = null)
    {
        $stats = [];

        require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.forum.php');

        $subForumTree = newbbGetSubForum();
        if (empty($passedSubForums)) {
            $sub_forums = $subForumTree;
        } else {
            foreach ($passedSubForums as $id) {
                $sub_forums[$id] = isset($subForumTree[$id]) ? $subForumTree[$id] : null;
            }
        }

        $forums_id = [];
        foreach (array_keys($sub_forums) as $id) {
            if (empty($sub_forums[$id])) {
                continue;
            }
            $forums_id = array_merge($forums_id, $sub_forums[$id]);
        }
        if (!$forums_id) {
            return $stats;
        }
        $sql = '    SELECT forum_posts AS posts, forum_topics AS topics, forum_id AS id' . '    FROM ' . $this->table . '    WHERE forum_id IN (' . implode(', ', $forums_id) . ')';
        if (!$result = $this->db->query($sql)) {
            return $stats;
        }

        $forum_stats = [];
        while (false !== ($row = $this->db->fetchArray($result))) {
            $forum_stats[$row['id']] = ['topics' => $row['topics'], 'posts' => $row['posts']];
        }

        foreach (array_keys($sub_forums) as $id) {
            if (empty($sub_forums[$id])) {
                continue;
            }
            $stats[$id] = ['topics' => 0, 'posts' => 0];
            foreach ($sub_forums[$id] as $fid) {
                $stats[$id]['topics'] += $forum_stats[$fid]['topics'];
                $stats[$id]['posts']  += $forum_stats[$fid]['posts'];
            }
        }

        return $stats;
    }

    /**
     * @param        $forums
     * @param  int   $length_title_index
     * @param  int   $count_subforum
     * @return array
     */
    public function &display($forums, $length_title_index = 30, $count_subforum = 1)
    {
        global $myts;

        $posts       = [];
        $postsObject = [];
        foreach (array_keys($forums) as $id) {
            $posts[] = $forums[$id]['forum_last_post_id'];
        }
        if (!empty($posts)) {
            $postHandler = Newbb\Helper::getInstance()->getHandler('Post');
            $tags_post   = ['uid', 'topic_id', 'post_time', 'poster_name', 'icon'];
            if (!empty($length_title_index)) {
                $tags_post[] = 'subject';
            }
            $posts = $postHandler->getAll(new \Criteria('post_id', '(' . implode(', ', $posts) . ')', 'IN'), $tags_post, false);
        }

        // Get topic/post stats per forum
        $stats_forum = [];

        if (!empty($count_subforum)) {
            $stats_forum = $this->getSubforumStats(array_keys($forums)); // irmtfan uncomment to count sub forum posts/topics
        }

        $users  = [];
        $reads  = [];
        $topics = [];

        foreach (array_keys($forums) as $id) {
            $forum =& $forums[$id];

            if (!$forum['forum_last_post_id']) {
                continue;
            }
            if (!$post = @$posts[$forum['forum_last_post_id']]) {
                $forum['forum_last_post_id'] = 0;
                continue;
            }

            $users[] = $post['uid'];
            if ($moderators[$id] = $forum['forum_moderator']) {
                $users = array_merge($users, $moderators[$id]);
            }

            // reads
            if (!empty($GLOBALS['xoopsModuleConfig']['read_mode'])) {
                $reads[$id] = (1 == $GLOBALS['xoopsModuleConfig']['read_mode']) ? $post['post_time'] : $post['post_id'];
            }
        }

        require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.user.php');
        require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.time.php');
        require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.render.php');
        require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.read.php');
        $forum_isread = newbbIsRead('forum', $reads);
        $users_linked = newbbGetUnameFromIds(array_unique($users), !empty($GLOBALS['xoopsModuleConfig']['show_realname']), true);

        $forums_array   = [];
        $name_anonymous = $myts->htmlSpecialChars($GLOBALS['xoopsConfig']['anonymous']);

        foreach (array_keys($forums) as $id) {
            $forum =& $forums[$id];

            $_forum_data                 = [];
            $_forum_data['forum_order']  = $forum['forum_order'];
            $_forum_data['forum_id']     = $id;
            $_forum_data['forum_cid']    = $forum['cat_id'];
            $_forum_data['forum_name']   = $forum['forum_name'];
            $forumDescLength             = $GLOBALS['xoopsModuleConfig']['forum_desc_length'];
            $_forum_data['forum_desc']   = mb_strimwidth($forum['forum_desc'], 0, $forumDescLength, '...');
            $_forum_data['forum_topics'] = $forum['forum_topics'] + @$stats_forum[$id]['topics'];
            $_forum_data['forum_posts']  = $forum['forum_posts'] + @$stats_forum[$id]['posts'];
            //$_forum_data["forum_type"]= $forum['forum_type'];

            $forum_moderators = [];
            if (!empty($moderators[$id])) {
                foreach (@$moderators[$id] as $moderator) {
                    $forum_moderators[] = @$users_linked[$moderator];
                }
            }
            $_forum_data['forum_moderators'] = implode(', ', $forum_moderators);

            // irmtfan change if/endif to if{} method
            if ($post_id = $forum['forum_last_post_id']) {
                $post                               =& $posts[$post_id];
                $_forum_data['forum_lastpost_id']   = $post_id;
                $_forum_data['forum_lastpost_time'] = newbbFormatTimestamp($post['post_time']);
                if (!empty($users_linked[$post['uid']])) {
                    $_forum_data['forum_lastpost_user'] = $users_linked[$post['uid']];
                } elseif ($poster_name = $post['poster_name']) {
                    $_forum_data['forum_lastpost_user'] = $poster_name;
                } else {
                    $_forum_data['forum_lastpost_user'] = $name_anonymous;
                }
                if (!empty($length_title_index)) {
                    $subject = $post['subject'];
                    if ($length_title_index < 255) {
                        $subject = xoops_substr($subject, 0, $length_title_index);
                    }
                    $_forum_data['forum_lastpost_subject'] = $subject;
                }
                // irmtfan - remove icon_path and use newbbDisplayImage
                $_forum_data['forum_lastpost_icon'] = newbbDisplayImage('lastposticon', _MD_NEWBB_GOTOLASTPOST);
                // START irmtfan change the method to add read smarty
                if (empty($forum_isread[$id])) {
                    $_forum_data['forum_folder'] = newbbDisplayImage('forum_new', _MD_NEWBB_NEWPOSTS);
                    $_forum_data['forum_read']   = 0; // irmtfan add forum-read/forum-new smarty variable
                } else {
                    $_forum_data['forum_folder'] = newbbDisplayImage('forum', _MD_NEWBB_NONEWPOSTS);
                    $_forum_data['forum_read']   = 1; // irmtfan add forum-read/forum-new smarty variable
                }
                // END irmtfan change the method to add read smarty
            }
            $forums_array[$forum['parent_forum']][] = $_forum_data;
        }

        return $forums_array;
    }

    /**
     * get a hierarchical tree of forums
     *
     * {@link newbbTree}
     *
     * @param  int    $cat_id     category ID
     * @param  int    $pid        Top forum ID
     * @param  string $permission permission type
     * @param  string $prefix     prefix for display
     * @param  string $tags       variables to fetch
     * @return array  associative array of category IDs and sanitized titles
     */
    public function &getTree($cat_id = 0, $pid = 0, $permission = 'access', $prefix = '--', $tags = null)
    {
        $pid         = (int)$pid;
        $perm_string = $permission;
        if (!is_array($tags) || 0 === count($tags)) {
            $tags = ['forum_id', 'parent_forum', 'forum_name', 'forum_order', 'cat_id'];
        }
        $forumsObject = $this->getByPermission($cat_id, $perm_string, $tags);

        require_once __DIR__ . '/tree.php';
        $forums_structured = [];
        foreach (array_keys($forumsObject) as $key) {
            $forums_structured[$forumsObject[$key]->getVar('cat_id')][$key] = $forumsObject[$key];
        }

        foreach (array_keys($forums_structured) as $cid) {
            $tree              = new ObjectTree($forums_structured[$cid]);
            $forum_array[$cid] = $tree->makeTree($prefix, $pid, $tags);
            unset($tree);
        }

        return $forum_array;
    }

    /**
     * get a hierarchical array tree of forums
     *
     * {@link newbbTree}
     *
     * @param  int     $cat_id     category ID
     * @param  int     $pid        Top forum ID
     * @param  string  $permission permission type
     * @param  string  $tags       variables to fetch
     * @param  integer $depth      level of subcategories
     * @return array   associative array of category IDs and sanitized titles
     */
    public function &getArrayTree($cat_id = 0, $pid = 0, $permission = 'access', $tags = null, $depth = 0)
    {
        $pid         = (int)$pid;
        $perm_string = $permission;
        if (!is_array($tags) || 0 === count($tags)) {
            $tags = ['forum_id', 'parent_forum', 'forum_name', 'forum_order', 'cat_id'];
        }
        $forumsObject = $this->getByPermission($cat_id, $perm_string, $tags);

        require_once __DIR__ . '/tree.php';
        $forums_structured = [];
        foreach (array_keys($forumsObject) as $key) {
            $forumObject                                             =& $forumsObject[$key];
            $forums_structured[$forumObject->getVar('cat_id')][$key] = $forumsObject[$key];
        }
        foreach (array_keys($forums_structured) as $cid) {
            $tree              = new ObjectTree($forums_structured[$cid]);
            $forum_array[$cid] = $tree->makeArrayTree($pid, $tags, $depth);
            unset($tree);
        }

        return $forum_array;
    }

    /**
     * @param $object
     * @return array|null
     */
    public function &getParents($object)
    {
        $ret = null;
        if (!$object->getVar('forum_id')) {
            return $ret;
        }

        require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.forum.php');
        if (!$parents = newbbGetParentForum($object->getVar('forum_id'))) {
            return $ret;
        }
        $parents_list = $this->getList(new \Criteria('forum_id', '(' . implode(', ', $parents) . ')', 'IN'));
        foreach ($parents as $key => $id) {
            $ret[] = ['forum_id' => $id, 'forum_name' => $parents_list[$id]];
        }
        unset($parents, $parents_list);

        return $ret;
    }

    // START irmtfan - get forum Ids by values. parse positive values to forum IDs and negative values to category IDs. value=0 => all valid forums

    /**
     * function for get forum Ids by positive and negative values
     *
     * @param  int|text $values : positive values = forums | negative values = cats | $values=0 = all valid forums, $permission , true/false $parse_cats
     * @param  string   $permission
     * @param  bool     $parse_cats
     * @return array|mixed $validForums
     */
    public function getIdsByValues($values = 0, $permission = 'access', $parse_cats = true)
    {
        // Get all valid forums with this permission
        $validForums = $this->getIdsByPermission($permission);
        // if no value or value=0 return all valid forums
        if (empty($values)) {
            return $validForums;
        }
        $values = is_numeric($values) ? [$values] : $values;
        //parse negative values to category IDs
        $forums = [];
        $cats   = [];
        foreach ($values as $val) {
            if (0 == $val) {
                // value=0 => all valid forums
                return $validForums;
            } elseif ($val > 0) {
                $forums[] = $val;
            } else {
                $cats[] = abs($val);
            }
        }
        // if dont want to parse categories OR no cats return all forums
        if (empty($parse_cats) || empty($cats)) {
            return array_intersect($validForums, $forums);
        }
        // Get all forums by category IDs
        $forumObjs = $this->getForumsByCategory($cats, $permission, true);
        $forums    = array_merge($forums, array_keys($forumObjs));

        return array_intersect($validForums, $forums);
    }
    // END irmtfan - get forum Ids by values. parse positive values to forum IDs and negative values to category IDs. value=0 => all valid forums
}

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

/**
 * Class DigestHandler
 */
class DigestHandler extends \XoopsPersistableObjectHandler
{
    public $last_digest;

    /**
     * Constructor
     *
     * @param null|\XoopsDatabase $db database connection
     */
    public function __construct(\XoopsDatabase $db)
    {
        parent::__construct($db, 'newbb_digest', Digest::class, 'digest_id');
    }

    /**
     * @param  bool $isForced
     * @return int
     */
    public function process($isForced = false)
    {
        $this->getLastDigest();
        if (!$isForced) {
            $status = $this->checkStatus();
            if ($status < 1) {
                return 1;
            }
        }
        $digest = $this->create();
        $status = $this->buildDigest($digest);
        if (!$status) {
            return 2;
        }
        $status = $this->insert($digest);
        if (!$status) {
            return 3;
        }
        $status = $this->notify($digest);
        if (!$status) {
            return 4;
        }

        return 0;
    }

    /**
     * @param \XoopsObject $digest
     * @return bool
     */
    public function notify(\XoopsObject $digest)
    {
        //$content                = $digest->getVar('digest_content');
        /** @var \XoopsNotificationHandler $notificationHandler */
        $notificationHandler    = xoops_getHandler('notification');
        $tags['DIGEST_ID']      = $digest->getVar('digest_id');
        $tags['DIGEST_CONTENT'] = $digest->getVar('digest_content', 'E');
        $notificationHandler->triggerEvent('global', 0, 'digest', $tags);

        return true;
    }

    /**
     * @param        $start
     * @param  int   $perpage
     * @return array
     */
    public function getAllDigests($start = 0, $perpage = 5)
    {
        //        if (empty($start)) {
        //            $start = 0;
        //        }

        $sql    = 'SELECT * FROM ' . $this->db->prefix('newbb_digest') . ' ORDER BY digest_id DESC';
        $result = $this->db->query($sql, $perpage, $start);
        $ret    = [];
        //        $reportHandler =  Newbb\Helper::getInstance()->getHandler('Report');
        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $ret[] = $myrow; // return as array
        }

        return $ret;
    }

    /**
     * @return int
     */
    public function getDigestCount()
    {
        $sql    = 'SELECT COUNT(*) AS count FROM ' . $this->db->prefix('newbb_digest');
        $result = $this->db->query($sql);
        if (!$result) {
            return 0;
        } else {
            $array = $this->db->fetchArray($result);

            return $array['count'];
        }
    }

    public function getLastDigest()
    {
        $sql    = 'SELECT MAX(digest_time) AS last_digest FROM ' . $this->db->prefix('newbb_digest');
        $result = $this->db->query($sql);
        if (!$result) {
            $this->last_digest = 0;
        // echo "<br>no data:".$query;
        } else {
            $array             = $this->db->fetchArray($result);
            $this->last_digest = isset($array['last_digest']) ? $array['last_digest'] : 0;
        }
    }

    /**
     * @return int
     */
    public function checkStatus()
    {
        if (!isset($this->last_digest)) {
            $this->getLastDigest();
        }
        $deadline  = (1 == $GLOBALS['xoopsModuleConfig']['email_digest']) ? 60 * 60 * 24 : 60 * 60 * 24 * 7;
        $time_diff = time() - $this->last_digest;

        return $time_diff - $deadline;
    }

    /**
     * @param  \XoopsObject $digest
     * @param  bool         $force flag to force the query execution despite security settings
     * @return mixed       object ID or false
     */
    public function insert(\XoopsObject $digest, $force = true)
    {
        $digest->setVar('digest_time', time());
        return parent::insert($digest, $force);
        /*
        $content = $digest->getVar('digest_content', 'E');

        $id  = $this->db->genId($digest->table . '_digest_id_seq');
        $sql = 'INSERT INTO ' . $digest->table . ' (digest_id, digest_time, digest_content)    VALUES (' . $id . ', ' . time() . ', ' . $this->db->quoteString($content) . ' )';

        if (!$this->db->queryF($sql)) {
            //echo "<br>digest insert error::" . $sql;
            return false;
        }
        if (empty($id)) {
            $id = $this->db->getInsertId();
        }
        $digest->setVar('digest_id', $id);

        return true;
        */
    }

    /**
     * @param \XoopsObject $digest
     * @param  bool        $force (ignored)
     * @return bool        FALSE if failed.
     */
    public function delete(\XoopsObject $digest, $force = false)
    {
        $digest_id = $digest->getVar('digest_id');

        if (!isset($this->last_digest)) {
            $this->getLastDigest();
        }
        if ($this->last_digest == $digest_id) {
            return false;
        } // It is not allowed to delete the last digest

        return parent::delete($digest, true);
    }

    /**
     * @param \XoopsObject $digest
     * @return bool
     */
    public function buildDigest(\XoopsObject $digest)
    {
        global $xoopsModule;

        if (!defined('SUMMARY_LENGTH')) {
            define('SUMMARY_LENGTH', 100);
        }

        /** @var Newbb\ForumHandler $forumHandler */
        $forumHandler         = Newbb\Helper::getInstance()->getHandler('Forum');
        $thisUser             = $GLOBALS['xoopsUser'];
        $GLOBALS['xoopsUser'] = null; // To get posts accessible by anonymous
        $GLOBALS['xoopsUser'] = $thisUser;

        $accessForums    = $forumHandler->getIdsByPermission(); // get all accessible forums
        $forumCriteria   = ' AND t.forum_id IN (' . implode(',', $accessForums) . ')';
        $approveCriteria = ' AND t.approved = 1 AND p.approved = 1';
        $time_criteria   = ' AND t.digest_time > ' . $this->last_digest;

        $karma_criteria = $GLOBALS['xoopsModuleConfig']['enable_karma'] ? ' AND p.post_karma=0' : '';
        $reply_criteria = $GLOBALS['xoopsModuleConfig']['allow_require_reply'] ? ' AND p.require_reply=0' : '';

        $query = 'SELECT t.topic_id, t.forum_id, t.topic_title, t.topic_time, t.digest_time, p.uid, p.poster_name, pt.post_text FROM '
                 . $this->db->prefix('newbb_topics')
                 . ' t, '
                 . $this->db->prefix('newbb_posts_text')
                 . ' pt, '
                 . $this->db->prefix('newbb_posts')
                 . ' p WHERE t.topic_digest = 1 AND p.topic_id=t.topic_id AND p.pid=0 '
                 . $forumCriteria
                 . $approveCriteria
                 . $time_criteria
                 . $karma_criteria
                 . $reply_criteria
                 . ' AND pt.post_id=p.post_id ORDER BY t.digest_time DESC';
        if (!$result = $this->db->query($query)) {
            //echo "<br>No result:<br>$query";
            return false;
        }
        $rows  = [];
        $users = [];
        while (false !== ($row = $this->db->fetchArray($result))) {
            $users[$row['uid']] = 1;
            $rows[]             = $row;
        }
        if (count($rows) < 1) {
            return false;
        }
        $uids = array_keys($users);
        if (count($uids) > 0) {
            /** @var \XoopsMemberHandler $memberHandler */
            $memberHandler = xoops_getHandler('member');
            $user_criteria = new \Criteria('uid', '(' . implode(',', $uids) . ')', 'IN');
            $users         = $memberHandler->getUsers($user_criteria, true);
        } else {
            $users = [];
        }

        foreach ($rows as $topic) {
            if ($topic['uid'] > 0) {
                if (isset($users[$topic['uid']]) && is_object($users[$topic['uid']])
                    && $users[$topic['uid']]->isActive()) {
                    $topic['uname'] = $users[$topic['uid']]->getVar('uname');
                } else {
                    $topic['uname'] = $GLOBALS['xoopsConfig']['anonymous'];
                }
            } else {
                $topic['uname'] = $topic['poster_name'] ?: $GLOBALS['xoopsConfig']['anonymous'];
            }
            $summary = \Xmf\Metagen::generateDescription($topic['post_text'], SUMMARY_LENGTH);
            $author  = $topic['uname'] . ' (' . formatTimestamp($topic['topic_time']) . ')';
            $link    = XOOPS_URL . '/modules/' . $xoopsModule->dirname() . '/viewtopic.php?topic_id=' . $topic['topic_id'] . '&amp;forum=' . $topic['forum_id'];
            $title   = $topic['topic_title'];
            $digest->addItem($title, $link, $author, $summary);
        }
        $digest->buildContent();

        return true;
    }
}

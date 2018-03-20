<?php namespace XoopsModules\Newbb;

//
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                  Copyright (c) 2000-2016 XOOPS.org                        //
//                       <https://xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
//  Author: phppp (D.J., infomax@gmail.com)                                  //
//  URL: https://xoops.org                                                    //
//  Project: Article Project                                                 //
//  ------------------------------------------------------------------------ //

use XoopsModules\Newbb;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

defined('NEWBB_FUNCTIONS_INI') || include $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

/**
 * Class PostHandler
 */
class PostHandler extends \XoopsPersistableObjectHandler
{
    /**
     * @param \XoopsDatabase $db
     */
    public function __construct(\XoopsDatabase $db)
    {
        parent::__construct($db, 'newbb_posts', Post::class, 'post_id', 'subject');
    }

    /**
     * @param  mixed $id
     * @param  null  $var
     * @return null|\XoopsObject
     */
    public function get($id = null, $var = null) //get($id)
    {
        $id   = (int)$id;
        $post = null;
        $sql  = 'SELECT p.*, t.* FROM ' . $this->db->prefix('newbb_posts') . ' p LEFT JOIN ' . $this->db->prefix('newbb_posts_text') . ' t ON p.post_id=t.post_id WHERE p.post_id=' . $id;
        if ($array = $this->db->fetchArray($this->db->query($sql))) {
            $post = $this->create(false);
            $post->assignVars($array);
        }

        return $post;
    }

    /**
     * @param  int             $limit
     * @param  int             $start
     * @param  \CriteriaElement $criteria
     * @param  null            $fields
     * @param  bool            $asObject
     * @param  int             $topic_id
     * @param  int             $approved
     * @return array
     */
    //    public function getByLimit($topic_id, $limit, $approved = 1)
    public function &getByLimit(
        $limit = 0,
        $start = 0,
        \CriteriaElement $criteria = null,
        $fields = null,
        $asObject = true,
        $topic_id = 0,
        $approved = 1
    ) {
        $sql    = 'SELECT p.*, t.*, tp.topic_status FROM '
                  . $this->db->prefix('newbb_posts')
                  . ' p LEFT JOIN '
                  . $this->db->prefix('newbb_posts_text')
                  . ' t ON p.post_id=t.post_id LEFT JOIN '
                  . $this->db->prefix('newbb_topics')
                  . ' tp ON tp.topic_id=p.topic_id WHERE p.topic_id='
                  . $topic_id
                  . ' AND p.approved ='
                  . $approved
                  . ' ORDER BY p.post_time DESC';
        $result = $this->db->query($sql, $limit, 0);
        $ret    = [];
        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $post = $this->create(false);
            $post->assignVars($myrow);

            $ret[$myrow['post_id']] = $post;
            unset($post);
        }

        return $ret;
    }

    /**
     * @param Post $post
     * @return mixed
     */
    public function getPostForPDF(&$post)
    {
        return $post->getPostBody(true);
    }

    /**
     * @param Post $post
     * @return mixed
     */
    public function getPostForPrint(&$post)
    {
        return $post->getPostBody();
    }

    /**
     * @param  Post $post
     * @param  bool $force
     * @return bool
     */
    public function approve(&$post, $force = false)
    {
        if (empty($post)) {
            return false;
        }
        if (is_numeric($post)) {
            $post = $this->get($post);
        }
        $post_id = $post->getVar('post_id');

        $wasApproved = $post->getVar('approved');
        // irmtfan approve post if the approved = 0 (pending) or -1 (deleted)
        if (empty($force) && $wasApproved > 0) {
            return true;
        }
        $post->setVar('approved', 1);
        $this->insert($post, true);

        /** @var Newbb\TopicHandler $topicHandler */
        $topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
        $topicObject  = $topicHandler->get($post->getVar('topic_id'));
        if ($topicObject->getVar('topic_last_post_id') < $post->getVar('post_id')) {
            $topicObject->setVar('topic_last_post_id', $post->getVar('post_id'));
        }
        if ($post->isTopic()) {
            $topicObject->setVar('approved', 1);
        } else {
            $topicObject->setVar('topic_replies', $topicObject->getVar('topic_replies') + 1);
        }
        $topicHandler->insert($topicObject, true);

        /** @var Newbb\ForumHandler $forumHandler */
        $forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
        $forumObject  = $forumHandler->get($post->getVar('forum_id'));
        if ($forumObject->getVar('forum_last_post_id') < $post->getVar('post_id')) {
            $forumObject->setVar('forum_last_post_id', $post->getVar('post_id'));
        }
        $forumObject->setVar('forum_posts', $forumObject->getVar('forum_posts') + 1);
        if ($post->isTopic()) {
            $forumObject->setVar('forum_topics', $forumObject->getVar('forum_topics') + 1);
        }
        $forumHandler->insert($forumObject, true);

        // Update user stats
        if ($post->getVar('uid') > 0) {
            /** @var \XoopsMemberHandler $memberHandler */
            $memberHandler = xoops_getHandler('member');
            $poster        = $memberHandler->getUser($post->getVar('uid'));
            if (is_object($poster) && $post->getVar('uid') == $poster->getVar('uid')) {
                $poster->setVar('posts', $poster->getVar('posts') + 1);
                $res = $memberHandler->insertUser($poster, true);
                unset($poster);
            }
        }

        // Update forum stats
        /** @var StatsHandler $statsHandler */
        $statsHandler = Newbb\Helper::getInstance()->getHandler('Stats');
        $statsHandler->update($post->getVar('forum_id'), 'post');
        if ($post->isTopic()) {
            $statsHandler->update($post->getVar('forum_id'), 'topic');
        }

        return true;
    }

    /**
     * @param \XoopsObject $post
     * @param  bool        $force
     * @return bool
     */
    public function insert(\XoopsObject $post, $force = true) //insert(&$post, $force = true)
    {
        $topicObject = null;
        // Set the post time
        // The time should be "publish" time. To be adjusted later
        if (!$post->getVar('post_time')) {
            $post->setVar('post_time', time());
        }

        /** @var Newbb\TopicHandler $topicHandler */
        $topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
        // Verify the topic ID
        if ($topic_id = $post->getVar('topic_id')) {
            $topicObject = $topicHandler->get($topic_id);
            // Invalid topic OR the topic is no approved and the post is not top post
            if (!$topicObject//    || (!$post->isTopic() && $topicObject->getVar("approved") < 1)
            ) {
                return false;
            }
        }
        if (empty($topic_id)) {
            $post->setVar('topic_id', 0);
            $post->setVar('pid', 0);
            $post->setNew();
            $topicObject = $topicHandler->create();
        }
        $textHandler    = Newbb\Helper::getInstance()->getHandler('Text');
        $post_text_vars = ['post_text', 'post_edit', 'dohtml', 'doxcode', 'dosmiley', 'doimage', 'dobr'];
        if ($post->isNew()) {
            if (!$topic_id = $post->getVar('topic_id')) {
                $topicObject->setVar('topic_title', $post->getVar('subject', 'n'));
                $topicObject->setVar('topic_poster', $post->getVar('uid'));
                $topicObject->setVar('forum_id', $post->getVar('forum_id'));
                $topicObject->setVar('topic_time', $post->getVar('post_time'));
                $topicObject->setVar('poster_name', $post->getVar('poster_name'));
                $topicObject->setVar('approved', $post->getVar('approved'));

                if (!$topic_id = $topicHandler->insert($topicObject, $force)) {
                    $post->deleteAttachment();
                    $post->setErrors('insert topic error');

                    //xoops_error($topicObject->getErrors());
                    return false;
                }
                $post->setVar('topic_id', $topic_id);

                $pid = 0;
                $post->setVar('pid', 0);
            } elseif (!$post->getVar('pid')) {
                $pid = $topicHandler->getTopPostId($topic_id);
                $post->setVar('pid', $pid);
            }

            $textObject = $textHandler->create();
            foreach ($post_text_vars as $key) {
                $textObject->vars[$key] = $post->vars[$key];
            }
            $post->destroyVars($post_text_vars);

            //            if (!$post_id = parent::insert($post, $force)) {
            //                return false;
            //            }

            if (!$post_id = parent::insert($post, $force)) {
                return false;
            } else {
                $post->unsetNew();
            }

            $textObject->setVar('post_id', $post_id);
            if (!$textHandler->insert($textObject, $force)) {
                $this->delete($post);
                $post->setErrors('post text insert error');

                //xoops_error($textObject->getErrors());
                return false;
            }
            if ($post->getVar('approved') > 0) {
                $this->approve($post, true);
            }
            $post->setVar('post_id', $post_id);
        } else {
            if ($post->isTopic()) {
                if ($post->getVar('subject') !== $topicObject->getVar('topic_title')) {
                    $topicObject->setVar('topic_title', $post->getVar('subject', 'n'));
                }
                if ($post->getVar('approved') !== $topicObject->getVar('approved')) {
                    $topicObject->setVar('approved', $post->getVar('approved'));
                }
                $topicObject->setDirty();
                if (!$result = $topicHandler->insert($topicObject, $force)) {
                    $post->setErrors('update topic error');

                    //xoops_error($topicObject->getErrors());
                    return false;
                }
            }
            $textObject = $textHandler->get($post->getVar('post_id'));
            $textObject->setDirty();
            foreach ($post_text_vars as $key) {
                $textObject->vars[$key] = $post->vars[$key];
            }
            $post->destroyVars($post_text_vars);
            if (!$post_id = parent::insert($post, $force)) {
                //xoops_error($post->getErrors());
                return false;
            } else {
                $post->unsetNew();
            }
            if (!$textHandler->insert($textObject, $force)) {
                $post->setErrors('update post text error');

                //xoops_error($textObject->getErrors());
                return false;
            }
        }

        return $post->getVar('post_id');
    }

    /**
     * @param \XoopsObject $post
     * @param  bool        $isDeleteOne
     * @param  bool        $force
     * @return bool
     */
    public function delete(\XoopsObject $post, $isDeleteOne = true, $force = false)
    {
        if (!is_object($post) || 0 == $post->getVar('post_id')) {
            return false;
        }

        if ($isDeleteOne) {
            if ($post->isTopic()) {
                $criteria = new \CriteriaCompo(new \Criteria('topic_id', $post->getVar('topic_id')));
                $criteria->add(new \Criteria('approved', 1));
                $criteria->add(new \Criteria('pid', 0, '>'));
                if ($this->getPostCount($criteria) > 0) {
                    return false;
                }
            }

            return $this->myDelete($post, $force);
        } else {
            require_once $GLOBALS['xoops']->path('class/xoopstree.php');
            $mytree = new \XoopsTree($this->db->prefix('newbb_posts'), 'post_id', 'pid');
            $arr    = $mytree->getAllChild($post->getVar('post_id'));
            // irmtfan - delete childs in a reverse order
            for ($i = count($arr) - 1; $i >= 0; $i--) {
                $childpost = $this->create(false);
                $childpost->assignVars($arr[$i]);
                $this->myDelete($childpost, $force);
                unset($childpost);
            }
            $this->myDelete($post, $force);
        }

        return true;
    }

    /**
     * @param  Post $post
     * @param  bool $force
     * @return bool
     */
    public function myDelete(Post $post, $force = false)
    {
        global $xoopsModule;

        if (!is_object($post) || 0 == $post->getVar('post_id')) {
            return false;
        }

        /* Set active post as deleted */
        if ($post->getVar('approved') > 0 && empty($force)) {
            $sql = 'UPDATE ' . $this->db->prefix('newbb_posts') . ' SET approved = -1 WHERE post_id = ' . $post->getVar('post_id');
            if (!$result = $this->db->queryF($sql)) {
            }
            /* delete pending post directly */
        } else {
            $sql = sprintf('DELETE FROM `%s` WHERE post_id = %u', $this->db->prefix('newbb_posts'), $post->getVar('post_id'));
            if (!$result = $this->db->queryF($sql)) {
                $post->setErrors('delete post error: ' . $sql);

                return false;
            }
            $post->deleteAttachment();

            $sql = sprintf('DELETE FROM %s WHERE post_id = %u', $this->db->prefix('newbb_posts_text'), $post->getVar('post_id'));
            if (!$result = $this->db->queryF($sql)) {
                $post->setErrors('Could not remove post text: ' . $sql);

                return false;
            }
        }

        if ($post->isTopic()) {
            $topicHandler = Newbb\Helper::getInstance()->getHandler('Topic');
            /** @var Topic $topicObject */
            $topicObject = $topicHandler->get($post->getVar('topic_id'));
            if (is_object($topicObject) && $topicObject->getVar('approved') > 0 && empty($force)) {
                $topiccount_toupdate = 1;
                $topicObject->setVar('approved', -1);
                $topicHandler->insert($topicObject);
                xoops_notification_deletebyitem($xoopsModule->getVar('mid'), 'thread', $post->getVar('topic_id'));
            } else {
                if (is_object($topicObject)) {
                    if ($topicObject->getVar('approved') > 0) {
                        xoops_notification_deletebyitem($xoopsModule->getVar('mid'), 'thread', $post->getVar('topic_id'));
                    }

                    $poll_id = $topicObject->getVar('poll_id');
                    // START irmtfan poll_module
                    $topicObject->deletePoll($poll_id);
                    // END irmtfan poll_module
                }

                $sql = sprintf('DELETE FROM %s WHERE topic_id = %u', $this->db->prefix('newbb_topics'), $post->getVar('topic_id'));
                if (!$result = $this->db->queryF($sql)) {
                    //xoops_error($this->db->error());
                }
                $sql = sprintf('DELETE FROM %s WHERE topic_id = %u', $this->db->prefix('newbb_votedata'), $post->getVar('topic_id'));
                if (!$result = $this->db->queryF($sql)) {
                    //xoops_error($this->db->error());
                }
            }
        } else {
            $sql = 'UPDATE ' . $this->db->prefix('newbb_topics') . ' t
                            LEFT JOIN ' . $this->db->prefix('newbb_posts') . ' p ON p.topic_id = t.topic_id
                            SET t.topic_last_post_id = p.post_id
                            WHERE t.topic_last_post_id = ' . $post->getVar('post_id') . '
                                    AND p.post_id = (SELECT MAX(post_id) FROM ' . $this->db->prefix('newbb_posts') . ' WHERE topic_id=t.topic_id)';
            if (!$result = $this->db->queryF($sql)) {
            }
        }

        $postcount_toupdate = $post->getVar('approved');

        if ($postcount_toupdate > 0) {

            // Update user stats
            if ($post->getVar('uid') > 0) {
                /** @var \XoopsMemberHandler $memberHandler */
                $memberHandler = xoops_getHandler('member');
                $poster        = $memberHandler->getUser($post->getVar('uid'));
                if (is_object($poster) && $post->getVar('uid') == $poster->getVar('uid')) {
                    $poster->setVar('posts', $poster->getVar('posts') - 1);
                    $res = $memberHandler->insertUser($poster, true);
                    unset($poster);
                }
            }
            // irmtfan - just update the pid for approved posts when the post is not topic (pid=0)
            if (!$post->isTopic()) {
                $sql = 'UPDATE ' . $this->db->prefix('newbb_posts') . ' SET pid = ' . $post->getVar('pid') . ' WHERE approved=1 AND pid=' . $post->getVar('post_id');
                if (!$result = $this->db->queryF($sql)) {
                    //xoops_error($this->db->error());
                }
            }
        }

        return true;
    }

    // START irmtfan enhance getPostCount when there is join (read_mode = 2)

    /**
     * @param  null $criteria
     * @param  null $join
     * @return int|null
     */
    public function getPostCount($criteria = null, $join = null)
    {
        // if not join get the count from XOOPS/class/model/stats as before
        if (empty($join)) {
            return parent::getCount($criteria);
        }

        $sql = 'SELECT COUNT(*) as count' . ' FROM ' . $this->db->prefix('newbb_posts') . ' AS p' . ' LEFT JOIN ' . $this->db->prefix('newbb_posts_text') . ' AS t ON t.post_id = p.post_id';
        // LEFT JOIN
        $sql .= $join;
        // WHERE
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
            $sql .= ' ' . $criteria->renderWhere();
        }
        if (!$result = $this->db->query($sql)) {
            //xoops_error($this->db->error().'<br>'.$sql);
            return null;
        }
        $myrow = $this->db->fetchArray($result);
        $count = $myrow['count'];

        return $count;
    }
    // END irmtfan enhance getPostCount when there is join (read_mode = 2)
    /*
     * TODO: combining viewtopic.php
     */
    /**
     * @param  null $criteria
     * @param  int  $limit
     * @param  int  $start
     * @param  null $join
     * @return array
     */
    public function getPostsByLimit($criteria = null, $limit = 1, $start = 0, $join = null)
    {
        $ret = [];
        $sql = 'SELECT p.*, t.* ' . ' FROM ' . $this->db->prefix('newbb_posts') . ' AS p' . ' LEFT JOIN ' . $this->db->prefix('newbb_posts_text') . ' AS t ON t.post_id = p.post_id';
        if (!empty($join)) {
            $sql .= $join;
        }
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
            $sql .= ' ' . $criteria->renderWhere();
            if ('' !== $criteria->getSort()) {
                $sql .= ' ORDER BY ' . $criteria->getSort() . ' ' . $criteria->getOrder();
            }
        }
        $result = $this->db->query($sql, (int)$limit, (int)$start);
        if (!$result) {
            //xoops_error($this->db->error());
            return $ret;
        }
        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $post = $this->create(false);
            $post->assignVars($myrow);
            $ret[$myrow['post_id']] = $post;
            unset($post);
        }

        return $ret;
    }

    /**
     * @return bool
     */
    public function synchronization()
    {
        //$this->cleanOrphan();
        return true;
    }

    /**
     * clean orphan items from database
     *
     * @param  string $table_link
     * @param  string $field_link
     * @param  string $field_object
     * @return bool   true on success
     */
    public function cleanOrphan($table_link = '', $field_link = '', $field_object = '') //cleanOrphan()
    {
        $this->deleteAll(new \Criteria('post_time', 0), true, true);
        parent::cleanOrphan($this->db->prefix('newbb_topics'), 'topic_id');
        parent::cleanOrphan($this->db->prefix('newbb_posts_text'), 'post_id');

        $sql = 'DELETE FROM ' . $this->db->prefix('newbb_posts_text') . ' WHERE (post_id NOT IN ( SELECT DISTINCT post_id FROM ' . $this->table . ') )';
        if (!$result = $this->db->queryF($sql)) {
            //xoops_error($this->db->error());
            return false;
        }

        return true;
    }

    /**
     * clean expired objects from database
     *
     * @param  int $expire time limit for expiration
     * @return bool true on success
     */
    public function cleanExpires($expire = 0)
    {
        // irmtfan if 0 no cleanup look include/plugin.php
        if (!func_num_args()) {
            $newbbConfig = newbbLoadConfig();
            $expire      = isset($newbbConfig['pending_expire']) ? (int)$newbbConfig['pending_expire'] : 7;
            $expire      = $expire * 24 * 3600; // days to seconds
        }
        if (empty($expire)) {
            return false;
        }
        $crit_expire = new \CriteriaCompo(new \Criteria('approved', 0, '<='));
        //if (!empty($expire)) {
        $crit_expire->add(new \Criteria('post_time', time() - (int)$expire, '<'));

        //}
        return $this->deleteAll($crit_expire, true/*, true*/);
    }
}

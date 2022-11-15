<?php declare(strict_types=1);

namespace XoopsModules\Newbb;

/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 */

use XoopsModules\Newbb;
use XoopsModules\Xoopspoll;

\defined('NEWBB_FUNCTIONS_INI') || require $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

/**
 * Class Topic
 */
class Topic extends \XoopsObject
{
    public $topic_id;
    public $topic_title;
    public $topic_poster;
    public $topic_time;
    public $topic_views;
    public $topic_replies;
    public $topic_last_post_id;
    public $forum_id;
    public $topic_status;
    public $type_id;
    public $topic_sticky;
    public $topic_digest;
    public $digest_time;
    public $approved;
    public $poster_name;
    public $rating;
    public $votes;
    public $topic_haspoll;
    public $poll_id;
    public $topic_tags;

    public function __construct()
    {
        parent::__construct();
        $this->initVar('topic_id', \XOBJ_DTYPE_INT);
        $this->initVar('topic_title', \XOBJ_DTYPE_TXTBOX);
        $this->initVar('topic_poster', \XOBJ_DTYPE_INT);
        $this->initVar('topic_time', \XOBJ_DTYPE_INT);
        $this->initVar('topic_views', \XOBJ_DTYPE_INT);
        $this->initVar('topic_replies', \XOBJ_DTYPE_INT);
        $this->initVar('topic_last_post_id', \XOBJ_DTYPE_INT);
        $this->initVar('forum_id', \XOBJ_DTYPE_INT);
        $this->initVar('topic_status', \XOBJ_DTYPE_INT);
        $this->initVar('type_id', \XOBJ_DTYPE_INT);
        $this->initVar('topic_sticky', \XOBJ_DTYPE_INT);
        $this->initVar('topic_digest', \XOBJ_DTYPE_INT);
        $this->initVar('digest_time', \XOBJ_DTYPE_INT);
        $this->initVar('approved', \XOBJ_DTYPE_INT);
        $this->initVar('poster_name', \XOBJ_DTYPE_TXTBOX);
        $this->initVar('rating', \XOBJ_DTYPE_OTHER);
        $this->initVar('votes', \XOBJ_DTYPE_INT);
        $this->initVar('topic_haspoll', \XOBJ_DTYPE_INT);
        $this->initVar('poll_id', \XOBJ_DTYPE_INT);
        $this->initVar('topic_tags', \XOBJ_DTYPE_SOURCE);
    }

    // irmtfan add LAST_INSERT_ID to enhance the mysql performances
    public function incrementCounter(): void
    {
        $sql = 'UPDATE ' . $GLOBALS['xoopsDB']->prefix('newbb_topics') . ' SET topic_views = LAST_INSERT_ID(topic_views + 1) WHERE topic_id =' . $this->getVar('topic_id');
        $GLOBALS['xoopsDB']->queryF($sql);
    }

    /**
     * Create full title of the topic
     *
     * the title is composed of [type_name] if type_id is greater than 0 plus topic_title
     */
    public function getFullTitle()
    {
        $topic_title = $this->getVar('topic_title');
        if (!$this->getVar('type_id')) {
            return $topic_title;
        }
        $typeHandler = Helper::getInstance()->getHandler('Type');
        if (!$typeObject = $typeHandler->get($this->getVar('type_id'))) {
            return $topic_title;
        }

        require_once \dirname(__DIR__) . '/include/functions.topic.php';

        return \getTopicTitle($topic_title, $typeObject->getVar('type_name'), $typeObject->getVar('type_color'));
    }

    // START irmtfan loadOldPoll function

    /**
     * Load functions needed for old xoopspoll (older than version 1.4 by zyspec) and umfrage modules
     *
     * @param string $pollModule dirname of the poll module
     * @return string|false = the name of the old poll class eg: "XoopsPoll" | "Umfrage"
     */
    public function loadOldPoll($pollModule = null)
    {
        static $classPoll = false;
        if ($classPoll && null === $pollModule) {
            return $classPoll;
        }
        $newbbConfig = \newbbLoadConfig();
        if (null !== $pollModule) {
            $newbbConfig['poll_module'] = $pollModule;
        }
        //        $relPath = $GLOBALS['xoops']->path('modules/' . $newbbConfig['poll_module'] . '/class/' . $newbbConfig['poll_module']);
        //        require_once $relPath . '.php';
        //        require_once $relPath . 'option.php';
        //        require_once $relPath . 'log.php';
        //        require_once $relPath . 'renderer.php';
        $classes = \get_declared_classes();
        foreach (\array_reverse($classes) as $class) {
            if (mb_strtolower($class) == $newbbConfig['poll_module']) {
                $classPoll = $class;

                return $classPoll;
            }
        }

        return false;
    }

    // END irmtfan loadOldPoll function
    // START irmtfan add deletePoll function

    /**
     * delete a poll in database
     *
     * @param int $poll_id
     * @return bool
     */
    public function deletePoll($poll_id)
    {
        if (empty($poll_id)) {
            return false;
        }
        /** @var \XoopsModuleHandler $moduleHandler */
        $moduleHandler     = \xoops_getHandler('module');
        $newbbConfig       = \newbbLoadConfig();
        $pollModuleHandler = $moduleHandler->getByDirname($newbbConfig['poll_module']);
        if (!\is_object($pollModuleHandler) || !$pollModuleHandler->getVar('isactive')) {
            return false;
        }
        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            /** @var \XoopsModules\Xoopspoll\PollHandler $pollHandler */
            $pollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
            if (false !== $pollHandler->deleteAll(new \Criteria('poll_id', $poll_id, '='))) {
                /** @var \XoopsModules\XoopsPoll\OptionHandler $optionHandler */
                $optionHandler = \XoopsModules\Xoopspoll\Helper::getInstance()->getHandler('Option');
                $optionHandler->deleteAll(new \Criteria('poll_id', $poll_id, '='));
                /** @var \XoopsModules\XoopsPoll\LogHandler $logHandler */
                $logHandler = \XoopsModules\Xoopspoll\Helper::getInstance()->getHandler('Log');
                $logHandler->deleteAll(new \Criteria('poll_id', $poll_id, '='));
                \xoops_comment_delete($GLOBALS['xoopsModule']->getVar('mid'), $poll_id);
            }
            // old Xoopspoll or Umfrage or any clone from them
        } else {
            $classPoll = $this->loadOldPoll();
            /** @var \XoopsPoll $poll */
            $poll = new $classPoll($poll_id);
            if (false !== $poll->delete()) {
                $classOption = $classPoll . 'Option';
                $classOption::deleteByPollId($poll->getVar('poll_id'));
                $classLog = $classPoll . 'Log';
                $classLog::deleteByPollId($poll->getVar('poll_id'));
                \xoops_comment_delete($GLOBALS['xoopsModule']->getVar('mid'), $poll->getVar('poll_id'));
            }
        } // end poll_module new or old

        return true;
    }

    // END irmtfan add deletePoll function

    // START irmtfan add getPoll function

    /**
     * get a poll object from a poll module.
     * note: can be used to find if a poll exist in a module
     * @param int    $poll_id
     * @param string $pollModule dirname of the poll module
     * @return bool|\XoopsObject poll
     */
    public function getPoll($poll_id, $pollModule = null)
    {
        if (empty($poll_id)) {
            return false;
        }
        /** @var \XoopsModuleHandler $moduleHandler */
        $moduleHandler = \xoops_getHandler('module');
        $newbbConfig   = \newbbLoadConfig();
        if (null !== $pollModule) {
            $newbbConfig['poll_module'] = $pollModule;
        }

        $pollModuleHandler = $moduleHandler->getByDirname($newbbConfig['poll_module']);
        if (!\is_object($pollModuleHandler) || !$pollModuleHandler->getVar('isactive')) {
            return false;
        }
        // new xoopspoll module
        if ($pollModuleHandler->getVar('version') >= 140) {
            $pollHandler = Xoopspoll\Helper::getInstance()->getHandler('Poll');
            $pollObject  = $pollHandler->get($poll_id);
            // old xoopspoll or umfrage or any clone from them
        } else {
            $classPoll  = $this->loadOldPoll($newbbConfig['poll_module']);
            $pollObject = new $classPoll($poll_id);
        } // end poll_module new or old

        return $pollObject;
    }
    // END irmtfan add getPoll function
}

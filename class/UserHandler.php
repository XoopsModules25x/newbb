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

\defined('NEWBB_FUNCTIONS_INI') || require $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

/**
 * Class UserHandler
 */
class UserHandler
{
    /** @var array */
    public $users = [];
    /** @var bool */
    private $enableGroup;
    /** @var bool */
    private $enableOnline;
    /** @var array */
    private $userlist = [];
    public $online;

    /**
     * @param bool $enableGroup
     * @param bool $enableOnline
     */
    public function __construct($enableGroup = true, $enableOnline = true)
    {
        $this->enableGroup  = $enableGroup;
        $this->enableOnline = $enableOnline;
    }

    public function loadUserInfo(): void
    {
        $helper = Helper::getInstance();
        $helper->loadLanguage('user');
        //        @require_once $GLOBALS['xoops']->path('modules/' . $GLOBALS['xoopsModule']->getVar('dirname', 'n') . '/language/' . $GLOBALS['xoopsConfig']['language'] . '/user.php');
        if (\class_exists('UserLanguage')) {
            $handler = new UserLanguage();
        } else {
            $handler = new User();
        }
        foreach (\array_keys($this->users) as $uid) {
            $this->userlist[$uid] = $handler->getInfo($this->users[$uid]);
        }
    }

    public function loadUserOnline(): void
    {
        if (empty($this->users) || !$this->enableOnline) {
            return;
        }
        require_once \dirname(__DIR__) . '/include/functions.render.php';
        $image_online  = \newbbDisplayImage('online', \_MD_NEWBB_ONLINE);
        $image_offline = \newbbDisplayImage('offline', \_MD_NEWBB_OFFLINE);

        /** @var Newbb\OnlineHandler $onlineHandler */
        $onlineHandler = Helper::getInstance()->getHandler('Online');
        $onlines       = $onlineHandler->checkStatus(\array_keys($this->users));

        foreach (\array_keys($this->users) as $uid) {
            $this->userlist[$uid]['status'] = empty($onlines[$uid]) ? $image_offline : $image_online;
        }
    }

    // START irmtfan remove function - no deprecated is needed because just use in this file
    //    function loadUserGroups()
    //    {
    //        return true;
    //    }
    // END irmtfan remove function - no deprecated is needed because just use in this file

    public function loadUserDigest(): void
    {
        if (empty($this->users)) {
            return;
        }

        $sql    = 'SELECT user_digests, uid FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_user_stats') . ' WHERE uid IN( ' . \implode(', ', \array_keys($this->users)) . ')';
        $result = $GLOBALS['xoopsDB']->query($sql);
        if ($GLOBALS['xoopsDB']->isResultSet($result)) {
            while (false !== ($myrow = $GLOBALS['xoopsDB']->fetchArray($result))) {
                $this->userlist[$myrow['uid']]['digests'] = (int)$myrow['user_digests'];
            }
        }
    }

    // START irmtfan remove function
    //    function loadUserRank()
    //    {
    //          return true;
    //    }
    // END irmtfan remove function

    /**
     * @return array
     */
    public function getUsers()
    {
        $this->loadUserInfo();
        $this->loadUserOnline();
        // irmtfan removed $this->loadUserGroups();
        // irmtfan removed $this->loadUserRank();
        $this->loadUserDigest();

        return $this->userlist;
    }
}

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

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

defined('NEWBB_FUNCTIONS_INI') || include $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

/**
 * @param $RPG
 * @param $RPGDIFF
 * @return array|number
 */
function newbb_calculateLevel($RPG, $RPGDIFF)
{

    //$RPG = $user->getVar('posts');
    //$RPGDIFF = $user->getVar('user_regdate');

    $today = time();
    $diff  = $today - $RPGDIFF;
    $exp   = round($diff / 86400, 0);
    if ($exp <= 0) {
        $exp = 1;
    }
    $ppd       = round($RPG / $exp, 0);
    $level     = pow(log10($RPG), 3);
    $ep        = floor(100 * ($level - floor($level)));
    $showlevel = floor($level + 1);
    $hpmulti   = round($ppd / 6, 1);
    if ($hpmulti > 1.5) {
        $hpmulti = 1.5;
    }
    if ($hpmulti < 1) {
        $hpmulti = 1;
    }
    $maxhp = $level * 25 * $hpmulti;
    $hp    = $ppd / 5;
    if ($hp >= 1) {
        $hp = $maxhp;
    } else {
        $hp = floor($hp * $maxhp);
    }
    $hp    = floor($hp);
    $maxhp = floor($maxhp);
    $zhp   = $maxhp;
    if ($maxhp <= 0) {
        $zhp = 1;
    }
    $hpf   = floor(100 * ($hp / $zhp)) - 1;
    $maxmp = ($exp * $level) / 5;
    $mp    = $RPG / 3;
    if ($mp >= $maxmp) {
        $mp = $maxmp;
    }
    $maxmp = floor($maxmp);
    $mp    = floor($mp);
    $zmp   = $maxmp;
    if ($maxmp <= 0) {
        $zmp = 1;
    }
    $mpf = floor(100 * ($mp / $zmp)) - 1;
    if ($hpf >= 98) {
        $hpf -= 2;
    }
    if ($ep >= 98) {
        $ep -= 2;
    }
    if ($mpf >= 98) {
        $mpf -= 2;
    }

    $level              = array();
    $level['level']     = $showlevel;
    $level['exp']       = $ep;
    $level['exp_width'] = $ep . '%';
    $level['hp']        = $hp;
    $level['hp_max']    = $maxhp;
    $level['hp_width']  = $hpf . '%';
    $level['mp']        = $mp;
    $level['mp_max']    = $maxmp;
    $level['mp_width']  = $mpf . '%';

    return $level;
}

/**
 * Class NewbbUser
 */
class NewbbUser
{
    public $user;

    public function User()
    {
    }

    /**
     * @return array
     */
    public function getUserbar()
    {
        global $isadmin;

        $userbar = array();
        if (empty($GLOBALS['xoopsModuleConfig']['userbar_enabled'])) {
            return $userbar;
        }

        $user               = $this->user;
        $userbar['profile'] = array(
            'link' => XOOPS_URL . '/userinfo.php?uid=' . $user->getVar('uid'),
            'name' => _PROFILE
        );

        if (is_object($GLOBALS['xoopsUser'])) {
            $userbar['pm'] = array(
                'link' => "javascript:void openWithSelfMain('" . XOOPS_URL . '/pmlite.php?send2=1&amp;to_userid=' . $user->getVar('uid') . "', 'pmlite', 450, 380);",
                'name' => _MD_PM
            );
        }
        if ($user->getVar('user_viewemail') || $isadmin) {
            $userbar['email'] = array(
                'link' => "javascript:void window.open('mailto:" . $user->getVar('email') . "', 'new');",
                'name' => _MD_EMAIL
            );
        }
        if ($url = $user->getVar('url')) {
            $userbar['url'] = array('link' => "javascript:void window.open('" . $url . "', 'new');", 'name' => _MD_WWW);
        }
        if ($icq = $user->getVar('user_icq')) {
            $userbar['icq'] = array(
                'link' => "javascript:void window.open('http://wwp.icq.com/scripts/search.dll?to=" . $icq . "', 'new');",
                'name' => _MD_ICQ
            );
        }
        if ($aim = $user->getVar('user_aim')) {
            $userbar['aim'] = array(
                'link' => "javascript:void window.open('aim:goim?screenname=" . $aim . '&amp;message=Hi+' . $aim . '+Are+you+there?' . "', 'new');",
                'name' => _MD_AIM
            );
        }
        if ($yim = $user->getVar('user_yim')) {
            $userbar['yim'] = array(
                'link' => "javascript:void window.open('http://edit.yahoo.com/config/send_webmesg?.target=" . $yim . '&.src=pg' . "', 'new');",
                'name' => _MD_YIM
            );
        }
        if ($msn = $user->getVar('user_msnm')) {
            $userbar['msnm'] = array(
                'link' => "javascript:void window.open('http://members.msn.com?mem=" . $msn . "', 'new');",
                'name' => _MD_MSNM
            );
        }

        return $userbar;
    }

    /**
     * @return string
     */
    public function getLevel()
    {
        global $forumUrl;

        $level = newbb_calculateLevel($this->user->getVar('posts'), $this->user->getVar('user_regdate'));
        $info  = '';
        if ($GLOBALS['xoopsModuleConfig']['user_level'] == 2) {
            static $rpg_images;
            if (!isset($rpg_images)) {
                $iconHandler = newbbGetIconHandler();
                $rpg_path    = $iconHandler->getPath('rpg');
                foreach (array('img_left', 'img_backing', 'img_right', 'blue', 'green', 'orange') as $img) {
                    // irmtfan fix: double "/" removed
                    $rpg_images[$img] = XOOPS_URL . $rpg_path . '/' . $img . '.gif';
                }
            }
            // irmtfan hardcore removed align="left"
            $table = "<table class='userlevel'><tr><td class='end'><img src='"
                     . $rpg_images['img_left']
                     . "' alt='' /></td><td class='center' background='"
                     . $rpg_images['img_backing']
                     . "'><img src='%s' width='%d' alt='' class='icon_left' /></td><td><img src='"
                     . $rpg_images['img_right']
                     . "' alt='' /></td></tr></table>";

            $info = _MD_LEVEL . ' ' . $level['level'] . '<br>' . _MD_HP . ' ' . $level['hp'] . ' / ' . $level['hp_max'] . '<br>' . sprintf($table, $rpg_images['orange'], $level['hp_width']);
            $info .= _MD_MP . ' ' . $level['mp'] . ' / ' . $level['mp_max'] . '<br>' . sprintf($table, $rpg_images['green'], $level['mp_width']);
            $info .= _MD_EXP . ' ' . $level['exp'] . '<br>' . sprintf($table, $rpg_images['blue'], $level['exp_width']);
        } else {
            $info = _MD_LEVEL . ' ' . $level['level'] . '; ' . _MD_EXP . ' ' . $level['exp'] . '<br>';
            $info .= _MD_HP . ' ' . $level['hp'] . ' / ' . $level['hp_max'] . '<br>';
            $info .= _MD_MP . ' ' . $level['mp'] . ' / ' . $level['mp_max'];
        }

        return $info;
    }

    /**
     * @param $user
     * @return mixed
     */
    public function getInfo(&$user)
    {
        global $myts;
        static $name_anonymous;

        if (!is_object($user) || !$user->isActive()) {
            if (!isset($name_anonymous)) {
                $name_anonymous = $myts->htmlSpecialChars($GLOBALS['xoopsConfig']['anonymous']);
            }

            return array('name' => $name_anonymous, 'link' => $name_anonymous);
        }

        $this->user = $user;

        $userinfo['uid'] = $user->getVar('uid');

        $name             = empty($GLOBALS['xoopsModuleConfig']['show_realname']) ? $user->getVar('uname') : $user->getVar('name');
        $userinfo['name'] = $name ?: $user->getVar('uname');

        $userinfo['link'] = '<a href=\'' . XOOPS_URL . '/userinfo.php?uid=' . $user->getVar('uid') . '\'>' . $userinfo['name'] . '</a>';

        $userinfo['avatar'] = $user->getVar('user_avatar');
        // START hacked by irmtfan - easier rank getting - consistency with previous version back rank.title and rank.image
        $userrank         = $user->rank();
        $userinfo['rank'] = array();
        if (isset($userrank['image']) && $userrank['image']) {
            $userinfo['rank']['image'] = $userrank['image'];
            $userinfo['rank']['title'] = $userrank['title'];
        }
        // END hacked by irmtfan - easier rank getting  - a little correctness dot removed
        // START hacked by irmtfan - easier groups getting - can we use $_SESSION['xoopsUserGroups']???
        //checks for user's groups
        $userinfo['groups'] = array();
        $memberHandler      = xoops_getHandler('member');
        $usergroups         = $memberHandler->getGroupsByUser($userinfo['uid'], true);
        foreach ($usergroups as $group) {
            $userinfo['groups'][] = $group->getVar('name');
        }
        // END hacked by irmtfan - easier groups getting - can we use $_SESSION['xoopsUserGroups']???
        $userinfo['from'] = $user->getVar('user_from');

        mod_loadFunctions('time', 'newbb');
        $userinfo['regdate']    = newbb_formatTimestamp($user->getVar('user_regdate'), 'reg');
        $userinfo['last_login'] = newbb_formatTimestamp($user->getVar('last_login')); // irmtfan add last_login

        $userinfo['posts'] = $user->getVar('posts');

        if (!empty($GLOBALS['xoopsModuleConfig']['user_level'])) {
            $userinfo['level'] = $this->getLevel();
        }

        if (!empty($GLOBALS['xoopsModuleConfig']['userbar_enabled'])) {
            $userinfo['userbar'] = $this->getUserbar();
        }

        $userinfo['signature'] = $user->getVar('user_sig');

        return $userinfo;
    }
}

/**
 * Class NewbbUserHandler
 */
class NewbbUserHandler
{
    public $enableGroup;
    public $enableOnline;
    public $userlist = array();
    public $users    = array();

    //var $online = array();

    /**
     * @param bool $enableGroup
     * @param bool $enableOnline
     */
    public function __construct($enableGroup = true, $enableOnline = true)
    {
        $this->enableGroup  = $enableGroup;
        $this->enableOnline = $enableOnline;
    }

    public function loadUserInfo()
    {
        @include_once $GLOBALS['xoops']->path('modules/' . $GLOBALS['xoopsModule']->getVar('dirname', 'n') . '/language/' . $GLOBALS['xoopsConfig']['language'] . '/user.php');
        if (class_exists('User_language')) {
            $handler = new User_language();
        } else {
            $handler = new NewbbUser();
        }
        foreach (array_keys($this->users) as $uid) {
            $this->userlist[$uid] = $handler->getInfo($this->users[$uid]);
        }
    }

    public function loadUserOnline()
    {
        if (empty($this->users) || !$this->enableOnline) {
            return;
        }
        mod_loadFunctions('render', 'newbb');
        $image_online  = newbbDisplayImage('online', _MD_ONLINE);
        $image_offline = newbbDisplayImage('offline', _MD_OFFLINE);

        $onlineHandler = xoops_getModuleHandler('online', 'newbb');
        $onlines       = $onlineHandler->checkStatus(array_keys($this->users));

        foreach (array_keys($this->users) as $uid) {
            $this->userlist[$uid]['status'] = empty($onlines[$uid]) ? $image_offline : $image_online;
        }
    }
    // START irmtfan remove function - no deprecated is needed because just use in this file
    //    function loadUserGroups()
    //    {
    //        return true;
    //    }
    // END irmtfan remove function - no deprecated is needed because just use in this file

    public function loadUserDigest()
    {
        if (empty($this->users)) {
            return;
        }

        $sql    = 'SELECT user_digests, uid FROM ' . $GLOBALS['xoopsDB']->prefix('bb_user_stats') . ' WHERE uid IN( ' . implode(', ', array_keys($this->users)) . ')';
        $result = $GLOBALS['xoopsDB']->query($sql);
        while ($myrow = $GLOBALS['xoopsDB']->fetchArray($result)) {
            $this->userlist[$myrow['uid']]['digests'] = (int)$myrow['user_digests'];
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

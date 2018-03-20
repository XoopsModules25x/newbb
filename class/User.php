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

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

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

    $level              = [];
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
 * Class User
 */
class User
{
    public $user;

    public function __construct()
    {
    }

    /**
     * @return array
     */
    public function getUserbar()
    {
        global $isAdmin;

        $userbar = [];
        if (empty($GLOBALS['xoopsModuleConfig']['userbar_enabled'])) {
            return $userbar;
        }

        $user               = $this->user;
        $userbar['profile'] = [
            'link' => XOOPS_URL . '/userinfo.php?uid=' . $user->getVar('uid'),
            'name' => _PROFILE
        ];

        if (is_object($GLOBALS['xoopsUser'])) {
            $userbar['pm'] = [
                'link' => "javascript:void openWithSelfMain('" . XOOPS_URL . '/pmlite.php?send2=1&amp;to_userid=' . $user->getVar('uid') . "', 'pmlite', 450, 380);",
                'name' => _MD_NEWBB_PM
            ];
        }
        if ($user->getVar('user_viewemail') || $isAdmin) {
            $userbar['email'] = [
                'link' => "javascript:void window.open('mailto:" . $user->getVar('email') . "', 'new');",
                'name' => _MD_NEWBB_EMAIL
            ];
        }
        if ($url = $user->getVar('url')) {
            $userbar['url'] = [
                'link' => "javascript:void window.open('" . $url . "', 'new');",
                'name' => _MD_NEWBB_WWW
            ];
        }
        if ($icq = $user->getVar('user_icq')) {
            $userbar['icq'] = [
                'link' => "javascript:void window.open('http://wwp.icq.com/scripts/search.dll?to=" . $icq . "', 'new');",
                'name' => _MD_NEWBB_ICQ
            ];
        }
        if ($aim = $user->getVar('user_aim')) {
            $userbar['aim'] = [
                'link' => "javascript:void window.open('aim:goim?screenname=" . $aim . '&amp;message=Hi+' . $aim . '+Are+you+there?' . "', 'new');",
                'name' => _MD_NEWBB_AIM
            ];
        }
        if ($yim = $user->getVar('user_yim')) {
            $userbar['yim'] = [
                'link' => "javascript:void window.open('http://edit.yahoo.com/config/send_webmesg?.target=" . $yim . '&.src=pg' . "', 'new');",
                'name' => _MD_NEWBB_YIM
            ];
        }
        if ($msn = $user->getVar('user_msnm')) {
            $userbar['msnm'] = [
                'link' => "javascript:void window.open('http://members.msn.com?mem=" . $msn . "', 'new');",
                'name' => _MD_NEWBB_MSNM
            ];
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
        if (2 == $GLOBALS['xoopsModuleConfig']['user_level']) {
            static $rpg_images;
            if (!isset($rpg_images)) {
                $iconHandler = newbbGetIconHandler();
                $rpg_path    = $iconHandler->getPath('rpg');
                foreach (['img_left', 'img_backing', 'img_right', 'blue', 'green', 'orange'] as $img) {
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

            $info = _MD_NEWBB_LEVEL . ' ' . $level['level'] . '<br><span title="' . _MD_NEWBB_HP_DESC . '">' . _MD_NEWBB_HP . ' ' . $level['hp'] . ' / ' . $level['hp_max'] . '</span><br>' . sprintf($table, $rpg_images['orange'], $level['hp_width']);
            $info .= '<span title="' . _MD_NEWBB_MP_DESC . '">' . _MD_NEWBB_MP . ' ' . $level['mp'] . ' / ' . $level['mp_max'] . '</span><br>' . sprintf($table, $rpg_images['green'], $level['mp_width']);
            $info .= '<span title="' . _MD_NEWBB_EXP_DESC . '">' . _MD_NEWBB_EXP . ' ' . $level['exp'] . '</span><br>' . sprintf($table, $rpg_images['blue'], $level['exp_width']);
        } else {
            $info = _MD_NEWBB_LEVEL . ' ' . $level['level'] . '; <span title="' . _MD_NEWBB_EXP_DESC . '">' . _MD_NEWBB_EXP . ' ' . $level['exp'] . '</span><br>';
            $info .= '<span title="' . _MD_NEWBB_HP_DESC . '">' . _MD_NEWBB_HP . ' ' . $level['hp'] . ' / ' . $level['hp_max'] . '</span><br>';
            $info .= '<span title="' . _MD_NEWBB_MP_DESC . '">' . _MD_NEWBB_MP . ' ' . $level['mp'] . ' / ' . $level['mp_max'] . '</span>';
        }

        return $info;
    }

    /**
     * @param \XoopsUser $user
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

            return ['name' => $name_anonymous, 'link' => $name_anonymous];
        }

        $this->user = $user;

        $userinfo['uid'] = $user->getVar('uid');

        $name             = empty($GLOBALS['xoopsModuleConfig']['show_realname']) ? $user->getVar('uname') : $user->getVar('name');
        $userinfo['name'] = $name ?: $user->getVar('uname');

        $userinfo['link'] = '<a href=\'' . XOOPS_URL . '/userinfo.php?uid=' . $user->getVar('uid') . '\'>' . $userinfo['name'] . '</a>';

        $userinfo['avatar'] = $user->getVar('user_avatar');
        // START hacked by irmtfan - easier rank getting - consistency with previous version back rank.title and rank.image
        $userrank         = $user->rank();
        $userinfo['rank'] = [];
        if (isset($userrank['image']) && $userrank['image']) {
            $userinfo['rank']['image'] = $userrank['image'];
            $userinfo['rank']['title'] = $userrank['title'];
        }
        // END hacked by irmtfan - easier rank getting  - a little correctness dot removed
        // START hacked by irmtfan - easier groups getting - can we use $_SESSION['xoopsUserGroups']???
        //checks for user's groups
        $userinfo['groups'] = [];
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = xoops_getHandler('member');
        $usergroups    = $memberHandler->getGroupsByUser($userinfo['uid'], true);
        foreach ($usergroups as $group) {
            $userinfo['groups'][] = $group->getVar('name');
        }
        // END hacked by irmtfan - easier groups getting - can we use $_SESSION['xoopsUserGroups']???
        $userinfo['from'] = $user->getVar('user_from');

        require_once __DIR__ . '/../include/functions.time.php';
        $userinfo['regdate']    = newbbFormatTimestamp($user->getVar('user_regdate'), 'reg');
        $userinfo['last_login'] = newbbFormatTimestamp($user->getVar('last_login')); // irmtfan add last_login

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

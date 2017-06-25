<?php
/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (http://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */

use Xmf\Request;

include_once __DIR__ . '/header.php';

global $xoTheme, $xoopsTpl;
$GLOBALS['xoopsOption']['template_main'] = 'newbb_moderate.tpl';
include $GLOBALS['xoops']->path('header.php');

$forum_userid = Request::getInt('uid', 0);
$forum_id     = Request::getInt('forum', 0);
$isadmin      = newbb_isAdmin($forum_id);
if (!$isadmin) {
    redirect_header(XOOPS_URL . '/index.php', 2, _MD_NEWBB_NORIGHTTOACCESS);
}
$is_administrator = $GLOBALS['xoopsUserIsAdmin'];
/** @var \NewbbModerateHandler $moderateHandler */
$moderateHandler = xoops_getModuleHandler('moderate', 'newbb');

if (Request::hasVar('submit', 'POST') && Request::getInt('expire', 0, 'POST')) {
    $ipWithMask = '';
    if (0 == $forum_userid) {
        $ipWithMask = Request::getString('ip', null, 'POST');
        $mask       = '';
        $ipParts    = explode('/', $ipWithMask);
        $ip         = new \Xmf\IPAddress($ipParts[0]);
        if (false === $ip->asReadable()) {
            $ipWithMask = '';
        } else {
            $ipWithMask = $ip->asReadable();
            $mask       = empty($ipParts[1]) ? 0 : (int)$ipParts[1];
            $mask       = ($mask > ((4 === $ip->ipVersion()) ? 32 : 128) || $mask < 8) ? '' : $mask;
            $ipWithMask .= empty($mask) ? '' : '/' . $mask;
        }
    }

    $mod_end  = time() + Request::getInt('expire', 0, 'POST') * 3600 * 24;
    $mod_desc = Request::getString('desc', '', 'POST');

    $moderate_obj = $moderateHandler->create();
    $moderate_obj->setVar('uid', $forum_userid);
    $moderate_obj->setVar('ip', $ipWithMask);
    $moderate_obj->setVar('forum_id', $forum_id);
    $moderate_obj->setVar('mod_start', time());
    $moderate_obj->setVar('mod_end', $mod_end);
    $moderate_obj->setVar('mod_desc', $mod_desc);
    $res = $moderateHandler->insert($moderate_obj);

    redirect_header("moderate.php?forum={$forum_id}", 2, _MD_NEWBB_DBUPDATED);
} elseif (Request::hasVar('del')) {
    $moderate_obj = $moderateHandler->get(Request::getInt('del', 0, 'GET'));
    if ($is_administrator || $moderate_obj->getVar('forum_id') == $forum_id) {
        $moderateHandler->delete($moderate_obj, true);
        redirect_header("moderate.php?forum={$forum_id}", 2, _MD_NEWBB_DBUPDATED);
    }
}

$start    = Request::getInt('start', 0, 'GET');
$sortname = Request::getString('sort', '', 'GET');

switch ($sortname) {
    case 'uid':
        $sort  = 'uid ASC, ip';
        $order = 'ASC';
        break;
    case 'start':
        $sort  = 'mod_start';
        $order = 'ASC';
        break;
    case 'expire':
        $sort  = 'mod_end';
        $order = 'DESC';
        break;
    default:
        $sort  = 'forum_id ASC, uid ASC, ip';
        $order = 'ASC';
        break;
}
// show all bans for module admin - for moderator just show its forum_id bans
$criteria = new CriteriaCompo();
if (!$is_administrator) {
    $criteria->add(new Criteria('forum_id', $forum_id, '='));
}
$criteria->setLimit($GLOBALS['xoopsModuleConfig']['topics_per_page']);
$criteria->setStart($start);
$criteria->setSort($sort);
$criteria->setOrder($order);
$moderate_objs  = $moderateHandler->getObjects($criteria);
$moderate_count = $moderateHandler->getCount($criteria);

$url = 'moderate.php';
if ($forum_id) {
    $url .= '?forum=' . $forum_id;
}
$xoopsTpl->assign('moderate_url', $url);

if (!empty($moderate_count)) {
    $_users = [];
    foreach (array_keys($moderate_objs) as $id) {
        $_users[$moderate_objs[$id]->getVar('uid')] = 1;
    }
    $users =& newbb_getUnameFromIds(array_keys($_users), $GLOBALS['xoopsModuleConfig']['show_realname'], true);

    $columnHeaders ['uid']    = [
        'url'    => 'moderate.php?forum=' . $forum_id . '&amp;start=' . $start . '&amp;sort=uid',
        'header' => _MD_NEWBB_SUSPEND_UID,
        'title'  => _MD_NEWBB_SUSPEND_UID,
    ];
    $columnHeaders ['start']  = [
        'url'    => 'moderate.php?forum=' . $forum_id . '&amp;start=' . $start . '&amp;sort=start',
        'header' => _MD_NEWBB_SUSPEND_START,
        'title'  => _MD_NEWBB_SUSPEND_START,
    ];
    $columnHeaders['expire']  = [
        'url'    => 'moderate.php?forum=' . $forum_id . '&amp;start=' . $start . '&amp;sort=expire',
        'header' => _MD_NEWBB_SUSPEND_EXPIRE,
        'title'  => _MD_NEWBB_SUSPEND_EXPIRE,
    ];
    $columnHeaders['forum']   = [
        'url'    => 'moderate.php?forum=' . $forum_id . '&amp;start=' . $start . '&amp;sort=forum',
        'header' => _MD_NEWBB_SUSPEND_SCOPE,
        'title'  => _MD_NEWBB_SUSPEND_SCOPE,
    ];
    $columnHeaders['desc']    = [
        'url'    => false,
        'header' => _MD_NEWBB_SUSPEND_DESC,
        'title'  => _MD_NEWBB_SUSPEND_DESC,
    ];
    $columnHeaders['options'] = [
        'url'    => false,
        'header' => _DELETE,
        'title'  => _DELETE,
    ];
    $xoopsTpl->assign('columnHeaders', $columnHeaders);

    /** @var \NewbbForumHandler $forumHandler */
    $forumHandler = xoops_getModuleHandler('forum', 'newbb');
    $forum_list   = $forumHandler->getAll(null, ['forum_name'], false);

    $columnRows = [];
    foreach (array_keys($moderate_objs) as $id) {
        // for anon, show ip instead
        $row['uid']     = ($moderate_objs[$id]->getVar('uid') ? (isset($users[$moderate_objs[$id]->getVar('uid')]) ? $users[$moderate_objs[$id]->getVar('uid')] : $moderate_objs[$id]->getVar('uid')) : $moderate_objs[$id]->getVar('ip'));
        $row['start']   = formatTimestamp($moderate_objs[$id]->getVar('mod_start'));
        $row['expire']  = formatTimestamp($moderate_objs[$id]->getVar('mod_end'));
        $row['forum']   = ($moderate_objs[$id]->getVar('forum_id') ? $forum_list[$moderate_objs[$id]->getVar('forum_id')]['forum_name'] : _ALL);
        $row['desc']    = ($moderate_objs[$id]->getVar('mod_desc') ?: _NONE);
        $row['options'] = (($is_administrator
                            || $moderate_objs[$id]->getVar('forum_id') == $forum_id) ? '<a href="moderate.php?forum=' . $forum_id . '&amp;del=' . $moderate_objs[$id]->getVar('mod_id') . '">' . _DELETE . '</a>' : '');
        $columnRows[]   = $row;
    }
    $xoopsTpl->assign('columnRows', $columnRows);

    if ($moderate_count > $GLOBALS['xoopsModuleConfig']['topics_per_page']) {
        include $GLOBALS['xoops']->path('class/pagenav.php');
        $nav = new XoopsPageNav($moderate_count, $GLOBALS['xoopsModuleConfig']['topics_per_page'], $start, 'start', 'forum=' . $forum_id . '&amp;sort=' . $sortname);
        //if (isset($GLOBALS['xoopsModuleConfig']['do_rewrite'])) {
        //    $nav->url = formatURL(Request::getString('SERVER_NAME', '', 'SERVER')) . ' /' . $nav->url;
        //}
        $xoopsTpl->assign('moderate_page_nav', $nav->renderNav());
    }
}

include_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
$forum_form = new XoopsThemeForm(_ADD, 'suspend_form', 'moderate.php', 'post');
$forum_form->addElement(new XoopsFormSelectUser(_MD_NEWBB_SUSPEND_UID, 'uid', true, $forum_userid, 1, false));
$forum_form->addElement(new XoopsFormText(_MD_NEWBB_SUSPEND_IP, 'ip', 50, 50));
$forum_form->addElement(new XoopsFormText(_MD_NEWBB_SUSPEND_DURATION, 'expire', 20, 25, '5'), true);
$forum_form->addElement(new XoopsFormText(_MD_NEWBB_SUSPEND_DESC, 'desc', 50, 255));
include_once __DIR__ . '/include/functions.forum.php';
if (newbb_isAdmin()) {
    $forumSel = '<select name="forum">';// if user doesn't select, default is "0" all forums
    $forumSel .= '<option value="0"';
    if ($forum_id == 0) {
        $forumSel .= ' selected';
    }
    $forumSel                         .= '>' . _ALL . '</option>';
    $forumSel                         .= newbb_forumSelectBox($forum_id, 'access', false); //$accessForums, $permission = "access", $delimitorCategory = true
    $forumSel                         .= '</select>';
    $forumEle                         = new XoopsFormLabel(_MD_NEWBB_SELFORUM, $forumSel);
    $forumEle->customValidationCode[] = 'if (document.suspend.forum.value < 0) {return false;} ';
    $forum_form->addElement($forumEle);
} else {
    $forum_form->addElement(new XoopsFormHidden('forum', $forum_id));
}
$forum_form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
$forum_form->assign($xoopsTpl);

include_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');

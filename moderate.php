<?php
/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */

use Xmf\Request;

require_once __DIR__ . '/header.php';

global $xoTheme, $xoopsTpl;
$GLOBALS['xoopsOption']['template_main'] = 'newbb_moderate.tpl';
include $GLOBALS['xoops']->path('header.php');

$forum_userid = Request::getInt('uid', 0);
$forum_id     = Request::getInt('forum', 0);
$isAdmin      = newbbIsAdmin($forum_id);
if (!$isAdmin) {
    redirect_header(XOOPS_URL . '/index.php', 2, _MD_NEWBB_NORIGHTTOACCESS);
}
$is_administrator = $GLOBALS['xoopsUserIsAdmin'];
///** @var Newbb\ModerateHandler $moderateHandler */
//$moderateHandler = Newbb\Helper::getInstance()->getHandler('Moderate');

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

    $moderateObject = $moderateHandler->create();
    $moderateObject->setVar('uid', $forum_userid);
    $moderateObject->setVar('ip', $ipWithMask);
    $moderateObject->setVar('forum_id', $forum_id);
    $moderateObject->setVar('mod_start', time());
    $moderateObject->setVar('mod_end', $mod_end);
    $moderateObject->setVar('mod_desc', $mod_desc);
    $res = $moderateHandler->insert($moderateObject);

    redirect_header("moderate.php?forum={$forum_id}", 2, _MD_NEWBB_DBUPDATED);
} elseif (Request::hasVar('del')) {
    $moderateObject = $moderateHandler->get(Request::getInt('del', 0, 'GET'));
    if ($is_administrator || $moderateObject->getVar('forum_id') == $forum_id) {
        $moderateHandler->delete($moderateObject, true);
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
$criteria = new \CriteriaCompo();
if (!$is_administrator) {
    $criteria->add(new \Criteria('forum_id', $forum_id, '='));
}
$criteria->setLimit($GLOBALS['xoopsModuleConfig']['topics_per_page']);
$criteria->setStart($start);
$criteria->setSort($sort);
$criteria->setOrder($order);
$moderateObjects = $moderateHandler->getObjects($criteria);
$moderate_count  = $moderateHandler->getCount($criteria);

$url = 'moderate.php';
if ($forum_id) {
    $url .= '?forum=' . $forum_id;
}
$xoopsTpl->assign('moderate_url', $url);

if (!empty($moderate_count)) {
    $_users = [];
    foreach (array_keys($moderateObjects) as $id) {
        $_users[$moderateObjects[$id]->getVar('uid')] = 1;
    }
    $users = newbbGetUnameFromIds(array_keys($_users), $GLOBALS['xoopsModuleConfig']['show_realname'], true);

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

    //    /** @var Newbb\ForumHandler $forumHandler */
    //    $forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
    $forum_list = $forumHandler->getAll(null, ['forum_name'], false);

    $columnRows = [];
    foreach (array_keys($moderateObjects) as $id) {
        // for anon, show ip instead
        $row['uid']     = ($moderateObjects[$id]->getVar('uid') ? (isset($users[$moderateObjects[$id]->getVar('uid')]) ? $users[$moderateObjects[$id]->getVar('uid')] : $moderateObjects[$id]->getVar('uid')) : $moderateObjects[$id]->getVar('ip'));
        $row['start']   = formatTimestamp($moderateObjects[$id]->getVar('mod_start'));
        $row['expire']  = formatTimestamp($moderateObjects[$id]->getVar('mod_end'));
        $row['forum']   = ($moderateObjects[$id]->getVar('forum_id') ? $forum_list[$moderateObjects[$id]->getVar('forum_id')]['forum_name'] : _ALL);
        $row['desc']    = ($moderateObjects[$id]->getVar('mod_desc') ?: _NONE);
        $row['options'] = (($is_administrator
                            || $moderateObjects[$id]->getVar('forum_id') == $forum_id) ? '<a href="moderate.php?forum=' . $forum_id . '&amp;del=' . $moderateObjects[$id]->getVar('mod_id') . '">' . _DELETE . '</a>' : '');
        $columnRows[]   = $row;
    }
    $xoopsTpl->assign('columnRows', $columnRows);

    if ($moderate_count > $GLOBALS['xoopsModuleConfig']['topics_per_page']) {
        include $GLOBALS['xoops']->path('class/pagenav.php');
        $nav = new \XoopsPageNav($moderate_count, $GLOBALS['xoopsModuleConfig']['topics_per_page'], $start, 'start', 'forum=' . $forum_id . '&amp;sort=' . $sortname);
        //if (isset($GLOBALS['xoopsModuleConfig']['do_rewrite'])) {
        //    $nav->url = formatURL(Request::getString('SERVER_NAME', '', 'SERVER')) . ' /' . $nav->url;
        //}
        $xoopsTpl->assign('moderate_page_nav', $nav->renderNav());
    }
}

require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
$forum_form = new \XoopsThemeForm(_ADD, 'suspend_form', 'moderate.php', 'post', true);
$forum_form->addElement(new \XoopsFormSelectUser(_MD_NEWBB_SUSPEND_UID, 'uid', true, $forum_userid, 1, false));
$forum_form->addElement(new \XoopsFormText(_MD_NEWBB_SUSPEND_IP, 'ip', 50, 50));
$forum_form->addElement(new \XoopsFormText(_MD_NEWBB_SUSPEND_DURATION, 'expire', 20, 25, '5'), true);
$forum_form->addElement(new \XoopsFormText(_MD_NEWBB_SUSPEND_DESC, 'desc', 50, 255));
require_once __DIR__ . '/include/functions.forum.php';
if (newbbIsAdmin()) {
    $forumSel = '<select name="forum">';// if user doesn't select, default is "0" all forums
    $forumSel .= '<option value="0"';
    if (0 == $forum_id) {
        $forumSel .= ' selected';
    }
    $forumSel                         .= '>' . _ALL . '</option>';
    $forumSel                         .= newbbForumSelectBox($forum_id, 'access', false); //$accessForums, $permission = "access", $delimitorCategory = true
    $forumSel                         .= '</select>';
    $forumEle                         = new \XoopsFormLabel(_MD_NEWBB_SELFORUM, $forumSel);
    $forumEle->customValidationCode[] = 'if (document.suspend.forum.value < 0) {return false;} ';
    $forum_form->addElement($forumEle);
} else {
    $forum_form->addElement(new \XoopsFormHidden('forum', $forum_id));
}
$forum_form->addElement(new \XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
$forum_form->assign($xoopsTpl);

require_once __DIR__ . '/footer.php';
include $GLOBALS['xoops']->path('footer.php');

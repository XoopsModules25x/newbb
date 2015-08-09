<?php
/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright    XOOPS Project (http://xoops.org)
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author        Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since        4.00
 * @version        $Id $
 * @package        module::newbb
 */

include_once __DIR__ . '/header.php';

$topic_id     = XoopsRequest::getInt('topic_id', XoopsRequest::getInt('topic_id', 0, 'POST'), 'GET');
$forum_userid = XoopsRequest::getInt('fuid', 0, 'GET');

$isadmin = newbb_isAdmin($forum_id);
if (!$isadmin) {
    redirect_header(XOOPS_URL . '/index.php', 2, _MD_NORIGHTTOACCESS);
}
$is_administrator = $GLOBALS['xoopsUserIsAdmin'];
$moderateHandler = &xoops_getmodulehandler('moderate', 'newbb');

if (XoopsRequest::getString('submit', '', 'POST') && XoopsRequest::getInt('expire', 0, 'POST')) {
    if (XoopsRequest::getString('ip', '', 'POST') && !preg_match("/^\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3}$/", XoopsRequest::getString('ip', '', 'POST'))) {
        $_POST["ip"] = "";
    }
    if (
        (XoopsRequest::getInt('uid', 0, 'POST') && $moderateHandler->getLatest(XoopsRequest::getInt('uid', 0, 'POST')) > (time() + XoopsRequest::getInt('expire', 0, 'POST') * 3600 * 24))
        ||
        (XoopsRequest::getString('ip', '', 'POST') && $moderateHandler->getLatest(XoopsRequest::getString('ip', '', 'POST'), false) > (time() + XoopsRequest::getInt('expire', 0, 'POST') * 3600 * 24))
        ||
        (!XoopsRequest::getInt('uid', 0, 'POST') && !XoopsRequest::getString('ip', '', 'POST'))
    ) {
    } else {
        $moderate_obj = $moderateHandler->create();
        $moderate_obj->setVar('uid', XoopsRequest::getInt('uid', 0, 'POST'));
        $moderate_obj->setVar('ip', XoopsRequest::getString('ip', '', 'POST'));
        $moderate_obj->setVar('forum_id', $forum_id);
        $moderate_obj->setVar('mod_start', time());
        $moderate_obj->setVar('mod_end', time() + XoopsRequest::getInt('expire', 0, 'POST') * 3600 * 24);
        $moderate_obj->setVar('mod_desc', XoopsRequest::getString('desc', '', 'POST'));
        $res = $moderateHandler->insert($moderate_obj);
        if (XoopsRequest::getInt('uid', 0, 'POST') > 0) {
            $onlineHandler = &xoops_gethandler('online');
            $onlines        =& $onlineHandler->getAll(new Criteria('online_uid', XoopsRequest::getInt('uid', 0, 'POST')));
            if (false !== $onlines) {
                $online_ip = $onlines[0]['online_ip'];
                $sql       = sprintf('DELETE FROM %s WHERE sess_ip = %s', $GLOBALS['xoopsDB']->prefix('session'), $GLOBALS['xoopsDB']->quoteString($online_ip));
                if (!$result = $GLOBALS['xoopsDB']->queryF($sql)) {
                }
            }
        }
        if (XoopsRequest::getString('ip', '', 'POST')) {
            $sql = 'DELETE FROM ' . $GLOBALS['xoopsDB']->prefix('session') . ' WHERE sess_ip LIKE ' . $GLOBALS['xoopsDB']->quoteString('%' . XoopsRequest::getString('ip', '', 'POST'));
            if (!$result = $GLOBALS['xoopsDB']->queryF($sql)) {
            }
        }
        redirect_header('moderate.php?forum=$forum_id', 2, _MD_DBUPDATED);
    }
} elseif (XoopsRequest::getString('del', '', 'GET')) {
    $moderate_obj = $moderateHandler->get(XoopsRequest::getString('del', '', 'GET'));
    if ($is_administrator || $moderate_obj->getVar('forum_id') === $forum_id) {
        $moderateHandler->delete($moderate_obj, true);
        redirect_header('moderate.php?forum=$forum_id', 2, _MD_DBUPDATED);
    }
}

$start    = XoopsRequest::getInt('start', 0, 'GET');
$sortname = XoopsRequest::getString('sort', '', 'GET');

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
// START - irmtfan - only show all bans for module admin - for moderator just show its forum_id bans
$criteria = new CriteriaCompo();
if (!$is_administrator) {
    $criteria->add(new Criteria('forum_id', $forum_id, '='));
}
// END - irmtfan - only show all bans for module admin - for moderator just show its forum_id bans
$criteria->setLimit($GLOBALS['xoopsModuleConfig']['topics_per_page']);
$criteria->setStart($start);
$criteria->setSort($sort);
$criteria->setOrder($order);
$moderate_objs  =& $moderateHandler->getObjects($criteria);
$moderate_count = $moderateHandler->getCount($criteria);

include $GLOBALS['xoops']->path('header.php');
$url = 'index.php';
if ($forum_id) {
    $url = 'viewforum.php?forum=' . $forum_id;
}
echo '<div class="forum_intro odd">
        <div class="forum_title">
            <a href="index.php">' . sprintf(_MD_WELCOME, htmlspecialchars($GLOBALS['xoopsConfig']['sitename'], ENT_QUOTES)) . '</a>
            <span class="delimiter">&raquo;</span>
            ' . _MD_SUSPEND_MANAGEMENT . '
            <br /><br />

        </div>
        <div style="clear:both;"></div>
    </div>
    <br />';
echo '<div style="padding: 10px; margin-left:auto; margin-right:auto; text-align:center;"><a href="' . $url . '"><h2>' . _MD_SUSPEND_MANAGEMENT . '</h2></a></div>';

if (!empty($moderate_count)) {
    $_users = array();
    foreach (array_keys($moderate_objs) as $id) {
        $_users[$moderate_objs[$id]->getVar('uid')] = 1;
    }
    $users =& newbb_getUnameFromIds(array_keys($_users), $GLOBALS['xoopsModuleConfig']['show_realname'], true);

    echo '
    <table class="outer" cellpadding="6" cellspacing="1" border="0" width="100%" align="center">
        <tr class="head" align="left">
            <td width="5%" align="center" nowrap="nowrap">
                <strong><a href="moderate.php?forum=' . $forum_id . '&amp;start=' . $start . '&amp;sort=uid" title="Sort by uid">' . _MD_SUSPEND_UID . '</a></strong>
                </td>
            <td width="10%" align="center" nowrap="nowrap">
                <strong><a href="moderate.php?forum=' . $forum_id . '&amp;start=' . $start . '&amp;sort=start" title="Sort by start">' . _MD_SUSPEND_START . '</a></strong>
                </td>
            <td width="10%" align="center" nowrap="nowrap">
                <strong><a href="moderate.php?forum=' . $forum_id . '&amp;start=' . $start . '&amp;sort=expire" title="Sort by expire">' . _MD_SUSPEND_EXPIRE . '</a></strong>
                </td>
            <td width="10%" align="center" nowrap="nowrap">
                <strong><a href="moderate.php?forum=' . $forum_id . '&amp;start=' . $start . '&amp;sort=forum" title="Sort by expire">' . _MD_SUSPEND_SCOPE . '</a></strong>
                </td>
            <td align="left">
                <strong>' . _MD_SUSPEND_DESC . '</strong>
                </td>
            <td width="5%" align="center" nowrap="nowrap">
                <strong>' . _DELETE . '</strong>
                </td>
        </tr>
    ';
    // START irmtfan add forum name in moderate.php
    $forumHandler =& xoops_getmodulehandler('forum', 'newbb');
    $forum_list    = $forumHandler->getAll(null, array('forum_name'), false);
    // END irmtfan add forum name in moderate.php
    foreach (array_keys($moderate_objs) as $id) {
        echo '
            <tr>
                <td width="5%" align="center" nowrap="nowrap">
                    ' . (
            $moderate_objs[$id]->getVar("uid") ?
                (isset($users[$moderate_objs[$id]->getVar("uid")]) ? $users[$moderate_objs[$id]->getVar("uid")] : $moderate_objs[$id]->getVar("uid"))
                : $moderate_objs[$id]->getVar("ip")
            ) . '
                    </td>
                <td width="10%" align="center">
                    ' . (formatTimestamp($moderate_objs[$id]->getVar("mod_start"))) . '
                    </td>
                <td width="10%" align="center">
                    ' . (formatTimestamp($moderate_objs[$id]->getVar("mod_end"))) . '
                    </td>
                <td width="10%" align="center">
                    ' . ($moderate_objs[$id]->getVar("forum_id") ? $forum_list[$moderate_objs[$id]->getVar("forum_id")]["forum_name"] /*irmtfan add forum name in moderate.php*/ : _ALL) . '
                    </td>
                <td align="left">
                    ' . ($moderate_objs[$id]->getVar("mod_desc") ? : _NONE) . '
                    </td>
                <td width="5%" align="center" nowrap="nowrap">
                    ' .
             (($is_administrator || $moderate_objs[$id]->getVar("forum_id") === $forum_id) ? '<a href="moderate.php?forum=' . $forum_id . '&amp;del=' . $moderate_objs[$id]->getVar("mod_id") . '">' . _DELETE . '</a>' : ' ') . '
                    </td>
            </tr>
        ';
    }
    echo '
        <tr class="head" align="left">
            <td width="5%" align="center" nowrap="nowrap">
                <strong><a href="moderate.php?forum=' . $forum_id . '&amp;start=' . $start . '&amp;sort=uid" title="Sort by uid">' . _MD_SUSPEND_UID . '</a></strong>
                </td>
            <td width="10%" align="center" nowrap="nowrap">
                <strong><a href="moderate.php?forum=' . $forum_id . '&amp;start=' . $start . '&amp;sort=start" title="Sort by start">' . _MD_SUSPEND_START . '</a></strong>
                </td>
            <td width="10%" align="center" nowrap="nowrap">
                <strong><a href="moderate.php?forum=' . $forum_id . '&amp;start=' . $start . '&amp;sort=expire" title="Sort by expire">' . _MD_SUSPEND_EXPIRE . '</a></strong>
                </td>
            <td width="10%" align="center" nowrap="nowrap">
                <strong><a href="moderate.php?forum=' . $forum_id . '&amp;start=' . $start . '&amp;sort=forum" title="Sort by expire">' . _MD_SUSPEND_SCOPE . '</a></strong>
                </td>
            <td align="left">
                <strong>' . _MD_SUSPEND_DESC . '</strong>
                </td>
            <td width="5%" align="center" nowrap="nowrap">
                <strong>' . _DELETE . '</strong>
                </td>
        </tr>
    ';
    if ($moderate_count > $GLOBALS['xoopsModuleConfig']['topics_per_page']) {
        include $GLOBALS['xoops']->path('class/pagenav.php');
        $nav = new XoopsPageNav($all_topics, $GLOBALS['xoopsModuleConfig']['topics_per_page'], $start, 'start', 'forum=' . $forum_id . '&amp;sort=' . $sortname);
        if (isset($GLOBALS['xoopsModuleConfig']['do_rewrite'])) {
            $nav->url = formatURL($_SERVER['SERVER_NAME']) . ' /' . $nav->url;
        }
        echo '<tr><td colspan="6">' . $nav->renderNav(4) . '</td></tr>';
    }

    echo '</table><br /><br />';
}

include_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
$forum_form = new XoopsThemeForm(_ADD, 'suspend', 'moderate.php', 'post');
//$forum_form->addElement(new XoopsFormText(_MD_SUSPEND_UID, 'uid', 20, 25 , $forum_userid));
$forum_form->addElement(new XoopsFormSelectUser(_MD_SUSPEND_UID, 'uid', true, $forum_userid, 1, false));
$forum_form->addElement(new XoopsFormText(_MD_SUSPEND_IP, 'ip', 20, 25));
$forum_form->addElement(new XoopsFormText(_MD_SUSPEND_DURATION, 'expire', 20, 25, '5'), true);
$forum_form->addElement(new XoopsFormText(_MD_SUSPEND_DESC, 'desc', 50, 255));
// START irmtfan add forum select box for admins
mod_loadFunctions('forum', 'newbb');
if (newbb_isAdmin()) {
    $forumSel = '<select name=\'forum\'>';// if user dont select any it select "0"
    $forumSel .= '<option value=\'0\' ';
    if ($forum_id === 0) {
        $forumSel .= ' selected';
    }
    $forumSel .= '>' . _ALL . '</option>';
    $forumSel .= newbb_forumSelectBox($forum_id, 'access', false); //$accessForums, $permission = "access", $delimitor_category = true
    $forumSel .= '</select>';
    $forumEle                         = new XoopsFormLabel(_MD_SELFORUM, $forumSel);
    $forumEle->customValidationCode[] = "if (document.suspend.forum.value < 0) {return false;} ";
    $forum_form->addElement($forumEle);
} else {
    $forum_form->addElement(new XoopsFormHidden('forum', $forum_id));
}
// END irmtfan add forum select box for admins
$forum_form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
$forum_form->display();
include $GLOBALS['xoops']->path('footer.php');

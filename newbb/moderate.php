<?php
/**
 * CBB 4.0, or newbb, the forum module for XOOPS project
 *
 * @copyright	The XOOPS Project http://xoops.sf.net
 * @license		http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author		Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since		4.00
 * @version		$Id $
 * @package		module::newbb
 */

include_once __DIR__ . "/header.php";

$forum_id = isset($_POST['forum']) ? intval($_POST['forum']) : 0;
$forum_id = isset($_GET['forum']) ? intval($_GET['forum']) : $forum_id;
$forum_userid = isset($_GET['fuid']) ? intval($_GET['fuid']) : 0;

$isadmin = newbb_isAdmin($forum_id);
if (!$isadmin) {
    redirect_header("index.php", 2, _MD_NORIGHTTOACCESS);
}
$is_administrator = $GLOBALS["xoopsUserIsAdmin"];
$moderate_handler = xoops_getmodulehandler('moderate', 'newbb');

if (!empty($_POST["submit"])&&!empty($_POST["expire"])) {
    if ( !empty($_POST["ip"]) && !preg_match("/^[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}$/", $_POST["ip"])) $_POST["ip"]="";
    if (
        (!empty($_POST["uid"]) && $moderate_handler->getLatest($_POST["uid"])>(time()+$_POST["expire"]*3600*24))
        ||
        (!empty($_POST["ip"]) && $moderate_handler->getLatest($_POST["ip"], false)>(time()+$_POST["expire"]*3600*24))
        ||
        (empty($_POST["uid"]) && empty($_POST["ip"]))
    ) {

    } else {
        $moderate_obj = $moderate_handler->create();
        $moderate_obj->setVar("uid", @$_POST["uid"]);
        $moderate_obj->setVar("ip", @$_POST["ip"]);
        $moderate_obj->setVar("forum_id", $forum_id);
        $moderate_obj->setVar("mod_start", time());
        $moderate_obj->setVar("mod_end", time()+$_POST["expire"]*3600*24);
        $moderate_obj->setVar("mod_desc", @$_POST["desc"]);
        $res = $moderate_handler->insert($moderate_obj);
        if ($_POST["uid"]>0) {
            $online_handler = xoops_gethandler('online');
            $onlines =& $online_handler->getAll(new Criteria("online_uid", $_POST["uid"]));
            if (false != $onlines) {
                $online_ip = $onlines[0]["online_ip"];
                $sql = sprintf('DELETE FROM %s WHERE sess_ip = %s', $xoopsDB->prefix('session'), $xoopsDB->quoteString($online_ip));
                if ( !$result = $xoopsDB->queryF($sql) ) {
                }
            }
        }
        if (!empty($_POST["ip"])) {
            $sql = 'DELETE FROM '.$xoopsDB->prefix('session').' WHERE sess_ip LIKE '.$xoopsDB->quoteString('%'.$_POST["ip"]);
            if ( !$result = $xoopsDB->queryF($sql) ) {
            }
        }
        redirect_header("moderate.php?forum=$forum_id", 2, _MD_DBUPDATED);
    }
} elseif (!empty($_GET["del"])) {
    $moderate_obj = $moderate_handler->get($_GET["del"]);
    if ($is_administrator || $moderate_obj->getVar("forum_id")==$forum_id) {
        $moderate_handler->delete($moderate_obj, true);
        redirect_header("moderate.php?forum=$forum_id", 2, _MD_DBUPDATED);
    }
}

$start = isset($_GET['start']) ? intval($_GET['start']) : 0;
$sortname = isset($_GET['sort']) ? $_GET['sort'] : "";

switch ($sortname) {
    case "uid":
        $sort = "uid ASC, ip";
        $order = "ASC";
        break;
    case "start":
        $sort = "mod_start";
        $order = "ASC";
        break;
    case "expire":
        $sort = "mod_end";
        $order = "DESC";
        break;
    default:
        $sort = "forum_id ASC, uid ASC, ip";
        $order = "ASC";
        break;
}
// START - irmtfan - only show all bans for module admin - for moderator just show its forum_id bans
$criteria= new CriteriaCompo();
if (!$is_administrator) {
    $criteria->add(new Criteria("forum_id",$forum_id, "="));
}
// END - irmtfan - only show all bans for module admin - for moderator just show its forum_id bans
$criteria->setLimit($xoopsModuleConfig['topics_per_page']);
$criteria->setStart($start);
$criteria->setSort($sort);
$criteria->setOrder($order);
$moderate_objs =& $moderate_handler->getObjects($criteria);
$moderate_count = $moderate_handler->getCount($criteria);

include XOOPS_ROOT_PATH.'/header.php';
if ($forum_id) {
    $url = 'viewforum.php?forum='.$forum_id;
} else {
    $url = 'index.php';
}
echo '<div class="forum_intro odd">
        <div class="forum_title">
            <a href="index.php">'.sprintf(_MD_WELCOME, htmlspecialchars($xoopsConfig['sitename'], ENT_QUOTES)).'</a>
            <span class="delimiter">&raquo;</span>
            '._MD_SUSPEND_MANAGEMENT.'
            <br /><br />

        </div>
        <div style="clear:both;"></div>
    </div>
    <br />';
echo '<div style="padding: 10px; margin-left:auto; margin-right:auto; text-align:center;"><a href="'.$url.'"><h2>'._MD_SUSPEND_MANAGEMENT.'</h2></a></div>';

if (!empty($moderate_count)) {
    $_users = array();
    foreach (array_keys($moderate_objs) as $id) {
        $_users[$moderate_objs[$id]->getVar("uid")] = 1;
    }
    $users =& newbb_getUnameFromIds(array_keys($_users), $xoopsModuleConfig['show_realname'], true);

    echo '
    <table class="outer" cellpadding="6" cellspacing="1" border="0" width="100%" align="center">
        <tr class="head" align="left">
            <td width="5%" align="center" nowrap="nowrap">
                <strong><a href="moderate.php?forum='.$forum_id.'&amp;start='.$start.'&amp;sort=uid" title="Sort by uid">'._MD_SUSPEND_UID.'</a></strong>
                </td>
            <td width="10%" align="center" nowrap="nowrap">
                <strong><a href="moderate.php?forum='.$forum_id.'&amp;start='.$start.'&amp;sort=start" title="Sort by start">'._MD_SUSPEND_START.'</a></strong>
                </td>
            <td width="10%" align="center" nowrap="nowrap">
                <strong><a href="moderate.php?forum='.$forum_id.'&amp;start='.$start.'&amp;sort=expire" title="Sort by expire">'._MD_SUSPEND_EXPIRE.'</a></strong>
                </td>
            <td width="10%" align="center" nowrap="nowrap">
                <strong><a href="moderate.php?forum='.$forum_id.'&amp;start='.$start.'&amp;sort=forum" title="Sort by expire">'._MD_SUSPEND_SCOPE.'</a></strong>
                </td>
            <td align="left">
                <strong>'._MD_SUSPEND_DESC.'</strong>
                </td>
            <td width="5%" align="center" nowrap="nowrap">
                <strong>'._DELETE.'</strong>
                </td>
        </tr>
    ';
    // START irmtfan add forum name in moderate.php
    $forum_handler =& xoops_getmodulehandler('forum', 'newbb');
    $forum_list = $forum_handler->getAll(null, array("forum_name"), false);
    // END irmtfan add forum name in moderate.php
    foreach (array_keys($moderate_objs) as $id) {
        echo '
            <tr>
                <td width="5%" align="center" nowrap="nowrap">
                    '.(
                        $moderate_objs[$id]->getVar("uid")?
                            (isset($users[$moderate_objs[$id]->getVar("uid")])?$users[$moderate_objs[$id]->getVar("uid")]:$moderate_objs[$id]->getVar("uid"))
                            :$moderate_objs[$id]->getVar("ip")
                    ).'
                    </td>
                <td width="10%" align="center">
                    '.(formatTimestamp($moderate_objs[$id]->getVar("mod_start"))).'
                    </td>
                <td width="10%" align="center">
                    '.(formatTimestamp($moderate_objs[$id]->getVar("mod_end"))).'
                    </td>
                <td width="10%" align="center">
                    '.($moderate_objs[$id]->getVar("forum_id")? $forum_list[$moderate_objs[$id]->getVar("forum_id")]["forum_name"] /*irmtfan add forum name in moderate.php*/:_ALL).'
                    </td>
                <td align="left">
                    '.($moderate_objs[$id]->getVar("mod_desc")?$moderate_objs[$id]->getVar("mod_desc"):_NONE).'
                    </td>
                <td width="5%" align="center" nowrap="nowrap">
                    '.
                    ( ($is_administrator || $moderate_objs[$id]->getVar("forum_id")==$forum_id)?'<a href="moderate.php?forum='.$forum_id.'&amp;del='.$moderate_objs[$id]->getVar("mod_id").'">'._DELETE.'</a>':' ').'
                    </td>
            </tr>
        ';
    }
    echo '
        <tr class="head" align="left">
            <td width="5%" align="center" nowrap="nowrap">
                <strong><a href="moderate.php?forum='.$forum_id.'&amp;start='.$start.'&amp;sort=uid" title="Sort by uid">'._MD_SUSPEND_UID.'</a></strong>
                </td>
            <td width="10%" align="center" nowrap="nowrap">
                <strong><a href="moderate.php?forum='.$forum_id.'&amp;start='.$start.'&amp;sort=start" title="Sort by start">'._MD_SUSPEND_START.'</a></strong>
                </td>
            <td width="10%" align="center" nowrap="nowrap">
                <strong><a href="moderate.php?forum='.$forum_id.'&amp;start='.$start.'&amp;sort=expire" title="Sort by expire">'._MD_SUSPEND_EXPIRE.'</a></strong>
                </td>
            <td width="10%" align="center" nowrap="nowrap">
                <strong><a href="moderate.php?forum='.$forum_id.'&amp;start='.$start.'&amp;sort=forum" title="Sort by expire">'._MD_SUSPEND_SCOPE.'</a></strong>
                </td>
            <td align="left">
                <strong>'._MD_SUSPEND_DESC.'</strong>
                </td>
            <td width="5%" align="center" nowrap="nowrap">
                <strong>'._DELETE.'</strong>
                </td>
        </tr>
    ';
    if ($moderate_count > $xoopsModuleConfig['topics_per_page']) {
        include XOOPS_ROOT_PATH.'/class/pagenav.php';
        $nav = new XoopsPageNav($all_topics, $xoopsModuleConfig['topics_per_page'], $start, "start", 'forum='.$forum_id.'&amp;sort='.$sortname);
        if (isset($xoopsModuleConfig['do_rewrite'])) $nav->url = formatURL($_SERVER['SERVER_NAME']) ." /" . $nav->url;
        echo '<tr><td colspan="6">'.$nav->renderNav(4).'</td></tr>';
    }

    echo '</table><br /><br />';
}

include_once XOOPS_ROOT_PATH."/class/xoopsformloader.php";
$forum_form = new XoopsThemeForm(_ADD, 'suspend', "moderate.php", 'post');
//$forum_form->addElement(new XoopsFormText(_MD_SUSPEND_UID, 'uid', 20, 25 , $forum_userid));
$forum_form->addElement(new XoopsFormSelectUser(_MD_SUSPEND_UID, 'uid', true, $forum_userid, 1, false));
$forum_form->addElement(new XoopsFormText(_MD_SUSPEND_IP, 'ip', 20, 25));
$forum_form->addElement(new XoopsFormText(_MD_SUSPEND_DURATION, 'expire', 20, 25, '5'), true);
$forum_form->addElement(new XoopsFormText(_MD_SUSPEND_DESC, 'desc', 50, 255));
// START irmtfan add forum select box for admins
mod_loadFunctions("forum", "newbb");
if (newbb_isAdmin()) {
    $forumSel = "<select name=\"forum\">";// if user dont select any it select "0"
    $forumSel .= "<option value=\"0\" ";
    if ($forum_id == 0) {
        $forumSel .= " selected";
    }
    $forumSel .= ">"._ALL."</option>";
    $forumSel .= newbb_forumSelectBox($forum_id, "access", false); //$access_forums, $permission = "access", $delimitor_category = true
    $forumSel .= "</select>";
    $forumEle = new XoopsFormLabel(_MD_SELFORUM, $forumSel);
    $forumEle->customValidationCode[]="if (document.suspend.forum.value < 0) {return false;} ";
    $forum_form->addElement($forumEle);
} else {
    $forum_form->addElement(new XoopsFormHidden('forum', $forum_id));
}
// END irmtfan add forum select box for admins
$forum_form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, "submit"));
$forum_form->display();
include XOOPS_ROOT_PATH.'/footer.php';

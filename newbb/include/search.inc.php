<?php
/**
 * CBB 4.0, or newbb, the forum module for XOOPS project
 *
 * @copyright	The XOOPS Project http://xoops.sf.net
 * @license		http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author		Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>, irmtfan <irmtfan@users.sourceforge.net>
 * @since		4.3
 * @version		$Id $
 * @package		module::newbb
 */
// completely rewrite by irmtfan - remove hardcode database access, solve order issues, add post_text & topic_id, add highlight and reduce queries
if (!defined('XOOPS_ROOT_PATH')) {
	exit();
}
include_once XOOPS_ROOT_PATH . "/modules/newbb/include/functions.ini.php";

function newbb_search($queryarray, $andor, $limit, $offset, $userid, $forums = 0, $sortby = 0, $searchin = "both", $criteriaExtra = "")
{
	global $myts;
	// irmtfan - in XOOPSCORE/search.php $xoopsModuleConfig is not set
	if ( !isset($GLOBALS["xoopsModuleConfig"]) ) {
		$GLOBALS["xoopsModuleConfig"] = newbb_load_config();
	}
	// irmtfan - in XOOPSCORE/search.php $xoopsModule is not set
	if ( !is_object($GLOBALS["xoopsModule"]) && is_object($GLOBALS["module"]) && $GLOBALS["module"]->getVar("dirname") == "newbb" ) {
		$GLOBALS["xoopsModule"] = $GLOBALS["module"];
	}
	$forum_handler = xoops_getmodulehandler('forum', 'newbb');
	$valid_forums = $forum_handler->getIdsByValues($forums); // can we use view permission? $forum_handler->getIdsByValues($forums, "view")

    $criteriaPost = new CriteriaCompo();
	$criteriaPost->add(new Criteria('p.approved', 1), 'AND'); // only active posts
	
	$forum_list = array();// get forum lists just for forum names
    if (count($valid_forums) > 0) {
        $criteriaPermissions = new CriteriaCompo();
    	$criteriaPermissions->add(new Criteria("p.forum_id", "(".implode(",",$valid_forums).")", "IN"), 'AND');
        $forum_list = $forum_handler->getAll(new Criteria("forum_id", "(".implode(", ", $valid_forums).")", "IN"), "forum_name" , false);
	}
	
	if ( is_numeric($userid) && $userid != 0 ) {
        $criteriaUser = new CriteriaCompo();
        $criteriaUser->add(new Criteria('p.uid', $userid), 'OR');
	} elseif (is_array($userid) && count($userid) > 0) {
		$userid = array_map('intval', $userid);
        $criteriaUser = new CriteriaCompo();
        $criteriaUser->add(new Criteria('p.uid', '(' . implode(',', $userid) . ')', 'IN'), 'OR');
    }
    
	$count = count($queryarray);
	$hightlight_key = "";
	if ( is_array($queryarray) && $count > 0) {
		$criteriaKeywords = new CriteriaCompo();
        for ($i = 0; $i < $count; $i++) {
            $criteriaKeyword = new CriteriaCompo();
	    	switch ($searchin) {
	    		case 'title':
                    $criteriaKeyword->add(new Criteria('p.subject', '%' . $queryarray[$i] . '%', 'LIKE'), 'OR');
                	break;
                case 'text':
                    $criteriaKeyword->add(new Criteria('t.post_text', '%' . $queryarray[$i] . '%', 'LIKE'), 'OR');
                	break;
                case 'both' :
                default :
                    $criteriaKeyword->add(new Criteria('p.subject', '%' . $queryarray[$i] . '%', 'LIKE'), 'OR');
                    $criteriaKeyword->add(new Criteria('t.post_text', '%' . $queryarray[$i] . '%', 'LIKE'), 'OR');
                    break;
            }
            $criteriaKeywords->add($criteriaKeyword, $andor);
            unset($criteriaKeyword);
        }
        // add highlight keywords to post links
        $hightlight_key = "&amp;keywords=" . implode('+', $queryarray);
	}
    $criteria = new CriteriaCompo();
    $criteria->add($criteriaPost, 'AND');
    if (!empty($criteriaPermissions)) {
    	$criteria->add($criteriaPermissions, 'AND');
    }
    if (!empty($criteriaUser)) {
        $criteria->add($criteriaUser, 'AND');
    }
    if (!empty($criteriaKeywords)) {
        $criteria->add($criteriaKeywords, 'AND');
    }
    if (!empty($criteriaExtra)) {
        $criteria->add($criteriaExtra, 'AND');
    }
    //$criteria->setLimit($limit); // no need for this
    //$criteria->setStart($offset); // no need for this
    
    if (empty($sortby)) {
        $sortby = "p.post_time";
    }
    $criteria->setSort($sortby);
    $order = 'ASC';
    if ($sortby == "p.post_time") {
         $order = 'DESC';
    }
    $criteria->setOrder($order);
    
	$post_handler =& xoops_getmodulehandler('post', 'newbb');
	$posts = $post_handler->getPostsByLimit($criteria, $limit, $offset);

    $ret = array();
	$i = 0;
	foreach (array_keys($posts) as $id) {
		$post =& $posts[$id];
		$post_data = $post->getPostBody();
		$ret[$i]['topic_id'] = $post->getVar('topic_id');
        $ret[$i]['link'] = XOOPS_URL . "/modules/newbb/viewtopic.php?post_id=" . $post->getVar('post_id') . $hightlight_key; // add highlight key
		$ret[$i]['title'] = $post_data['subject'];
		$ret[$i]['time'] = $post_data['date'];
		$ret[$i]['forum_name'] = $myts->htmlSpecialChars($forum_list[$post->getVar('forum_id')]["forum_name"]);
		$ret[$i]['forum_link'] = XOOPS_URL . "/modules/newbb/viewforum.php?forum=" .$post->getVar('forum_id');
		$ret[$i]['post_text'] = $post_data['text'];
		$ret[$i]['uid'] = $post->getVar('uid');
		$ret[$i]['poster'] = $post->getVar('uid') ? '<a href="'.XOOPS_URL.'/userinfo.php?uid='.$ret[$i]['uid'].'">' . $post_data['author'] . '</a>' : $post_data['author'];
		$i++;
	}
	return $ret;
}
?>
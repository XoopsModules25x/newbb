<?php
// $Id: admin_forum_manager.php 62 2012-08-17 10:15:26Z alfred $
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System                      //
// Copyright (c) 2000 XOOPS.org                           //
// <http://www.xoops.org/>                             //
// ------------------------------------------------------------------------ //
// This program is free software; you can redistribute it and/or modify     //
// it under the terms of the GNU General Public License as published by     //
// the Free Software Foundation; either version 2 of the License, or        //
// (at your option) any later version.                                      //
// //
// You may not change or alter any portion of this comment or credits       //
// of supporting developers from this source code or any supporting         //
// source code which is considered copyrighted (c) material of the          //
// original comment or credit authors.                                      //
// //
// This program is distributed in the hope that it will be useful,          //
// but WITHOUT ANY WARRANTY; without even the implied warranty of           //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
// GNU General Public License for more details.                             //
// //
// You should have received a copy of the GNU General Public License        //
// along with this program; if not, write to the Free Software              //
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------ //
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, http://www.xoops.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //
include 'admin_header.php';
include XOOPS_ROOT_PATH . "/class/xoopstree.php";
include_once XOOPS_ROOT_PATH . "/class/pagenav.php";
mod_loadFunctions("forum", "newbb");
mod_loadFunctions("render", "newbb");
load_functions("cache");

xoops_cp_header();

$op = !empty($_GET['op'])? $_GET['op'] : (!empty($_POST['op'])?$_POST['op']:"");
$forum_id = intval( !empty($_GET['forum'])? $_GET['forum'] : (!empty($_POST['forum'])?$_POST['forum']:0) );

$forum_handler =& xoops_getmodulehandler('forum', 'newbb');
switch ($op) {
    case 'moveforum':
        //if (!$newXoopsModuleGui) loadModuleAdminMenu(2, "");

        if (!empty($_POST['dest_forum'])) {
            $dest = $_POST['dest_forum'];
            if ($dest > 0) {
	            $pid = intval($dest);
            	$forum_dest =& $forum_handler->get($pid);
            	$cid = $forum_dest->getVar("cat_id");
            	unset($forum_dest);
            } else {
	            $cid = abs(intval($dest));
	            $pid = 0;
            }
            $forum_obj =& $forum_handler->get($forum_id);
            $forum_obj->setVar("cat_id", $cid);
            $forum_obj->setVar("parent_forum", $pid);
            $forum_handler->insert($forum_obj);
            if ($forum_handler->insert($forum_obj)) {
	            if ( $cid != $forum_obj->getVar("cat_id") && $subforums = newbb_getSubForum($forum_id) ) {
			        $forums = array_map("intval", array_values($subforums));
	            	$forum_handler->updateAll("cat_id", $cid, new Criteria("forum_id", "(".implode(", ", $forums).")", "IN") );
		        }
            
			    mod_clearCacheFile("forum", "newbb");
                redirect_header('./admin_forum_manager.php?op=manage', 2, _AM_NEWBB_MSG_FORUM_MOVED);
            } else {
                redirect_header('./admin_forum_manager.php?op=manage', 2, _AM_NEWBB_MSG_ERR_FORUM_MOVED);
            }
            exit();
        } else {
	        $box = '<select name="dest_forum">';
            $box .= '<option value=0 selected>' . _SELECT . '</option>';
            $box .= newbb_forumSelectBox($forum_id, "all",true,true);
            $box .= '</select>';

            echo '<form action="./admin_forum_manager.php" method="post" name="forummove" id="forummove">';
            echo '<input type="hidden" name="op" value="moveforum" />';
            echo '<input type="hidden" name="forum" value=' . $forum_id . ' />';
            echo '<table border="0" cellpadding="1" cellspacing="0" align="center" valign="top" width="95%"><tr>';
            echo '<td class="bg2" align="center"><strong>' . _AM_NEWBB_MOVETHISFORUM . '</strong></td>';
            echo '</tr>';
            echo '<tr><td class="bg1" align="center">' . $box . '</td></tr>';
            echo '<tr><td align="center"><input type="submit" name="save" value=' . _GO . ' class="button" /></td></tr>';
            echo '</table></form>';
        }
        break;

    case 'mergeforum':
        //if (!$newXoopsModuleGui) loadModuleAdminMenu(2, "");

        if (!empty($_POST['dest_forum'])) {
        
            $forum_dest =& $forum_handler->get($_POST['dest_forum']);
            if (is_object($forum_dest)) {
                $cid = $forum_dest->getVar("cat_id");
                $sql = 	"	UPDATE " . $xoopsDB->prefix('bb_posts') . 
                        "	SET forum_id=" . intval($_POST['dest_forum']) . 
                        "	WHERE forum_id=$forum_id";
                $result_post = $xoopsDB->queryF($sql);
            
                $sql =	"	UPDATE " . $xoopsDB->prefix('bb_topics') . 
                        "	SET forum_id=" . intval($_POST['dest_forum']) . 
                        "	WHERE forum_id=$forum_id";
                $result_topic = $xoopsDB->queryF($sql);    	
        	
                $forum_obj =& $forum_handler->get($forum_id);
                $forum_handler->updateAll("parent_forum", intval($_POST['dest_forum']), new Criteria("parent_forum", $forum_id) );
                if ( $cid != $forum_obj->getVar("cat_id") && $subforums = newbb_getSubForum($forum_id) ) {
                    $forums = array_map("intval", array_values($subforums));
                    $forum_handler->updateAll("cat_id", $cid, new Criteria("forum_id", "(".implode(", ", $forums).")", "IN") );
                }
            
                $forum_handler->delete($forum_obj);
            
                //mod_clearCacheFile("forum", "newbb");
                $forum_handler->synchronization($forum_dest);
                unset($forum_dest);            
                mod_clearCacheFile("forum", "newbb"); 

                redirect_header('./admin_forum_manager.php?op=manage', 2, _AM_NEWBB_MSG_FORUM_MERGED);
            } else {
                redirect_header('./admin_forum_manager.php?op=manage', 2, _AM_NEWBB_MSG_ERR_FORUM_MOVED);
            }
            exit();
        } else {
			
	        $box = '<select name="dest_forum">';
            $box .= '<option value=0 selected>' . _SELECT . '</option>';
            $box .= newbb_forumSelectBox($forum_id, "all");
            $box .= '</select>';

            echo '<form action="'.xoops_getenv('PHP_SELF').'" method="post" name="forummove" id="forummove">';
            echo '<input type="hidden" name="op" value="mergeforum" />';
            echo '<input type="hidden" name="forum" value=' . $forum_id . ' />';
            echo '<table border="0" cellpadding="1" cellspacing="0" align="center" valign="top" width="95%"><tr>';
            echo '<td class="bg2" align="center"><strong>' . _AM_NEWBB_MERGETHISFORUM . '</strong></td>';
            echo '</tr>';
            echo '<tr><td class="bg1" align="center">' . _AM_NEWBB_MERGETO_FORUM . '</td></tr>';
            echo '<tr><td class="bg1" align="center">' . $box . '</td></tr>';
            echo '<tr><td align="center"><input type="submit" name="save" value=' . _GO . ' class="button" /></td></tr>';
            echo '</form></table>';
        }
        break;

    case "save":

        if ($forum_id) {
            $forum_obj =& $forum_handler->get($forum_id);
            $message = _AM_NEWBB_FORUMUPDATE;
        } else {
            $forum_obj =& $forum_handler->create();
            $message = _AM_NEWBB_FORUMCREATED;
        }

        $forum_obj->setVar('forum_name', $_POST['forum_name']);
        $forum_obj->setVar('forum_desc', $_POST['forum_desc']);
        $forum_obj->setVar('forum_order', @$_POST['forum_order']);
        $forum_obj->setVar('forum_moderator', !empty($_POST['forum_moderator']) ? $_POST['forum_moderator']:array());
        $forum_obj->setVar('parent_forum', @$_POST['parent_forum']);        
        $forum_obj->setVar('attach_maxkb', @$_POST['attach_maxkb']);
        $forum_obj->setVar('attach_ext', @$_POST['attach_ext']);
        $forum_obj->setVar('hot_threshold', @$_POST['hot_threshold']);
        if (!empty($_POST['parent_forum'])) {
	        $parent_obj =& $forum_handler->get($_POST['parent_forum'], array("cat_id"));
	        $_POST['cat_id'] = $parent_obj->getVar("cat_id");
        }
        $forum_obj->setVar('cat_id', $_POST['cat_id']);
        
        if ($forum_handler->insert($forum_obj)) {
		    mod_clearCacheFile("forum", "newbb");
			if (!empty($_POST["perm_template"])) {
			    $groupperm_handler = xoops_getmodulehandler('permission', $xoopsModule->getVar("dirname"));
			    $perm_template = $groupperm_handler->getTemplate();
			    $member_handler =& xoops_gethandler('member');
			    $glist = $member_handler->getGroupList();
            	$perms = $groupperm_handler->getValidForumPerms(true);
				foreach (array_keys($glist) as $group) {
				    foreach ($perms as $perm) {
						$ids = $groupperm_handler->getItemIds($perm, $group, $xoopsModule->getVar("mid"));						
						if (!in_array($forum_obj->getVar("forum_id"), $ids)) {
							if (empty($perm_template[$group][$perm])) {
								$groupperm_handler->deleteRight($perm, $forum_obj->getVar("forum_id"), $group, $xoopsModule->getVar("mid"));
							} else {
								$groupperm_handler->addRight($perm, $forum_obj->getVar("forum_id"), $group, $xoopsModule->getVar("mid"));
							}
						}
				    }
				}				
	        }			
            redirect_header("admin_forum_manager.php", 2, $message);
            exit();
        } else {
            redirect_header("admin_forum_manager.php?op=mod&amp;forum=" . $forum_obj->getVar('forum_id') . "", 2, _AM_NEWBB_FORUM_ERROR);
            exit();
        }

    case "mod":
        $forum_obj =& $forum_handler->get($forum_id);
        //if (!$newXoopsModuleGui) loadModuleAdminMenu(2, _AM_NEWBB_EDITTHISFORUM . $forum_obj->getVar('forum_name'));
        echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_NEWBB_EDITTHISFORUM . "</legend>";

        include XOOPS_ROOT_PATH."/modules/".$xoopsModule->getVar("dirname")."/include/form.forum.php";

        echo "</fieldset>";
        break;

    case "del":

        if (isset($_POST['confirm']) != 1) {
            xoops_confirm(array('op' => 'del', 'forum' => intval($_GET['forum']), 'confirm' => 1), 'admin_forum_manager.php', _AM_NEWBB_TWDAFAP);
            break;
        } else {
            $forum_obj =& $forum_handler->get($_POST['forum']);
            $forum_handler->delete($forum_obj);
		    mod_clearCacheFile("forum", "newbb");
            redirect_header("admin_forum_manager.php?op=manage", 1, _AM_NEWBB_FORUMREMOVED);
            exit();
        }
        break;

    case "addforum":
        //if (!$newXoopsModuleGui) loadModuleAdminMenu(2, _AM_NEWBB_CREATENEWFORUM);
        echo "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_NEWBB_CREATENEWFORUM . "</legend>";
        echo "<br />";
        $parent_forum = @intval($_GET['forum']); 
        $cat_id = @intval($_GET['cat_id']);
        if (!$parent_forum && !$cat_id) {
	        break;
        }
        $forum_obj =& $forum_handler->create();
        $forum_obj->setVar("parent_forum", $parent_forum);
        $forum_obj->setVar("cat_id", $cat_id);
        include XOOPS_ROOT_PATH."/modules/".$xoopsModule->getVar("dirname")."/include/form.forum.php";

        echo "</fieldset>";

        break;

    default:        
		
		if (!$newXoopsModuleGui) {
			//loadModuleAdminMenu(2, _AM_NEWBB_FORUM_MANAGER);
			$echo = "<fieldset><legend style='font-weight: bold; color: #900;'>" . _AM_NEWBB_FORUM_MANAGER . "</legend>";
		} else {
			$echo = $indexAdmin->addNavigation('admin_forum_manager.php') ;
			$echo .= "<fieldset>";
		}
		$echo .= "<table border='0' cellpadding='4' cellspacing='1' width='100%' class='outer'>";
        $echo .= "<tr align='center'>";
        $echo .= "<td class='bg3' colspan='2'>" . _AM_NEWBB_NAME . "</td>";
        $echo .= "<td class='bg3'>" . _AM_NEWBB_EDIT . "</td>";
        $echo .= "<td class='bg3'>" . _AM_NEWBB_DELETE . "</td>";
        $echo .= "<td class='bg3'>" . _AM_NEWBB_ADD . "</td>";
        $echo .= "<td class='bg3'>" . _AM_NEWBB_MOVE . "</td>";
        $echo .= "<td class='bg3'>" . _AM_NEWBB_MERGE . "</td>";
        $echo .= "</tr>";

		$category_handler =& xoops_getmodulehandler('category', 'newbb');
		$criteria_category = new CriteriaCompo(new criteria('1', 1));
		$criteria_category->setSort('cat_order');
    	$categories = $category_handler->getList($criteria_category);
		$forums = $forum_handler->getTree(array_keys($categories), 0, "all");
		foreach (array_keys($categories) as $c) {
            $category = $categories[$c];
            $cat_id = $c;
            $cat_link = "<a href=\"" . XOOPS_URL.'/modules/'.$xoopsModule->getVar("dirname", "n") . "/index.php?viewcat=" . $cat_id . "\">" . $category . "</a>";
            $cat_edit_link = "<a href=\"admin_cat_manager.php?op=mod&amp;cat_id=" . $cat_id. "\">".newbb_displayImage('admin_edit', _EDIT)."</a>";
            $cat_del_link = "<a href=\"admin_cat_manager.php?op=del&amp;cat_id=" . $cat_id . "\">".newbb_displayImage('admin_delete', _DELETE)."</a>";
            $forum_add_link = "<a href=\"admin_forum_manager.php?op=addforum&amp;cat_id=" . $cat_id . "\">".newbb_displayImage('new_forum')."</a>";
			$echo .= "<tr class='even' align='left'>";
            $echo .= "<td width='100%' colspan='2'><strong>" . $cat_link . "</strong></td>";
            $echo .= "<td align='center'>" . $cat_edit_link . "</td>";
            $echo .= "<td align='center'>" . $cat_del_link . "</td>";
            $echo .= "<td align='center'>" . $forum_add_link . "</td>";
            $echo .= "<td></td>";
            $echo .= "<td></td>";
            $echo .= "</tr>";
            if (!isset($forums[$c])) continue;
            $i = 0;
			foreach (array_keys($forums[$c]) as $f) {
				$forum = $forums[$c][$f];
                $f_link = $forum["prefix"]."<a href=\"" . XOOPS_URL.'/modules/'.$xoopsModule->getVar("dirname", "n") . "/viewforum.php?forum=" . $f . "\">" . $forum["forum_name"] . "</a>";
                $f_edit_link = "<a href=\"admin_forum_manager.php?op=mod&amp;forum=" . $f . "\">".newbb_displayImage('admin_edit',_AM_NEWBB_EDIT)."</a>";
                $f_del_link = "<a href=\"admin_forum_manager.php?op=del&amp;forum=" . $f . "\">".newbb_displayImage('admin_delete',_AM_NEWBB_DELETE)."</a>";
                $sf_add_link = "<a href=\"admin_forum_manager.php?op=addforum&amp;cat_id=" . $c . "&forum=" . $f . "\">".newbb_displayImage('new_forum',_AM_NEWBB_CREATEFORUM)."</a>";
                $f_move_link = "<a href=\"admin_forum_manager.php?op=moveforum&amp;forum=" . $f . "\">".newbb_displayImage('admin_move',_AM_NEWBB_MOVE)."</a>";
                $f_merge_link = "<a href=\"admin_forum_manager.php?op=mergeforum&amp;forum=" . $f . "\">".newbb_displayImage('admin_merge',_AM_NEWBB_MERGE)."</a>";

            	$class = ((++$i)%2)?"odd":"even";
                $echo .= "<tr class='".$class."' align='left'><td></td>";
                $echo .= "<td><strong>" . $f_link . "</strong></td>";
                $echo .= "<td align='center'>" . $f_edit_link . "</td>";
                $echo .= "<td align='center'>" . $f_del_link . "</td>";
                $echo .= "<td align='center'>" . $sf_add_link . "</td>";
                $echo .= "<td align='center'>" . $f_move_link . "</td>";
                $echo .= "<td align='center'>" . $f_merge_link . "</td>";
                $echo .= "</tr>";
			}
		}
    	unset($forums, $categories);

        echo $echo;
        echo "</table>";
        echo "</fieldset>";
        break;
}
xoops_cp_footer();

?>
<?php
//
// ------------------------------------------------------------------------ //
// XOOPS - PHP Content Management System                      //
// Copyright (c) 2000-2016 XOOPS.org                           //
// <https://xoops.org/>                             //
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

use Xmf\Request;

require_once __DIR__ . '/admin_header.php';
require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');

xoops_cp_header();
$adminObject->displayNavigation(basename(__FILE__));

//if (!$newXoopsModuleGui) loadModuleAdminMenu(5, _AM_NEWBB_PRUNE_TITLE);
//    else $adminObject->displayNavigation(basename(__FILE__));

echo "<table width='100%' border='0' cellspacing='1' class='outer'>" . "<tr><td class='odd'>";

if (Request::getString('submit', '', 'POST')) {
    $post_list       = null;
    $topic_list      = null;
    $topics_number   = 0;
    $posts_number    = 0;
    $selected_forums = '';
    // irmtfan fix if it is array
    $temp = Request::getArray('forums', null, 'POST');
    if (!$temp || !$temp[0]) {
        redirect_header('admin_forum_prune.php', 1, _AM_NEWBB_PRUNE_FORUMSELERROR);
    } elseif (is_array(Request::getArray('forums', '', 'POST'))) {
        $selected_forums = implode(',', Request::getArray('forums', null, 'POST'));
    } else {
        $selected_forums = Request::getArray('forums', null, 'POST');
    }

    $prune_days  = Request::getInt('days', 0, 'POST');
    $prune_ddays = time() - $prune_days;
    $archive     = Request::getInt('archive', 0, 'POST');
    $sticky      = Request::getInt('sticky', 0, 'POST');
    $digest      = Request::getInt('digest', 0, 'POST');
    $lock        = Request::getInt('lock', 0, 'POST');
    $hot         = Request::getInt('hot', 0, 'POST');
    $store       = Request::getInt('store', null, 'POST');

    $sql = 'SELECT t.topic_id FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_topics') . ' t, ' . $GLOBALS['xoopsDB']->prefix('newbb_posts') . '  p
                    WHERE t.forum_id IN (' . $selected_forums . ')
                    AND p.post_id =t.topic_last_post_id ';

    if ($sticky) {
        $sql .= ' AND t.topic_sticky <> 1 ';
    }
    if ($digest) {
        $sql .= ' AND t.topic_digest <> 1 ';
    }
    if ($lock) {
        $sql .= ' AND t.topic_status <> 1 ';
    }
    if (0 !== $hot) {
        $sql .= ' AND t.topic_replies < ' . $hot . ' ';
    }

    $sql .= ' AND p.post_time<= ' . $prune_ddays . ' ';
    // Ok now we have the sql query completed, go for topic_id's and posts_id's
    $topics = [];
    if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
        return _MD_NEWBB_ERROR;
    }
    // Dave_L code
    while (false !== ($row = $GLOBALS['xoopsDB']->fetchArray($result))) {
        $topics[] = $row['topic_id'];
    }
    $topics_number = count($topics);
    $topic_list    = implode(',', $topics);
    $post_list     = '';

    if ('' !== $topic_list) {
        $sql = 'SELECT post_id FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_posts') . ' WHERE topic_id IN (' . $topic_list . ')';

        $posts = [];
        if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
            return _MD_NEWBB_ERROR;
        }
        // Dave_L code
        while (false !== ($row = $GLOBALS['xoopsDB']->fetchArray($result))) {
            $posts[] = $row['post_id'];
        }
        $posts_number = count($posts);
        $post_list    = implode(',', $posts);
    }
    // OKZ Now we have al posts id and topics id
    if ('' !== $post_list) {
        // COPY POSTS TO OTHER FORUM
        if (null !== $store) {
            $sql = 'UPDATE ' . $GLOBALS['xoopsDB']->prefix('newbb_posts') . " SET forum_id=$store WHERE topic_id IN ($topic_list)";
            if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
                return _MD_NEWBB_ERROR;
            }

            $sql = 'UPDATE ' . $GLOBALS['xoopsDB']->prefix('newbb_topics') . " SET forum_id=$store WHERE topic_id IN ($topic_list)";
            if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
                return _MD_NEWBB_ERROR;
            }
        } else {
            // ARCHIVING POSTS
            if (1 == $archive) {
                $result = $GLOBALS['xoopsDB']->query('SELECT p.topic_id, p.post_id, t.post_text FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_posts') . ' p, ' . $GLOBALS['xoopsDB']->prefix('newbb_posts_text') . " t WHERE p.post_id IN ($post_list) AND p.post_id=t.post_id");
                while (false !== (list($topic_id, $post_id, $post_text) = $GLOBALS['xoopsDB']->fetchRow($result))) {
                    $sql = $GLOBALS['xoopsDB']->query('INSERT INTO ' . $GLOBALS['xoopsDB']->prefix('newbb_archive') . " (topic_id, post_id, post_text) VALUES ($topic_id, $post_id, $post_text)");
                }
            }
            // DELETE POSTS
            $sql = 'DELETE FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_posts') . " WHERE topic_id IN ($topic_list)";
            if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
                return _MD_NEWBB_ERROR;
            }
            // DELETE TOPICS
            $sql = 'DELETE FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_topics') . " WHERE topic_id IN ($topic_list)";
            if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
                return _MD_NEWBB_ERROR;
            }
            // DELETE POSTS_TEXT
            $sql = 'DELETE FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_posts_text') . " WHERE post_id IN ($post_list)";
            if (!$result = $GLOBALS['xoopsDB']->query($sql)) {
                return _MD_NEWBB_ERROR;
            }
            // SYNC FORUMS AFTER DELETE
            //            /** @var Newbb\ForumHandler $forumHandler */
            //            $forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
            $forumHandler->synchronization();
            // I THINK POSTS AND TOPICS HAVE BEEN DESTROYED :LOL:
        }
    }

    $tform = new \XoopsThemeForm(_AM_NEWBB_PRUNE_RESULTS_TITLE, 'prune_results', xoops_getenv('PHP_SELF'));
    $tform->addElement(new \XoopsFormLabel(_AM_NEWBB_PRUNE_RESULTS_FORUMS, $selected_forums));
    $tform->addElement(new \XoopsFormLabel(_AM_NEWBB_PRUNE_RESULTS_TOPICS, $topics_number));
    $tform->addElement(new \XoopsFormLabel(_AM_NEWBB_PRUNE_RESULTS_POSTS, $posts_number));
    $tform->display();
} else {
    $sform = new \XoopsThemeForm(_AM_NEWBB_PRUNE_TITLE, 'prune', xoops_getenv('PHP_SELF'));
    $sform->setExtra('enctype="multipart/form-data"');

    /* Let User select the number of days
    $sform->addElement( new \XoopsFormText(_AM_NEWBB_PRUNE_DAYS , 'days', 5, 10,100 ), true );
    */
    // $sql="SELECT p.topic_id, p.post_id t.post_text FROM ".$GLOBALS['xoopsDB']->prefix("newbb_posts")." p, ".$GLOBALS['xoopsDB']->prefix("newbb_posts_text")." t WHERE p.post_id IN ($post_list) AND p.post_id=t.post_id";
    // $result = $GLOBALS['xoopsDB']->query();
    // Days selected by selbox (better error control :lol:)
    $days = new \XoopsFormSelect(_AM_NEWBB_PRUNE_DAYS, 'days', null, 1, false);
    $days->addOptionArray([
                              604800   => _AM_NEWBB_PRUNE_WEEK,
                              1209600  => _AM_NEWBB_PRUNE_2WEEKS,
                              2592000  => _AM_NEWBB_PRUNE_MONTH,
                              5184000  => _AM_NEWBB_PRUNE_2MONTH,
                              10368000 => _AM_NEWBB_PRUNE_4MONTH,
                              31536000 => _AM_NEWBB_PRUNE_YEAR,
                              63072000 => _AM_NEWBB_PRUNE_2YEARS
                          ]);
    $sform->addElement($days);
    // START irmtfan remove hardcode db access
    require_once $GLOBALS['xoops']->path('modules/' . $xoopsModule->getVar('dirname') . '/footer.php'); // to include js files
    require_once __DIR__ . '/../include/functions.forum.php';
    $forumSelMulti  = "<select name=\"forums[]\" multiple=\"multiple\" onfocus = \"validate('forums[]','select', false,true)\">";// disable all categories
    $forumSelSingle = "<select name=\"store\" onfocus = \"validate('store','select', false,true)\">"; // disable all categories
    $forumSelBox    = '<option value = 0 >-- ' . _AM_NEWBB_PERM_FORUMS . ' --</option>';
    $forumSelBox    .= newbbForumSelectBox(null, 'access', false); //$accessForums = nothing, $permission = "access", $delimitorCategory = false
    $forumSelBox    .= '</select>';
    $forumEle       = new \XoopsFormLabel(_AM_NEWBB_PRUNE_FORUMS, $forumSelMulti . $forumSelBox);
    $storeEle       = new \XoopsFormLabel(_AM_NEWBB_PRUNE_STORE, $forumSelSingle . $forumSelBox);
    /* irmtfan remove hardcode
    $checkbox = new \XoopsFormCheckBox(_AM_NEWBB_PRUNE_FORUMS, 'forums');
    $radiobox = new \XoopsFormRadio(_AM_NEWBB_PRUNE_STORE, 'store');
    // PUAJJ I HATE IT, please tidy up
    $sql = "SELECT forum_name, forum_id FROM " . $GLOBALS['xoopsDB']->prefix("newbb_forums") . " ORDER BY forum_id";
    if ($result = $GLOBALS['xoopsDB']->query($sql)) {
        if ($myrow = $GLOBALS['xoopsDB']->fetchArray($result)) {
            do {
                $checkbox->addOption($myrow['forum_id'], $myrow['forum_name']);
                $radiobox->addOption($myrow['forum_id'], $myrow['forum_name']);
            } while (false !== ($myrow = $GLOBALS['xoopsDB']->fetchArray($result)));
        } else {
            echo "NO FORUMS";
        }
    } else {
        echo "DB ERROR";
    }
    */
    // END irmtfan remove hardcode db access

    $sform->addElement(/*$checkbox*/
        $forumEle); // irmtfan

    $sticky_confirmation = new \XoopsFormRadio(_AM_NEWBB_PRUNE_STICKY, 'sticky', 1);
    $sticky_confirmation->addOption(1, _AM_NEWBB_PRUNE_YES);
    $sticky_confirmation->addOption(0, _AM_NEWBB_PRUNE_NO);
    $sform->addElement($sticky_confirmation);

    $digest_confirmation = new \XoopsFormRadio(_AM_NEWBB_PRUNE_DIGEST, 'digest', 1);
    $digest_confirmation->addOption(1, _AM_NEWBB_PRUNE_YES);
    $digest_confirmation->addOption(0, _AM_NEWBB_PRUNE_NO);
    $sform->addElement($digest_confirmation);

    $lock_confirmation = new \XoopsFormRadio(_AM_NEWBB_PRUNE_LOCK, 'lock', 0);
    $lock_confirmation->addOption(1, _AM_NEWBB_PRUNE_YES);
    $lock_confirmation->addOption(0, _AM_NEWBB_PRUNE_NO);
    $sform->addElement($lock_confirmation);

    $hot_confirmation = new \XoopsFormSelect(_AM_NEWBB_PRUNE_HOT, 'hot', null, 1, false);
    $hot_confirmation->addOptionArray([
                                          '0'  => 0,
                                          '5'  => 5,
                                          '10' => 10,
                                          '15' => 15,
                                          '20' => 20,
                                          '25' => 25,
                                          '30' => 30
                                      ]);
    $sform->addElement($hot_confirmation);

    $sform->addElement(/*$radiobox*/
        $storeEle); // irmtfan

    $archive_confirmation = new \XoopsFormRadio(_AM_NEWBB_PRUNE_ARCHIVE, 'archive', 1);
    $archive_confirmation->addOption(1, _AM_NEWBB_PRUNE_YES);
    $archive_confirmation->addOption(0, _AM_NEWBB_PRUNE_NO);
    $sform->addElement($archive_confirmation);

    $button_tray = new \XoopsFormElementTray('', '');
    $button_tray->addElement(new \XoopsFormButton('', 'submit', _AM_NEWBB_PRUNE_SUBMIT, 'submit'));
    $button_tray->addElement(new \XoopsFormButton('', 'reset', _AM_NEWBB_PRUNE_RESET, 'reset'));
    $sform->addElement($button_tray);

    $sform->display();
}

echo '</td></tr></table>';
echo '<fieldset>';
echo '<legend>&nbsp;' . _MI_NEWBB_ADMENU_PRUNE . '&nbsp;</legend>';
echo _AM_NEWBB_HELP_PRUNE_TAB;
echo '</fieldset>';
require_once __DIR__ . '/admin_footer.php';

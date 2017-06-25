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

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

defined('NEWBB_FUNCTIONS_INI') || include __DIR__ . '/functions.ini.php';
define('NEWBB_FUNCTIONS_FORUM_LOADED', true);

if (!defined('NEWBB_FUNCTIONS_FORUM')) {
    define('NEWBB_FUNCTIONS_FORUM', 1);

    /**
     * @param  null|array   $value             selected forum id
     * @param  string $permission        permission (access, all, etc.)
     * @param  bool   $categoryDelimiter show delimiter between categories
     * @param  bool   $see
     * @return string
     */
    function newbb_forumSelectBox($value = null, $permission = 'access', $categoryDelimiter = true, $see = false)
    {
        global $xoopsUser;

        /** @var \NewbbCategoryHandler $categoryHandler */
        $categoryHandler = xoops_getModuleHandler('category', 'newbb');
        $categories      = $categoryHandler->getByPermission($permission, ['cat_id', 'cat_order', 'cat_title'], false);

        $cacheHelper = new \Xmf\Module\Helper\Cache('newbb');

        $groups = [XOOPS_GROUP_ANONYMOUS];
        if (is_object($xoopsUser)) {
            $groups = $xoopsUser->getGroups();
        }
        sort($groups);
        $groupKey = 'forumselect_' . $permission . '_' . md5(implode(',', $groups));
        $forums   = $cacheHelper->cacheRead($groupKey, function () use ($categories, $permission) {
            /** @var \NewbbCategoryHandler $categoryHandler */
            $categoryHandler = xoops_getModuleHandler('category', 'newbb');
            $categories      = $categoryHandler->getByPermission($permission, ['cat_id', 'cat_order', 'cat_title'], false);

            /** @var \NewbbForumHandler $forumHandler */
            $forumHandler = xoops_getModuleHandler('forum', 'newbb');
            $forums       = $forumHandler->getTree(array_keys($categories), 0, 'all');

            return $forums;
        }, 300);

        $value = is_array($value) ? $value : [$value];
        //$see = is_array($see) ? $see : array($see);
        $box = '';
        if (count($forums) > 0) {
            foreach (array_keys($categories) as $key) {
                if ($categoryDelimiter) {
                    $box .= "<option value=0>&nbsp;</option>\n";
                }
                $box .= "<option value='" . (-1 * $key) . "'>[" . $categories[$key]['cat_title'] . "]</option>\n";
                if (empty($forums[$key])) {
                    continue;
                }
                foreach ($forums[$key] as $f => $forum) {
                    if ($see && in_array($f, $value)) {
                        continue;
                    }
                    $box .= "<option value='{$f}' " . (in_array($f, $value) ? ' selected' : '') . '>' . $forum['prefix'] . $forum['forum_name'] . "</option>\n";
                }
            }
        } else {
            $box .= '<option value=0>' . _MD_NEWBB_NOFORUMINDB . "</option>\n";
        }
        unset($forums, $categories);

        return $box;
    }

    /**
     * @param  int $forum_id
     * @return string
     */
    function newbb_make_jumpbox($forum_id = 0)
    {
        $box = '<form name="forum_jumpbox" method="get" action="' . XOOPS_URL . '/modules/newbb/viewforum.php" onsubmit="javascript: if (document.forum_jumpbox.forum.value &lt; 1) {return false;}">';
        $box .= '<select class="select" name="forum" onchange="if (this.options[this.selectedIndex].value >0) { document.forms.forum_jumpbox.submit();}">';
        $box .= '<option value=0>-- ' . _MD_NEWBB_SELFORUM . ' --</option>';
        $box .= newbb_forumSelectBox($forum_id);
        $box .= "</select> <input type='submit' class='button' value='" . _GO . "' /></form>";
        unset($forums, $categories);

        return $box;
    }

    /**
     * Get structured forums
     *
     * This is a temporary solution
     * To be substituted with a new tree handler
     *
     * @int integer    $pid    parent forum ID
     *
     * @param  int  $pid
     * @param  bool $refresh
     * @return array
     */
    function newbb_getSubForum($pid = 0, $refresh = false)
    {
        static $list;
        if (!isset($list)) {
            $cacheHelper = new \Xmf\Module\Helper\Cache('newbb');
            $list        = $cacheHelper->read('forum_sub');
        }

        if (!is_array($list) || $refresh) {
            $list = newbb_createSubForumList();
        }
        if ($pid == 0) {
            return $list;
        } else {
            return @$list[$pid];
        }
    }

    /**
     * @return array
     */
    function newbb_createSubForumList()
    {
        /** @var \NewbbForumHandler $forumHandler */
        $forumHandler = xoops_getModuleHandler('forum', 'newbb');
        $criteria     = new CriteriaCompo(null, 1);
        $criteria->setSort('cat_id ASC, parent_forum ASC, forum_order');
        $criteria->setOrder('ASC');
        $forums_obj = $forumHandler->getObjects($criteria);
        require_once $GLOBALS['xoops']->path('modules/newbb/class/tree.php');
        $tree        = new NewbbObjectTree($forums_obj, 'forum_id', 'parent_forum');
        $forum_array = [];
        foreach (array_keys($forums_obj) as $key) {
            if (!$child = array_keys($tree->getAllChild($forums_obj[$key]->getVar('forum_id')))) {
                continue;
            }
            $forum_array[$forums_obj[$key]->getVar('forum_id')] = $child;
        }
        unset($forums_obj, $tree, $criteria);

        $cacheHelper = new \Xmf\Module\Helper\Cache('newbb');
        $cacheHelper->write('forum_sub', $forum_array);

        return $forum_array;
    }

    /**
     * @param  int  $forum_id
     * @param  bool $refresh
     * @return array|mixed|null
     */
    function newbb_getParentForum($forum_id = 0, $refresh = false)
    {
        static $list = null;

        if (!isset($list)) {
            $cacheHelper = new \Xmf\Module\Helper\Cache('newbb');
            $list        = $cacheHelper->read('forum_parent');
        }
        if (!is_array($list) || $refresh) {
            $list = newbb_createParentForumList();
        }
        if ($forum_id == 0) {
            return $list;
        } else {
            return @$list[$forum_id];
        }
    }

    /**
     * @return array
     */
    function newbb_createParentForumList()
    {
        /** @var \NewbbForumHandler $forumHandler */
        $forumHandler = xoops_getModuleHandler('forum', 'newbb');
        $criteria     = new Criteria('1', 1);
        $criteria->setSort('parent_forum');
        $criteria->setOrder('ASC');
        $forums_obj = $forumHandler->getObjects($criteria);
        require_once $GLOBALS['xoops']->path('modules/newbb/class/tree.php');
        $tree        = new NewbbObjectTree($forums_obj, 'forum_id', 'parent_forum');
        $forum_array = [];
        foreach (array_keys($forums_obj) as $key) {
            $parent_forum = $forums_obj[$key]->getVar('parent_forum');
            if (!$parent_forum) {
                continue;
            }
            if (isset($forum_array[$parent_forum])) {
                $forum_array[$forums_obj[$key]->getVar('forum_id')]   = $forum_array[$parent_forum];
                $forum_array[$forums_obj[$key]->getVar('forum_id')][] = $parent_forum;
            } else {
                $forum_array[$forums_obj[$key]->getVar('forum_id')] = $tree->getParentForums($forums_obj[$key]->getVar('forum_id'));
            }
        }
        unset($forums_obj, $tree, $criteria);

        $cacheHelper = new \Xmf\Module\Helper\Cache('newbb');
        $cacheHelper->write('forum_parent', $forum_array);

        return $forum_array;
    }
}

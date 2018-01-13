<?php
//
// FILE        ::    newbb.php
// AUTHOR    ::    Ryuji AMANO <info@ryus.biz>
// WEB        ::    Ryu's Planning <http://ryus.biz/>

// NewBB plugin: D.J., https://xoops.org.cn

use XoopsModules\Newbb;

/**
 * @return array
 */
function b_sitemap_newbb()
{
    global $sitemap_configs;
    $sitemap = [];

    /** @var Newbb\ForumHandler $forumHandler */
    $forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
    /* Allowed forums */
    $forums_allowed = $forumHandler->getIdsByPermission();

    /* fetch top forums */
    $forums_top_id = [];
    if (!empty($forums_allowed)) {
        $crit_top = new \CriteriaCompo(new \Criteria('parent_forum', 0));
        //$crit_top->add(new \Criteria("cat_id", "(".implode(", ", array_keys($categories)).")", "IN"));
        $crit_top->add(new \Criteria('forum_id', '(' . implode(', ', $forums_allowed) . ')', 'IN'));
        $forums_top_id = $forumHandler->getIds($crit_top);
    }

    $forums_sub_id = [];
    if ((bool)$forums_top_id && $sitemap_configs['show_subcategoris']) {
        $crit_sub = new \CriteriaCompo(new \Criteria('parent_forum', '(' . implode(', ', $forums_top_id) . ')', 'IN'));
        $crit_sub->add(new \Criteria('forum_id', '(' . implode(', ', $forums_allowed) . ')', 'IN'));
        $forums_sub_id = $forumHandler->getIds($crit_sub);
    }

    /* Fetch forum data */
    $forums_available = array_merge($forums_top_id, $forums_sub_id);
    $forums_array     = [];
    if ((bool)$forums_available) {
        $crit_forum = new \Criteria('forum_id', '(' . implode(', ', $forums_available) . ')', 'IN');
        $crit_forum->setSort('cat_id ASC, parent_forum ASC, forum_order');
        $crit_forum->setOrder('ASC');
        $forums_array = $forumHandler->getAll($crit_forum, ['forum_name', 'parent_forum', 'cat_id'], false);
    }

    $forums = [];
    foreach ($forums_array as $forumid => $forum) {
        if ((bool)$forum['parent_forum']) {
            $forums[$forum['parent_forum']]['fchild'][$forumid] = [
                'id'    => $forumid,
                'url'   => 'viewforum.php?forum=' . $forumid,
                'title' => $forum['forum_name']
            ];
        } else {
            $forums[$forumid] = [
                'id'    => $forumid,
                'cid'   => $forum['cat_id'],
                'url'   => 'viewforum.php?forum=' . $forumid,
                'title' => $forum['forum_name']
            ];
        }
    }

    if ($sitemap_configs['show_subcategoris']) {
        /** @var Newbb\CategoryHandler $categoryHandler */
        $categoryHandler = Newbb\Helper::getInstance()->getHandler('Category');
        $categories      = [];
        $categories      = $categoryHandler->getByPermission('access', ['cat_id', 'cat_title'], false);

        foreach ($categories as $key => $category) {
            $cat_id                         = $category['cat_id'];
            $i                              = $cat_id;
            $sitemap['parent'][$i]['id']    = $cat_id;
            $sitemap['parent'][$i]['title'] = $category['cat_title'];
            $sitemap['parent'][$i]['url']   = XOOPS_URL . '/modules/newbb/index.php?cat=' . $cat_id;
        }
        foreach ($forums as $id => $forum) {
            $cid                                            = $forum['cid'];
            $sitemap['parent'][$cid]['child'][$id]          = $forum;
            $sitemap['parent'][$cid]['child'][$id]['image'] = 2;
            if (empty($forum['fchild'])) {
                continue;
            }

            foreach ($forum['fchild'] as $_id => $_forum) {
                $sitemap['parent'][$cid]['child'][$_id]          = $_forum;
                $sitemap['parent'][$cid]['child'][$_id]['image'] = 3;
            }
        }
    } else {
        foreach ($forums as $id => $forum) {
            $sitemap['parent'][$id] = $forum;
        }
    }

    return $sitemap;
}

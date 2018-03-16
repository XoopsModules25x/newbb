<?php namespace XoopsModules\Newbb;

/**
 * Newbb module
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package         newbb
 * @since           4.0
 * @author          Taiwen Jiang <phppp@users.sourceforge.net>
 */

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

class Forum extends \XoopsObject
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->initVar('forum_id', XOBJ_DTYPE_INT);
        $this->initVar('forum_name', XOBJ_DTYPE_TXTBOX);
        $this->initVar('forum_desc', XOBJ_DTYPE_TXTAREA);
        $this->initVar('forum_moderator', XOBJ_DTYPE_ARRAY, serialize([]));
        $this->initVar('forum_topics', XOBJ_DTYPE_INT);
        $this->initVar('forum_posts', XOBJ_DTYPE_INT);
        $this->initVar('forum_last_post_id', XOBJ_DTYPE_INT);
        $this->initVar('cat_id', XOBJ_DTYPE_INT);
        $this->initVar('parent_forum', XOBJ_DTYPE_INT);
        $this->initVar('hot_threshold', XOBJ_DTYPE_INT, 20);
        $this->initVar('attach_maxkb', XOBJ_DTYPE_INT, 500);
        $this->initVar('attach_ext', XOBJ_DTYPE_SOURCE, 'zip|jpg|gif|png');
        $this->initVar('forum_order', XOBJ_DTYPE_INT, 99);
        $this->initVar('dohtml', XOBJ_DTYPE_INT, 1);
    }

    /**
     * @return string
     */
    public function dispForumModerators()
    {
        $ret = '';
        if (!$valid_moderators = $this->getVar('forum_moderator')) {
            return $ret;
        }
        require_once $GLOBALS['xoops']->path('modules/newbb/include/functions.user.php');
        $moderators = newbbGetUnameFromIds($valid_moderators, !empty($GLOBALS['xoopsModuleConfig']['show_realname']), true);
        $ret        = implode(', ', $moderators);

        return $ret;
    }
}

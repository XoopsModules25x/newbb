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

// defined('XOOPS_ROOT_PATH') || exit('Restricted access.');

defined('NEWBB_FUNCTIONS_INI') || include $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

/**
 * Class Ntext
 */
class Ntext extends XoopsObject
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->initVar('post_id', XOBJ_DTYPE_INT);
        $this->initVar('post_text', XOBJ_DTYPE_TXTAREA);
        $this->initVar('post_edit', XOBJ_DTYPE_SOURCE);
    }
}

/**
 * Class NewbbTextHandler
 */
class NewbbTextHandler extends XoopsPersistableObjectHandler
{
    /**
     * @param XoopsDatabase|null $db
     */
    public function __construct(XoopsDatabase $db)
    {
        parent::__construct($db, 'newbb_posts_text', 'Ntext', 'post_id', '');
    }

    /**
     * clean orphan items from database
     *
     * @param  string $table_link
     * @param  string $field_link
     * @param  string $field_object
     * @return bool   true on success
     */
    public function cleanOrphan($table_link = '', $field_link = '', $field_object = '') //cleanOrphan()
    {
        return parent::cleanOrphan($this->db->prefix('newbb_posts'), 'post_id');
    }
}

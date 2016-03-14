<?php
/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (http://xoops.org)
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @version        $Id $
 * @package        module::newbb
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

defined('NEWBB_FUNCTIONS_INI') || include $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');
newbb_load_object();

/**
 * Class Ntext
 */
class Ntext extends ArtObject
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct('bb_posts_text');
        $this->initVar('post_id', XOBJ_DTYPE_INT);
        $this->initVar('post_text', XOBJ_DTYPE_TXTAREA);
        $this->initVar('post_edit', XOBJ_DTYPE_SOURCE);
    }
}

/**
 * Class NewbbTextHandler
 */
class NewbbTextHandler extends ArtObjectHandler
{
    /**
     * @param XoopsDatabase $db
     */
    public function __construct(XoopsDatabase $db)
    {
        parent::__construct($db, 'bb_posts_text', 'Ntext', 'post_id', '');
    }

    /**
     * clean orphan items from database
     *
     * @param string $table_link
     * @param string $field_link
     * @param string $field_object
     * @return bool true on success
     */
    public function cleanOrphan($table_link = '', $field_link = '', $field_object = '') //cleanOrphan()
    {
        return parent::cleanOrphan($this->db->prefix('bb_posts'), 'post_id');
    }
}

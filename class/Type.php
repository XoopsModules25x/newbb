<?php namespace XoopsModules\Newbb;

/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

/**
 * Type
 *
 * @author    D.J. (phppp)
 * @copyright copyright &copy; 2006 XoopsForge.com
 * @package   module::newbb
 **/
class Type extends \XoopsObject
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->initVar('type_id', XOBJ_DTYPE_INT);
        $this->initVar('type_name', XOBJ_DTYPE_TXTBOX, '');
        $this->initVar('type_color', XOBJ_DTYPE_SOURCE, '');
        $this->initVar('type_description', XOBJ_DTYPE_TXTBOX, '');
    }
}

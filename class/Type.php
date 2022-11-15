<?php declare(strict_types=1);

namespace XoopsModules\Newbb;

/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 */

/**
 * Type
 *
 * @author    D.J. (phppp)
 * @copyright copyright &copy; 2006 XoopsForge.com
 **/
class Type extends \XoopsObject
{
    public $type_id;
    public $type_name;
    public $type_color;
    public $type_description;
    public function __construct()
    {
        parent::__construct();
        $this->initVar('type_id', \XOBJ_DTYPE_INT);
        $this->initVar('type_name', \XOBJ_DTYPE_TXTBOX, '');
        $this->initVar('type_color', \XOBJ_DTYPE_SOURCE, '');
        $this->initVar('type_description', \XOBJ_DTYPE_TXTBOX, '');
    }
}

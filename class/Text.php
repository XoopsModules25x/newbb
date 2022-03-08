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
\defined('NEWBB_FUNCTIONS_INI') || require $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

/**
 * Class Text
 */
class Text extends \XoopsObject
{
    public function __construct()
    {
        parent::__construct();
        $this->initVar('post_id', \XOBJ_DTYPE_INT);
        $this->initVar('post_text', \XOBJ_DTYPE_TXTAREA);
        $this->initVar('post_edit', \XOBJ_DTYPE_SOURCE);
    }
}

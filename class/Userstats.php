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

defined('NEWBB_FUNCTIONS_INI') || include $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

/**
 * Class Userstats
 */
class Userstats extends \XoopsObject
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->initVar('uid', XOBJ_DTYPE_INT);
        $this->initVar('user_topics', XOBJ_DTYPE_INT);
        $this->initVar('user_digests', XOBJ_DTYPE_INT);
        $this->initVar('user_posts', XOBJ_DTYPE_INT);
        $this->initVar('user_lastpost', XOBJ_DTYPE_INT);
    }
}

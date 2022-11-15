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
 * Class Userstats
 */
class Userstats extends \XoopsObject
{
    public $uid;
    public $user_topics;
    public $user_digests;
    public $user_posts;
    public $user_lastpost;

    public function __construct()
    {
        parent::__construct();
        $this->initVar('uid', \XOBJ_DTYPE_INT);
        $this->initVar('user_topics', \XOBJ_DTYPE_INT);
        $this->initVar('user_digests', \XOBJ_DTYPE_INT);
        $this->initVar('user_posts', \XOBJ_DTYPE_INT);
        $this->initVar('user_lastpost', \XOBJ_DTYPE_INT);
    }
}

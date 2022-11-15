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
 * A handler for User moderation management
 *
 *
 * @author        D.J. (phppp, https://xoopsforge.com)
 * @copyright     copyright (c) 2005 XOOPS.org
 */
class Moderate extends \XoopsObject
{
    public $mod_id;
    public $mod_start;
    public $mod_end;
    public $mod_desc;
    public $uid;
    public $ip;
    public $forum_id;

    public function __construct()
    {
        parent::__construct();
        $this->initVar('mod_id', \XOBJ_DTYPE_INT);
        $this->initVar('mod_start', \XOBJ_DTYPE_INT);
        $this->initVar('mod_end', \XOBJ_DTYPE_INT);
        $this->initVar('mod_desc', \XOBJ_DTYPE_TXTBOX);
        $this->initVar('uid', \XOBJ_DTYPE_INT);
        $this->initVar('ip', \XOBJ_DTYPE_TXTBOX);
        $this->initVar('forum_id', \XOBJ_DTYPE_INT);
    }
}

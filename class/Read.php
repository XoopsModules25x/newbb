<?php declare(strict_types=1);

namespace XoopsModules\Newbb;

/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */
//  ------------------------------------------------------------------------ //
//  Author: phppp (D.J., infomax@gmail.com)                                  //
//  URL: https://xoops.org                                                    //
//  Project: Article Project                                                 //
//  ------------------------------------------------------------------------ //

\defined('NEWBB_FUNCTIONS_INI') || require $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

/**
 * A handler for read/unread handling
 *
 *
 * @author        D.J. (phppp, https://xoopsforge.com)
 * @copyright     copyright (c) 2005 XOOPS.org
 */
class Read extends \XoopsObject
{
    public $read_id;
    public $uid;
    public $read_item;
    public $post_id;
    public $read_time;

    /**
     * @internal param $type
     */
    public function __construct()
    {
        // parent::__construct("newbb_reads_" . $type);
        parent::__construct();
        $this->initVar('read_id', \XOBJ_DTYPE_INT);
        $this->initVar('uid', \XOBJ_DTYPE_INT);
        $this->initVar('read_item', \XOBJ_DTYPE_INT);
        $this->initVar('post_id', \XOBJ_DTYPE_INT);
        $this->initVar('read_time', \XOBJ_DTYPE_INT);
    }
}

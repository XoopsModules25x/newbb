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

use XoopsModules\Newbb;

require_once __DIR__ . '/Read.php';

/**
 * A handler for read/unread handling
 *
 *
 * @author        D.J. (phppp, https://xoopsforge.com)
 * @copyright     copyright (c) 2005 XOOPS.org
 */
class Readtopic extends Newbb\Read
{
    public function __construct()
    {
        parent::__construct();
        //$this->initVar('forum_id', XOBJ_DTYPE_INT);
    }
}

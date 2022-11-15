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
 * Class Rate
 */
class Rate extends \XoopsObject
{
    public $ratingid;
    public $topic_id;
    public $ratinguser;
    public $rating;
    public $ratingtimestamp;
    public $ratinghostname;

    public function __construct()
    {
        parent::__construct();
        $this->initVar('ratingid', \XOBJ_DTYPE_INT);
        $this->initVar('topic_id', \XOBJ_DTYPE_INT);
        $this->initVar('ratinguser', \XOBJ_DTYPE_INT);
        $this->initVar('rating', \XOBJ_DTYPE_INT);
        $this->initVar('ratingtimestamp', \XOBJ_DTYPE_INT);
        $this->initVar('ratinghostname', \XOBJ_DTYPE_TXTBOX);
    }
}

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
 * Class Report
 */
class Report extends \XoopsObject
{
    public $report_id;
    public $post_id;
    public $reporter_uid;
    public $reporter_ip;
    public $report_time;
    public $report_text;
    public $report_result;
    public $report_memo;

    public function __construct()
    {
        parent::__construct();
        $this->initVar('report_id', \XOBJ_DTYPE_INT);
        $this->initVar('post_id', \XOBJ_DTYPE_INT);
        $this->initVar('reporter_uid', \XOBJ_DTYPE_INT);
        $this->initVar('reporter_ip', \XOBJ_DTYPE_TXTBOX);
        $this->initVar('report_time', \XOBJ_DTYPE_INT);
        $this->initVar('report_text', \XOBJ_DTYPE_TXTBOX);
        $this->initVar('report_result', \XOBJ_DTYPE_INT);
        $this->initVar('report_memo', \XOBJ_DTYPE_TXTBOX);
    }
}

<?php
/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (http://xoops.org)
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

defined('NEWBB_FUNCTIONS_INI') || include $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');
newbb_load_object();

/**
 * Class Report
 */
class Report extends XoopsObject
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct('bb_report');
        $this->initVar('report_id', XOBJ_DTYPE_INT);
        $this->initVar('post_id', XOBJ_DTYPE_INT);
        $this->initVar('reporter_uid', XOBJ_DTYPE_INT);
        $this->initVar('reporter_ip', XOBJ_DTYPE_INT);
        $this->initVar('report_time', XOBJ_DTYPE_INT);
        $this->initVar('report_text', XOBJ_DTYPE_TXTBOX);
        $this->initVar('report_result', XOBJ_DTYPE_INT);
        $this->initVar('report_memo', XOBJ_DTYPE_TXTBOX);
    }
}

/**
 * Class NewbbReportHandler
 */
//class NewbbReportHandler extends ArtObjectHandler
class NewbbReportHandler extends XoopsPersistableObjectHandler
{
    /**
     * @param XoopsDatabase $db
     */
    public function __construct(XoopsDatabase $db)
    {
        parent::__construct($db, 'bb_report', 'Report', 'report_id', '');
    }

    /**
     * @param $posts
     * @return array
     */
    public function getByPost($posts)
    {
        $ret = [];
        if (!$posts) {
            return $ret;
        }
        if (!is_array($posts)) {
            $posts = [$posts];
        }
        $post_criteria = new Criteria('post_id', '(' . implode(', ', $posts) . ')', 'IN');
        $ret           = $this->getAll($post_criteria);

        return $ret;
    }

    /**
     * @param  int    $forums
     * @param  string $order
     * @param  int    $perpage
     * @param         $start
     * @param  int    $report_result
     * @param  int    $report_id
     * @return array
     */
    public function getAllReports(
        $forums = 0,
        $order = 'ASC',
        $perpage = 0,
        &$start,
        $report_result = 0,
        $report_id = 0
    ) {
        if ('DESC' === $order) {
            $operator_for_position = '>';
        } else {
            $order                 = 'ASC';
            $operator_for_position = '<';
        }
        $order_criteria = " ORDER BY r.report_id $order";

        if ($perpage <= 0) {
            $perpage = 10;
        }
        if (empty($start)) {
            $start = 0;
        }
        $result_criteria = ' AND r.report_result = ' . $report_result;

        if (!$forums) {
            $forumCriteria = '';
        } elseif (!is_array($forums)) {
            $forums        = [$forums];
            $forumCriteria = ' AND p.forum_id IN (' . implode(',', $forums) . ')';
        }
        $tables_criteria = ' FROM ' . $this->db->prefix('bb_report') . ' r, ' . $this->db->prefix('bb_posts') . ' p WHERE r.post_id= p.post_id';

        if ($report_id) {
            $result = $this->db->query('SELECT COUNT(*) as report_count' . $tables_criteria . $forumCriteria . $result_criteria . " AND report_id $operator_for_position $report_id" . $order_criteria);
            if ($result) {
                $row = $this->db->fetchArray($result);
            }
            $position = $row['report_count'];
            $start    = (int)($position / $perpage) * $perpage;
        }

        $sql    = 'SELECT r.*, p.subject, p.topic_id, p.forum_id' . $tables_criteria . $forumCriteria . $result_criteria . $order_criteria;
        $result = $this->db->query($sql, $perpage, $start);
        $ret    = [];
        //$reportHandler = xoops_getModuleHandler('report', 'newbb');
        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $ret[] = $myrow; // return as array
        }

        return $ret;
    }

    /**
     *
     */
    public function synchronization()
    {
        //        return;
    }

    /**
     * clean orphan items from database
     *
     * @param  string $table_link
     * @param  string $field_link
     * @param  string $field_object
     * @return bool   true on success
     */
    public function cleanOrphan($table_link = '', $field_link = '', $field_object = '') //cleanOrphan()
    {
        return parent::cleanOrphan($this->db->prefix('bb_posts'), 'post_id');
    }
}

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
 * Class ReportHandler
 */
class ReportHandler extends \XoopsPersistableObjectHandler
{
    /**
     * @param \XoopsDatabase $db
     */
    public function __construct(\XoopsDatabase $db)
    {
        parent::__construct($db, 'newbb_report', Report::class, 'report_id', '');
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
        $post_criteria = new \Criteria('post_id', '(' . implode(', ', $posts) . ')', 'IN');
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
        $forumCriteria = '';
        $row           = [];
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

        if ($forums) {
            $forumCriteria = '';
        } elseif (!is_array($forums)) {
            $forums        = [$forums];
            $forumCriteria = ' AND p.forum_id IN (' . implode(',', $forums) . ')';
        }
        $tables_criteria = ' FROM ' . $this->db->prefix('newbb_report') . ' r, ' . $this->db->prefix('newbb_posts') . ' p WHERE r.post_id= p.post_id';

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
        //$reportHandler =  Newbb\Helper::getInstance()->getHandler('Report');
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
        return parent::cleanOrphan($this->db->prefix('newbb_posts'), 'post_id');
    }
}

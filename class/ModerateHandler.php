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

use Xmf\IPAddress;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

/**
 * Class ModerateHandler
 */
class ModerateHandler extends \XoopsPersistableObjectHandler
{
    /**
     * @param null|\XoopsDatabase $db
     */
    public function __construct(\XoopsDatabase $db)
    {
        parent::__construct($db, 'newbb_moderates', Moderate::class, 'mod_id', 'uid');
    }

    /**
     * Clear garbage
     *
     * Delete all moderation information that has expired
     *
     * @param int $expire Expiration time in UNIX, 0 for time()
     */
    public function clearGarbage($expire = 0)
    {
        $expire = time() - (int)$expire;
        $sql    = sprintf('DELETE FROM `%s` WHERE mod_end < %u', $this->db->prefix('newbb_moderates'), $expire);
        $this->db->queryF($sql);
    }

    /**
     * Check if a user is moderated, according to his uid and ip
     *
     *
     * @param  int    $uid user id
     * @param  string $ip  user ip
     * @param  int    $forum
     * @return bool true if IP is banned
     */
    public function verifyUser($uid = -1, $ip = '', $forum = 0)
    {
        error_reporting(E_ALL);
        // if user is admin do not suspend
        if (newbbIsAdmin($forum)) {
            return true;
        }

        $uid = ($uid < 0) ? (is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid') : 0) : (int)$uid;

        $criteria      = new \CriteriaCompo(new \Criteria('uid', (int)$uid));
        $forumCriteria = new \CriteriaCompo(new \Criteria('forum_id', 0), 'OR');
        if (!empty($forum)) {
            $forumCriteria->add(new \Criteria('forum_id', (int)$forum), 'OR');
        }
        $criteria->add($forumCriteria);
        $criteria->add(new \Criteria('mod_end', time(), '>'));

        $matches = $this->getAll($criteria);

        if (0 === count($matches)) {
            return true; // no matches
        }

        if (count($matches) > 0 && $uid > 0) {
            return false; // user is banned
        }
        // verify possible matches against IP address
        $ip = empty($ip) ? IPAddress::fromRequest()->asReadable() : $ip;

        foreach ($matches as $modMatch) {
            $rawModIp = trim($modMatch->getVar('ip', 'n'));
            if (empty($rawModIp)) {
                return false; // banned without IP
            }
            $parts   = explode('/', $rawModIp);
            $modIp   = $parts[0];
            $checkIp = new IPAddress($modIp);
            if (false !== $checkIp->asReadable()) {
                $defaultMask = (6 === $checkIp->ipVersion()) ? 128 : 32;
                $netMask     = isset($parts[1]) ? (int)$parts[1] : $defaultMask;
                if ($checkIp->sameSubnet($ip, $netMask, $netMask)) {
                    return false; // IP is banned
                }
            }
        }

        return true;
    }

    /**
     * Get latest expiration for a user moderation
     *
     *
     * @param  mixed  $item user id or ip
     * @param  bool $isUid
     * @return int
     */
    public function getLatest($item, $isUid = true)
    {
        $ips = [];
        if ($isUid) {
            $criteria = 'uid =' . (int)$item;
        } else {
            $ip_segs = explode('.', $item);
            $segs    = min(count($ip_segs), 4);
            for ($i = 1; $i <= $segs; ++$i) {
                $ips[] = $this->db->quoteString(implode('.', array_slice($ip_segs, 0, $i)));
            }
            $criteria = 'ip IN(' . implode(',', $ips) . ')';
        }
        $sql = 'SELECT MAX(mod_end) AS expire FROM ' . $this->db->prefix('newbb_moderates') . ' WHERE ' . $criteria;
        if (!$result = $this->db->query($sql)) {
            return -1;
        }
        $row = $this->db->fetchArray($result);

        return $row['expire'];
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
        $sql = 'DELETE FROM ' . $this->table . ' WHERE (forum_id >0 AND forum_id NOT IN ( SELECT DISTINCT forum_id FROM ' . $this->db->prefix('newbb_forums') . ') )';
        if (!$result = $this->db->queryF($sql)) {
            //xoops_error($this->db->error());
            return false;
        }

        return true;
    }
}

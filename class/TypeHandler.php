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

/**
 * Type object handler class.
 * @package   module::newbb
 *
 * @author    D.J. (phppp)
 * @copyright copyright &copy; 2006 XOOPS Project
 */
class TypeHandler extends \XoopsPersistableObjectHandler
{
    /**
     * @param null|\XoopsDatabase $db
     */
    public function __construct(\XoopsDatabase $db)
    {
        parent::__construct($db, 'newbb_type', Type::class, 'type_id', 'type_name');
    }

    /**
     * Get types linked to a forum
     *
     * @param  mixed $forums single forum ID or an array of forum IDs
     * @return array associative array of types (name, color, order)
     */
    public function getByForum($forums = null)
    {
        $ret = [];

        $forums = (is_array($forums) ? array_filter(array_map('intval', array_map('trim', $forums))) : (empty($forums) ? 0 : [(int)$forums]));

        $sql = '    SELECT o.type_id, o.type_name, o.type_color, l.type_order'
               . '     FROM '
               . $this->db->prefix('newbb_type_forum')
               . ' AS l '
               . "         LEFT JOIN {$this->table} AS o ON o.{$this->keyName} = l.{$this->keyName} "
               . '     WHERE '
               . '        l.forum_id '
               . (empty($forums) ? 'IS NOT NULL' : 'IN (' . implode(', ', $forums) . ')')
               . '         ORDER BY l.type_order ASC';
        if (false === ($result = $this->db->query($sql))) {
            //xoops_error($this->db->error());
            return $ret;
        }

        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $ret[$myrow[$this->keyName]] = [
                'type_id'    => $myrow[$this->keyName],
                'type_order' => $myrow['type_order'],
                'type_name'  => htmlspecialchars($myrow['type_name']),
                'type_color' => htmlspecialchars($myrow['type_color'])
            ];
        }

        return $ret;
    }

    /**
     * Update types linked to a forum
     *
     * @param  integer $forum_id
     * @param  array   $types
     * @return boolean
     */
    public function updateByForum($forum_id, $types)
    {
        $forum_id = (int)$forum_id;
        if (empty($forum_id)) {
            return false;
        }

        $types_existing = $this->getByForum($forum_id);
        $types_valid    = [];
        $types_add      = [];
        $types_update   = [];
        foreach (array_keys($types_existing) as $key) {
            if (empty($types[$key])) {
                continue;
            }
            $types_valid[] = $key;
            if ($types[$key] !== $types_existing[$key]['type_order']) {
                $types_update[] = $key;
            }
        }
        foreach (array_keys($types) as $key) {
            if (!empty($types[$key]) && !isset($types_existing[$key])) {
                $types_add[] = $key;
            }
        }
        $types_valid  = array_filter($types_valid);
        $types_add    = array_filter($types_add);
        $types_update = array_filter($types_update);

        if (!empty($types_valid)) {
            $sql = 'DELETE FROM ' . $this->db->prefix('newbb_type_forum') . ' WHERE ' . ' forum_id = ' . $forum_id . ' AND ' . // irmtfan bug fix: delete other forums types when update the type for a specific forum
                   "     {$this->keyName} NOT IN (" . implode(', ', $types_valid) . ')';
            if (false === ($result = $this->db->queryF($sql))) {
            }
        }

        if (!empty($types_update)) {
            $type_query = [];
            foreach ($types_update as $key) {
                $order = $types[$key];
                if ($types_existing[$key]['type_order'] == $order) {
                    continue;
                }
                $sql = 'UPDATE ' . $this->db->prefix('newbb_type_forum') . " SET type_order = {$order}" . " WHERE  {$this->keyName} = {$key} AND forum_id = {$forum_id}";
                if (false === ($result = $this->db->queryF($sql))) {
                }
            }
        }

        if (!empty($types_add)) {
            $type_query = [];
            foreach ($types_add as $key) {
                $order = $types[$key];
                //if (!in_array($key, $types_add)) continue;
                $type_query[] = "({$key}, {$forum_id}, {$order})";
            }
            $sql = 'INSERT INTO ' . $this->db->prefix('newbb_type_forum') . ' (type_id, forum_id, type_order) ' . ' VALUES ' . implode(', ', $type_query);
            if (false === ($result = $this->db->queryF($sql))) {
                //xoops_error($this->db->error());
            }
        }

        return true;
    }

    /**
     * delete an object as well as links relying on it
     *
     * @param \XoopsObject $object {@link Type}
     * @param  bool        $force  flag to force the query execution despite security settings
     * @return bool
     */
    public function delete(\XoopsObject $object, $force = true)
    {
        if (!is_object($object) || !$object->getVar($this->keyName)) {
            return false;
        }
        $queryFunc = empty($force) ? 'query' : 'queryF';

        /*
         * Remove forum-type links
         */
        $sql = 'DELETE' . ' FROM ' . $this->db->prefix('newbb_type_forum') . ' WHERE  ' . $this->keyName . ' = ' . $object->getVar($this->keyName);
        if (false === ($result = $this->db->{$queryFunc}($sql))) {
            // xoops_error($this->db->error());
        }

        /*
         * Reset topic type linked to this type
         */
        $sql = 'UPATE' . ' ' . $this->db->prefix('newbb_topics') . ' SET ' . $this->keyName . '=0' . ' WHERE  ' . $this->keyName . ' = ' . $object->getVar($this->keyName);
        if (false === ($result = $this->db->{$queryFunc}($sql))) {
            //xoops_error($this->db->error());
        }

        return parent::delete($object, $force);
    }

    /**
     * clean orphan links from database
     *
     * @param  string $table_link
     * @param  string $field_link
     * @param  string $field_object
     * @return bool   true on success
     */
    public function cleanOrphan($table_link = '', $field_link = '', $field_object = '') //cleanOrphan()
    {
        /* clear forum-type links */
        $sql = 'DELETE FROM ' . $this->db->prefix('newbb_type_forum') . " WHERE ({$this->keyName} NOT IN ( SELECT DISTINCT {$this->keyName} FROM {$this->table}) )";
        $this->db->queryF($sql);

        /* reconcile topic-type link */
        $sql = 'UPATE ' . $this->db->prefix('newbb_topics') . " SET {$this->keyName} = 0" . " WHERE ({$this->keyName} NOT IN ( SELECT DISTINCT {$this->keyName} FROM {$this->table}) )";
        $this->db->queryF($sql);

        return true;
    }
}

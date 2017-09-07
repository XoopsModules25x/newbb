<?php
/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */

// defined('XOOPS_ROOT_PATH') || exit('Restricted access.');

defined('NEWBB_FUNCTIONS_INI') || include $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');

/**
 * Class NewbbUserstats
 */
class NewbbUserstats extends XoopsObject
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->initVar('uid', XOBJ_DTYPE_INT);
        $this->initVar('user_topics', XOBJ_DTYPE_INT);
        $this->initVar('user_digests', XOBJ_DTYPE_INT);
        $this->initVar('user_posts', XOBJ_DTYPE_INT);
        $this->initVar('user_lastpost', XOBJ_DTYPE_INT);
    }
}

/**
 * user stats
 *
 */
class NewbbUserstatsHandler extends XoopsPersistableObjectHandler
{
    /**
     * @param XoopsDatabase|null $db
     */
    public function __construct(XoopsDatabase $db)
    {
        parent::__construct($db, 'newbb_user_stats', 'NewbbUserstats', 'uid', '');
    }

    /**
     * @param  null $db
     * @return NewbbUserstatsHandler
     */
    public static function getInstance($db = null)
    {
        static $instance;
        if (null === $instance) {
            $instance = new static($db);
        }

        return $instance;
    }

    /**
     * @param  mixed $id
     * @param  null  $fields
     * @return null|XoopsObject
     */
    public function get($id = null, $fields = null) //get($id)
    {
        $object = null;
        if (!$id = (int)$id) {
            return $object;
        }
        $object = $this->create(false);
        $object->setVar($this->keyName, $id);
        if (!$row = $this->getStats($id)) {
            return $object;
        }
        $object->assignVars($row);

        /*
        $sql = "SELECT * FROM " . $this->table . " WHERE ".$this->keyName." = " . $id;
        if (!$result = $this->db->query($sql)) {
            return $object;
        }
        while ($row = $this->db->fetchArray($result)) {
            $object->assignVars($row);
        }
        */

        return $object;
    }

    /**
     * @param $id
     * @return null|array
     */
    public function getStats($id)
    {
        if (empty($id)) {
            return null;
        }
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE ' . $this->keyName . ' = ' . (int)$id;
        if (!$result = $this->db->query($sql)) {
            return null;
        }
        $row = $this->db->fetchArray($result);

        return $row;
    }
    /*
        function insert(XoopsObject $object, $force = true)
        {
            if (!$object->isDirty()) {
                $object->setErrors("not isDirty");

                return $object->getVar($this->keyName);
            }
            $this->loadHandler("write");
            if (!$changedVars = $this->_handler["write"]->cleanVars($object)) {
                $object->setErrors("cleanVars failed");

                return $object->getVar($this->keyName);
            }
            $queryFunc = empty($force) ? "query" : "queryF";

            $keys = array();
            foreach ($changedVars as $k => $v) {
                $keys[] = " {$k} = {$v}";
            }
            $sql = "REPLACE INTO " . $this->table . " SET ".implode(",",$keys);
            if (!$result = $this->db->{$queryFunc}($sql)) {
                $object->setErrors("update object error:" . $sql);

                return false;
            }
            unset($changedVars);

            return $object->getVar($this->keyName);
        }
    */
}

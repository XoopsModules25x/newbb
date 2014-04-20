<?php
/**
 * CBB 4.0, or newbb, the forum module for XOOPS project
 *
 * @copyright	The XOOPS Project http://xoops.sf.net
 * @license		http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author		Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since		4.00
 * @version		$Id $
 * @package		module::newbb
 */

if (!defined('XOOPS_ROOT_PATH')) { exit(); }

defined("NEWBB_FUNCTIONS_INI") || include XOOPS_ROOT_PATH.'/modules/newbb/include/functions.ini.php';
newbb_load_object();

class NewbbUserstats extends ArtObject 
{
    function NewbbUserstats()
    {
        $this->ArtObject("bb_user_stats");
        $this->initVar('uid', 				XOBJ_DTYPE_INT);
        $this->initVar('user_topics', 		XOBJ_DTYPE_INT);
        $this->initVar('user_digests', 		XOBJ_DTYPE_INT);
        $this->initVar('user_posts', 		XOBJ_DTYPE_INT);
        $this->initVar('user_lastpost', 	XOBJ_DTYPE_INT);
    }
}



/**
 * user stats
 *
 */
class NewbbUserstatsHandler extends ArtObjectHandler
{
    function NewbbUserstatsHandler(&$db) {
        $this->ArtObjectHandler($db, 'bb_user_stats', 'NewbbUserstats', 'uid');
    }
	
	function &instance($db = null)
	{
		static $instance;
		if (!isset($instance)) {
			$instance = new NewbbUserstatsHandler($db);
		}
		return $instance;
	}
	
	function &get($id)
	{
    	$object = null;
	    if (!$id = intval($id)) {
		    return $object;
	    }
    	$object =& $this->create(false);
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
	
	function getStats($id)
	{
		if (empty($id)) return null;
        $sql = "SELECT * FROM " . $this->table . " WHERE ".$this->keyName." = " . intval($id);
        if (!$result = $this->db->query($sql)) {
            return null;
        }
        $row = $this->db->fetchArray($result);

        return $row;
	}
/*	
	function insert(&$object, $force = true)
	{
        if (!$object->isDirty()) {
	        $object->setErrors("not isDirty");
	        return $object->getVar($this->keyName);
        }
	    $this->_loadHandler("write");
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

?>
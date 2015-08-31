<?php

/**
 * Class NewbbUtilities
 */
class NewbbUtilities
{
    /**
     * Verify that a mysql table exists
     *
     * @package       News
     * @author        Hervé Thouzard (http://www.herve-thouzard.com)
     * @copyright (c) Hervé Thouzard
     * @param $tablename
     * @return bool
     */
    public function tableExists($tablename)
    {
        global $xoopsDB;
        $result = $xoopsDB->queryF("SHOW TABLES LIKE '$tablename'");

        return ($xoopsDB->getRowsNum($result) > 0);
    }

    /**
     * Verify that a field exists inside a mysql table
     *
     * @package       News
     * @author        Hervé Thouzard (http://www.herve-thouzard.com)
     * @copyright (c) Hervé Thouzard
     * @param $fieldname
     * @param $table
     * @return bool
     */
    public function fieldExists($fieldname, $table)
    {
        global $xoopsDB;
        $result = $xoopsDB->queryF("SHOW COLUMNS FROM   $table LIKE '$fieldname'");

        return ($xoopsDB->getRowsNum($result) > 0);
    }

    /**
     * Add a field to a mysql table
     *
     * @package       News
     * @author        Hervé Thouzard (http://www.herve-thouzard.com)
     * @copyright (c) Hervé Thouzard
     * @param $field
     * @param $table
     * @return
     */
    public function addField($field, $table)
    {
        global $xoopsDB;
        $result = $xoopsDB->queryF('ALTER TABLE ' . $table . " ADD $field;");

        return $result;
    }
}

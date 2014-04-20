<?php
/**
 * CBB, XOOPS forum module
 *
 * @copyright	The XOOPS Project http://xoops.sf.net
 * @license		http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author		Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since		4.00
 * @version		$Id $
 * @package		module::newbb
 */
 
if (!defined("XOOPS_ROOT_PATH")) {
	exit();
}

include_once XOOPS_ROOT_PATH . "/class/uploader.php";

class newbb_uploader extends XoopsMediaUploader {

    /**
     * No admin check for uploads
     */
    /**
     * Constructor
     *
     * @param string 	$uploadDir
     * @param array 	$allowedMimeTypes
     * @param int 		$maxFileSize
     * @param int 		$maxWidth
     * @param int 		$maxHeight
     */
    function newbb_uploader($uploadDir, $allowedMimeTypes = 0, $maxFileSize = 0, $maxWidth = 0, $maxHeight = 0)
    {
        if (!is_array($allowedMimeTypes)) {
	        if (empty($allowedMimeTypes) || $allowedMimeTypes == "*") {
                $allowedMimeTypes = array();
	        } else {
	            $allowedMimeTypes = array_filter(array_map("trim", explode("|", strtolower($allowedMimeTypes))));
            }
        }
        $_allowedMimeTypes = array();
		$extensionToMime = include $GLOBALS['xoops']->path('/include/mimetypes.inc.php');
        foreach ($allowedMimeTypes as $type) {
            if (isset($extensionToMime[$type])) {
	            $_allowedMimeTypes[] = $extensionToMime[$type];
            } else {
	            $_allowedMimeTypes[] = $type;
            }
        }
        $this->XoopsMediaUploader($uploadDir, $_allowedMimeTypes, $maxFileSize, $maxWidth, $maxHeight);
    }

    /**
     * Set the CheckMediaTypeByExt
     * Deprecated
     *
     * @param string $value
     */
    function setCheckMediaTypeByExt($value = true)
    {
    }

    /**
     * Set the imageSizeCheck
     * Deprecated
     *
     * @param string $value
     */
    function setImageSizeCheck($value)
    {
    }

    /**
     * Set the fileSizeCheck
     * Deprecated
     *
     * @param string $value
     */
    function setFileSizeCheck($value)
    {
    }

    /**
     * Get the file extension
     *
     * @return string
     */
    function getExt()
    {
        $this->ext = strtolower(ltrim(strrchr($this->getMediaName(), '.'), '.'));
        return $this->ext;
    }
}

?>
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
 
if (!defined("XOOPS_ROOT_PATH")) {
	exit();
}

defined("NEWBB_FUNCTIONS_INI") || include XOOPS_ROOT_PATH.'/modules/newbb/include/functions.ini.php';
load_functions("locale");

/**
 * Description
 *
 * @param type $var description
 * @return type description
 * @link
 */
class Xmlrss {

    var $xml_version;
    var $rss_version;
    var $xml_encoding;

    var $channel_title;
    var $channel_link;
    var $channel_desc;
	var $channel_lastbuild;
	var $channel_webmaster;
	var $channel_editor;
	var $channel_category;
	var $channel_generator;
	var $channel_language;

    var $image_title;
    var $image_url;
    var $image_link;
    var $image_description;
    var $image_height;
    var $image_width;

    var $max_items;
    var $max_item_description;
    var $items = array();

    function Xmlrss()
    {
	    global $xoopsModuleConfig;

        $this->xml_version = '1.0';
        $this->xml_encoding = empty($xoopsModuleConfig['rss_utf8'])?_CHARSET:'UTF-8';
        $this->rss_version = '2.0';
        $this->image_height = 31;
        $this->image_width = 88;
        $this->max_items = 10;
        $this->max_item_description = 0;
        $this->items = array();
    }

    function setVarRss($var, $val)
    {
        $this->$var = $this->cleanup($val);
    }

    function addItem($title, $link, $description = '', $label = '', $pubdate = 0)
    {
        if (count($this->items) < $this->max_items) {
            if (!empty($label)) {
                $label = '[' . $this->cleanup($label) . ']';
            }
            if (!empty($description)) {
                $description = $this->cleanup($description, $this->max_item_description);
                //$description .= ' ' . $label;
            } else {
                //$description = $label;
            }

            $title = $this->cleanup($title).' ' . $label;
            $pubdate = $this->cleanup($pubdate);
            $this->items[] = array('title' => $title, 'link' => $link, 'guid' => $link, 'description' => $description, 'pubdate'=>$pubdate);
        }
        return true;
    }

    function cleanup($text, $trim = 0)
    {
        if (strtolower($this->xml_encoding) == "utf-8" && strncasecmp(_CHARSET, $this->xml_encoding, 5)) {
        	$text = XoopsLocal::convert_encoding($text, "utf-8");
    	}
        if (!empty($trim)) {
	        $text = xoops_substr($text, 0, intval($trim));
        }
        $text = htmlspecialchars($text, ENT_QUOTES);

        return $text;
    }
}

class NewbbXmlrssHandler
{
    function &create()
    {
        $xmlrss = new Xmlrss();
        return $xmlrss;
    }

    function &get(&$rss)
    {
	    $rss_array = array();
		$rss_array['xml_version'] = $rss->xml_version;
		$rss_array['xml_encoding'] = $rss->xml_encoding;
		$rss_array['rss_version'] = $rss->rss_version;
		$rss_array['channel_title'] = $rss->channel_title;
		$rss_array['channel_link'] = $rss->channel_link;
		$rss_array['channel_desc'] = $rss->channel_desc;
		$rss_array['channel_lastbuild'] = $rss->channel_lastbuild;
		$rss_array['channel_webmaster'] = $rss->channel_webmaster;
		$rss_array['channel_editor'] = $rss->channel_editor;
		$rss_array['channel_category'] = $rss->channel_category;
		$rss_array['channel_generator'] = $rss->channel_generator;
		$rss_array['channel_language'] = $rss->channel_language;
		$rss_array['image_title'] = $rss->channel_title;
		$rss_array['image_url'] = $rss->image_url;
		$rss_array['image_link'] = $rss->channel_link;
		$rss_array['image_width'] = $rss->image_width;
		$rss_array['image_height'] = $rss->image_height;
		$rss_array['items'] = $rss->items;

		return $rss_array;
    }

}

?>
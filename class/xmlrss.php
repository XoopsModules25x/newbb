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
//load_functions('locale');

/**
 * Description
 *
 * @param  type $var description
 * @return type description
 * @link
 */
class Xmlrss
{
    public $xml_version;
    public $rss_version;
    public $xml_encoding;

    public $channel_title;
    public $channel_link;
    public $channel_desc;
    public $channel_lastbuild;
    public $channel_webmaster;
    public $channel_editor;
    public $channel_category;
    public $channel_generator;
    public $channel_language;

    public $image_title;
    public $image_url;
    public $image_link;
    public $image_description;
    public $image_height;
    public $image_width;

    public $max_items;
    public $max_item_description;
    public $items = [];

    /**
     *
     */
    public function __construct()
    {
        $this->xml_version          = '1.0';
        $this->xml_encoding         = empty($GLOBALS['xoopsModuleConfig']['rss_utf8']) ? _CHARSET : 'UTF-8';
        $this->rss_version          = '2.0';
        $this->image_height         = 31;
        $this->image_width          = 88;
        $this->max_items            = 10;
        $this->max_item_description = 0;
        $this->items                = [];
    }

    /**
     * @param $var
     * @param $val
     */
    public function setVarRss($var, $val)
    {
        $this->$var = $this->cleanup($val);
    }

    /**
     * @param         $title
     * @param         $link
     * @param  string $description
     * @param  string $label
     * @param  int    $pubdate
     * @return bool
     */
    public function addItem($title, $link, $description = '', $label = '', $pubdate = 0)
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

            $title         = $this->cleanup($title) . ' ' . $label;
            $pubdate       = $this->cleanup($pubdate);
            $this->items[] = [
                'title'       => $title,
                'link'        => $link,
                'guid'        => $link,
                'description' => $description,
                'pubdate'     => $pubdate
            ];
        }

        return true;
    }

    /**
     * @param               $text
     * @param  int          $trim
     * @return mixed|string
     */
    public function cleanup($text, $trim = 0)
    {
        if (strtolower($this->xml_encoding) === 'utf-8' && strncasecmp(_CHARSET, $this->xml_encoding, 5)) {
            $text = XoopsLocal::convert_encoding($text, 'utf-8');
        }
        if (!empty($trim)) {
            $text = xoops_substr($text, 0, (int)$trim);
        }
        $text = htmlspecialchars($text, ENT_QUOTES);

        return $text;
    }
}

/**
 * Class NewbbXmlrssHandler
 */
class NewbbXmlrssHandler
{
    /**
     * @return Xmlrss
     */
    public function &create()
    {
        $xmlrss = new Xmlrss();

        return $xmlrss;
    }

    /**
     * @param $rss
     * @return array
     */
    public function &get(Xmlrss $rss)
    {
        $rss_array                      = [];
        $rss_array['xml_version']       = $rss->xml_version;
        $rss_array['xml_encoding']      = $rss->xml_encoding;
        $rss_array['rss_version']       = $rss->rss_version;
        $rss_array['channel_title']     = $rss->channel_title;
        $rss_array['channel_link']      = $rss->channel_link;
        $rss_array['channel_desc']      = $rss->channel_desc;
        $rss_array['channel_lastbuild'] = $rss->channel_lastbuild;
        $rss_array['channel_webmaster'] = $rss->channel_webmaster;
        $rss_array['channel_editor']    = $rss->channel_editor;
        $rss_array['channel_category']  = $rss->channel_category;
        $rss_array['channel_generator'] = $rss->channel_generator;
        $rss_array['channel_language']  = $rss->channel_language;
        $rss_array['image_title']       = $rss->channel_title;
        $rss_array['image_url']         = $rss->image_url;
        $rss_array['image_link']        = $rss->channel_link;
        $rss_array['image_width']       = $rss->image_width;
        $rss_array['image_height']      = $rss->image_height;
        $rss_array['items']             = $rss->items;

        return $rss_array;
    }
}

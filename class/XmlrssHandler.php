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

use XoopsModules\Newbb;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

defined('NEWBB_FUNCTIONS_INI') || include $GLOBALS['xoops']->path('modules/newbb/include/functions.ini.php');
//load_functions('locale');

/**
 * Class XmlrssHandler
 */
class XmlrssHandler
{
    /**
     * @return Xmlrss
     */
    public function create()
    {
        $xmlrss = new Newbb\Xmlrss();

        return $xmlrss;
    }

    /**
     * @param $rss
     * @return array
     */
    public function get(Xmlrss $rss)
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

<?php
/**
 * CBB 4.0, or newbb, the forum module for XOOPS project
 *
 * @copyright    The XOOPS Project http://xoops.sf.net
 * @license        http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author        Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since        4.00
 * @version        $Id $
 * @package        module::newbb
 */

// defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

defined("NEWBB_FUNCTIONS_INI") || include __DIR__ . "/functions.ini.php";
define("NEWBB_FUNCTIONS_RENDER_LOADED", true);

if (!defined("NEWBB_FUNCTIONS_RENDER")) {
    define("NEWBB_FUNCTIONS_RENDER", 1);

    /*
     * Sorry, we have to use the stupid solution unless there is an option in MyTextSanitizer:: htmlspecialchars();
     */
    function newbb_htmlSpecialChars($text)
    {
        return preg_replace(array("/&amp;/i", "/&nbsp;/i"), array('&', '&amp;nbsp;'), htmlspecialchars($text));
    }

    function &newbb_displayTarea(&$text, $html = 0, $smiley = 1, $xcode = 1, $image = 1, $br = 1)
    {
        global $myts;

        if ($html != 1) {
            // html not allowed
            $text = newbb_htmlSpecialChars($text);
        }
        $text = $myts->codePreConv($text, $xcode); // Ryuji_edit(2003-11-18)
        $text = $myts->makeClickable($text);
        if ($smiley != 0) {
            // process smiley
            $text = $myts->smiley($text);
        }
        if ($xcode != 0) {
            // decode xcode
            if ($image != 0) {
                // image allowed
                $text = $myts->xoopsCodeDecode($text);
            } else {
                // image not allowed
                $text = $myts->xoopsCodeDecode($text, 0);
            }
        }
        if ($br != 0) {
            $text = $myts->nl2Br($text);
        }
        $text = $myts->codeConv($text, $xcode, $image);    // Ryuji_edit(2003-11-18)

        return $text;
    }

    function newbb_html2text($document)
    {
        $text = strip_tags($document);

        return $text;
    }

    /**
     * Display forrum button
     *
     * @param  string $image image/button name, without extension
     * @param  string $alt alt message
     * @param  boolean $asImage true for image mode; false for text mode
     * @param  string $extra extra attribute for the button
     * @return mixed
     */
    function newbb_getButton($link, $button, $alt = "", $asImage = true, $extra = "class='forum_button'")
    {
        if (empty($asImage)) {
            $button = "<a href='{$link}' title='{$alt}' {$extra}>" . newbb_displayImage($button, $alt, true) . "</a>";
        } else {
            $button = "<input type='button' name='{$button}' {$extra} value='{$alt}' onclick='window.location.href={$link}' />";
        }

        return $button;
    }

    /**
     * Display forrum images
     *
     * @param  string $image image name, without extension
     * @param  string $alt alt message
     * @param  boolean $display true for return image anchor; faulse for assign to $xoopsTpl
     * @param  string $extra extra attribute for the image
     * @return mixed
     */
    function newbb_displayImage($image, $alt = "", $display = true, $extra = "class='forum_icon'")
    {
        $icon_handler = newbb_getIconHandler();
        // START hacked by irmtfan
        // to show text links instead of buttons - func_num_args()==2 => only when $image, $alt is set and optional $display not set
        global $xoopsModuleConfig;
        if (func_num_args() == 2) {
            // overall setting
            if (!empty($xoopsModuleConfig['display_text_links'])) {
                $display = false;
            }
            // if set for each link => overwrite $display
            if (isset($xoopsModuleConfig['display_text_each_link'][$image])) {
                $display = empty($xoopsModuleConfig['display_text_each_link'][$image]);
            }
        }
        // END hacked by irmtfan
        if (empty($display)) {
            return $icon_handler->assignImage($image, $alt, $extra);
        } else {
            return $icon_handler->getImage($image, $alt, $extra);
        }
    }

    function newbb_getIconHandler()
    {
        global $xoTheme, $xoopsConfig;
        static $icon_handler;

        if (isset($icon_handler)) return $icon_handler;

        if (!class_exists("NewbbIconHandler")) {
            require_once dirname(__DIR__) . "/class/icon.php";
        }

        $icon_handler           = NewbbIconHandler::instance();
        $icon_handler->template =& $xoTheme->template;
        $icon_handler->init($xoopsConfig["language"]);

        return $icon_handler;
    }

}

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

// defined("XOOPS_ROOT_PATH") || exit("XOOPS root path not defined");

/**
 * Set forum image
 *
 * Priority for path per types:
 * 	NEWBB_ROOT 	-	IF EXISTS XOOPS_THEME/modules/newbb/images/, TAKE IT;
 *					ELSEIF EXISTS  XOOPS_THEME_DEFAULT/modules/newbb/assets/images/, TAKE IT;
 *					ELSE TAKE  XOOPS_ROOT/modules/newbb/templates/images/.
 * 	types:
 *		button/misc	- 	language specified;
 *		//indicator	-	language specified;
 *		icon		-	universal;
 *		mime		-	universal;
 */

/**
 * Icon Renderer
 *
 * @author D.J. (phppp)
 * @copyright copyright &copy; Xoops Project
 * @package module::newbb
 *
 */

class NewbbIconHandler
{
    /**
     * reference to XOOPS template
     */
    var $template;

    /**
     * image set
     */
    var $forumImage = array();

    /**
     * prefix
     */
    var $prefix = "";

    /**
     * postfix, including extension
     */
    //var $postfix = ".gif";
    var $postfix = ".png";

    /**
     * images to be assigned to template
     */
    var $images = array();

    /**
     * Constructor
     */
    function NewbbIconHandler()
    {
    }

    /**
     * Access the only instance of this class
     *
     * @return
     **/
    function &instance()
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new NewbbIconHandler();
        }

        return $instance;
    }

    /**
     * TODO: get compatible with new theme engine
     */
     // START irmtfan - improve to get other "end dirnames" like "css" and "js" - change images with $end_dir
    function getPath(/*$set, */$type, $dirname = "newbb", $default = "", $end_dir = "images")
    {
        global $xoopsConfig;
        static $paths;
        if (isset($paths[$end_dir . '/' . $type])) {
            return $paths[$end_dir . '/' . $type];
        }

        $theme_path = $this->template->currentTheme->path;
        $rel_dir = "modules/{$dirname}/{$end_dir}";
        // START irmtfan add default for all pathes
        if (empty($default)) {
            $path = is_dir($theme_path."/{$rel_dir}/{$type}/")
                        ? $theme_path."/{$rel_dir}/{$type}"
                        : ( is_dir(XOOPS_THEME_PATH."/default/{$rel_dir}/{$type}/")
                            ? XOOPS_THEME_PATH."/default/{$rel_dir}/{$type}"
                            : XOOPS_ROOT_PATH."/modules/{$dirname}/templates/{$end_dir}/{$type}"
                        );
        } else {
            $path = is_dir($theme_path."/{$rel_dir}/{$type}/")
                        ? $theme_path."/{$rel_dir}/{$type}"
                        :	( is_dir($theme_path."/{$rel_dir}/{$default}/")
                                ? $theme_path."/{$rel_dir}/{$default}"
                                :	( is_dir(XOOPS_THEME_PATH."/default/{$rel_dir}/{$type}/")
                                        ? XOOPS_THEME_PATH."/default/{$rel_dir}/{$type}"
                                        :	( is_dir(XOOPS_THEME_PATH."/default/{$rel_dir}/{$default}/")
                                                ? XOOPS_THEME_PATH."/default/{$rel_dir}/{$default}"
                                                :	( is_dir(XOOPS_ROOT_PATH."/modules/{$dirname}/templates/{$end_dir}/{$type}/")
                                                        ? XOOPS_ROOT_PATH."/modules/{$dirname}/templates/{$end_dir}/{$type}"
                                                        : XOOPS_ROOT_PATH."/modules/{$dirname}/templates/{$end_dir}/{$default}"
                                                ) // XOOPS_ROOT_PATH
                                        ) // XOOPS_THEME_PATH {$default}
                                ) // XOOPS_THEME_PATH
                        ); // $theme_path {$default}
        }
        // END irmtfan add default for all pathes
        $paths[$end_dir . '/' . $type] = str_replace(XOOPS_ROOT_PATH, "", $path);

        return $paths[$end_dir . '/' . $type];
    }
     // END irmtfan - improve to get other "end dirnames" like "css" and "js" - change images with $end_dir

    function init(/*$set = "default", */$language = "english", $dirname = "newbb")
    {
        $this->forumImage = include XOOPS_ROOT_PATH."/modules/{$dirname}/include/images.php";

        $this->forumImage['icon'] = XOOPS_URL.$this->getPath(/*$set, */"icon", $dirname) . "/";
        $this->forumImage['language'] = XOOPS_URL.$this->getPath(/*$set, */"language/{$language}", $dirname, "language/english") . "/";
    }

    function setImage($image, $alt = "", $extra = "")
    {
        if (!isset($this->images[$image])) {
            $image_src = $this->getImageSource($image);
            // irmtfan add id={$image}
            $this->images[$image] = "<img src=\"{$image_src}\" alt=\"{$alt}\" title=\"{$alt}\" align=\"middle\" {$extra} id={$image} />";
        }
    }

    /**
     * TODO: How about image not exist?
     */
    function getImageSource($image)
    {
        return $this->forumImage[$this->forumImage[$image]].$this->prefix.$image.$this->postfix;
    }

    function getImage($image, $alt = "", $extra = "")
    {
        $this->setImage($image, $alt, $extra);

        return $this->images[$image];
    }

    function assignImage($image, $alt = "", $extra = "")
    {
        $this->setImage($image, $alt, $extra);
        // START hacked by irmtfan - improve function to CSS3 buttons - add alt and title attributes - use span instead of button to support IE7&8
        $tag="span";
        if (in_array( substr($image,0,2),array('t_','p_','up') ) && $extra === "class='forum_icon'") {
            $extra ="class='forum_icon forum_button'";
        }

        return "<{$tag} alt=\"{$alt}\" title=\"{$alt}\" align=\"middle\" {$extra} id={$image}>$alt</{$tag}>";
        // END hacked by irmtfan - improve function to CSS3 buttons
    }

    function assignImages($images)
    {
        foreach ($images as $_image) {
            list($image, $alt, $extra) = $_image;
            $this->assignImage($image, $alt, $extra);
        }
    }

    function render()
    {
        //$this->template->assign_by_ref("image", $this->images);
        $this->template->assign($this->images);

        return count($this->images);
    }

}

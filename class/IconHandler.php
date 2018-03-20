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
 * Set forum image
 *
 * Priority for path per types:
 *    NEWBB_ROOT    -    IF EXISTS XOOPS_THEME/modules/newbb/images/, TAKE IT;
 *                    ELSEIF EXISTS  XOOPS_THEME_DEFAULT/modules/newbb/assets/images/, TAKE IT;
 *                    ELSE TAKE  XOOPS_ROOT/modules/newbb/templates/images/.
 *    types:
 *        button/misc    -    language specified;
 *        //indicator    -    language specified;
 *        icon        -    universal;
 *        mime        -    universal;
 */

/**
 * Icon Renderer
 *
 * @author    D.J. (phppp)
 * @copyright copyright &copy; Xoops Project
 * @package   module::newbb
 *
 */
class IconHandler
{
    /**
     * reference to XOOPS template
     */
    public $template;

    /**
     * image set
     */
    public $forumImage = [];

    /**
     * prefix
     */
    public $prefix = '';

    /**
     * postfix, including extension
     */
    //var $postfix = ".gif";
    public $postfix = '.png';

    /**
     * images to be assigned to template
     */
    public $images = [];

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Access the only instance of this class
     * @return IconHandler
     */
    public static function getInstance()
    {
        static $instance;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    /**
     * TODO: get compatible with new theme engine
     * @param         $type
     * @param  string $dirname
     * @param  string $default
     * @param  string $endDir
     * @return mixed
     */
    // START irmtfan - improve to get other "end dirnames" like "css" and "js" - change images with $endDir
    public function getPath($type, $dirname = 'newbb', $default = '', $endDir = 'images')
    {
        static $paths;
        if (isset($paths[$endDir . '/' . $type])) {
            return $paths[$endDir . '/' . $type];
        }

        $theme_path = $this->template->currentTheme->path;
        $rel_dir    = "modules/{$dirname}/{$endDir}";
        // START irmtfan add default for all pathes
        if (empty($default)) {
            $path = is_dir($theme_path . "/{$rel_dir}/{$type}/") ? $theme_path . "/{$rel_dir}/{$type}"
                : (is_dir(XOOPS_THEME_PATH . "/default/{$rel_dir}/{$type}/") ? XOOPS_THEME_PATH . "/default/{$rel_dir}/{$type}"
                : $GLOBALS['xoops']->path("modules/{$dirname}/templates/{$endDir}/{$type}"));
        } else {
            $path = is_dir($theme_path . "/{$rel_dir}/{$type}/") ? $theme_path . "/{$rel_dir}/{$type}" : (
                is_dir($theme_path . "/{$rel_dir}/{$default}/") ? $theme_path . "/{$rel_dir}/{$default}" : (
                is_dir(XOOPS_THEME_PATH . "/default/{$rel_dir}/{$type}/") ? XOOPS_THEME_PATH
                 . "/default/{$rel_dir}/{$type}" : (
                     is_dir(XOOPS_THEME_PATH . "/default/{$rel_dir}/{$default}/") ? XOOPS_THEME_PATH . "/default/{$rel_dir}/{$default}"
                    : (is_dir($GLOBALS['xoops']->path("modules/{$dirname}/templates/{$endDir}/{$type}/")) ? $GLOBALS['xoops']->path("modules/{$dirname}/templates/{$endDir}/{$type}")
                    : $GLOBALS['xoops']->path("modules/{$dirname}/templates/{$endDir}/{$default}")) // XOOPS_ROOT_PATH
            ) // XOOPS_THEME_PATH {$default}
            ) // XOOPS_THEME_PATH
            ); // $theme_path {$default}
        }
        // END irmtfan add default for all pathes
        $paths[$endDir . '/' . $type] = str_replace(XOOPS_ROOT_PATH, '', str_replace('\\', '/', $path));

        return $paths[$endDir . '/' . $type];
    }

    // END irmtfan - improve to get other "end dirnames" like "css" and "js" - change images with $endDir

    /**
     * @param string $language
     * @param string $dirname
     */
    public function init(/*$set = "default", */
        $language = 'english',
        $dirname = 'newbb'
    ) {
        $this->forumImage = include $GLOBALS['xoops']->path("modules/{$dirname}/include/images.php");

        $this->forumImage['icon']     = XOOPS_URL . $this->getPath('icon', $dirname) . '/';
        $this->forumImage['language'] = XOOPS_URL . $this->getPath("language/{$language}", $dirname, 'language/english') . '/';
    }

    /**
     * @param        $image
     * @param string $alt
     * @param string $extra
     */
    public function setImage($image, $alt = '', $extra = '')
    {
        if (!isset($this->images[$image])) {
            $imageSource = $this->getImageSource($image);
            // irmtfan add id={$image}
            $this->images[$image] = "<img src=\"{$imageSource}\" alt=\"{$alt}\" title=\"{$alt}\" align=\"middle\" {$extra} id={$image} />";
        }
    }

    /**
     * TODO: How about image not exist?
     * @param $image
     * @return string
     */
    public function getImageSource($image)
    {
        return $this->forumImage[$this->forumImage[$image]] . $this->prefix . $image . $this->postfix;
    }

    /**
     * @param         $image
     * @param  string $alt
     * @param  string $extra
     * @return mixed
     */
    public function getImage($image, $alt = '', $extra = '')
    {
        $this->setImage($image, $alt, $extra);

        return $this->images[$image];
    }

    /**
     * @param         $image
     * @param  string $alt
     * @param  string $extra
     * @return string
     */
    public function assignImage($image, $alt = '', $extra = '')
    {
        $this->setImage($image, $alt, $extra);
        // START hacked by irmtfan - improve function to CSS3 buttons - add alt and title attributes - use span instead of button to support IE7&8
        $tag = 'span';
        if ("class='forum_icon'" === $extra && in_array(substr($image, 0, 2), ['t_', 'p_', 'up'])) {
            $extra = "class='forum_icon forum_button'";
        }

        return "<{$tag} alt=\"{$alt}\" title=\"{$alt}\" align=\"middle\" {$extra} id={$image}>$alt</{$tag}>";
        // END hacked by irmtfan - improve function to CSS3 buttons
    }

    /**
     * @param $images
     */
    public function assignImages($images)
    {
        foreach ($images as $myImage) {
            list($image, $alt, $extra) = $myImage;
            $this->assignImage($image, $alt, $extra);
        }
    }

    /**
     * @return int|void
     */
    public function render()
    {
        //$this->template->assign_by_ref("image", $this->images);
        $this->template->assign($this->images);

        return count($this->images);
    }
}

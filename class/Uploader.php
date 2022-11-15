<?php declare(strict_types=1);

namespace XoopsModules\Newbb;

/**
 * NewBB, XOOPS forum module
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 */
require_once $GLOBALS['xoops']->path('class/uploader.php');

/**
 * Class Uploader
 */
class Uploader extends \XoopsMediaUploader
{
    /**
     * No admin check for uploads
     * @param mixed $uploadDir
     * @param mixed $allowedMimeTypes
     * @param mixed $maxFileSize
     * @param mixed $maxWidth
     * @param mixed $maxHeight
     */

    /**
     * Constructor
     *
     * @param string           $uploadDir
     * @param array|int|string $allowedMimeTypes
     * @param int              $maxFileSize
     * @param int              $maxWidth
     * @param int              $maxHeight
     */
    public function __construct($uploadDir, $allowedMimeTypes = 0, $maxFileSize = 0, $maxWidth = 0, $maxHeight = 0)
    {
        //        $this->XoopsMediaUploader($uploadDir, $allowedMimeTypes, $maxFileSize, $maxWidth, $maxHeight);

        if (!\is_array($allowedMimeTypes)) {
            if (empty($allowedMimeTypes) || '*' === $allowedMimeTypes) {
                $allowedMimeTypes = [];
            } else {
                $allowedMimeTypes = \array_filter(\array_map('\trim', \explode('|', \mb_strtolower($allowedMimeTypes))));
            }
        }
        $_allowedMimeTypes = [];
        $extensionToMime   = require $GLOBALS['xoops']->path('include/mimetypes.inc.php');
        foreach ($allowedMimeTypes as $type) {
            if (isset($extensionToMime[$type])) {
                $_allowedMimeTypes[] = $extensionToMime[$type];
            } else {
                $_allowedMimeTypes[] = $type;
            }
        }
        parent::__construct($uploadDir, $_allowedMimeTypes, $maxFileSize, $maxWidth, $maxHeight);
    }

    /**
     * Set the CheckMediaTypeByExt
     * Deprecated
     *
     * @param bool|string $value
     */
    public function setCheckMediaTypeByExt($value = true): void
    {
    }

    /**
     * Set the imageSizeCheck
     * Deprecated
     *
     * @param string $value
     */
    public function setImageSizeCheck($value): void
    {
    }

    /**
     * Set the fileSizeCheck
     * Deprecated
     *
     * @param string $value
     */
    public function setFileSizeCheck($value): void
    {
    }

    /**
     * Get the file extension
     *
     * @return string
     */
    public function getExt()
    {
        $this->ext = \mb_strtolower(\ltrim(mb_strrchr($this->getMediaName(), '.'), '.'));

        return $this->ext;
    }
}

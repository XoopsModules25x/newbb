<?php declare(strict_types=1);
/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */
//  ------------------------------------------------------------------------ //
//  Author: phppp (D.J., infomax@gmail.com)                                  //
//  URL: https://xoops.org                                                    //
//  Project: Article Project                                                 //
//  ------------------------------------------------------------------------ //

if (!defined('NEWBB_FUNCTIONS_IMAGE')) {
    define('NEWBB_FUNCTIONS_IMAGE', true);

    /**
     * @param $source
     * @return string
     */
    function newbbAttachmentImage($source)
    {
        $img_path   = $GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments']);
        $img_url    = XOOPS_URL . '/' . $GLOBALS['xoopsModuleConfig']['dir_attachments'];
        $thumb_path = $img_path . '/thumbs';
        $thumb_url  = $img_url . '/thumbs';

        $thumb     = $thumb_path . '/' . $source;
        $image     = $img_path . '/' . $source;
        $thumb_url .= '/' . $source;
        $image_url = $img_url . '/' . $source;
        $img_info  = '';

        $imginfo = @getimagesize($image);

        // Change by BigKev73 - Removed the is_array check, otherwise the img_info is never set
        //if (is_array($image)) {
        $img_info = (is_array($imginfo) && count($imginfo) > 0) ? $imginfo[0] . 'X' . $imginfo[1] . ' px' : '';
        //}

        if (is_array($imginfo) && $GLOBALS['xoopsModuleConfig']['max_image_width'] > 0
            && $GLOBALS['xoopsModuleConfig']['max_image_height'] > 0) {
            if ($imginfo[0] > $GLOBALS['xoopsModuleConfig']['max_image_width']
                || $imginfo[1] > $GLOBALS['xoopsModuleConfig']['max_image_height']) {
                //if (!file_exists($thumb_path.'/'.$source) && $imginfo[0] > $GLOBALS['xoopsModuleConfig']['max_img_width']) {
                if (!file_exists($thumb_path . '/' . $source)) {
                    newbbCreateThumbnail($source, $GLOBALS['xoopsModuleConfig']['max_image_width']);
                }
            }
        }

        //BigKev73 Change to remove height value

        if (is_array($imginfo) && ($imginfo[0] > $GLOBALS['xoopsModuleConfig']['max_image_width']
            || $imginfo[1] > $GLOBALS['xoopsModuleConfig']['max_image_height'])) {
            $pseudo_width  = $GLOBALS['xoopsModuleConfig']['max_image_width'];
            $pseudo_height = $GLOBALS['xoopsModuleConfig']['max_image_width'] * ($imginfo[1] / $imginfo[0]);
            $pseudo_size   = "width='" . $pseudo_width . "' height='" . $pseudo_height . "'";
        }
        // irmtfan to fix Undefined variable: pseudo_height
        if (!empty($pseudo_height) && $GLOBALS['xoopsModuleConfig']['max_image_height'] > 0
            && $pseudo_height > $GLOBALS['xoopsModuleConfig']['max_image_height']) {
            $pseudo_height = $GLOBALS['xoopsModuleConfig']['max_image_height'];
            $pseudo_width  = $GLOBALS['xoopsModuleConfig']['max_image_height'] * ($imginfo[0] / $imginfo[1]);
            $pseudo_size   = "width='" . $pseudo_width . "' height='" . $pseudo_height . "'";
        }

        //BigKev73 Change to add max with property to properly scale photos
        if (file_exists($thumb)) {
            $attachmentImage = '<a href="' . $image_url . '" title="' . $source . ' ' . $img_info . '" target="_blank">';
            $attachmentImage .= '<img src="' . $thumb_url . '" ' . $pseudo_size . ' alt="' . $source . ' ' . $img_info . '" style="max-width: 100%; height: auto;">';
            $attachmentImage .= '</a>';
        } elseif (!empty($pseudo_size)) {
            $attachmentImage = '<a href="' . $image_url . '" title="' . $source . ' ' . $img_info . '" target="_blank">';
            $attachmentImage .= '<img src="' . $image_url . '" ' . $pseudo_size . ' alt="' . $source . ' ' . $img_info . '" style="max-width: 100%; height: auto;">';
            $attachmentImage .= '</a>';
        } elseif (file_exists($image)) {
            $attachmentImage = '<img src="' . $image_url . '" alt="' . $source . ' ' . $img_info . '" width="' . $imginfo[0] . '" height="' . $imginfo[1] . '" style="max-width: 100%; height: auto;">';
        } else {
            $attachmentImage = '';
        }

        return $attachmentImage;
    }

    /**
     * @param $source
     * @param $thumb_width
     * @return bool
     */
    function newbbCreateThumbnail($source, $thumb_width)
    {
        $cmd        = '';
        $img_path   = $GLOBALS['xoops']->path($GLOBALS['xoopsModuleConfig']['dir_attachments']);
        $thumb_path = $img_path . '/thumbs';
        $src_file   = $img_path . '/' . $source;
        $new_file   = $thumb_path . '/' . $source;
        //$imageLibs = newbb_getImageLibs();

        if (!filesize($src_file) || !is_readable($src_file)) {
            return false;
        }

        if (!is_dir($thumb_path) || !is_writable($thumb_path)) {
            return false;
        }

        $imginfo = @getimagesize($src_file);

        if (null === $imginfo) {
            return false;
        }
        if ($imginfo[0] < $thumb_width) {
            return false;
        }

        $newWidth  = (int)min($imginfo[0], $thumb_width);
        $newHeight = (int)($imginfo[1] * $newWidth / $imginfo[0]);

        if (1 == $GLOBALS['xoopsModuleConfig']['image_lib'] || 0 == $GLOBALS['xoopsModuleConfig']['image_lib']) {
            if (preg_match('#[A-Z]:|\\\\#Ai', __FILE__)) {
                $cur_dir     = __DIR__;
                $src_file_im = '"' . $cur_dir . '\\' . str_replace('/', '\\', $src_file) . '"';
                $new_file_im = '"' . $cur_dir . '\\' . str_replace('/', '\\', $new_file) . '"';
            } else {
                $src_file_im = @escapeshellarg($src_file);
                $new_file_im = @escapeshellarg($new_file);
            }
            $path           = empty($GLOBALS['xoopsModuleConfig']['path_magick']) ? '' : $GLOBALS['xoopsModuleConfig']['path_magick'] . '/';
            $magick_command = $path . 'convert -auto-orient -quality 85 -antialias -sample ' . $newWidth . 'x' . $newHeight . ' ' . $src_file_im . ' +profile "*" ' . str_replace('\\', '/', $new_file_im) . '';

            @passthru($magick_command);
            if (file_exists($new_file)) {
                return true;
            }
        }

        if (2 == $GLOBALS['xoopsModuleConfig']['image_lib'] || 0 == $GLOBALS['xoopsModuleConfig']['image_lib']) {
            $path = empty($GLOBALS['xoopsModuleConfig']['path_netpbm']) ? '' : $GLOBALS['xoopsModuleConfig']['path_netpbm'] . '/';
            if (preg_match('/\.png$/', $source)) {
                $cmd = $path . "pngtopnm $src_file | " . $path . "pnmscale -xysize $newWidth $newHeight | " . $path . "pnmtopng > $new_file";
            } elseif (preg_match('/\.(jpg|jpeg)$/', $source)) {
                $cmd = $path . "jpegtopnm $src_file | " . $path . "pnmscale -xysize $newWidth $newHeight | " . $path . "ppmtojpeg -quality=90 > $new_file";
            } elseif (preg_match('/\.gif$/', $source)) {
                $cmd = $path . "giftopnm $src_file | " . $path . "pnmscale -xysize $newWidth $newHeight | ppmquant 256 | " . $path . "ppmtogif > $new_file";
            }

            @exec($cmd, $output, $retval);
            if (file_exists($new_file)) {
                return true;
            }
        }

        $type            = $imginfo[2];
        $supported_types = [];

        if (!extension_loaded('gd')) {
            return false;
        }
        if (function_exists('imagegif')) {
            $supported_types[] = 1;
        }
        if (function_exists('imagejpeg')) {
            $supported_types[] = 2;
        }
        if (function_exists('imagepng')) {
            $supported_types[] = 3;
        }

        $imageCreateFunction = function_exists('imagecreatetruecolor') ? 'imagecreatetruecolor' : 'imagecreate';

        if (in_array($type, $supported_types, true)) {
            switch ($type) {
                case 1:
                    if (!function_exists('imagecreatefromgif')) {
                        return false;
                    }
                    $im     = imagecreatefromgif($src_file);
                    $new_im = imagecreate($newWidth, $newHeight);
                    imagecopyresized($new_im, $im, 0, 0, 0, 0, $newWidth, $newHeight, $imginfo[0], $imginfo[1]);
                    imagegif($new_im, $new_file);
                    imagedestroy($im);
                    imagedestroy($new_im);
                    break;
                case 2:
                    $im     = imagecreatefromjpeg($src_file);
                    $new_im = $imageCreateFunction($newWidth, $newHeight);
                    imagecopyresized($new_im, $im, 0, 0, 0, 0, $newWidth, $newHeight, $imginfo[0], $imginfo[1]);
                    imagejpeg($new_im, $new_file, 90);
                    imagedestroy($im);
                    imagedestroy($new_im);
                    break;
                case 3:
                    $im     = imagecreatefrompng($src_file);
                    $new_im = $imageCreateFunction($newWidth, $newHeight);
                    imagecopyresized($new_im, $im, 0, 0, 0, 0, $newWidth, $newHeight, $imginfo[0], $imginfo[1]);
                    imagepng($new_im, $new_file);
                    imagedestroy($im);
                    imagedestroy($new_im);
                    break;
            }
        }

        return file_exists($new_file);
    }
}

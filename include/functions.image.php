<?php
//
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                  Copyright (c) 2000-2016 XOOPS.org                        //
//                       <https://xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
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
        $thumb_url = $thumb_url . '/' . $source;
        $image_url = $img_url . '/' . $source;

        $imginfo  = @getimagesize($image);
        $img_info = (count($imginfo) > 0) ? $imginfo[0] . 'X' . $imginfo[1] . ' px' : '';

        if ($GLOBALS['xoopsModuleConfig']['max_image_width'] > 0
            && $GLOBALS['xoopsModuleConfig']['max_image_height'] > 0) {
            if ($imginfo[0] > $GLOBALS['xoopsModuleConfig']['max_image_width']
                || $imginfo[1] > $GLOBALS['xoopsModuleConfig']['max_image_height']) {
                //if (!file_exists($thumb_path.'/'.$source) && $imginfo[0] > $GLOBALS['xoopsModuleConfig']['max_img_width']) {
                if (!file_exists($thumb_path . '/' . $source)) {
                    newbbCreateThumbnail($source, $GLOBALS['xoopsModuleConfig']['max_image_width']);
                }
            }

            if ($imginfo[0] > $GLOBALS['xoopsModuleConfig']['max_image_width']
                || $imginfo[1] > $GLOBALS['xoopsModuleConfig']['max_image_height']) {
                $pseudo_width  = $GLOBALS['xoopsModuleConfig']['max_image_width'];
                $pseudo_height = $GLOBALS['xoopsModuleConfig']['max_image_width'] * ($imginfo[1] / $imginfo[0]);
                $pseudo_size   = "width='" . $pseudo_width . "px' height='" . $pseudo_height . "px'";
            }
            // irmtfan to fix Undefined variable: pseudo_height
            if (!empty($pseudo_height) && $GLOBALS['xoopsModuleConfig']['max_image_height'] > 0
                && $pseudo_height > $GLOBALS['xoopsModuleConfig']['max_image_height']) {
                $pseudo_height = $GLOBALS['xoopsModuleConfig']['max_image_height'];
                $pseudo_width  = $GLOBALS['xoopsModuleConfig']['max_image_height'] * ($imginfo[0] / $imginfo[1]);
                $pseudo_size   = "width='" . $pseudo_width . "px' height='" . $pseudo_height . "px'";
            }
        }

        if (file_exists($thumb)) {
            $attachmentImage = '<a href="' . $image_url . '" title="' . $source . ' ' . $img_info . '" target="_blank">';
            // $attachmentImage .= '<img src="' . $thumb_url . '" alt="' . $source . ' ' . $img_info . '" />';
            $attachmentImage .= '<img src="' . $image_url . '" ' . $pseudo_size . ' alt="' . $source . ' ' . $img_info . '" />';
            $attachmentImage .= '</a>';
        } elseif (!empty($pseudo_size)) {
            $attachmentImage = '<a href="' . $image_url . '" title="' . $source . ' ' . $img_info . '" target="_blank">';
            $attachmentImage .= '<img src="' . $image_url . '" ' . $pseudo_size . ' alt="' . $source . ' ' . $img_info . '" />';
            $attachmentImage .= '</a>';
        } elseif (file_exists($image)) {
            $attachmentImage = '<img src="' . $image_url . '" alt="' . $source . ' ' . $img_info . '" />';
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
            if (preg_match("#[A-Z]:|\\\\#Ai", __FILE__)) {
                $cur_dir     = __DIR__;
                $src_file_im = '"' . $cur_dir . '\\' . str_replace('/', '\\', $src_file) . '"';
                $new_file_im = '"' . $cur_dir . '\\' . str_replace('/', '\\', $new_file) . '"';
            } else {
                $src_file_im = @escapeshellarg($src_file);
                $new_file_im = @escapeshellarg($new_file);
            }
            $path           = empty($GLOBALS['xoopsModuleConfig']['path_magick']) ? '' : $GLOBALS['xoopsModuleConfig']['path_magick'] . '/';
            $magick_command = $path . 'convert -quality 85 -antialias -sample ' . $newWidth . 'x' . $newHeight . ' ' . $src_file_im . ' +profile "*" ' . str_replace('\\', '/', $new_file_im) . '';

            @passthru($magick_command);
            if (file_exists($new_file)) {
                return true;
            }
        }

        if (2 == $GLOBALS['xoopsModuleConfig']['image_lib'] || 0 == $GLOBALS['xoopsModuleConfig']['image_lib']) {
            $path = empty($GLOBALS['xoopsModuleConfig']['path_netpbm']) ? '' : $GLOBALS['xoopsModuleConfig']['path_netpbm'] . '/';
            if (preg_match("/\.png/", $source)) {
                $cmd = $path . "pngtopnm $src_file | " . $path . "pnmscale -xysize $newWidth $newHeight | " . $path . "pnmtopng > $new_file";
            } elseif (preg_match("/\.(jpg|jpeg)/", $source)) {
                $cmd = $path . "jpegtopnm $src_file | " . $path . "pnmscale -xysize $newWidth $newHeight | " . $path . "ppmtojpeg -quality=90 > $new_file";
            } elseif (preg_match("/\.gif/", $source)) {
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

        if (in_array($type, $supported_types)) {
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

        if (file_exists($new_file)) {
            return true;
        } else {
            return false;
        }
    }
}

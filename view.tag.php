<?php declare(strict_types=1);

/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 */
require_once __DIR__ . '/header.php';

if ((!class_exists('TagFormTag')) || (class_exists('TagFormTag') && !@require $GLOBALS['xoops']->path('modules/tag/view.tag.php'))) {
    return null;
}

<?php declare(strict_types=1);

namespace XoopsModules\Newbb\Common;

/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */
/**
 * Breadcrumb Class
 *
 * @copyright   XOOPS Project (https://xoops.org)
 * @license     https://www.fsf.org/copyleft/gpl.html GNU public license
 * @author      lucio <lucio.rota@gmail.com>
 *
 * Example:
 * $breadcrumb = new Breadcrumb();
 * $breadcrumb->addLink( 'bread 1', 'index1.php' );
 * $breadcrumb->addLink( 'bread 2', '' );
 * $breadcrumb->addLink( 'bread 3', 'index3.php' );
 * echo $breadcrumb->render();
 */

/**
 * Class Breadcrumb
 */
class Breadcrumb
{
    /** @var string */
    private $dirname;
    /** @var array */
    private $bread = [];

    public function __construct()
    {
        $this->dirname = \basename(\dirname(__DIR__, 2));
    }

    /**
     * Add link to breadcrumb
     *
     * @param string $title
     * @param string $link
     */
    public function addLink($title = '', $link = ''): void
    {
        $this->bread[] = [
            'link'  => $link,
            'title' => $title,
        ];
    }

    /**
     * Render BreadCrumb
     *
     * @return void
     */
    public function render(): void
    {
        /*
        TODO if you want to use the render code below,
        1) create ./templates/chess_common_breadcrumb.tpl)
        2) add declaration to  xoops_version.php
        */
        /*
        if (!isset($GLOBALS['xoTheme']) || !\is_object($GLOBALS['xoTheme'])) {
            require $GLOBALS['xoops']->path('class/theme.php');

            $GLOBALS['xoTheme'] = new \xos_opal_Theme();
        }

        require $GLOBALS['xoops']->path('class/template.php');

        $breadcrumbTpl = new \XoopsTpl();

        $breadcrumbTpl->assign('breadcrumb', $this->bread);

        $html = $breadcrumbTpl->fetch('db:' . $this->dirname . '_common_breadcrumb.tpl');

        unset($breadcrumbTpl);

        return $html;
        */
    }
}

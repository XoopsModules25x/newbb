<?php
//
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                  Copyright (c) 2000-2020 XOOPS.org                        //
//                       <https://xoops.org>                             //
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
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, https://xoops.org/, http://jp.xoops.org/ //
// Project: XOOPS Project                                                    //
// ------------------------------------------------------------------------- //

use Xmf\Request;
use Xmf\Module\Admin;
use XoopsModules\Newbb\{Common\Configurator,
    Common\Migrate,
    Helper
};

/** @var Admin $adminObject */
/** @var Helper $helper */

require_once __DIR__ . '/admin_header.php';
xoops_cp_header();

$adminObject->displayNavigation(basename(__FILE__));

echo <<<EOF
<form method="post" class="form-inline">
<div class="form-group">
<input name="show" class="btn btn-default" type="submit" value="Show SQL">
</div>
<div class="form-group">
<input name="migrate" class="btn btn-default" type="submit" value="Do Migration">
</div>
<div class="form-group">
<input name="schema" class="btn btn-default" type="submit" value="Write Schema">
</div>
</form>
EOF;

//XoopsLoad::load('migrate', 'newbb');

/** @var Newbb\Common\Configurator $configurator */
$configurator = new Configurator();

$migrator = new Migrate($configurator);

$op        = Request::getCmd('op', 'show');
$opShow    = Request::getCmd('show', null, 'POST');
$opMigrate = Request::getCmd('migrate', null, 'POST');
$opSchema  = Request::getCmd('schema', null, 'POST');
$op        = !empty($opShow) ? 'show' : $op;
$op        = !empty($opMigrate) ? 'migrate' : $op;
$op        = !empty($opSchema) ? 'schema' : $op;

$message = '';

switch ($op) {
    case 'show':
    default:
        $queue = $migrator->getSynchronizeDDL();
        if (!empty($queue)) {
            echo "<pre>\n";
            foreach ($queue as $line) {
                echo $line . ";\n";
            }
            echo "</pre>\n";
        }
        break;
    case 'migrate':
        $migrator->synchronizeSchema();
        $message = constant('CO_' . $moduleDirNameUpper . '_' . 'MIGRATE_OK');
        break;
    case 'schema':
        xoops_confirm(['op' => 'confirmwrite'], 'migrate.php', constant('CO_' . $moduleDirNameUpper . '_' . 'MIGRATE_WARNING'), constant('CO_' . $moduleDirNameUpper . '_' . 'CONFIRM'));
        break;
    case 'confirmwrite':
        if ($GLOBALS['xoopsSecurity']->check()) {
            $migrator->saveCurrentSchema();

            $message = constant('CO_' . $moduleDirNameUpper . '_' . 'MIGRATE_SCHEMA_OK');
        }
        break;
}

echo "<div>$message</div>";

require_once __DIR__ . '/admin_footer.php';

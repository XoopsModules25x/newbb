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

/**
 * @copyright    XOOPS Project (https://xoops.org)/
 * @license      GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author       Dirk Herrmann (AKA alfred) https://www.mymyxoops.org/, https://simple-xoops.de/
 * @author       XOOPS Development Team
 */

use Xmf\Module\Admin;
use Xmf\Request;

/** @var Admin $adminObject */
require_once __DIR__ . '/admin_header.php';
xoops_cp_header();
require_once $GLOBALS['xoops']->path('class/xoopsformloader.php');
$adminObject->displayNavigation(basename(__FILE__));
/** @var \XoopsMemberHandler $memberHandler */
$memberHandler = xoops_getHandler('member');
///** @var Newbb\ForumHandler $forumHandler */
//$forumHandler = \XoopsModules\Newbb\Helper::getInstance()->getHandler('Forum');
if (Request::getString('submit', '', 'POST')) {
    $fgroups = Request::getArray('group', '', 'POST'); // !empty($_POST['group']) ? $_POST['group'] : '';
    $fforum  = Request::getInt('forenid', 0, 'POST'); // (int)($_POST['forenid']);
    $fuser   = [];
    if (0 !== $fforum) {
        if ('' !== $fgroups) {
            $gg = [];
            foreach ($fgroups as $k) {
                $gg = $memberHandler->getUsersByGroup($k, false);
                foreach ($gg as $f) {
                    if (!in_array($f, $fuser, true)) {
                        $fuser[] = $f;
                    }
                }
            }
        }
        if (-1 == $fforum) { // alle Foren
            $sql = 'UPDATE ' . $GLOBALS['xoopsDB']->prefix('newbb_forums') . " SET forum_moderator='" . serialize($fuser) . "'";
        } else {
            $sql = 'UPDATE ' . $GLOBALS['xoopsDB']->prefix('newbb_forums') . " SET forum_moderator='" . serialize($fuser) . "' WHERE forum_id =" . $fforum;
        }
        if (is_array($fuser) && $GLOBALS['xoopsDB']->queryF($sql)) {
            $mess = _AM_NEWBB_GROUPMOD_ADDMOD;
        } else {
            $mess = _AM_NEWBB_GROUPMOD_ERRMOD . '<br><small>( ' . $sql . ' )</small>';
        }
        redirect_header('admin_groupmod.php', 1, $mess);
        //        echo '<div class="confirmMsg">' . $mess . '</div><br><br>';
    }
}

echo _AM_NEWBB_GROUPMOD_TITLEDESC;
echo "<br><br><table width='100%' border='0' cellspacing='1' class='outer'>" . "<tr><td class='odd'>";
echo "<form name='reorder' method='post'>";
///** @var Newbb\CategoryHandler $categoryHandler */
//$categoryHandler  = \XoopsModules\Newbb\Helper::getInstance()->getHandler('Category');
$criteriaCategory = new \CriteriaCompo(new \Criteria('cat_id'));
$criteriaCategory->setSort('cat_order');
$categories = $categoryHandler->getAll($criteriaCategory, ['cat_id', 'cat_order', 'cat_title']);
$forums     = $forumHandler->getTree(array_keys($categories), 0, 'all', '&nbsp;&nbsp;&nbsp;&nbsp;');
echo '<select name="forenid">';
echo '<option value="-1">-- ' . _AM_NEWBB_GROUPMOD_ALLFORUMS . ' --</option>';
foreach (array_keys($categories) as $c) {
    if (!isset($forums[$c])) {
        continue;
    }
    $i = 0;
    foreach ($forums[$c] as $key => $forum) {
        echo '<option value="' . $forum['forum_id'] . '"> ' . $categories[$c]->getVar('cat_title') . '::' . $forum['forum_name'] . '</option>';
    }
}
echo '</select>';
echo "</td><tr><tr><td class='even'>";

$groups = $memberHandler->getGroups();
foreach ($groups as $value) {
    echo '<input type="checkbox" name="group[]" value="' . $value->getVar('groupid') . '" > ' . $value->getVar('name') . '<br>';
}
echo "</td><tr><tr><td class='odd' style='text-align:center;'>";
echo '<input type="submit" value="' . _SUBMIT . '" name="submit" >';
echo '</td></tr></table>';
echo '</form>';
echo '<fieldset>';
echo '<legend>&nbsp;' . _MI_NEWBB_ADMENU_GROUPMOD . '&nbsp;</legend>';
echo _AM_NEWBB_HELP_GROUPMOD_TAB;
echo '</fieldset>';
require_once __DIR__ . '/admin_footer.php';

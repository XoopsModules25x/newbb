<?php

declare(strict_types=1);

namespace XoopsModules\Newbb\Common;

/**
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 *
 * @category        Module
 * @author          XOOPS Development Team
 * @copyright       XOOPS Project
 * @link            https://xoops.org
 * @license         GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 */

use Xmf\Request;
use XoopsModules\Newbb\{
    Helper
};

//require_once __DIR__ . '/admin_header.php';

/**
 * class Blocksadmin
 */
final class Blocksadmin
{
    /**
     * @var \XoopsMySQLDatabase|null
     */
    public $db;
    /**
     * @var Helper
     */
    public Helper $helper;
    /**
     * @var string
     */
    public string $moduleDirName;
    /**
     * @var string
     */
    public $moduleDirNameUpper;

    /**
     * Blocksadmin constructor.
     * @param \XoopsDatabase|null $db
     * @param Helper              $helper
     */
    public function __construct(?\XoopsDatabase $db, Helper $helper)
    {
        if (null === $db) {
            $db = \XoopsDatabaseFactory::getDatabaseConnection();
        }
        $this->db                 = $db;
        $this->helper             = $helper;
        $this->moduleDirName      = \basename(\dirname(__DIR__, 2));
        $this->moduleDirNameUpper = \mb_strtoupper($this->moduleDirName);
        \xoops_loadLanguage('admin', 'system');
        \xoops_loadLanguage('admin/blocksadmin', 'system');
        \xoops_loadLanguage('admin/groups', 'system');
        \xoops_loadLanguage('common', $this->moduleDirName);
        \xoops_loadLanguage('blocksadmin', $this->moduleDirName);
    }

    /**
     * @return void
     */
    public function listBlocks(): void
    {
        global $xoopsModule, $pathIcon16;
        require_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
        //        xoops_loadLanguage('admin', 'system');
        //        xoops_loadLanguage('admin/blocksadmin', 'system');
        //        xoops_loadLanguage('admin/groups', 'system');
        //        xoops_loadLanguage('common', $moduleDirName);
        //        xoops_loadLanguage('blocks', $moduleDirName);

        /** @var \XoopsModuleHandler $moduleHandler */
        $moduleHandler = \xoops_getHandler('module');
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = \xoops_getHandler('member');
        /** @var \XoopsGroupPermHandler $grouppermHandler */
        $grouppermHandler = \xoops_getHandler('groupperm');
        $groups           = $memberHandler->getGroups();
        $criteria         = new \CriteriaCompo(new \Criteria('hasmain', '1'));
        $criteria->add(new \Criteria('isactive', '1'));
        $moduleList     = $moduleHandler->getList($criteria);
        $moduleList[-1] = \_AM_SYSTEM_BLOCKS_TOPPAGE;
        $moduleList[0]  = \_AM_SYSTEM_BLOCKS_ALLPAGES;
        \ksort($moduleList);
        echo "<h4 style='text-align:left;'>" . \constant('CO_' . $this->moduleDirNameUpper . '_' . 'BADMIN') . '</h4>';
        echo "<form action='" . $_SERVER['SCRIPT_NAME'] . "' name='blockadmin' method='post'>";
        echo $GLOBALS['xoopsSecurity']->getTokenHTML();
        echo "<table width='100%' class='outer' cellpadding='4' cellspacing='1'>
        <tr valign='middle'><th align='center'>" . \_AM_SYSTEM_BLOCKS_TITLE . "</th><th align='center' nowrap='nowrap'>" . \constant('CO_' . $this->moduleDirNameUpper . '_' . 'SIDE') . '<br>' . \_LEFT . '-' . \_CENTER . '-' . \_RIGHT . "</th>
        <th align='center'>" . \constant('CO_' . $this->moduleDirNameUpper . '_' . 'WEIGHT') . "</th>
        <th align='center'>" . \constant('CO_' . $this->moduleDirNameUpper . '_' . 'VISIBLE') . "</th><th align='center'>" . \_AM_SYSTEM_BLOCKS_VISIBLEIN . "</th>
        <th align='center'>" . \_AM_SYSTEM_ADGS . "</th>
        <th align='center'>" . \_AM_SYSTEM_BLOCKS_BCACHETIME . "</th>
        <th align='center'>" . \constant('CO_' . $this->moduleDirNameUpper . '_' . 'ACTION') . '</th>
        </tr>';
        $blockArray = \XoopsBlock::getByModule($xoopsModule->mid());
        $blockCount = \count($blockArray);
        $class      = 'even';
        $cachetimes = [
            0       => \_NOCACHE,
            30      => \sprintf(\_SECONDS, 30),
            60      => \_MINUTE,
            300     => \sprintf(\_MINUTES, 5),
            1800    => \sprintf(\_MINUTES, 30),
            3600    => \_HOUR,
            18000   => \sprintf(\_HOURS, 5),
            86400   => \_DAY,
            259200  => \sprintf(\_DAYS, 3),
            604800  => \_WEEK,
            2592000 => \_MONTH,
        ];
        foreach ($blockArray as $i) {
            $modules = [];
            $groupsPermissions = $grouppermHandler->getGroupIds('block_read', $i->getVar('bid'));
            $sql               = 'SELECT module_id FROM ' . $this->db->prefix('block_module_link') . ' WHERE block_id=' . $i->getVar('bid');
            $result            = $this->db->query($sql);
            if (!$this->db->isResultSet($result)) {
                \trigger_error("Query Failed! SQL: $sql Error: " . $this->db->error(), \E_USER_ERROR);
            } else {
                  while (false !== ($row = $this->db->fetchArray($result))) {
                    $modules[] = (int)$row['module_id'];
                }
            }
            $cachetimeOptions = '';
            foreach ($cachetimes as $cachetime => $cachetimeName) {
                if ($i->getVar('bcachetime') == $cachetime) {
                    $cachetimeOptions .= "<option value='$cachetime' selected='selected'>$cachetimeName</option>\n";
                } else {
                    $cachetimeOptions .= "<option value='$cachetime'>$cachetimeName</option>\n";
                }
            }

            $ssel7 = '';
            $ssel6 = $ssel7;
            $ssel5 = $ssel6;
            $ssel4 = $ssel5;
            $ssel3 = $ssel4;
            $ssel2 = $ssel3;
            $ssel1 = $ssel2;
            $ssel0 = $ssel1;
            $sel1  = $ssel0;
            $sel0  = $sel1;
            if (1 === $i->getVar('visible')) {
                $sel1 = ' checked';
            } else {
                $sel0 = ' checked';
            }
            if (\XOOPS_SIDEBLOCK_LEFT === $i->getVar('side')) {
                $ssel0 = ' checked';
            } elseif (\XOOPS_SIDEBLOCK_RIGHT === $i->getVar('side')) {
                $ssel1 = ' checked';
            } elseif (\XOOPS_CENTERBLOCK_LEFT === $i->getVar('side')) {
                $ssel2 = ' checked';
            } elseif (\XOOPS_CENTERBLOCK_RIGHT === $i->getVar('side')) {
                $ssel4 = ' checked';
            } elseif (\XOOPS_CENTERBLOCK_CENTER === $i->getVar('side')) {
                $ssel3 = ' checked';
            } elseif (\XOOPS_CENTERBLOCK_BOTTOMLEFT === $i->getVar('side')) {
                $ssel5 = ' checked';
            } elseif (\XOOPS_CENTERBLOCK_BOTTOMRIGHT === $i->getVar('side')) {
                $ssel6 = ' checked';
            } elseif (\XOOPS_CENTERBLOCK_BOTTOM === $i->getVar('side')) {
                $ssel7 = ' checked';
            }
            $title = '' === $i->getVar('title') ? '&nbsp;' : $i->getVar('title');
            $name = $i->getVar('name');
            echo "<tr valign='top'><td class='$class' align='center'><input type='text' name='title[" . $i->getVar('bid') . "]' value='" . $title . "'></td>
            <td class='$class' align='center' nowrap='nowrap'><div align='center' >
                    <input type='radio' name='side[" . $i->getVar('bid') . "]' value='" . \XOOPS_CENTERBLOCK_LEFT . "'$ssel2>
                    <input type='radio' name='side[" . $i->getVar('bid') . "]' value='" . \XOOPS_CENTERBLOCK_CENTER . "'$ssel3>
                    <input type='radio' name='side[" . $i->getVar('bid') . "]' value='" . \XOOPS_CENTERBLOCK_RIGHT . "'$ssel4>
                    </div>
                    <div>
                        <span style='float:right;'><input type='radio' name='side[" . $i->getVar('bid') . "]' value='" . \XOOPS_SIDEBLOCK_RIGHT . "'$ssel1></span>
                    <div align='left'><input type='radio' name='side[" . $i->getVar('bid') . "]' value='" . \XOOPS_SIDEBLOCK_LEFT . "'$ssel0></div>
                    </div>
                    <div align='center'>
                    <input type='radio' name='side[" . $i->getVar('bid') . "]' value='" . \XOOPS_CENTERBLOCK_BOTTOMLEFT . "'$ssel5>
                        <input type='radio' name='side[" . $i->getVar('bid') . "]' value='" . \XOOPS_CENTERBLOCK_BOTTOM . "'$ssel7>
                    <input type='radio' name='side[" . $i->getVar('bid') . "]' value='" . \XOOPS_CENTERBLOCK_BOTTOMRIGHT . "'$ssel6>
                    </div>
                </td>
                <td class='$class' align='center'><input type='text' name='weight[" . $i->getVar('bid') . "]' value='" . $i->getVar('weight') . "' size='5' maxlength='5'></td>
                <td class='$class' align='center' nowrap><input type='radio' name='visible[" . $i->getVar('bid') . "]' value='1'$sel1>" . \_YES . "&nbsp;<input type='radio' name='visible[" . $i->getVar('bid') . "]' value='0'$sel0>" . \_NO . '</td>';

            echo "<td class='$class' align='center'><select size='5' name='bmodule[" . $i->getVar('bid') . "][]' id='bmodule[" . $i->getVar('bid') . "][]' multiple='multiple'>";
            foreach ($moduleList as $k => $v) {
                echo "<option value='$k'" . (\in_array($k, $modules) ? " selected='selected'" : '') . ">$v</option>";
            }
            echo '</select></td>';

            echo "<td class='$class' align='center'><select size='5' name='groups[" . $i->getVar('bid') . "][]' id='groups[" . $i->getVar('bid') . "][]' multiple='multiple'>";
            foreach ($groups as $grp) {
                echo "<option value='" . $grp->getVar('groupid') . "' " . (\in_array($grp->getVar('groupid'), $groupsPermissions) ? " selected='selected'" : '') . '>' . $grp->getVar('name') . '</option>';
            }
            echo '</select></td>';

            // Cache lifetime
            echo '<td class="' . $class . '" align="center"> <select name="bcachetime[' . $i->getVar('bid') . ']" size="1">' . $cachetimeOptions . '</select>
                                    </td>';

            // Actions

            echo "<td class='$class' align='center'>
                <a href='blocksadmin.php?op=edit&amp;bid=" . $i->getVar('bid') . "'><img src=" . $pathIcon16 . '/edit.png' . " alt='" . \_EDIT . "' title='" . \_EDIT . "'></a> 
                <a href='blocksadmin.php?op=clone&amp;bid=" . $i->getVar('bid') . "'><img src=" . $pathIcon16 . '/editcopy.png' . " alt='" . \_CLONE . "' title='" . \_CLONE . "'></a>";
            //            if ('S' !== $i->getVar('block_type') && 'M' !== $i->getVar('block_type')) {
            //                echo "&nbsp;<a href='" . XOOPS_URL . '/modules/system/admin.php?fct=blocksadmin&amp;op=delete&amp;bid=' . $i->getVar('bid') . "'><img src=" . $pathIcon16 . '/delete.png' . " alt='" . _DELETE . "' title='" . _DELETE . "'>
            //                     </a>";
            //            }

            //            if ('S' !== $i->getVar('block_type') && 'M' !== $i->getVar('block_type')) {
            if (!\in_array($i->getVar('block_type'), ['M', 'S'])) {
                echo "&nbsp;
                <a href='blocksadmin.php?op=delete&amp;bid=" . $i->getVar('bid') . "'><img src=" . $pathIcon16 . '/delete.png' . " alt='" . \_DELETE . "' title='" . \_DELETE . "'>
                     </a>";
            }
            echo "
            <input type='hidden' name='oldtitle[" . $i->getVar('bid') . "]' value='" . $i->getVar('title') . "'>
            <input type='hidden' name='oldside[" . $i->getVar('bid') . "]' value='" . $i->getVar('side') . "'>
            <input type='hidden' name='oldweight[" . $i->getVar('bid') . "]' value='" . $i->getVar('weight') . "'>
            <input type='hidden' name='oldvisible[" . $i->getVar('bid') . "]' value='" . $i->getVar('visible') . "'>
            <input type='hidden' name='oldgroups[" . $i->getVar('groups') . "]' value='" . $i->getVar('groups') . "'>
            <input type='hidden' name='oldbcachetime[" . $i->getVar('bid') . "]' value='" . $i->getVar('bcachetime') . "'>
            <input type='hidden' name='bid[" . $i->getVar('bid') . "]' value='" . $i->getVar('bid') . "'>
            </td></tr>
            ";
            $class = ('even' === $class) ? 'odd' : 'even';
        }
        echo "<tr><td class='foot' align='center' colspan='8'> <input type='hidden' name='op' value='order'>" . $GLOBALS['xoopsSecurity']->getTokenHTML() . "<input type='submit' name='submit' value='" . \_SUBMIT . "'></td></tr></table></form><br><br>";
    }

    /**
     * @param int $bid
     */
    public function deleteBlock(int $bid): void
    {
        //        \xoops_cp_header();

        \xoops_loadLanguage('admin', 'system');
        \xoops_loadLanguage('admin/blocksadmin', 'system');
        \xoops_loadLanguage('admin/groups', 'system');

        $myblock = new \XoopsBlock($bid);

        $sql = \sprintf('DELETE FROM %s WHERE bid = %u', $this->db->prefix('newblocks'), $bid);
        $this->db->queryF($sql) || \trigger_error($GLOBALS['xoopsDB']->error());
        $sql = \sprintf('DELETE FROM %s WHERE block_id = %u', $this->db->prefix('block_module_link'), $bid);
        $this->db->queryF($sql) || \trigger_error($GLOBALS['xoopsDB']->error());

        $this->helper->redirect('admin/blocksadmin.php?op=list', 1, _AM_DBUPDATED);
    }

    /**
     * @param int $bid
     */
    public function cloneBlock(int $bid): void
    {
        //require_once __DIR__ . '/admin_header.php';
        //        \xoops_cp_header();

        \xoops_loadLanguage('admin', 'system');
        \xoops_loadLanguage('admin/blocksadmin', 'system');
        \xoops_loadLanguage('admin/groups', 'system');

        $modules = [];
        $myblock = new \XoopsBlock($bid);
        $sql     = 'SELECT module_id FROM ' . $this->db->prefix('block_module_link') . ' WHERE block_id=' . $bid;
        $result  = $this->db->query($sql);
        if (!$this->db->isResultSet($result)) {
            \trigger_error("Query Failed! SQL: $sql Error: " . $this->db->error(), \E_USER_ERROR);
        } else {
            while (false !== ($row = $this->db->fetchArray($result))) {
                $modules[] = (int)$row['module_id'];
            }
        }

        $isCustom = \in_array($myblock->getVar('block_type'), ['C', 'E']);
        $block    = [
            'title'      => $myblock->getVar('title') . ' Clone',
            'form_title' => \constant('CO_' . $this->moduleDirNameUpper . '_' . 'BLOCKS_CLONEBLOCK'),
            'name'       => $myblock->getVar('name'),
            'side'       => $myblock->getVar('side'),
            'weight'     => $myblock->getVar('weight'),
            'visible'    => $myblock->getVar('visible'),
            'content'    => $myblock->getVar('content', 'N'),
            'modules'    => $modules,
            'is_custom'  => $isCustom,
            'ctype'      => $myblock->getVar('c_type'),
            'bcachetime' => $myblock->getVar('bcachetime'),
            'op'         => 'clone_ok',
            'bid'        => $myblock->getVar('bid'),
            'edit_form'  => $myblock->getOptions(),
            'template'   => $myblock->getVar('template'),
            'options'    => $myblock->getVar('options'),
        ];
        echo '<a href="blocksadmin.php">' . \constant('CO_' . $this->moduleDirNameUpper . '_' . 'BADMIN') . '</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;' . \_AM_SYSTEM_BLOCKS_CLONEBLOCK . '<br><br>';
        //        $form = new Blockform();
        //        $form->render();

        echo $this->render($block);
        //        xoops_cp_footer();
        //        require_once __DIR__ . '/admin_footer.php';
        //        exit();
    }

    /**
     * @param int        $bid
     * @param string     $bside
     * @param string     $bweight
     * @param string     $bvisible
     * @param string     $bcachetime
     * @param array|null $bmodule
     * @param array|null $options
     * @param array|null $groups
     */
    public function isBlockCloned(int $bid, string $bside, string $bweight, string $bvisible, string $bcachetime, ?array $bmodule, ?array $options, ?array $groups): void
    {
        \xoops_loadLanguage('admin', 'system');
        \xoops_loadLanguage('admin/blocksadmin', 'system');
        \xoops_loadLanguage('admin/groups', 'system');

        $block = new \XoopsBlock($bid);
        $clone = $block->xoopsClone();
        if (empty($bmodule)) {
            //            \xoops_cp_header();
            \xoops_error(\sprintf(_AM_NOTSELNG, _AM_VISIBLEIN));
            \xoops_cp_footer();
            exit();
        }
        $clone->setVar('side', $bside);
        $clone->setVar('weight', $bweight);
        $clone->setVar('visible', $bvisible);
        //$clone->setVar('content', $_POST['bcontent']);
        $clone->setVar('title', Request::getString('btitle', '', 'POST'));
        $clone->setVar('bcachetime', $bcachetime);
        if (\is_array($options) && ($options !== [])) {
            $optionsImploded = \implode('|', $options);
            $clone->setVar('options', $optionsImploded);
        }
        $clone->setVar('bid', 0);
        if (\in_array($block->getVar('block_type'), ['C', 'E'])) {
            $clone->setVar('block_type', 'E');
        } else {
            $clone->setVar('block_type', 'D');
        }
        //        $newid = $clone->store(); //see https://github.com/XOOPS/XoopsCore25/issues/1105
        if ($clone->store()) {
            $newid = $clone->id();  //get the id of the cloned block
        }
        if (!$newid) {
            //            \xoops_cp_header();
            $clone->getHtmlErrors();
            \xoops_cp_footer();
            exit();
        }
        if ('' !== $clone->getVar('template')) {
            /** @var \XoopsTplfileHandler $tplfileHandler */
            $tplfileHandler = \xoops_getHandler('tplfile');
            $btemplate      = $tplfileHandler->find($GLOBALS['xoopsConfig']['template_set'], 'block', (string)$bid);
            if (\count($btemplate) > 0) {
                $tplclone = $btemplate[0]->xoopsClone();
                $tplclone->setVar('tpl_id', 0);
                $tplclone->setVar('tpl_refid', $newid);
                $tplfileHandler->insert($tplclone);
            }
        }

        foreach ($bmodule as $bmid) {
            $sql = 'INSERT INTO ' . $this->db->prefix('block_module_link') . ' (block_id, module_id) VALUES (' . $newid . ', ' . $bmid . ')';
            $this->db->query($sql);
        }
        //$groups = &$GLOBALS['xoopsUser']->getGroups();
        foreach ($groups as $iValue) {
            $sql = 'INSERT INTO ' . $this->db->prefix('group_permission') . ' (gperm_groupid, gperm_itemid, gperm_modid, gperm_name) VALUES (' . $iValue . ', ' . $newid . ", 1, 'block_read')";
            $this->db->query($sql);
        }
        $this->helper->redirect('admin/blocksadmin.php?op=list', 1, _AM_DBUPDATED);
    }

    /**
     * @param string     $bid
     * @param string     $title
     * @param string     $weight
     * @param string     $visible
     * @param string     $side
     * @param string     $bcachetime
     * @param array|null $bmodule
     */
    public function setOrder(string $bid, string $title, string $weight, string $visible, string $side, string $bcachetime, ?array $bmodule = null): void
    {
        $myblock = new \XoopsBlock($bid);
        $myblock->setVar('title', $title);
        $myblock->setVar('weight', $weight);
        $myblock->setVar('visible', $visible);
        $myblock->setVar('side', $side);
        $myblock->setVar('bcachetime', $bcachetime);
        $myblock->store();
        //        /** @var \XoopsBlockHandler $blockHandler */
        //        $blockHandler = \xoops_getHandler('block');
        //        return $blockHandler->insert($myblock);
    }

    /**
     * @param int $bid
     * @return void
     */
    public function editBlock(int $bid): void
    {
        //        require_once \dirname(__DIR__,2) . '/admin/admin_header.php';
        //        \xoops_cp_header();
        \xoops_loadLanguage('admin', 'system');
        \xoops_loadLanguage('admin/blocksadmin', 'system');
        \xoops_loadLanguage('admin/groups', 'system');
        //        mpu_adm_menu();
        $myblock = new \XoopsBlock($bid);
        $modules = [];
        $sql     = 'SELECT module_id FROM ' . $this->db->prefix('block_module_link') . ' WHERE block_id=' . $bid;
        $result  = $this->db->query($sql);
        if (!$this->db->isResultSet($result)) {
            \trigger_error("Query Failed! SQL: $sql Error: " . $this->db->error(), \E_USER_ERROR);
        } else {
            while (false !== ($row = $this->db->fetchArray($result))) {
                $modules[] = (int)$row['module_id'];
            }
        }

        $isCustom = \in_array($myblock->getVar('block_type'), ['C', 'E']);
        $block    = [
            'title'      => $myblock->getVar('title'),
            'form_title' => \_AM_SYSTEM_BLOCKS_EDITBLOCK,
            //        'name'       => $myblock->getVar('name'),
            'side'       => $myblock->getVar('side'),
            'weight'     => $myblock->getVar('weight'),
            'visible'    => $myblock->getVar('visible'),
            'content'    => $myblock->getVar('content', 'N'),
            'modules'    => $modules,
            'is_custom'  => $isCustom,
            'ctype'      => $myblock->getVar('c_type'),
            'bcachetime' => $myblock->getVar('bcachetime'),
            'op'         => 'edit_ok',
            'bid'        => $myblock->getVar('bid'),
            'edit_form'  => $myblock->getOptions(),
            'template'   => $myblock->getVar('template'),
            'options'    => $myblock->getVar('options'),
        ];
        echo '<a href="blocksadmin.php">' . \constant('CO_' . $this->moduleDirNameUpper . '_' . 'BADMIN') . '</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;' . \_AM_SYSTEM_BLOCKS_EDITBLOCK . '<br><br>';

        echo $this->render($block);
    }

    /**
     * @param int        $bid
     * @param string     $btitle
     * @param string     $bside
     * @param string     $bweight
     * @param string     $bvisible
     * @param string     $bcachetime
     * @param array|null $bmodule
     * @param array|null $options
     * @param array|null $groups
     */
    public function updateBlock(int $bid, string $btitle, string $bside, string $bweight, string $bvisible, string $bcachetime, ?array $bmodule, ?array $options, ?array $groups): void
    {
        $myblock = new \XoopsBlock($bid);
        $myblock->setVar('title', $btitle);
        $myblock->setVar('weight', $bweight);
        $myblock->setVar('visible', $bvisible);
        $myblock->setVar('side', $bside);
        $myblock->setVar('bcachetime', $bcachetime);
        //update block options
        if (isset($options)) {
            $optionsCount = \count($options);
            if ($optionsCount > 0) {
                //Convert array values to comma-separated
                foreach ($options as $i => $iValue) {
                    if (\is_array($iValue)) {
                        $options[$i] = \implode(',', $iValue);
                    }
                }
                $optionsImploded = \implode('|', $options);
                $myblock->setVar('options', $optionsImploded);
            }
        }
        $myblock->store();
        //        /** @var \XoopsBlockHandler $blockHandler */
        //        $blockHandler = \xoops_getHandler('block');
        //        $blockHandler->insert($myblock);

        if (!empty($bmodule) && $bmodule !== []) {
            $sql = \sprintf('DELETE FROM `%s` WHERE block_id = %u', $this->db->prefix('block_module_link'), $bid);
            $this->db->query($sql);
            if (\in_array(0, $bmodule)) {
                $sql = \sprintf('INSERT INTO `%s` (block_id, module_id) VALUES (%u, %d)', $this->db->prefix('block_module_link'), $bid, 0);
                $this->db->query($sql);
            } else {
                foreach ($bmodule as $bmid) {
                    $sql = \sprintf('INSERT INTO `%s` (block_id, module_id) VALUES (%u, %d)', $this->db->prefix('block_module_link'), $bid, (int)$bmid);
                    $this->db->query($sql);
                }
            }
        }
        $sql = \sprintf('DELETE FROM `%s` WHERE gperm_itemid = %u', $this->db->prefix('group_permission'), $bid);
        $this->db->query($sql);
        if (!empty($groups)) {
            foreach ($groups as $grp) {
                $sql = \sprintf("INSERT INTO `%s` (gperm_groupid, gperm_itemid, gperm_modid, gperm_name) VALUES (%u, %u, 1, 'block_read')", $this->db->prefix('group_permission'), $grp, $bid);
                $this->db->query($sql);
            }
        }
        $this->helper->redirect('admin/blocksadmin.php', 1, \constant('CO_' . $this->moduleDirNameUpper . '_' . 'UPDATE_SUCCESS'));
    }

    /**
     * @param array $bid
     * @param array $oldtitle
     * @param array $oldside
     * @param array $oldweight
     * @param array $oldvisible
     * @param array $oldgroups
     * @param array $oldbcachetime
     * @param array $oldbmodule
     * @param array $title
     * @param array $weight
     * @param array $visible
     * @param array $side
     * @param array $bcachetime
     * @param array $groups
     * @param array $bmodule
     */
    public function orderBlock(
        array $bid, array $oldtitle, array $oldside, array $oldweight, array $oldvisible, array $oldgroups, array $oldbcachetime, array $oldbmodule, array $title, array $weight, array $visible, array $side, array $bcachetime, array $groups, array $bmodule
    ): void {
        if (!$GLOBALS['xoopsSecurity']->check()) {
            \redirect_header($_SERVER['SCRIPT_NAME'], 3, \implode('<br>', $GLOBALS['xoopsSecurity']->getErrors()));
        }
        foreach (\array_keys($bid) as $i) {
            if ($oldtitle[$i] !== $title[$i]
                || $oldweight[$i] !== $weight[$i]
                || $oldvisible[$i] !== $visible[$i]
                || $oldside[$i] !== $side[$i]
                || $oldbcachetime[$i] !== $bcachetime[$i]
                || $oldbmodule[$i] !== $bmodule[$i]) {
                $this->setOrder($bid[$i], $title[$i], $weight[$i], $visible[$i], $side[$i], $bcachetime[$i], $bmodule[$i]);
            }
            if (!empty($bmodule[$i]) && \count($bmodule[$i]) > 0) {
                $sql = \sprintf('DELETE FROM `%s` WHERE block_id = %u', $this->db->prefix('block_module_link'), $bid[$i]);
                $this->db->query($sql);
                if (\in_array(0, $bmodule[$i], true)) {
                    $sql = \sprintf('INSERT INTO `%s` (block_id, module_id) VALUES (%u, %d)', $this->db->prefix('block_module_link'), $bid[$i], 0);
                    $this->db->query($sql);
                } else {
                    foreach ($bmodule[$i] as $bmid) {
                        $sql = \sprintf('INSERT INTO `%s` (block_id, module_id) VALUES (%u, %d)', $this->db->prefix('block_module_link'), $bid[$i], (int)$bmid);
                        $this->db->query($sql);
                    }
                }
            }
            $sql = \sprintf('DELETE FROM `%s` WHERE gperm_itemid = %u', $this->db->prefix('group_permission'), $bid[$i]);
            $this->db->query($sql);
            if (!empty($groups[$i])) {
                foreach ($groups[$i] as $grp) {
                    $sql = \sprintf("INSERT INTO `%s` (gperm_groupid, gperm_itemid, gperm_modid, gperm_name) VALUES (%u, %u, 1, 'block_read')", $this->db->prefix('group_permission'), $grp, $bid[$i]);
                    $this->db->query($sql);
                }
            }
        }

        $this->helper->redirect('admin/blocksadmin.php', 1, \constant('CO_' . $this->moduleDirNameUpper . '_' . 'UPDATE_SUCCESS'));
    }

    /**
     * @param array|null $block
     * @return void
     */
    public function render(?array $block = null): void
    {
        \xoops_load('XoopsFormLoader');
        \xoops_loadLanguage('common', $this->moduleDirNameUpper);

        $form = new \XoopsThemeForm($block['form_title'], 'blockform', 'blocksadmin.php', 'post', true);
        if (isset($block['name'])) {
            $form->addElement(new \XoopsFormLabel(\_AM_SYSTEM_BLOCKS_NAME, $block['name']));
        }
        $sideSelect = new \XoopsFormSelect(\_AM_SYSTEM_BLOCKS_TYPE, 'bside', $block['side']);
        $sideSelect->addOptionArray([
                                        0 => \_AM_SYSTEM_BLOCKS_SBLEFT,
                                        1 => \_AM_SYSTEM_BLOCKS_SBRIGHT,
                                        3 => \_AM_SYSTEM_BLOCKS_CBLEFT,
                                        4 => \_AM_SYSTEM_BLOCKS_CBRIGHT,
                                        5 => \_AM_SYSTEM_BLOCKS_CBCENTER,
                                        7 => \_AM_SYSTEM_BLOCKS_CBBOTTOMLEFT,
                                        8 => \_AM_SYSTEM_BLOCKS_CBBOTTOMRIGHT,
                                        9 => \_AM_SYSTEM_BLOCKS_CBBOTTOM,
                                    ]);
        $form->addElement($sideSelect);
        $form->addElement(new \XoopsFormText(\constant('CO_' . $this->moduleDirNameUpper . '_' . 'WEIGHT'), 'bweight', 2, 5, $block['weight']));
        $form->addElement(new \XoopsFormRadioYN(\constant('CO_' . $this->moduleDirNameUpper . '_' . 'VISIBLE'), 'bvisible', $block['visible']));
        $modSelect = new \XoopsFormSelect(\constant('CO_' . $this->moduleDirNameUpper . '_' . 'VISIBLEIN'), 'bmodule', $block['modules'], 5, true);
        /** @var \XoopsModuleHandler $moduleHandler */
        $moduleHandler = \xoops_getHandler('module');
        $criteria      = new \CriteriaCompo(new \Criteria('hasmain', '1'));
        $criteria->add(new \Criteria('isactive', '1'));
        $moduleList     = $moduleHandler->getList($criteria);
        $moduleList[-1] = \_AM_SYSTEM_BLOCKS_TOPPAGE;
        $moduleList[0]  = \_AM_SYSTEM_BLOCKS_ALLPAGES;
        \ksort($moduleList);
        $modSelect->addOptionArray($moduleList);
        $form->addElement($modSelect);
        $form->addElement(new \XoopsFormText(\_AM_SYSTEM_BLOCKS_TITLE, 'btitle', 50, 255, $block['title']), false);
        if ($block['is_custom']) {
            $textarea = new \XoopsFormDhtmlTextArea(\_AM_SYSTEM_BLOCKS_CONTENT, 'bcontent', $block['content'], 15, 70);
            $textarea->setDescription('<span style="font-size:x-small;font-weight:bold;">' . \_AM_SYSTEM_BLOCKS_USEFULTAGS . '</span><br><span style="font-size:x-small;font-weight:normal;">' . \sprintf(_AM_BLOCKTAG1, '{X_SITEURL}', XOOPS_URL . '/') . '</span>');
            $form->addElement($textarea, true);
            $ctypeSelect = new \XoopsFormSelect(\_AM_SYSTEM_BLOCKS_CTYPE, 'bctype', $block['ctype']);
            $ctypeSelect->addOptionArray([
                                             'H' => \_AM_SYSTEM_BLOCKS_HTML,
                                             'P' => \_AM_SYSTEM_BLOCKS_PHP,
                                             'S' => \_AM_SYSTEM_BLOCKS_AFWSMILE,
                                             'T' => \_AM_SYSTEM_BLOCKS_AFNOSMILE,
                                         ]);
            $form->addElement($ctypeSelect);
        } else {
            if ('' !== $block['template']) {
                /** @var \XoopsTplfileHandler $tplfileHandler */
                $tplfileHandler = \xoops_getHandler('tplfile');
                $btemplate      = $tplfileHandler->find($GLOBALS['xoopsConfig']['template_set'], 'block', $block['bid']);
                if (\count($btemplate) > 0) {
                    $form->addElement(new \XoopsFormLabel(\_AM_SYSTEM_BLOCKS_CONTENT, '<a href="' . XOOPS_URL . '/modules/system/admin.php?fct=tplsets&amp;op=edittpl&amp;id=' . $btemplate[0]->getVar('tpl_id') . '">' . \_AM_SYSTEM_BLOCKS_EDITTPL . '</a>'));
                } else {
                    $btemplate2 = $tplfileHandler->find('default', 'block', $block['bid']);
                    if (\count($btemplate2) > 0) {
                        $form->addElement(new \XoopsFormLabel(\_AM_SYSTEM_BLOCKS_CONTENT, '<a href="' . XOOPS_URL . '/modules/system/admin.php?fct=tplsets&amp;op=edittpl&amp;id=' . $btemplate2[0]->getVar('tpl_id') . '" target="_blank">' . \_AM_SYSTEM_BLOCKS_EDITTPL . '</a>'));
                    }
                }
            }
            if (false !== $block['edit_form']) {
                $form->addElement(new \XoopsFormLabel(\_AM_SYSTEM_BLOCKS_OPTIONS, $block['edit_form']));
            }
        }
        $cache_select = new \XoopsFormSelect(\_AM_SYSTEM_BLOCKS_BCACHETIME, 'bcachetime', $block['bcachetime']);
        $cache_select->addOptionArray([
                                          0       => \_NOCACHE,
                                          30      => \sprintf(\_SECONDS, 30),
                                          60      => \_MINUTE,
                                          300     => \sprintf(\_MINUTES, 5),
                                          1800    => \sprintf(\_MINUTES, 30),
                                          3600    => \_HOUR,
                                          18000   => \sprintf(\_HOURS, 5),
                                          86400   => \_DAY,
                                          259200  => \sprintf(\_DAYS, 3),
                                          604800  => \_WEEK,
                                          2592000 => \_MONTH,
                                      ]);
        $form->addElement($cache_select);

        /** @var \XoopsGroupPermHandler $grouppermHandler */
        $grouppermHandler = \xoops_getHandler('groupperm');
        $groups           = $grouppermHandler->getGroupIds('block_read', $block['bid']);

        $form->addElement(new \XoopsFormSelectGroup(\_AM_SYSTEM_BLOCKS_GROUP, 'groups', true, $groups, 5, true));

        if (isset($block['bid'])) {
            $form->addElement(new \XoopsFormHidden('bid', $block['bid']));
        }
        $form->addElement(new \XoopsFormHidden('op', $block['op']));
        $form->addElement(new \XoopsFormHidden('fct', 'blocksadmin'));
        $buttonTray = new \XoopsFormElementTray('', '&nbsp;');
        if ($block['is_custom']) {
            $buttonTray->addElement(new \XoopsFormButton('', 'previewblock', \_PREVIEW, 'submit'));
        }

        //Submit buttons
        $buttonTray   = new \XoopsFormElementTray('', '');
        $submitButton = new \XoopsFormButton('', 'submitblock', \_SUBMIT, 'submit');
        $buttonTray->addElement($submitButton);

        $cancelButton = new \XoopsFormButton('', '', \_CANCEL, 'button');
        $cancelButton->setExtra('onclick="history.go(-1)"');
        $buttonTray->addElement($cancelButton);

        $form->addElement($buttonTray);
        $form->display();
    }
}

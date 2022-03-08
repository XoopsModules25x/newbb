<?php declare(strict_types=1);

namespace XoopsModules\Newbb;

use XoopsModules\Newbb;

/**
 * Class GroupPermForm
 */
class GroupPermForm extends \XoopsGroupPermForm
{
    /**
     * @param        $title
     * @param        $modid
     * @param        $permname
     * @param        $permdesc
     * @param string $url
     */
    public function __construct($title, $modid, $permname, $permdesc, $url = '')
    {
        parent::__construct($title, $modid, $permname, $permdesc, $url);
    }

    /**
     * @param        $title
     * @param        $modid
     * @param        $permname
     * @param        $permdesc
     * @param string $url
     */

    /*
    public function newbb_XoopsGroupPermForm($title, $modid, $permname, $permdesc, $url = "")
    {
//        $this->XoopsGroupPermForm($title, $modid, $permname, $permdesc, $url);
        self::__construct($title, $modid, $permname, $permdesc, $url);
    }
*/

    /**
     * @return string
     */
    public function render()
    {
        // load all child ids for javascript codes
        foreach (\array_keys($this->_itemTree) as $item_id) {
            $this->_itemTree[$item_id]['allchild'] = [];
            $this->_loadAllChildItemIds($item_id, $this->_itemTree[$item_id]['allchild']);
        }
        /** @var \XoopsGroupPermHandler $grouppermHandler */
        $grouppermHandler = \xoops_getHandler('groupperm');
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = \xoops_getHandler('member');
        $glist         = $memberHandler->getGroupList();
        foreach (\array_keys($glist) as $i) {
            // get selected item id(s) for each group
            $selected = $grouppermHandler->getItemIds($this->_permName, $i, $this->_modid);
            $ele      = new GroupFormCheckBox($glist[$i], 'perms[' . $this->_permName . ']', $i, $selected);
            $ele->setOptionTree($this->_itemTree);
            $this->addElement($ele);
            unset($ele);
        }
        $tray = new \XoopsFormElementTray('');
        $tray->addElement(new \XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
        $tray->addElement(new \XoopsFormButton('', 'reset', _CANCEL, 'reset'));
        $this->addElement($tray);
        $ret      = '<br><strong>' . $this->getTitle() . '</strong><br>' . $this->_permDesc . '<br>';
        $ret      .= "<form name='" . $this->getName() . "' id='" . $this->getName() . "' action='" . $this->getAction() . "' method='" . $this->getMethod() . "'" . $this->getExtra() . ">\n<table width='100%' class='outer' cellspacing='1' valign='top'>\n";
        $elements = $this->getElements();
        $hidden   = '';
        foreach (\array_keys($elements) as $i) {
            if (!\is_object($elements[$i])) {
                $ret .= $elements[$i];
            } elseif ($elements[$i]->isHidden()) {
                $hidden .= $elements[$i]->render();
            } else {
                $ret .= "<tr valign='top' align='left'><td class='head'>" . $elements[$i]->getCaption();
                if ('' !== $elements[$i]->getDescription()) {
                    $ret .= '<br><br><span style="font-weight: normal;">' . $elements[$i]->getDescription() . '</span>';
                }
                $ret .= "</td>\n<td class='even' style='text-align:center;'>\n" . $elements[$i]->render() . "\n</td></tr>\n";
            }
        }
        $ret .= "</table>$hidden</form>";
        $ret .= $this->renderValidationJS(true);

        return $ret;
    }
}

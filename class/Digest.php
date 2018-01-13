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

class Digest extends \XoopsObject
{
    public $digest_id;
    public $digest_time;
    public $digest_content;

    public $items;
    public $isHtml    = false;
    public $isSummary = true;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->initVar('digest_id', XOBJ_DTYPE_INT);
        $this->initVar('digest_time', XOBJ_DTYPE_INT);
        $this->initVar('digest_content', XOBJ_DTYPE_TXTAREA);
        $this->items = [];
    }

    public function setHtml()
    {
        $this->isHtml = true;
    }

    public function setSummary()
    {
        $this->isSummary = true;
    }

    /**
     * @param        $title
     * @param        $link
     * @param        $author
     * @param string $summary
     */
    public function addItem($title, $link, $author, $summary = '')
    {
        $title  = $this->cleanup($title);
        $author = $this->cleanup($author);
        if (!empty($summary)) {
            $summary = $this->cleanup($summary);
        }
        $this->items[] = ['title' => $title, 'link' => $link, 'author' => $author, 'summary' => $summary];
    }

    /**
     * @param $text
     * @return mixed|string
     */
    public function cleanup($text)
    {
        global $myts;

        $clean = stripslashes($text);
        $clean =& $myts->displayTarea($clean, 1, 0, 1);
        $clean = strip_tags($clean);
        $clean = htmlspecialchars($clean, ENT_QUOTES);

        return $clean;
    }

    /**
     * @param  bool $isSummary
     * @param  bool $isHtml
     * @return bool
     */
    public function buildContent($isSummary = true, $isHtml = false)
    {
        $digest_count = count($this->items);
        $content      = '';
        if ($digest_count > 0) {
            $linebreak = $isHtml ? '<br>' : "\n";
            for ($i = 0; $i < $digest_count; ++$i) {
                if ($isHtml) {
                    $content .= ($i + 1) . '. <a href=' . $this->items[$i]['link'] . '>' . $this->items[$i]['title'] . '</a>';
                } else {
                    $content .= ($i + 1) . '. ' . $this->items[$i]['title'] . $linebreak . $this->items[$i]['link'];
                }

                $content .= $linebreak . $this->items[$i]['author'];
                if ($isSummary) {
                    $content .= $linebreak . $this->items[$i]['summary'];
                }
                $content .= $linebreak . $linebreak;
            }
        }
        $this->setVar('digest_content', $content);

        return true;
    }
}

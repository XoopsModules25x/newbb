<?php declare(strict_types=1);

namespace XoopsModules\Newbb;

/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 */
class Digest extends \XoopsObject
{
    public $digest_id;
    public $digest_time;
    public $digest_content;
    public $items;
    public $isHtml    = false;
    public $isSummary = true;

    public function __construct()
    {
        parent::__construct();
        $this->initVar('digest_id', \XOBJ_DTYPE_INT);
        $this->initVar('digest_time', \XOBJ_DTYPE_INT);
        $this->initVar('digest_content', \XOBJ_DTYPE_TXTAREA);
        $this->items = [];
    }

    public function setHtml(): void
    {
        $this->isHtml = true;
    }

    public function setSummary(): void
    {
        $this->isSummary = true;
    }

    /**
     * @param        $title
     * @param        $link
     * @param        $author
     * @param string $summary
     */
    public function addItem($title, $link, $author, $summary = ''): void
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
     * @return string
     */
    public function cleanup($text)
    {
        global $myts;

        $clean = \stripslashes($text);
        $clean = &$myts->displayTarea($clean, 1, 0, 1);
        $clean = \strip_tags($clean);
        $clean = \htmlspecialchars((string)$clean, \ENT_QUOTES);

        return $clean;
    }

    /**
     * @param bool $isSummary
     * @param bool $isHtml
     * @return bool
     */
    public function buildContent($isSummary = true, $isHtml = false)
    {
        $digest_count = \count($this->items);
        $content      = '';
        if ($digest_count > 0) {
            $linebreak = $isHtml ? '<br>' : "\n";
            foreach ($this->items as $i => $iValue) {
                if ($isHtml) {
                    $content .= ($i + 1) . '. <a href=' . $iValue['link'] . '>' . $iValue['title'] . '</a>';
                } else {
                    $content .= ($i + 1) . '. ' . $iValue['title'] . $linebreak . $iValue['link'];
                }

                $content .= $linebreak . $iValue['author'];
                if ($isSummary) {
                    $content .= $linebreak . $iValue['summary'];
                }
                $content .= $linebreak . $linebreak;
            }
        }
        $this->setVar('digest_content', $content);

        return true;
    }
}

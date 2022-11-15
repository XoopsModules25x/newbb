<?php declare(strict_types=1);
/**
 * NewBB 4.3x, the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>, irmtfan <irmtfan@users.sourceforge.net>
 * @since          4.3
 */
define('NEWBB_FUNCTIONS_TEXT_LOADED', true);

if (!defined('NEWBB_FUNCTIONS_TEXT')) {
    define('NEWBB_FUNCTIONS_TEXT', 1);
    /**
     * function for select from a text where it have some keywords
     *
     * @param string       $text
     * @param array|string $queryarray
     * @param int          $selectstartlag
     * @param int          $selectlength
     * @param bool         $striptags
     * @param string       $excludetags
     * @param string       $start_trimmarker
     * @param string       $end_trimmarker
     * @return string
     */
    function newbb_selectText(
        $text,
        $queryarray,
        $selectstartlag = 100,
        $selectlength = 200,
        $striptags = true,
        $excludetags = '<br>',
        $start_trimmarker = '[...]',
        $end_trimmarker = '[...]'
    ) {
        $sanitized_text       = $striptags ? strip_tags($text, $excludetags) : $text;
        $queryarray           = newbb_str2array($queryarray);
        $text_i               = mb_strtolower($sanitized_text);
        $queryarray           = array_map('\strtolower', $queryarray);
        $lengtharray          = array_map('\strlen', $queryarray);
        $maxlengthquery       = max($lengtharray);
        $lengthend_trimmarker = mb_strlen($end_trimmarker);
        $select_text          = '';
        $startpos             = 0;
        $endpos               = mb_strlen($sanitized_text);
        while ($startpos < $endpos) {
            $pos = $endpos;
            foreach ($queryarray as $query) {
                if (false !== ($thispos = mb_strpos($text_i, $query, $startpos))) {
                    $pos = min($thispos, $pos);
                }
            }
            if ($pos == $endpos) {
                break;
            }
            $start       = max($pos - $selectstartlag, $startpos - $maxlengthquery, 0); // $startpos is the last position in the previous select text
            $length      = $maxlengthquery + $selectlength; //xoops_local("strlen", $query) + 200;
            $select_text .= '<p>';
            $select_text .= ($start > 0) ? $start_trimmarker . ' ' : ' ';
            $select_text .= xoops_substr($sanitized_text, $start, $length + $lengthend_trimmarker + 1, ' ' . $end_trimmarker) . '</p>';
            $startpos    = $start + $length + 1; // start searching from next position.
        }
        if (empty($select_text)) {
            return '';
        } // if no text return empty string

        return '<span class="newbb_select_text">' . $select_text . '</span>';
    }

    /**
     * function for highlight a text when it have some keywords
     *
     * @param string       $text
     * @param array|string $queryarray
     * @return string
     */
    function newbb_highlightText($text, $queryarray)
    {
        if (empty($GLOBALS['xoopsModuleConfig']['highlight_search_enable'])) {
            return $text;
        }
        $queryarray = newbb_str2array($queryarray);
        // if $queryarray is string
        $highlight_text = $text;
        foreach ($queryarray as $key => $query) {
            // use preg_replace instead of str_replace to exclude all $queries inside html span tag
            $highlight_text = preg_replace('/(?!(?:[^<]+>|[^>]+<\/a>))(' . preg_quote($query, '/') . ')/si', newbb_highlighter($query, $key), $highlight_text);
        }

        return $highlight_text;
    }

    /**
     * function for highlighting search results
     *
     * @param string $query
     * @param int    $i
     * @return string
     */
    function newbb_highlighter($query, $i)
    {
        return '<span class="newbb_highlight term' . $i . '">' . $query . '</span>';
    }

    /**
     * function for convert string to array
     *
     * @param string|array $str
     * @return array
     */
    function newbb_str2array($str)
    {
        if (is_array($str)) {
            return $str;
        }

        // split the phrase by any number of commas or space characters,
        // which include " ", \r, \t, \n and \f
        $temp_str = preg_split('/[\s,]+/', $str);
        $strarray = [];
        foreach ($temp_str as $s) {
            $strarray[] = addslashes($s);
        }

        return $strarray;
    }
}

<?php
/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 * @package        module::newbb
 */

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

defined('NEWBB_FUNCTIONS_INI') || require_once __DIR__ . '/functions.ini.php';
define('NEWBB_FUNCTIONS_TIME_LOADED', true);

if (!defined('NEWBB_FUNCTIONS_TIME')) {
    define('NEWBB_FUNCTIONS_TIME', 1);

    /**
     * Function to convert UNIX time to formatted time string
     * @param         $time
     * @param  string $format
     * @param  string $timeoffset
     * @return string
     */
    function newbbFormatTimestamp($time, $format = 'c', $timeoffset = '')
    {
        xoops_loadLanguage('locale');
        $newbbConfig = newbbLoadConfig();

        $format = strtolower($format);
        if ('reg' === $format || '' === $format) {
            $format = 'c';
        }
        if (('custom' === $format || 'c' === $format) && !empty($newbbConfig['formatTimestamp_custom'])) {
            $format = $newbbConfig['formatTimestamp_custom'];
        }

        return XoopsLocal::formatTimestamp($time, $format, $timeoffset);
    }

    /**
     * @param  int $selected
     * @return string
     */
    function newbbSinceSelectBox($selected = 100)
    {
        $newbbConfig = newbbLoadConfig();
        // irmtfan - new method to get user inputs
        preg_match_all('/-?\d+/', $newbbConfig['since_options'], $match);
        $select_array = array_unique($match[0]);
        //$select_array = explode(',', $newbbConfig['since_options']);
        //$select_array = array_map('trim', $select_array);
        // irmtfan - if the array is empty do not show selection box
        if (!(bool)$select_array) {
            $since = $newbbConfig['since_default'];
            switch ($since) {
                case 0:
                    $forum_since = _MD_NEWBB_BEGINNING;
                    break;
                case 365:
                    $forum_since = _MD_NEWBB_THELASTYEAR;
                    break;
                default:
                    if ($since > 0) {
                        $forum_since = sprintf(_MD_NEWBB_FROMLASTDAYS, $since);
                    } else {
                        $forum_since = sprintf(_MD_NEWBB_FROMLASTHOURS, abs($since));
                    }
            }

            return $forum_since;
        }
        $forum_selection_since = '<select class="form-control" name="since" id="since">';
        // irmtfan no option when no selected value
        $forum_selection_since .= '<option value="">--------</option>';
        foreach ($select_array as $since) {
            $forum_selection_since .= '<option value="' . $since . '"' . (($selected == $since) ? ' selected="selected"' : '') . '>';
            // START irmtfan functional since 0 and 365
            switch ($since) {
                case 0:
                    $forum_selection_since .= _MD_NEWBB_BEGINNING;
                    break;
                case 365:
                    $forum_selection_since .= _MD_NEWBB_THELASTYEAR;
                    break;
                default:
                    if ($since > 0) {
                        $forum_selection_since .= sprintf(_MD_NEWBB_FROMLASTDAYS, $since);
                    } else {
                        $forum_selection_since .= sprintf(_MD_NEWBB_FROMLASTHOURS, abs($since));
                    }
            }
            // END irmtfan functional since 0 and 365
            $forum_selection_since .= '</option>';
        }
        // irmtfan remove hardcodes
        //$forum_selection_since .= '<option value="365"'.(($selected === 365) ? ' selected="selected"' : '').'>'._MD_NEWBB_THELASTYEAR.'</option>';
        //$forum_selection_since .= '<option value="0"'.(($selected === 0) ? ' selected="selected"' : '').'>'._MD_NEWBB_BEGINNING.'</option>';
        $forum_selection_since .= '</select>';

        return $forum_selection_since;
    }

    /**
     * @param  int $since
     * @return int
     */
    function newbbGetSinceTime($since = 100)
    {
        // irmtfan bad coding
        //if ($since==1000) return 0;
        if ($since > 0) {
            return (int)$since * 24 * 3600;
        } else {
            return (int)abs($since) * 3600;
        }
    }
}

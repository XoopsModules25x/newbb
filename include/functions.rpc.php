<?php declare(strict_types=1);
/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>
 * @since          4.00
 */
defined('NEWBB_FUNCTIONS_INI') || require __DIR__ . '/functions.ini.php';
define('NEWBB_FUNCTIONS_RPC_LOADED', true);

if (!defined('NEWBB_FUNCTIONS_RPC')) {
    define('NEWBB_FUNCTIONS_RPC', 1);

    /**
     * Function to respond to a trackback
     * @param int    $error
     * @param string $error_message
     */
    function newbbTrackbackResponse($error = 0, $error_message = ''): void
    {
        $moduleConfig = newbbLoadConfig();

        if (!empty($moduleConfig['rss_utf8'])) {
            $charset       = 'utf-8';
            $error_message = xoops_utf8_encode($error_message);
        } else {
            $charset = _CHARSET;
        }
        header('Content-Type: text/xml; charset="' . $charset . '"');
        if ($error) {
            echo '<?xml version="1.0" encoding="' . $charset . '"?' . ">\n";
            echo '<response>\n';
            echo '<error>1</error>\n';
            echo '<message>$error_message</message>\n';
            echo '</response>';
            exit();
        }
        echo '<?xml version="1.0" encoding="' . $charset . '"?' . ">\n";
        echo '<response>\n';
        echo '<error>0</error>\n';
        echo '</response>';
    }
}

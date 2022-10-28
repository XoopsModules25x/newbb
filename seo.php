<?php declare(strict_types=1);

use Xmf\Request;

require_once __DIR__ . '/header.php';
/*
 *
 * Module: newbbss
 * Author: Sudhaker Raj <https://xoops.biz>
 * Licence: GNU
 */
$seoOp    = Request::getString('seoOp', '', 'GET');
$seoArg   = Request::getInt('seoArg', 0, 'GET');
$seoOther = Request::getString('seoOther', '', 'GET');

$seos = ['c', 'f', 't', 'p', 'rc', 'rf', 'v', 'pr', 'pdf'];

$seoMap = [
    'c'   => 'index.php',
    'f'   => 'viewforum.php',
    't'   => 'viewtopic.php',
    'p'   => 'viewtopic.php',
    'rc'  => 'rss.php',
    'rf'  => 'rss.php',
    'pr'  => 'print.php',
    'pdf' => 'makepdf.php',
];

if (!empty($seoOp) && !empty($seoMap[$seoOp]) && in_array($seoOp, $seos, true)) {
    // module specific dispatching logic, other module must implement as
    // per their requirements.
    $ori_self               = Request::getString('SCRIPT_NAME', '', 'SERVER');
    $ori_self               = explode('modules/newbb', $ori_self);
    $newUrl                 = $ori_self[0] . 'modules/newbb/' . $seoMap[$seoOp];
    $_ENV['SCRIPT_NAME']    = $newUrl;
    $_SERVER['SCRIPT_NAME'] = $newUrl;
    $_SERVER['SCRIPT_NAME'] = $newUrl;
    switch ($seoOp) {
        case 'c':
            $_SERVER['REQUEST_URI'] = $newUrl . '?cat=' . $seoArg;
            $_GET['cat']            = $seoArg;
            break;
        case 'f':
            $_SERVER['REQUEST_URI'] = $newUrl . '?forum=' . $seoArg;
            $_GET['forum']          = $seoArg;
            break;
        case 'p':
            $_SERVER['REQUEST_URI'] = $newUrl . '?post_id=' . $seoArg;
            $_GET['post_id']        = $seoArg;
            break;
        case 'rc':
            $_SERVER['REQUEST_URI'] = $newUrl . '?c=' . $seoArg;
            $_GET['c']              = $seoArg;
            break;
        case 'rf':
            $_SERVER['REQUEST_URI'] = $newUrl . '?f=' . $seoArg;
            $_GET['f']              = $seoArg;
            break;
        default:
        case 't':
        case 'pr':
            $_SERVER['REQUEST_URI'] = $newUrl . '?topic_id=' . $seoArg;
            $_GET['topic_id']       = $seoArg;
            break;
    }
    require_once $seoMap[$seoOp];
} else {
    $last = $seoOp . '/' . $seoArg;
    if ('' !== $seoOther) {
        $last .= '/' . $seoOther;
    }
    require_once $last;
}
exit();

/**
 * @param $value
 * @return string
 */
function checker(&$value)
{
    // keine Tags erlaubt
    $value = strip_tags($value);

    // HTML-Tags maskieren
    $value = htmlspecialchars((string)$value, ENT_QUOTES);

    // Leerzeichen am Anfang und Ende beseitigen
    $value = trim($value);

    // pruefe auf javascript include
    if (false !== mb_strpos($value, '<script')) {
        $value = '';
    }

    // pruefe auf Kommentare (SQL-Injections)
    if (false !== mb_strpos($value, '/*')) {
        $value = '';
    }

    // pruefe UNION Injections
    if (preg_match('/\sUNION\s+(ALL|SELECT)/i', $value)) {
        $value = '';
    }

    // Nullbyte Injection
    if (false !== mb_strpos($value, chr(0))) {
        $value = '';
    }

    //pruefe Verzeichnis
    if (false !== mb_strpos($value, '../')) {
        $value = '';
    }

    //pruefe auf externe
    $str = mb_strstr($value, '://');
    if (false !== mb_strpos($value, '://')) {
        $value = '';
    }

    return $value;
}

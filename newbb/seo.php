<?php
/*
 * $Id: seo.php 12504 2014-04-26 01:01:06Z beckmi $
 * Module: newbbss
 * Author: Sudhaker Raj <http://xoops.biz>
 * Licence: GNU
 */
$seoOp        = $_GET['seoOp'] = checker ( $_GET['seoOp'] );
$seoArg    = (int) $_GET['seoArg'];
$seoOther    = $_GET['seoOther'] = checker ( $_GET['seoOther'] );

$seos=array('c','f','t','p','rc','rf','v','pr','pdf');

$seoMap = array(
    'c'     => 'index.php',
    'f'     => 'viewforum.php',
    't'     => 'viewtopic.php',
    'p'     => 'viewtopic.php',
    'rc'    => 'rss.php',
    'rf'    => 'rss.php',
    'pr'    => 'print.php',
    'pdf'    => 'makepdf.php'
);

if (! empty($seoOp) && ! empty($seoMap[$seoOp]) && in_array($seoOp,$seos) ) {
    // module specific dispatching logic, other module must implement as
    // per their requirements.
    $ori_self = $_SERVER['PHP_SELF'];
    $ori_self = explode("modules/newbb", $ori_self);
    $newUrl = $ori_self[0] . 'modules/newbb/' . $seoMap[$seoOp];
    $_ENV['PHP_SELF']        = $newUrl;
    $_SERVER['SCRIPT_NAME'] = $newUrl;
    $_SERVER['PHP_SELF']    = $newUrl;
    switch ($seoOp) {
        case 'c':
            $_SERVER['REQUEST_URI'] = $newUrl . '?cat=' . $seoArg;
            $_GET['cat'] = $seoArg;
            break;
        case 'f':
            $_SERVER['REQUEST_URI'] = $newUrl . '?forum=' . $seoArg;
            $_GET['forum'] = $seoArg;
            break;
        case 'p':
            $_SERVER['REQUEST_URI'] = $newUrl . '?post_id=' . $seoArg;
            $_GET['post_id'] = $seoArg;
            break;
        case 'rc':
            $_SERVER['REQUEST_URI'] = $newUrl . '?c=' . $seoArg;
            $_GET['c'] = $seoArg;
            break;
        case 'rf':
            $_SERVER['REQUEST_URI'] = $newUrl . '?f=' . $seoArg;
            $_GET['f'] = $seoArg;
            break;
        default:
        case 't':
        case 'pr':
            $_SERVER['REQUEST_URI'] = $newUrl . '?topic_id=' . $seoArg;
            $_GET['topic_id'] = $seoArg;
            break;
    }
    include( $seoMap[$seoOp]);

} else {
    $last = $seoOp . "/" . $seoArg ;
    if ($seoOther != '') $last .= "/" . $seoOther;
    include $last;
}
exit();

function checker(&$value)
{
    // keine Tags erlaubt
    $value = strip_tags($value);

    // HTML-Tags maskieren
    $value = htmlspecialchars($value, ENT_QUOTES);

    // Leerzeichen am Anfang und Ende beseitigen
    $value = trim($value);

    // pruefe auf javascript include
    if ( strstr($value , '<script') !== false ) $value = '';

    // pruefe auf Kommentare (SQL-Injections)
    if ( strstr($value , '/*' !== false) ) $value = '';

    // pruefe UNION Injections
    if ( preg_match('/\sUNION\s+(ALL|SELECT)/i' , $value) ) $value = '';

    // Nullbyte Injection
    if ( strstr($value , chr(0)) !== false ) $value = '';

    //pruefe Verzeichnis
    if ( strstr($value , '../') !== false ) $value = '';

    //pruefe auf externe
    $str = strstr( $value , '://' ) ;
    if ( strstr($value , '://') !== false ) $value = '';

    return $value;
}

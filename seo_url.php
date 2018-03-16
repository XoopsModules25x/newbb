<?php
// defined('XOOPS_ROOT_PATH') || die('Restricted access');

use XoopsModules\Newbb;

define('REAL_MODULE_NAME', 'modules/newbb');  //this is the Real Module directory
define('SEO_MODULE_NAME', 'modules/newbb');  //this is SEO Name for rewrite Hack

//ob_start('seo_urls');

/**
 * @param $s
 * @return mixed
 */
function seo_urls($s)
{
    $XPS_URL     = str_replace('/', '\/', quotemeta(XOOPS_URL));
    $module_name = str_replace('/', '\/', quotemeta(SEO_MODULE_NAME));

    $search = [

        // Search URLs of modules' directry.
        '/<(a|meta)([^>]*)(href|url)=([\'\"]{0,1})' . $XPS_URL . '\/' . $module_name . '\/(index.php)([^>\'\"]*)([\'\"]{1})([^>]*)>/i',
        '/<(a|meta)([^>]*)(href|url)=([\'\"]{0,1})' . $XPS_URL . '\/' . $module_name . '\/(viewpost.php)([^>\'\"]*)([\'\"]{1})([^>]*)>/i',
        '/<(a|meta)([^>]*)(href|url)=([\'\"]{0,1})' . $XPS_URL . '\/' . $module_name . '\/(rss.php)([^>\'\"]*)([\'\"]{1})([^>]*)>/i',
        '/<(a|meta)([^>]*)(href|url)=([\'\"]{0,1})' . $XPS_URL . '\/' . $module_name . '\/(viewforum.php)([^>\'\"]*)([\'\"]{1})([^>]*)>/i',
        '/<(a|meta)([^>]*)(href|url)=([\'\"]{0,1})' . $XPS_URL . '\/' . $module_name . '\/(viewtopic.php)([^>\'\"]*)([\'\"]{1})([^>]*)>/i',
        '/<(a|meta)([^>]*)(href|url)=([\'\"]{0,1})' . $XPS_URL . '\/' . $module_name . '\/(newtopic.php)([^>\'\"]*)([\'\"]{1})([^>]*)>/i',
        '/<(a|meta)([^>]*)(href|url)=([\'\"]{0,1})' . $XPS_URL . '\/' . $module_name . '\/(.*)([^>\'\"]*)([\'\"]{1})([^>]*)>/i'
    ];

    $s = preg_replace_callback($search, 'replace_links', $s);

    return $s;
}

/**
 * @param $matches
 * @return string
 */
function replace_links($matches)
{
    switch ($matches[5]) {
        case 'index.php':
            $add_to_url = '';
            $req_string = $matches[6];
            if (!empty($matches[6])) {
                //                replacing cat=x
                if (preg_match('/cat=(\d+)/', $matches[6], $mvars)) {
                    $add_to_url = 'c-' . $mvars[1] . '/' . forum_seo_cat($mvars[1]) . '';
                    $req_string = preg_replace('/cat=\d+/', '', $matches[6]);
                } else {
                    return $matches['0'];
                }
            }
            break;
        case 'viewpost.php':
            $add_to_url = '';
            $req_string = $matches[6];
            if (!empty($matches[6])) {
                //                replacing status=x
                if (preg_match('/status=([a-z]+)/', $matches[6], $mvars)) {
                    $add_to_url = 'viewpost.php' . $matches[6];
                    $req_string = preg_replace('/status=([a-z])+/', '', $matches[6]);
                } else {
                    return $matches['0'];
                }
            } else {
                $add_to_url = 'viewpost.php' . $matches[6];
            }
            break;
        case 'rss.php':
            $add_to_url = '';
            $req_string = $matches[6];
            if (!empty($matches[6])) {
                //                replacing c=x
                if (preg_match('/c=(\d+)/', $matches[6], $mvars)) {
                    $add_to_url = 'rc-';
                    if ($mvars[1] > 0) {
                        $add_to_url .= $mvars[1] . '/' . forum_seo_cat($mvars[1]) . '';
                    } else {
                        $add_to_url .= $mvars[1] . '/rss.html';
                    }
                    $req_string = preg_replace('/c=\d+/', '', $matches[6]);
                } elseif (preg_match('/f=(\d+)/', $matches[6], $mvars)) {
                    $add_to_url = 'rf-';
                    if ($mvars[1] > 0) {
                        $add_to_url .= $mvars[1] . '/' . forum_seo_forum($mvars[1]) . '';
                    } else {
                        $add_to_url .= $mvars[1] . '/rss.html';
                    }
                    $req_string = preg_replace('/f=\d+/', '', $matches[6]);
                } else {
                    return $matches['0'];
                }
                //$add_to_url .= 'rss-feed.html';
            }
            break;
        case 'viewforum.php':
            $add_to_url = '';
            $req_string = $matches[6];
            if (!empty($matches[6])) {
                //                replacing forum=x
                if (preg_match('/forum=(\d+)/', $matches[6], $mvars)) {
                    $add_to_url = 'f-' . $mvars[1] . '/' . forum_seo_forum($mvars[1]) . '';
                    $req_string = preg_replace('/forum=\d+/', '', $matches[6]);
                } else {
                    return $matches['0'];
                }
            }
            break;
        case 'viewtopic.php':
            $add_to_url = '';
            $req_string = $matches[6];
            if (!empty($matches[6])) {
                //                replacing topic_id=x
                if (preg_match('/topic_id=(\d+)/', $matches[6], $mvars)) {
                    $add_to_url = 't-' . $mvars[1] . '/' . forum_seo_topic($mvars[1]) . '';
                    $req_string = preg_replace('/topic_id=\d+/', '', $matches[6]);
                } //replacing post_id=x
                elseif (preg_match('/post_id=(\d+)/', $matches[6], $mvars)) {
                    $add_to_url = 'p-' . $mvars[1] . '/' . forum_seo_post($mvars[1]) . '';
                    $req_string = preg_replace('/post_id=\d+/', '', $matches[6]);
                } else {
                    return $matches['0'];
                }
            }
            break;
        case 'print.php':
            $add_to_url = '';
            $req_string = $matches[6];
            if (!empty($matches[6])) {
                //                replacing topic_id=x
                if (preg_match('/topic_id=(\d+)/', $matches[6], $mvars)) {
                    $add_to_url = 'pr-' . $mvars[1] . '/' . forum_seo_topic($mvars[1]) . '';
                    $req_string = preg_replace('/topic_id=\d+/', '', $matches[6]);
                } //replacing post_id=x
                elseif (preg_match('/post_id=(\d+)/', $matches[6], $mvars)) {
                    $add_to_url = 'pr-' . $mvars[1] . '/' . forum_seo_post($mvars[1]) . '';
                    $req_string = preg_replace('/post_id=\d+/', '', $matches[6]);
                } else {
                    return $matches['0'];
                }
            }
            break;
        case 'makepdf.php':
            $add_to_url = '';
            $req_string = $matches[6];
            if (!empty($matches[6])) {
                //                replacing topic_id=x
                if (preg_match('/topic_id=(\d+)/', $matches[6], $mvars)) {
                    $add_to_url = 'pdf-' . $mvars[1] . '/' . forum_seo_topic($mvars[1]) . '';
                    $req_string = preg_replace('/topic_id=\d+/', '', $matches[6]);
                } //replacing post_id=x
                elseif (preg_match('/post_id=(\d+)/', $matches[6], $mvars)) {
                    $add_to_url = 'pdf-' . $mvars[1] . '/' . forum_seo_post($mvars[1]) . '';
                    $req_string = preg_replace('/post_id=\d+/', '', $matches[6]);
                } else {
                    return $matches['0'];
                }
            }
            break;
        default:
            $req_string = $matches[6];
            $add_to_url = $matches[5];
            //if ($add_to_url === '') $add_to_url ='index.php';
            break;
    }
    if ('?' === $req_string) {
        $req_string = '';
    }
    $ret = '<' . $matches[1] . $matches[2] . $matches[3] . '=' . $matches[4] . XOOPS_URL . '/' . SEO_MODULE_NAME . '/' . $add_to_url . $req_string . $matches[7] . $matches[8] . '>';

    //$ret = '<'.$matches[1].$matches[2].$matches[3].'='.$matches[4].XOOPS_URL.'/'.REAL_MODULE_NAME.'/'.$add_to_url.$req_string.$matches[7].$matches[8].'>';
    return $ret;
}

/**
 * @param $_cat_id
 * @return bool|mixed
 */
function forum_seo_cat($_cat_id)
{
    xoops_load('XoopsCache');
    $key = 'newbb_seo_cat';
    $ret = false;
    if ($ret = \XoopsCache::read($key)) {
        $ret = @$ret[$_cat_id];
        if ($ret) {
            return $ret;
        }
    }
    $query  = 'SELECT cat_id, cat_title FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_categories');
    $result = $GLOBALS['xoopsDB']->query($query);
    $_ret   = [];
    while (false !== ($res = $GLOBALS['xoopsDB']->fetchArray($result))) {
        $_ret[$res['cat_id']] = forum_seo_title($res['cat_title']);
    }
    XoopsCache::write($key, $_ret);
    $ret = \XoopsCache::read($key);
    $ret = $ret[$_cat_id];

    return $ret;
}

/**
 * @param $_cat_id
 * @return bool|mixed
 */
function forum_seo_forum($_cat_id)
{
    xoops_load('XoopsCache');
    $key = 'newbb_seo_forum';
    $ret = false;
    if ($ret = \XoopsCache::read($key)) {
        $ret = @$ret[$_cat_id];
        if ($ret) {
            return $ret;
        }
    }
    $query  = 'SELECT forum_id, forum_name    FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_forums');
    $result = $GLOBALS['xoopsDB']->query($query);
    $_ret   = [];
    while (false !== ($res = $GLOBALS['xoopsDB']->fetchArray($result))) {
        $_ret[$res['forum_id']] = forum_seo_title($res['forum_name']);
    }
    XoopsCache::write($key, $_ret);
    $ret = \XoopsCache::read($key);
    $ret = $ret[$_cat_id];

    return $ret;
}

/**
 * @param $_cat_id
 * @return mixed|string
 */
function forum_seo_topic($_cat_id)
{
    $query  = 'SELECT    topic_title    FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_topics') . ' WHERE topic_id = ' . $_cat_id;
    $result = $GLOBALS['xoopsDB']->query($query);
    $res    = $GLOBALS['xoopsDB']->fetchArray($result);
    $ret    = forum_seo_title($res['topic_title']);

    $moduleDirName = basename(__DIR__);
    /** @var Newbb\TopicHandler $topicsHandler */
    $topicsHandler = Newbb\Helper::getInstance()->getHandler('Topic');
    $criteria      = new \CriteriaCompo(new \Criteria('topic_id', $_cat_id, '='));
    $fields        = ['topic_title'];
    $ret0          = $topicsHandler->getAll($criteria, $fields, false);

    return $ret;
}

/**
 * @param $_cat_id
 * @return mixed|string
 */
function forum_seo_post($_cat_id)
{
    $query  = 'SELECT    subject    FROM ' . $GLOBALS['xoopsDB']->prefix('newbb_posts') . ' WHERE post_id = ' . $_cat_id;
    $result = $GLOBALS['xoopsDB']->query($query);
    $res    = $GLOBALS['xoopsDB']->fetchArray($result);
    $ret    = forum_seo_title($res['subject']);

    return $ret;
}

/**
 * @param  string $title
 * @param  bool   $withExt
 * @return mixed|string
 */
function forum_seo_title($title = '', $withExt = true)
{
    /**
     * if XOOPS ML is present, let's sanitize the title with the current language
     */
    $myts = \MyTextSanitizer::getInstance();
    if (method_exists($myts, 'formatForML')) {
        $title = $myts->formatForML($title);
    }

    // Transformation de la chaine en minuscule
    // Codage de la chaine afin d'�viter les erreurs 500 en cas de caract�res impr�vus
    $title = rawurlencode(strtolower($title));

    // Transformation des ponctuations
    //                 Tab     Space      !        "        #        %        &        '        (        )        ,        /        :        ;        <        =        >        ?        @        [        \        ]        ^        {        |        }        ~       .
    $pattern = [
        '/%09/',
        '/%20/',
        '/%21/',
        '/%22/',
        '/%23/',
        '/%25/',
        '/%26/',
        '/%27/',
        '/%28/',
        '/%29/',
        '/%2C/',
        '/%2F/',
        '/%3A/',
        '/%3B/',
        '/%3C/',
        '/%3D/',
        '/%3E/',
        '/%3F/',
        '/%40/',
        '/%5B/',
        '/%5C/',
        '/%5D/',
        '/%5E/',
        '/%7B/',
        '/%7C/',
        '/%7D/',
        '/%7E/',
        '/\./',
        '/%2A/'
    ];
    $rep_pat = [
        '-',
        '-',
        '',
        '',
        '',
        '-100',
        '',
        '-',
        '',
        '',
        '',
        '-',
        '',
        '',
        '',
        '-',
        '',
        '',
        '-at-',
        '',
        '-',
        '',
        '-',
        '',
        '-',
        '',
        '-',
        '',
        ''
    ];
    $title   = preg_replace($pattern, $rep_pat, $title);

    // Transformation des caractères accentués
    //                  è         é        ê         ë         ç         à         â         ä        î         ï        ù         ü         û         ô        ö
    $pattern = [
        '/%B0/',
        '/%E8/',
        '/%E9/',
        '/%EA/',
        '/%EB/',
        '/%E7/',
        '/%E0/',
        '/%E2/',
        '/%E4/',
        '/%EE/',
        '/%EF/',
        '/%F9/',
        '/%FC/',
        '/%FB/',
        '/%F4/',
        '/%F6/',
        '/%E3%BC/',
        '/%E3%96/',
        '/%E3%84/',
        '/%E3%9C/',
        '/%E3%FF/',
        '/%E3%B6/',
        '/%E3%A4/',
        '/%E3%9F/'
    ];
    $rep_pat = [
        '-',
        'e',
        'e',
        'e',
        'e',
        'c',
        'a',
        'a',
        'a',
        'i',
        'i',
        'u',
        'u',
        'u',
        'o',
        'o',
        'ue',
        'oe',
        'ae',
        'ue',
        'ss',
        'oe',
        'ae',
        'ss'
    ];
    $title   = preg_replace($pattern, $rep_pat, $title);

    /*$string = str_replace(' ', '-', $title);
    $string = iconv('utf-8', 'ascii//translit', $string);
    $string = preg_replace('#[^a-z0-9\-\.]#si', '', $string);
    $title  = str_replace('\/','', $string);  */

    if (count($title) > 0) {
        if ($withExt) {
            $title .= '.html';
        }

        return $title;
    } else {
        return '';
    }
}

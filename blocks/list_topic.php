<?php
/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>, irmtfan <irmtfan@users.sourceforge.net>
 * @author         The Persian Xoops Support Site <www.xoops.ir>
 * @since          4.3
 * @package        module::newbb
 */

use XoopsModules\Newbb;

// defined('XOOPS_ROOT_PATH') || die('Restricted access');

if (defined('LIST_TOPIC_DEFINED')) {
    return;
}
define('LIST_TOPIC_DEFINED', true);

//require_once dirname(__DIR__) . '/include/functions.ini.php';
require_once dirname(__DIR__) . '/class/TopicRenderer.php';
require_once dirname(__DIR__) . '/footer.php'; // to include js/style files like validate function

xoops_loadLanguage('main', 'newbb');

require_once __DIR__ . '/../include/functions.config.php';
require_once __DIR__ . '/../include/functions.time.php';
require_once __DIR__ . '/../include/functions.session.php';
require_once __DIR__ . '/../include/functions.render.php';
require_once __DIR__ . '/../include/functions.user.php';

// options[0] - Status in WHERE claus: all(by default), sticky, digest,lock, poll, voted, viewed, replied, read, (UN_) , active, pending, deleted (admin) (It is  multi-select)
// options[1] - Uid in WHERE claus: uid of the topic poster : -1 - all users (by default)
// options[2] - Lastposter in WHERE claus: uid of the lastposter in topic : -1 - all users (by default)
// options[3] - Type in WHERE claus: topic type in the forum : 0 - none (by default)
// options[4] - Sort in ORDER claus: topic, forum, poster, replies, views, lastpost(by default), lastposttime, lastposter, lastpostmsgicon, ratings, votes, publish, digest, sticky, lock, poll, type (if exist), approve(admin mode)
// options[5] - Order in ORDER claus: Descending 0(by default), Ascending 1
// options[6] - NumberToDisplay: any positive integer - 5 by default
// options[7] - TimeDuration: negative for hours, positive for days, for instance, -5 for 5 hours and 5 for 5 days - 360 by default
// options[8] - DisplayMode: all fields in sort PLUS attachment, read, pagenav
// options[9] - Display Navigator: 1 (by default), 0 (No)
// options[10] - Title Length : 0 by default - no limit and show complete title
// options[11] - Post text Length: 0 - dont show post text - 200 by default
// options[12] - SelectedForumIDs: multi-select ngative values for categories and positive values for forums: null for all(by default)

/**
 * @param $options
 * @return array
 */
function newbb_list_topic_show($options)
{
    $newbbConfig = newbbLoadConfig(); // load all newbb configs

    $topicRenderer            = new Newbb\TopicRenderer();
    $topicRenderer->userlevel = $GLOBALS['xoopsUserIsAdmin'] ? 2 : is_object($GLOBALS['xoopsUser']); // Vistitor's level: 0 - anonymous; 1 - user; 2 - moderator or admin

    $topicRenderer->force = true; // force against static vars for parse

    $topicRenderer->is_multiple = true; // is it for multiple forums
    $topicRenderer->config      =& $newbbConfig; // get all configs
    if (!empty($options[6])) {
        $topicRenderer->config['topics_per_page'] = (int)$options[6]; // number of topics (items) to display
    }
    $topicRenderer->config['topic_title_excerpt'] = (int)$options[10]; // topic title length 0 = dont excerpt
    $topicRenderer->config['post_excerpt']        = (int)$options[11]; // post text excerpt 0 = no post text

    $optionsStatus = explode(',', $options[0]); // status in where claus
    $optionsForum  = explode(',', $options[12]);

    // set and parse values:
    // forum: parse positive values to forum IDs and negative values to category IDs. value=0 => all valid forums
    $topicRenderer->setVars([
                                'status'     => $optionsStatus,
                                'uid'        => $options[1],
                                'lastposter' => $options[2],
                                'type'       => $options[3],
                                'sort'       => $options[4],
                                'order'      => $options[5],
                                'since'      => $options[7],
                                'forum'      => $optionsForum
                            ]);
    $block = [];
    // headers to display in block
    $block['headers'] = $topicRenderer->getHeader($options[8]);

    // render a list of topics using all above criterias
    list($block['topics'], $block['sticky']) = $topicRenderer->renderTopics();

    // show index navigation
    $block['indexNav'] = !empty($options[9]);

    return $block;
}

/**
 * @param $options
 * @return string
 */
function newbb_list_topic_edit($options)
{
    // require_once $GLOBALS['xoops']->path('class/blockform.php'); //reserve for 2.6
    xoops_load('XoopsFormLoader');
    // $form = new \XoopsBlockForm(); //reserve for 2.6
    $form = new \XoopsThemeForm(_MB_NEWBB_DISPLAYMODE_DESC, 'list_topic', '');

    $topicRenderer            = new Newbb\TopicRenderer();
    $topicRenderer->userlevel = 2; // 2 - moderator or admin

    // status element
    $optionsStatus = explode(',', $options[0]);
    $statusEle     = new \XoopsFormSelect(_MB_NEWBB_CRITERIA, 'options[0]', $optionsStatus, 5, true);
    $status        = $topicRenderer->getStatus($topicRenderer->userlevel); // get all public status + admin status (admin mode, pending deleted)
    $statusEle->addOptionArray($status);
    $statusEle->setExtra("onchange = \"validate('options[0][]','select', true)\""); // if user dont select any option it select "all"
    $statusEle->setDescription(_MB_NEWBB_CRITERIA_DESC);

    // topic_poster element
    $topicPosterRadioEle = new \XoopsFormRadio(_MB_NEWBB_AUTHOR, 'options[1]', $options[1]);
    $topicPosterRadioEle->addOption(-1, _MD_NEWBB_TOTALUSER);
    $topicPosterRadioEle->addOption((-1 !== $options[1]) ? $options[1] : 0, _SELECT); // if no user in selection box it select uid=0 anon users
    $topicPosterRadioEle->setExtra("onchange=\"var el=document.getElementById('options[1]'); el.disabled=(this.id == 'options[1]1'); if (!el.value) {el.value= this.value}\""); // if user dont select any option it select "all"
    $topicPosterSelectEle = new \XoopsFormSelectUser(_MB_NEWBB_AUTHOR, 'options[1]', true, explode(',', $options[1]), 5, true);// show $limit = 200 users when no user is selected;
    $topicPosterEle       = new \XoopsFormLabel(_MB_NEWBB_AUTHOR, $topicPosterRadioEle->render() . $topicPosterSelectEle->render());

    // lastposter element
    $lastPosterRadioEle = new \XoopsFormRadio(_MD_NEWBB_POSTER, 'options[2]', $options[2]);
    $lastPosterRadioEle->addOption(-1, _MD_NEWBB_TOTALUSER);
    $lastPosterRadioEle->addOption((-1 !== $options[2]) ? $options[2] : 0, _SELECT); // if no user in selection box it select uid=1
    $lastPosterRadioEle->setExtra("onchange=\"var el=document.getElementById('options[2]'); el.disabled=(this.id == 'options[2]1'); if (!el.value) {el.value= this.value}\""); // if user dont select any option it select "all"
    $lastPosterSelectEle = new \XoopsFormSelectUser(_MD_NEWBB_POSTER, 'options[2]', true, explode(',', $options[2]), 5, true);// show $limit = 200 users when no user is selected;
    $lastPosterEle       = new \XoopsFormLabel(_MD_NEWBB_POSTER, $lastPosterRadioEle->render() . $lastPosterSelectEle->render());

    // type element
    $types   = $topicRenderer->getTypes(); // get all available types in all forums
    $typeEle = new \XoopsFormSelect(_MD_NEWBB_TYPE, 'options[3]', $options[3]);
    $typeEle->addOption(0, _NONE);
    if (!empty($types)) {
        foreach ($types as $type_id => $type) {
            $typeEle->addOption($type_id, $type['type_name']);
        }
    }

    // sort element
    $sortEle = new \XoopsFormSelect(_MD_NEWBB_SORTBY, 'options[4]', $options[4]);
    $sortEle->setDescription(_MB_NEWBB_CRITERIA_SORT_DESC);
    $sorts = $topicRenderer->getSort('', 'title');
    $sortEle->addOptionArray($sorts);

    // order element
    $orderEle = new \XoopsFormSelect(_MB_NEWBB_CRITERIA_ORDER, 'options[5]', $options[5]);
    $orderEle->addOption(0, _DESCENDING);
    $orderEle->addOption(1, _ASCENDING);

    // number of topics to display element
    $numdispEle = new \XoopsFormText(_MB_NEWBB_DISPLAY, 'options[6]', 10, 255, (int)$options[6]);

    $timeEle = new \XoopsFormText(_MB_NEWBB_TIME, 'options[7]', 10, 255, $options[7]);
    $timeEle->setDescription(_MB_NEWBB_TIME_DESC);

    // mode disp element
    $options_headers = explode(',', $options[8]);
    $modeEle         = new \XoopsFormCheckBox(_MB_NEWBB_DISPLAYMODE, 'options[8][]', $options_headers);
    $modeEle->setDescription(_MB_NEWBB_DISPLAYMODE_DESC);
    $modeEle->columns = 4;
    $disps            = $topicRenderer->getHeader();
    $modeEle->addOptionArray($disps);
    $modeEle->setExtra("onchange = \"validate('options[8][]','checkbox', true)\""); // prevent user select no option
    // Index navigation element
    $navEle = new \XoopsFormRadioYN(_MB_NEWBB_INDEXNAV, 'options[9]', !empty($options[9]));

    // Topic title element
    $lengthEle = new \XoopsFormText(_MB_NEWBB_TITLE_LENGTH, 'options[10]', 10, 255, (int)$options[10]);
    $lengthEle->setDescription(_MB_NEWBB_TITLE_LENGTH_DESC);

    // Post text element
    $postExcerptEle = new \XoopsFormText(_MB_NEWBB_POST_EXCERPT, 'options[11]', 10, 255, (int)$options[11]);
    $postExcerptEle->setDescription(_MB_NEWBB_POST_EXCERPT_DESC);

    //  forum element
    $optionsForum = explode(',', $options[12]);
    require_once __DIR__ . '/../include/functions.forum.php';
    /** @var Newbb\ForumHandler $forumHandler */
    $forumHandler = Newbb\Helper::getInstance()->getHandler('Forum');
    //get forum Ids by values. parse positive values to forum IDs and negative values to category IDs. value=0 => all valid forums
    // Get accessible forums
    $accessForums = $forumHandler->getIdsByValues(array_map('intval', $optionsForum));
    $isAll        = (0 === count($optionsForum) || empty($optionsForum[0]));
    $forumSel     = "<select name=\"options[12][]\" multiple=\"multiple\" onchange = \"validate('options[12][]','select', true)\">";// if user dont select any it select "0"
    $forumSel     .= '<option value="0" ';
    if ($isAll) {
        $forumSel     .= ' selected';
        $accessForums = null; // just select _ALL option
    }
    $forumSel .= '>' . _ALL . '</option>';
    $forumSel .= newbbForumSelectBox($accessForums, 'access', false); //$accessForums, $permission = "access", $delimitorCategory = false
    $forumSel .= '</select>';
    $forumEle = new \XoopsFormLabel(_MB_NEWBB_FORUMLIST, $forumSel);

    // add all elements to form
    $form->addElement($statusEle);
    $form->addElement($topicPosterEle);
    $form->addElement($lastPosterEle);
    $form->addElement($typeEle);
    $form->addElement($sortEle);
    $form->addElement($orderEle);
    $form->addElement($numdispEle);
    $form->addElement($timeEle);
    $form->addElement($modeEle, true); // required: user should select at least one otherwise it will select the first one
    $form->addElement($navEle);
    $form->addElement($lengthEle);
    $form->addElement($postExcerptEle);
    $form->addElement($forumEle);

    return $form->render();
}

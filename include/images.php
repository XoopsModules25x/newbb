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

/**
 * Set image path
 *
 * Priority for path per types:
 *    NEWBB_ROOT    -    IF EXISTS XOOPS_THEME/modules/newbb/images/, TAKE IT;
 *                    ELSEIF EXISTS  XOOPS_THEME_DEFAULT/modules/newbb/assets/images/, TAKE IT;
 *                    ELSE TAKE  XOOPS_ROOT/modules/newbb/templates/images/.
 *    types:
 *        button        -    language specified;
 *        indicator    -    language specified;
 *        icon        -    universal;
 *        mime        -    universal;
 */

// Forum image type
// irmtfan add anonym.png more.png and less.png icon
$forumImage[''] = $forumImage['blank'] =

$forumImage['attachment'] = $forumImage['whosonline'] = $forumImage['statistik'] = $forumImage['lastposticon'] =

$forumImage['plus'] = $forumImage['minus'] =

$forumImage['forum'] = $forumImage['forum_new'] =

$forumImage['topic'] = $forumImage['topic_hot'] = $forumImage['topic_sticky'] = $forumImage['topic_digest'] = $forumImage['topic_locked'] = $forumImage['topic_new'] = $forumImage['topic_hot_new'] = $forumImage['topic_my'] =

$forumImage['post'] =

$forumImage['poll'] = $forumImage['rss'] = $forumImage['pdf'] = $forumImage['subforum'] =

$forumImage['admin_move'] = $forumImage['admin_merge'] = $forumImage['admin_edit'] = $forumImage['admin_delete'] =

$forumImage['document'] =

$forumImage['previous'] = $forumImage['next'] = $forumImage['right'] = $forumImage['down'] = $forumImage['up'] = $forumImage['printer'] = $forumImage['new_forum'] =

$forumImage['facebook'] = $forumImage['twitter'] = $forumImage['linkedin'] = $forumImage['googleplus'] = $forumImage['stumbleupon'] = $forumImage['friendfeed'] = $forumImage['digg'] = $forumImage['reddit'] = $forumImage['delicious'] = $forumImage['technorati'] = $forumImage['wong'] = $forumImage['anonym'] = $forumImage['more'] = $forumImage['less'] = 'icon';

for ($i = 1; $i <= 5; ++$i) {
    $forumImage['rate' . $i] = 'icon';
}

$forumImage['p_delete'] = $forumImage['p_reply'] = $forumImage['p_quote'] = $forumImage['p_edit'] = $forumImage['p_report'] =

$forumImage['t_new'] = $forumImage['t_poll'] = $forumImage['t_qr'] = $forumImage['t_qr_expand'] = $forumImage['t_reply'] =

$forumImage['online'] = $forumImage['offline'] =

$forumImage['new_subforum'] =

$forumImage['p_bann'] =

    'language';

/*
$forumImage[''] = $forumImage['blank'] = $forumImage['icon']."blank";

$forumImage['attachment'] = $forumImage['icon']."attachment";
$forumImage['whosonline'] = $forumImage['icon']."whosonline";

$forumImage['forum'] = $forumImage['icon']."forum";
$forumImage['forum_new'] = $forumImage['icon']."forum_new";

$forumImage['topic'] = $forumImage['icon']."topic";
$forumImage['topic_hot'] = $forumImage['icon']."topic_hot";
$forumImage['topic_sticky'] = $forumImage['icon']."topic_sticky";
$forumImage['topic_digest'] = $forumImage['icon']."topic_digest";
$forumImage['topic_locked'] = $forumImage['icon']."topic_locked";
$forumImage['topic_new'] = $forumImage['icon']."topic_new";
$forumImage['topic_hot_new'] = $forumImage['icon']."topic_hot_new";
$forumImage['topic_my'] = $forumImage['icon']."topic_my";

$forumImage['post'] = $forumImage['icon']."post";

$forumImage['poll'] = $forumImage['icon']."poll";
$forumImage['rss'] = $forumImage['icon']."rss";
$forumImage['subforum'] = $forumImage['icon']."subforum";

$forumImage['admin_move'] = $forumImage['icon']."admin_move";

$forumImage['admin_edit'] = $forumImage['icon']."admin_edit";
$forumImage['admin_delete'] = $forumImage['icon']."admin_delete";

$forumImage['document'] = $forumImage['icon']."document";

$forumImage['previous'] = $forumImage['icon']."previous";
$forumImage['right'] = $forumImage['icon']."next";
$forumImage['down'] = $forumImage['icon']."down";
$forumImage['up'] = $forumImage['icon']."up";

for ($i = 1; $i <= 5; ++$i) {
    $forumImage['rate'.$i] = $forumImage['icon'].'/rate'.$i;
}

$forumImage['p_delete'] = $forumImage['language']."p_delete";
$forumImage['p_reply'] = $forumImage['language']."p_reply";
$forumImage['p_quote'] = $forumImage['language']."p_quote";
$forumImage['p_edit'] = $forumImage['language']."p_edit";
$forumImage['p_report'] = $forumImage['language']."p_report";

$forumImage['t_new'] = $forumImage['language']."t_new";
$forumImage['t_poll'] = $forumImage['language']."t_poll";
$forumImage['t_qr'] = $forumImage['language']."t_qr";
$forumImage['t_reply'] = $forumImage['language']."t_reply";

$forumImage['online'] = $forumImage['language']."online";
$forumImage['offline'] = $forumImage['language']."offline";

$forumImage['new_forum']    = $forumImage['language']."new_forum";
$forumImage['new_subforum'] = $forumImage['language']."new_subforum";
*/

return $forumImage;

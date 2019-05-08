<?php
/**
 * NewBB 5.0x,  the forum module for XOOPS project
 *
 * @copyright      XOOPS Project (https://xoops.org)
 * @license        GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @author         Taiwen Jiang (phppp or D.J.) <phppp@users.sourceforge.net>, irmtfan <irmtfan@users.sourceforge.net>
 * @since          4.3
 * @package        module::newbb
 */

/*
    1- Display Text links instead of images and vice versa  => text links=true/images=false
    The overall value is defined in include/plugin.php $customConfig["display_text_links"].
    It means if you set $customConfig["display_text_links"] to true it will show all images in text links (and vice versa)
    You can overwrite that overall value here for each link one by one.
    If you change any setting in this file:
        - the image will be shown in text (if set to true) OR,
        - the text link will be shown as image (if set to false)
    regardless of setting of $customConfig["display_text_links"]
    ===============================================================
    Find images in newbb/templates/images
    ===============================================================
    * Priority for image path OR style.css path:
    *     NEWBB_ROOT     -    IF EXISTS XOOPS_THEME/modules/newbb/images/, TAKE IT;
    *                    ELSEIF EXISTS  XOOPS_THEME_DEFAULT/modules/newbb/assets/images/, TAKE IT;
    *                    ELSE TAKE  XOOPS_ROOT/modules/newbb/templates/images/.
    ===============================================================
    2- If you choose to show text links (set to true):
    2-1- customize the text show in style.css with the help of class="forum_icon" id=$image_name
    eg:
    For buttons:
    all buttons:
    span.forum_icon.forum_button
    span.forum_icon.forum_button:hover
    span.forum_icon.forum_button:active

    each button (p_edit):
    span.forum_icon.forum_button#p_edit
    span.forum_icon.forum_button#p_edit:hover
    span.forum_icon.forum_button#p_edit:active

    For other images:
    all images:
    span.forum_icon
    span.forum_icon:hover
    span.forum_icon:active
    each image (pdf):
    span.forum_icon#pdf
    span.forum_icon#pdf:hover
    span.forum_icon#pdf:active

    2-2- no style means plain text links

    3- If you choose to show images (set to false):
    3-1- customize the image show in style.css with the help of class="forum_icon" id=$image_name
    all images:
    img.forum_icon
    img.forum_icon:hover
    img.forum_icon:active

    each image (p_edit):
    img.forum_icon#p_edit
    img.forum_icon#p_edit:hover
    img.forum_icon#p_edit:active
    3-2- no style means just image
*/

// uncomment to show text link instead of images (set to true)
$displayText[''] = //$displayText['blank'] =

    //$displayText['attachment'] =
    //$displayText['whosonline'] =
    //$displayText['statistik'] =
    //$displayText['lastposticon'] =

    //$displayText['plus'] =
    //$displayText['minus'] =

    //$displayText['forum'] =
    //$displayText['forum_new'] =

    //$displayText['topic'] =
    //$displayText['topic_hot'] =
    //$displayText['topic_sticky'] =
    //$displayText['topic_digest'] =
    //$displayText['topic_locked'] =
    //$displayText['topic_new'] =
    //$displayText['topic_hot_new'] =
    //$displayText['topic_my'] =

    //$displayText['post'] =

    //$displayText['poll'] =
    //$displayText['rss'] =
    //$displayText['pdf'] =
    //$displayText['subforum'] =

    //$displayText['admin_move'] =
    //$displayText['admin_merge'] =
    //$displayText['admin_edit'] =
    //$displayText['admin_delete'] =

    //$displayText['document'] =

    //$displayText['previous'] =
    //$displayText['next'] =
    //$displayText['right'] =
    //$displayText['down'] =
    //$displayText['up'] =
    //$displayText['printer'] =
    //$displayText['new_forum']  =

    //$displayText['facebook'] =
    //$displayText['twitter'] =
    //$displayText['linkedin'] =
    //$displayText['googleplus'] =
    //$displayText['stumbleupon'] =
    //$displayText['friendfeed'] =
    //$displayText['digg'] =
    //$displayText['reddit'] =
    //$displayText['delicious'] =
    //$displayText['technorati'] =
    //$displayText['wong'] =
    //$displayText['anonym'] =
    //$displayText['more'] =
    //$displayText['less'] =

$displayText['p_delete'] = $displayText['p_reply'] = $displayText['p_quote'] = $displayText['p_edit'] = $displayText['p_report'] = $displayText['t_new'] = $displayText['t_poll'] = $displayText['t_qr'] = $displayText['t_qr_expand'] = $displayText['t_reply'] =

    //$displayText['online'] =
    //$displayText['offline'] =

    //$displayText['new_subforum'] =

$displayText['p_bann'] =

    true;

for ($i = 1; $i <= 5; ++$i) {
    //$displayText['rate'.$i] = true;
}

// uncomment to show images instead of text links (set to false)
$displayText[''] = //$displayText['blank'] =

    //$displayText['attachment'] =
    //$displayText['whosonline'] =
    //$displayText['statistik'] =
    //$displayText['lastposticon'] =

    //$displayText['plus'] =
    //$displayText['minus'] =

    //$displayText['forum'] =
    //$displayText['forum_new'] =

    //$displayText['topic'] =
    //$displayText['topic_hot'] =
    //$displayText['topic_sticky'] =
    //$displayText['topic_digest'] =
    //$displayText['topic_locked'] =
    //$displayText['topic_new'] =
    //$displayText['topic_hot_new'] =
    //$displayText['topic_my'] =

    //$displayText['post'] =

    //$displayText['poll'] =
    //$displayText['rss'] =
    //$displayText['pdf'] =
    //$displayText['subforum'] =

    //$displayText['admin_move'] =
    //$displayText['admin_merge'] =
    //$displayText['admin_edit'] =
    //$displayText['admin_delete'] =

    //$displayText['document'] =

    //$displayText['previous'] =
    //$displayText['next'] =
    //$displayText['right'] =
    //$displayText['down'] =
    //$displayText['up'] =
    //$displayText['printer'] =
    //$displayText['new_forum']  =

    //$displayText['facebook'] =
    //$displayText['twitter'] =
    //$displayText['linkedin'] =
    //$displayText['googleplus'] =
    //$displayText['stumbleupon'] =
    //$displayText['friendfeed'] =
    //$displayText['digg'] =
    //$displayText['reddit'] =
    //$displayText['delicious'] =
    //$displayText['technorati'] =
    //$displayText['wong'] =
    //$displayText['anonym'] =
    //$displayText['more'] =
    //$displayText['less'] =

    //$displayText['p_delete'] =
    //$displayText['p_reply'] =
    //$displayText['p_quote'] =
    //$displayText['p_edit'] =
    //$displayText['p_report'] =

    //$displayText['t_new'] =
    //$displayText['t_poll'] =
    //$displayText['t_qr'] =
    //$displayText['t_qr_expand'] =
    //$displayText['t_reply'] =

    //$displayText['online'] =
    //$displayText['offline'] =

    //$displayText['new_subforum'] =

    //$displayText['p_bann'] =

    false;

for ($i = 1; $i <= 5; ++$i) {
    //$displayText['rate'.$i] = false;
}

return $displayText;

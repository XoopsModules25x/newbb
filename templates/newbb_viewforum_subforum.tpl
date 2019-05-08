<table cellspacing="1" class="outer" width="100%">
    <tr class="head" align="center">
        <td width="5%">&nbsp;</td>
        <!-- irmtfan hardcode removed align="left" -->
        <td nowrap="nowrap" class="align_left"><{$smarty.const._MD_NEWBB_SUBFORUMS}></td>
        <td nowrap="nowrap">&nbsp;</td>
        <td nowrap="nowrap"><{$smarty.const._MD_NEWBB_LASTPOST}></td>
    </tr>
    <!-- start subforums -->
    <{foreach item=sforum from=$subforum}>
    <tr>
        <td class="even" align="center" valign="middle"><{$sforum.forum_folder}></td>
        <td class="odd" onclick="window.location='<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$sforum.forum_id}>'"><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$sforum.forum_id}>"><strong><{$sforum.forum_name}></strong></a><br>

            <div id="index_forum">
                <{$sforum.forum_desc}>
                <{if $sforum.forum_moderators}>
                    <br>
                    <span class="extra"><{$smarty.const._MD_NEWBB_MODERATOR}>:&nbsp;</span><{$sforum.forum_moderators}>
                <{/if}>
            </div>
        </td>
        <td class="even" align="center" valign="middle">
            <{$sforum.forum_topics}>  <{$smarty.const._MD_NEWBB_TOPICS}>
            <br>
            <{$sforum.forum_posts}> <{$smarty.const._MD_NEWBB_POSTS}>
        </td>
        <!-- irmtfan hardcode removed align="right" -->
        <td class="odd" id="align_right" valign="middle">
            <{if $sforum.forum_lastpost_subject}>
                <{$sforum.forum_lastpost_time}> <{$smarty.const._MD_NEWBB_BY}> <{$sforum.forum_lastpost_user}>
                <br>
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewtopic.php?post_id=<{$sforum.forum_lastpost_id}>">
                    <{$sforum.forum_lastpost_subject}>&nbsp;&nbsp;
                    <!-- irmtfan removed icon_path -->
                    <{$sforum.forum_lastpost_icon}>
                </a>
            <{else}>
                <{$smarty.const._MD_NEWBB_NONEWPOSTS}>
            <{/if}>
        </td>
    </tr>
    <{/foreach}>
    <!-- end subforums -->
</table>

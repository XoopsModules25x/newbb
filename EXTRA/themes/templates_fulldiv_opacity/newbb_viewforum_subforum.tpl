<div class="forum_table outer">
    <div class="forum_row head">
        <div class="forum_folder align_center">&nbsp;</div>
        <div class="forum_name left"><{$smarty.const._MD_NEWBB_SUBFORUMS}></div>
        <div class="forum_topics align_center"><{$smarty.const._MD_NEWBB_TOPICS}></div>
        <div class="forum_posts align_center"><{$smarty.const._MD_NEWBB_POSTS}></div>
        <div class="forum_lastpost"><{$smarty.const._MD_NEWBB_LASTPOST}></div>
    </div>
    <!-- start subforums -->
    <{foreach item=sforum from=$subforum}>
    <div class="forum_row">
        <div class="forum_folder even align_center"><{$sforum.forum_folder}></div>
        <div class="forum_name odd left">
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$sforum.forum_id}>"><strong><{$sforum.forum_name}></strong></a>
            <br>
            <div class="index_forum">
                <{$sforum.forum_desc}>
                <{if $sforum.forum_moderators}>
                    <br>
                    <span class="extra"><{$smarty.const._MD_NEWBB_MODERATOR}>:&nbsp;</span><{$sforum.forum_moderators}>
                <{/if}>
            </div>
        </div>
        <div class="forum_topics even align_center"><{$sforum.forum_topics}></div>
        <div class="forum_posts odd align_center"><{$sforum.forum_posts}></div>
        <div class="forum_lastpost even">
            <{if $sforum.forum_lastpost_subject}>
                <{$sforum.forum_lastpost_time}> <{$smarty.const._MD_NEWBB_BY}> <{$sforum.forum_lastpost_user}>
                <br>
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewtopic.php?post_id=<{$sforum.forum_lastpost_id}>">
                    <{$sforum.forum_lastpost_subject}>&nbsp;&nbsp;
                    <{$sforum.forum_lastpost_icon}>
                </a>
            <{else}>
                <{$smarty.const._MD_NEWBB_NONEWPOSTS}>
            <{/if}>
        </div>
    </div>
    <{/foreach}>
    <!-- end subforums -->
</div>

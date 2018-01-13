<div class="forum_header">
    <div class="forum_title">
        <h2><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php"><{$lang_forum_index}></a></h2>
        <hr class="align_left"/>
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php"><{$smarty.const._MD_NEWBB_FORUMHOME}></a>
        <{if $category}>
            <span class="delimiter">&raquo;</span>
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php?cat=<{$category.id}>"><{$category.title}></a>
        <{/if}>
        <{if $parentforum}>
            <{foreach item=forum from=$parentforum}>
            <span class="delimiter">&raquo;</span>
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum.forum_id}>"><{$forum.forum_name}></a>
        <{/foreach}>
        <{/if}>
        <{if $forum_name}>
            <span class="delimiter">&raquo;</span>
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum_id}>"><{$forum_name}></a>
        <{/if}>
        <span class="delimiter">&raquo;</span>
        <strong><{$lang_title}></strong>
    </div>
</div>
<{if $viewer_level gt 1}>
    <div class="icon_right" id="admin">
        <{if $mode gt 1}>
        <!-- START admin form -->
        <form name="form_posts_admin" action="action.post.php" method="POST" onsubmit="if(window.document.form_posts_admin.op.value &lt; 1){return false;}">
            <{$smarty.const._ALL}>: <input type="checkbox" name="post_check" id="post_check" value="1" onclick="xoopsCheckAll('form_posts_admin', 'post_check');"/>
            <select name="op">
                <option value="0"><{$smarty.const._SELECT}></option>
                <option value="delete"><{$smarty.const._DELETE}></option>
                <{if $status eq "pending"}>
                    <option value="approve"><{$smarty.const._MD_NEWBB_APPROVE}></option>
                <{elseif $status eq "deleted"}>
                    <option value="restore"><{$smarty.const._MD_NEWBB_RESTORE}></option>
                <{/if}>
            </select>
            <input type="hidden" name="uid" value="<{$uid}>"/> |
            <input type="submit" name="submit" value="<{$smarty.const._SUBMIT}>"/> |
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewpost.php?uid=<{$uid}>" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_VIEW}>"><{$smarty.const._MD_NEWBB_TYPE_VIEW}></a>
            <{else}>
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewpost.php?uid=<{$uid}>&amp;status=active#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_ADMIN}>"><{$smarty.const._MD_NEWBB_TYPE_ADMIN}></a> |
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewpost.php?uid=<{$uid}>&amp;status=pending#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_PENDING}>"><{$smarty.const._MD_NEWBB_TYPE_PENDING}></a> |
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewpost.php?uid=<{$uid}>&amp;status=deleted#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_DELETED}>"><{$smarty.const._MD_NEWBB_TYPE_DELETED}></a>
            <{/if}>
    </div>
    <div class="clear"></div>
    <br>
<{/if}>

<div class="icon_left">
    <a id="threadtop"></a><{$down}><a href="#threadbottom"><{$smarty.const._MD_NEWBB_BOTTOM}></a>
</div>
<div class="clear"></div>
<br>
<div>
    <div class="dropdown floatleft">
        <select name="topicoption" id="topicoption" class="menu" onchange="if(this.options[this.selectedIndex].value.length >0 )    { window.document.location=this.options[this.selectedIndex].value;}">
            <option value=""><{$smarty.const._MD_NEWBB_TOPICOPTION}></option>
            <option value="<{$post_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_ALLPOSTS}></option>
            <option value="<{$newpost_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_NEWPOSTS}></option>
            <option value="<{$all_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_ALL}></option>
            <option value="<{$digest_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_DIGEST}></option>
            <option value="<{$unreplied_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_UNREPLIED}></option>
            <option value="<{$unread_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_UNREAD}></option>
        </select>
        <select name="viewmode" id="viewmode" class="menu" onchange="if(this.options[this.selectedIndex].value.length >0 )  { window.document.location=this.options[this.selectedIndex].value;}">
            <option value=""><{$smarty.const._MD_NEWBB_VIEWMODE}></option>
            <{foreach item=act from=$viewmode_options}>
            <option value="<{$act.link}>"><{$act.title}></option>
            <{/foreach}>
        </select>
    </div>
    <div class="pagenav">
        <{$pagenav|replace:'form':'div'|replace:'id="xo-pagenav"':''}>
    </div>
</div>
<div class="clear"></div>
<br>
<br>
<{foreach item=post from=$posts}>
<{includeq file="db:newbb_thread.tpl" topic_post=$post}>
<div class="icon_right">
    <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewtopic.php?topic_id=<{$post.topic_id}>"><strong><{$smarty.const._MD_NEWBB_VIEWTOPIC}></strong></a>
    <{if !$forum_name }>
        |
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$post.forum_id}>"><strong><{$smarty.const._MD_NEWBB_VIEWFORUM}></strong></a>
    <{/if}>
</div>
<div class="clear"></div>
<br>
<br>
<{/foreach}>
<!-- END admin form -->
<{if $mode gt 1}>
    </form>
<{/if}>
<br>
<div>
    <div class="icon_left">
        <a id="threadbottom"></a><a href="#threadtop" alt="<{$smarty.const._MD_NEWBB_TOP}>" title="<{$smarty.const._MD_TOP}>"><{$p_up}> <{$smarty.const._MD_TOP}></a>
    </div>
    <div class="pagenav">
        <{$pagenav|replace:'form':'div'|replace:'id="xo-pagenav"':''}>
    </div>
</div>
<div class="clear"></div>
<br>
<br>
<div>
    <div class="icon_left">
        <form action="<{$xoops_url}>/modules/<{$xoops_dirname}>/search.php" method="get">
            <input name="term" id="term" type="text" size="15"/>
            <input type="hidden" name="sortby" id="sortby" value="p.post_time desc"/>
            <input type="hidden" name="action" id="action" value="yes"/>
            <input type="hidden" name="searchin" id="searchin" value="both"/>
            <input type="submit" class="formButton" value="<{$smarty.const._MD_NEWBB_SEARCH}>"/><br>
            [<a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/search.php"><{$smarty.const._MD_NEWBB_ADVSEARCH}></a>]
        </form>
    </div>
    <div class="icon_right">
        <{$forum_jumpbox}>
    </div>
</div>
<div class="clear"></div>
<br>
<{if $online}>
    <br>
    <{includeq file="db:newbb_online.tpl"}>
<{/if}>
<{includeq file='db:newbb_notification_select.tpl'}>

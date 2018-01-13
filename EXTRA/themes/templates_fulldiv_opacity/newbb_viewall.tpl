<div class="forum_header">
    <div class="forum_title">
        <h2><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php"><{$forum_index_title}></a></h2>
        <hr class="align_left"/>
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php"><{$smarty.const._MD_NEWBB_FORUMHOME}></a>
        <{if $parent_forum}>
            <span class="delimiter">&raquo;</span>
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$parent_forum}>"><{$parent_name}></a>
            <span class="delimiter">&raquo;</span>
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum_id}>"><{$forum_name}></a>
        <{elseif $forum_name}>
            <span class="delimiter">&raquo;</span>
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum_id}>"><{$forum_name}></a>
        <{/if}>
        <{if $current}>
            <span class="delimiter">&raquo;</span>
            <a href="<{$current.link}>"><{$current.title}></a>
        <{/if}>
    </div>
</div>
<div class="clear"></div>

<{if $mode gt 1}>
<form name="form_topics_admin" action="<{$xoops_url}>/modules/<{$xoops_dirname}>/action.topic.php" method="POST" onsubmit="if(window.document.form_topics_admin.op.value &lt; 1){return false;}">
    <{/if}>

    <{if $viewer_level gt 1}>
        <div class="pagenav" id="admin">
            <{if $mode gt 1}>
                <{$smarty.const._ALL}>:
                <input type="checkbox" name="topic_check1" id="topic_check1" value="1" onclick="xoopsCheckAll('form_topics_admin', 'topic_check1');"/>
                <select name="op">
                    <option value="0"><{$smarty.const._SELECT}></option>
                    <option value="delete"><{$smarty.const._DELETE}></option>
                    <{if $status eq "pending"}>
                        <option value="approve"><{$smarty.const._MD_NEWBB_APPROVE}></option>
                        <option value="move"><{$smarty.const._MD_NEWBB_MOVE}></option>
                    <{elseif $status eq "deleted"}>
                        <option value="restore"><{$smarty.const._MD_NEWBB_RESTORE}></option>
                    <{else}>
                        <option value="move"><{$smarty.const._MD_NEWBB_MOVE}></option>
                    <{/if}>
                </select>
                <input type="hidden" name="forum_id" value="<{$forum_id}>"/>
                <input type="submit" name="submit" value="<{$smarty.const._SUBMIT}>"/>
                |
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/list.topic.php" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_VIEW}>"><{$smarty.const._MD_NEWBB_TYPE_VIEW}></a>
            <{else}>
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/list.topic.php?status=active#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_ADMIN}>"><{$smarty.const._MD_NEWBB_TYPE_ADMIN}></a>
                |
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/list.topic.php?status=pending#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_PENDING}>"><{$smarty.const._MD_NEWBB_TYPE_PENDING}></a>
                |
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/list.topic.php?status=deleted#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_DELETED}>"><{$smarty.const._MD_NEWBB_TYPE_DELETED}></a>
                |
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/moderate.php" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_SUSPEND}>"><{$smarty.const._MD_NEWBB_TYPE_SUSPEND}></a>
            <{/if}>
        </div>
        <br>
    <{else}>
        <br>
    <{/if}>
    <div class="clear"></div>

    <div class="dropdown">
        <{if $menumode eq 0}>
            <select name="topicoption" id="topicoption" class="menu"
                    onchange="if(this.options[this.selectedIndex].value.length >0 ) { window.document.location=this.options[this.selectedIndex].value;}"
            >
                <option value=""><{$smarty.const._MD_NEWBB_TOPICOPTION}></option>
                <option value="<{$post_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_ALLPOSTS}></option>
                <option value="<{$newpost_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_NEWPOSTS}></option>
                <option value="">--------</option>
                <{foreach item=filter from=$filters}>
                    <option value="<{$filter.link}>"><{$filter.title}></option>
                <{/foreach}>
                <option value="">--------</option>
                <{foreach item=filter from=$types}>
                    <option value="<{$filter.link}>"><{$filter.title}></option>
                <{/foreach}>
            </select>
        <{elseif $menumode eq 1}>
            <div id="topicoption" class="menu">
                <a class="item floatleft" href="<{$post_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_ALLPOSTS}></a>
                <a class="item floatleft" href="<{$newpost_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_NEWPOSTS}></a>
                <a class="item floatleft" href="<{$all_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_ALL}></a>
                <a class="item floatleft" href="<{$digest_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_DIGEST}></a>
                <a class="item floatleft" href="<{$unreplied_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_UNREPLIED}></a>
                <a class="item _col_end" href="<{$unread_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_UNREAD}></a>
                <div class="clear"></div>
            </div>
            <script type="text/javascript">document.getElementById("topicoption").onmouseout = closeMenu;</script>
            <div class="menubar"><a href="" onclick="openMenu(event, 'topicoption');return false;"><{$smarty.const._MD_NEWBB_TOPICOPTION|escape:'quotes'}></a></div>
        <{elseif $menumode eq 2}>
            <div class="menu">
                <ul>
                    <li>
                        <div class="item"><strong><{$smarty.const._MD_NEWBB_TOPICOPTION}></strong></div>
                        <ul>
                            <li>
                                <div class="item floatleft"><a href="<{$post_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_ALLPOSTS}></a></div>
                                <div class="item floatleft"><a href="<{$newpost_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_NEWPOSTS}></a></div>
                                <div class="item floatleft"><a href="<{$all_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_ALL}></a></div>
                                <div class="item floatleft"><a href="<{$digest_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_DIGEST}></a></div>
                                <div class="item floatleft"><a href="<{$unreplied_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_UNREPLIED}></a></div>
                                <div class="item _col_end"><a href="<{$unread_link}>"><{$smarty.const._MD_NEWBB_VIEW}>&nbsp;<{$smarty.const._MD_NEWBB_UNREAD}></a></div>
                                <div class="clear"></div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        <{/if}>
    </div>
    <div class="pagenav">
        <{$pagenav|replace:'form':'div'|replace:'id="xo-pagenav"':''}>
    </div>
    <div class="clear"></div>
    <br>
    <br>
    <!-- start topic main div -->
    <div class="topic_list outer">
        <div class="head align_center">
            <div class="topic_folder floatleft">
                <{if $mode gt 1}>
                    <{$smarty.const._ALL}>:
                <{else}>
                    &nbsp;
                <{/if}>
            </div>
            <div class="topic_icon floatleft">
                <{if $mode gt 1}>
                    <input type="checkbox" name="topic_check" id="topic_check" value="1" onclick="xoopsCheckAll('form_topics_admin', 'topic_check');"/>
                <{else}>
                    &nbsp;
                <{/if}>
            </div>
            <div class="topic_name floatleft">&nbsp;<strong><a href="<{$headers.topic.link}>"><{$headers.topic.title}></a></strong></div>
            <div class="topic_forumname floatleft"><strong><a href="<{$headers.forum.link}>"><{$headers.forum.title}></a></strong></div>
            <div class="topic_reply floatleft"><strong><a href="<{$headers.replies.link}>"><{$headers.replies.title}></a></strong></div>
            <div class="topic_poster floatleft"><strong><a href="<{$headers.poster.link}>"><{$headers.poster.title}></a></strong></div>
            <div class="topic_view floatleft"><strong><a href="<{$headers.views.link}>"><{$headers.views.title}></a></strong></div>
            <div class="_col_end"><strong><a href="<{$headers.lastpost.link}>"><{$headers.lastpost.title}></a></strong></div>
            <div class="clear"></div>
        </div>
        <!-- start forum topic -->
        <{foreach name=loop item=topic from=$topics}>
        <div class="<{cycle values="even,odd"}>">
            <div class="topic_folder floatleft <{if $topic.topic_read eq 1 }>topic-read<{else}>topic-new<{/if}> align_center"><{$topic.topic_folder}><{$topic.lock}></div>
            <div class="topic_icon floatleft align_center">
                <{if $mode gt 1}>
                    <input type="checkbox" name="topic_id[]" id="topic_id[<{$topic.topic_id}>]" value="<{$topic.topic_id}>"/>
                <{else}>
                    <{$topic.topic_icon}><{$topic.sticky}>
                    <br>
                    <{$topic.digest}><{$topic.poll}>
                <{/if}>
            </div>
            <div class="topic_name floatleft left">
                &nbsp;
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/<{$topic.topic_link}>" title="<{$topic.topic_excerpt}>">
                    <{$topic.topic_title}>
                </a>
                <{$topic.attachment}><{$topic.topic_page_jump}>
                <br>
                <span>
                <{$headers.publish.title}>: <{$topic.topic_time}>
            </span>
                <{if $rating_enable && $topic.votes}>
                    |&nbsp;
                    <span>
                    <{$headers.votes.title}>: <{$topic.votes}>&nbsp;<{$topic.rating_img}>
                </span>
                <{/if}>
            </div>
            <div class="topic_forumname floatleft left"><{$topic.topic_forum_link}></div>
            <div class="topic_reply floatleft align_center"><{$topic.topic_replies}></div>
            <div class="topic_poster floatleft align_center"><{$topic.topic_poster}></div>
            <div class="topic_view floatleft align_center"><{$topic.topic_views}></div>
            <div class="_col_end right">
                <{$topic.topic_last_posttime}><br>
                <{$smarty.const._MD_NEWBB_BY}> <{$topic.topic_last_poster}>&nbsp;&nbsp;<{$topic.topic_page_jump_icon}>
            </div>
            <div class="clear"></div>
        </div>
        <{/foreach}>
        <!-- end forum topic -->
        <{if $mode gt 1}>
</form>
<{/if}>
<div class="foot align_center">
    <{strip}>
        <form method="get" action="<{$selection.action}>">
            <strong><{$smarty.const._MD_NEWBB_SORTEDBY}></strong>&nbsp;
            <{$selection.sort}>&nbsp;
            <{$selection.order}>&nbsp;
            <{$selection.since}>&nbsp;
            <{foreach item=hidval key=hidvar from=$selection.vars}>
                <{if $hidval && $hidvar neq "sort" && $hidvar neq "order" && $hidvar neq "since"}>
                    <input type="hidden" name="<{$hidvar}>" value="<{$hidval}>"/>
                <{/if}>
            <{/foreach}>
            <input type="submit" value="<{$smarty.const._SUBMIT}>"/>
        </form>
    <{/strip}>
</div>
</div>
<!-- end topic main div -->
<{if $pagenav}>
    <div class="pagenav"><{$pagenav|replace:'form':'div'|replace:'id="xo-pagenav"':''}></div>
    <br>
<{/if}>
<div class="clear"></div>
<!-- bottom of the page -->
<div>
    <div class="icon_left">
        <{$img_newposts}> = <{$smarty.const._MD_NEWBB_NEWPOSTS}> (<{$img_hotnewposts}> = <{$smarty.const._MD_NEWBB_MORETHAN}>) <br>
        <{$img_folder}> = <{$smarty.const._MD_NEWBB_NONEWPOSTS}> (<{$img_hotfolder}> = <{$smarty.const._MD_NEWBB_MORETHAN2}>) <br>
        <{$img_locked}> = <{$smarty.const._MD_NEWBB_TOPICLOCKED}> <br>
        <{$img_sticky}> = <{$smarty.const._MD_NEWBB_TOPICSTICKY}> <br>
        <{$img_digest}> = <{$smarty.const._MD_NEWBB_TOPICDIGEST}> <br>
        <{$img_poll}> = <{$smarty.const._MD_NEWBB_TOPICHASPOLL}>
    </div>
    <div class="icon_right">
        <form action="<{$xoops_url}>/modules/<{$xoops_dirname}>/search.php" method="get">
            <input name="term" id="term" type="text" size="15"/>
            <{foreach item=hidval key=hidvar from=$search}>
                <{if $hidval }>
                    <input type="hidden" name="<{$hidvar}>" value="<{$hidval}>"/>
                <{/if}>
            <{/foreach}>
            <input type="submit" class="formButton" value="<{$smarty.const._MD_NEWBB_SEARCH}>"/><br>
            [<a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/search.php"><{$smarty.const._MD_NEWBB_ADVSEARCH}></a>]
        </form>
        <br>
        <!-- START irmtfan add forum selection box -->
        <{if $forum_jumpbox }>
            <form method="get" action="<{$selection.action}>">
                <{$selection.forum}>&nbsp;
                <{foreach item=hidval key=hidvar from=$selection.vars}>
                    <{if $hidval && $hidvar neq "forum"}>
                        <input type="hidden" name="<{$hidvar}>" value="<{$hidval}>"/>
                    <{/if}>
                <{/foreach}>
                <input type="submit" value="<{$smarty.const._SUBMIT}>"/>
            </form>
            <br>
            <{$forum_jumpbox}>
        <{/if}>
        <!-- END irmtfan add forum selection box -->
    </div>
    <div class="clear"></div>
    <br>
</div>
<{if $online}><{includeq file="db:newbb_online.tpl"}><{/if}>
<{includeq file='db:newbb_notification_select.tpl'}>
<!-- end module contents -->

<div class="forum_header">
    <div class="forum_title">
        <h2><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php"><{$forum_index_title}></a></h2>
        <hr class="align_left"/>
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php"><{$smarty.const._MD_NEWBB_FORUMHOME}></a>
        <span class="delimiter">&raquo;</span>
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php?cat=<{$category.id}>"><{$category.title}></a>
        <{if $parentforum}>
            <{foreach item=forum from=$parentforum}>
            <span class="delimiter">&raquo;</span>
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum.forum_id}>"><{$forum.forum_name}></a>
        <{/foreach}>
        <{/if}>
        <span class="delimiter">&raquo;</span>
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum_id}>"><{$forum_name}></a>
        <{if $forum_topictype}> <{$forum_topictype}> <{/if}>
        <{if $forum_topicstatus}> [<{$forum_topicstatus}>]
        <{else}> [
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum_id}>&amp;status=digest" title="<{$smarty.const._MD_NEWBB_DIGEST}>"><{$smarty.const._MD_NEWBB_DIGEST}></a>
            ]
        <{/if}>
    </div>
    <div class="clear"></div>
</div>
<br>

<{if $subforum}>
    <{includeq file="db:newbb_viewforum_subforum.tpl"}>
    <br>
<{/if}>

<{if $mode gt 1}>
<form name="form_topics_admin" action="<{$xoops_url}>/modules/<{$xoops_dirname}>/action.topic.php" method="POST" onsubmit="if(window.document.form_topics_admin.op.value &lt; 1){return false;}">
    <{/if}>

    <{if $viewer_level gt 1}>
        <div class="floatleft" id="admin">
            <{$forum_addpoll}> <{$forum_post_or_register}>
        </div>
        <div class="_col_end right">
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
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum_id}>" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_VIEW}>"><{$smarty.const._MD_NEWBB_TYPE_VIEW}></a>
            <{else}>
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum_id}>&amp;status=active#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_ADMIN}>"><{$smarty.const._MD_NEWBB_TYPE_ADMIN}></a>
                |
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum_id}>&amp;status=pending#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_PENDING}>"><{$smarty.const._MD_NEWBB_TYPE_PENDING}></a>
                |
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum_id}>&amp;status=deleted#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_DELETED}>"><{$smarty.const._MD_NEWBB_TYPE_DELETED}></a>
                |
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/moderate.php?forum=<{$forum_id}>" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_SUSPEND}>"><{$smarty.const._MD_NEWBB_TYPE_SUSPEND}></a>
            <{/if}>
        </div>
    <{else}>
        <div class="floatright">
            <{$forum_addpoll}> <{$forum_post_or_register}>
        </div>
    <{/if}>
    <div class="clear"></div>
    <br>

    <div>
        <div class="dropdown floatleft">
            <{includeq file="db:newbb_viewforum_menu.tpl"}>
        </div>
        <div class="pagenav">
            <{$forum_pagenav|replace:'form':'div'|replace:'id="xo-pagenav"':''}>
        </div>
        <div class="clear"></div>
    </div>
    <br>
    <div class="topic_list outer">
        <div class="head">
            <div class="topic_folder floatleft align_center">
                <{if $mode gt 1}>
                    <{$smarty.const._ALL}>:
                <{else}>
                    &nbsp;
                <{/if}>
            </div>
            <div class="topic_icon floatleft align_center">
                <{if $mode gt 1}>
                    <input type="checkbox" name="topic_check" id="topic_check" value="1" onclick="xoopsCheckAll('form_topics_admin', 'topic_check');"/>
                <{else}>
                    &nbsp;
                <{/if}>
            </div>
            <div class="topic_name floatleft left">
                &nbsp;
                <strong><a href="<{$h_topic_link}>"><{$smarty.const._MD_NEWBB_TOPICS}></a></strong>
                <{if $sticky > 0}>
                    <br \>
                    <strong><{$smarty.const._MD_NEWBB_IMTOPICS}></strong>
                <{/if}>
            </div>
            <div class="topic_poster floatleft align_center"><strong><a href="<{$h_poster_link}>"><{$smarty.const._MD_NEWBB_TOPICPOSTER}></a></strong></div>
            <div class="topic_publish floatleft align_center"><strong><a href="<{$h_publish_link}>"><{$smarty.const._MD_NEWBB_TOPICTIME}></a></strong></div>
            <div class="topic_reply floatleft align_center"><strong><a href="<{$h_reply_link}>"><{$smarty.const._MD_NEWBB_REPLIES}></a></strong></div>
            <div class="topic_view floatleft align_center"><strong><a href="<{$h_views_link}>"><{$smarty.const._MD_NEWBB_VIEWS}></a></strong></div>
            <div class="topic_rate floatleft align_center">
                <{if $rating_enable}>
                    <strong><a href="<{$h_rating_link}>"><{$smarty.const._MD_NEWBB_RATINGS}></a></strong>
                <{/if}>
            </div>
            <div class="_col_end align_center"><strong><a href="<{$h_date_link}>"><{$smarty.const._MD_NEWBB_LASTPOSTTIME}></a></strong></div>
            <div class="clear"></div>
        </div>
        <!-- start forum topic -->

        <{foreach name=loop item=topic from=$topics}>
        <{if $topic.stick AND $smarty.foreach.loop.iteration == $sticky+1}>
            <div class="head">
                <div class="topic_folder floatleft align_center">&nbsp;</div>
                <div class="topic_icon floatleft align_center">&nbsp;</div>
                <div class="_col_end left">
                    <strong><{$smarty.const._MD_NEWBB_NOTIMTOPICS}></strong>
                </div>
                <div class="clear"></div>
            </div>
        <{/if}>
        <div class="<{cycle values="even,odd"}>">
            <div class="topic_folder floatleft <{if $topic.topic_read eq 1 }>topic-read<{else}>topic-new<{/if}> align_center"><{$topic.topic_folder}></div>
            <div class="topic_icon floatleft align_center">
                <{if $mode gt 1}>
                    <input type="checkbox" name="topic_id[]" id="topic_id[<{$topic.topic_id}>]" value="<{$topic.topic_id}>"/>
                <{else}>
                    <{$topic.topic_icon}>
                <{/if}>
            </div>
            <div class="topic_name floatleft left">
                &nbsp;
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/<{$topic.topic_link}>" title="<{$topic.topic_excerpt}>">
                    <{$topic.topic_title}>
                </a>
                <{$topic.attachment}> <{$topic.topic_page_jump}>
            </div>
            <div class="topic_poster floatleft align_center"><{$topic.topic_poster}></div>
            <div class="topic_publish floatleft align_center"><{$topic.topic_time}></div>
            <div class="topic_reply floatleft align_center"><{$topic.topic_replies}></div>
            <div class="topic_view floatleft align_center"><{$topic.topic_views}></div>
            <div class="topic_rate floatleft align_center">
                <{if $rating_enable}>
                    <{$topic.rating_img}>
                <{/if}>
            </div>
            <div class="_col_end right">
                <{$topic.topic_last_posttime}><br>
                <{$smarty.const._MD_NEWBB_BY}>&nbsp;<{$topic.topic_last_poster}>&nbsp;&nbsp;<{$topic.topic_page_jump_icon}>
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
        <form method="get" action="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php">
            <strong><{$smarty.const._MD_NEWBB_SORTEDBY}></strong>&nbsp;<{$forum_selection_sort}>&nbsp;<{$forum_selection_order}>&nbsp;<{$forum_selection_since}>&nbsp;
            <input type="hidden" name="forum" id="forum" value="<{$forum_id}>"/>
            <input type="hidden" name="status" value="<{$status}>"/>
            <input type="submit" value="<{$smarty.const._SUBMIT}>"/>
        </form>
    <{/strip}>
</div>
</div>
<!-- end forum main table -->
<br>
<div>
    <div class="icon_left">
        <{$forum_addpoll}> <{$forum_post_or_register}>
    </div>
    <div class="pagenav">
        <{$forum_pagenav|replace:'form':'div'|replace:'id="xo-pagenav"':''}>
    </div>
</div>
<div class="clear"></div>
<br>
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
            <input type="hidden" name="forum" id="forum" value="<{$forum_id}>"/>
            <input type="hidden" name="sortby" id="sortby" value="p.post_time desc"/>
            <input type="hidden" name="since" id="since" value="<{$forum_since}>"/>
            <input type="hidden" name="action" id="action" value="yes"/>
            <input type="hidden" name="searchin" id="searchin" value="both"/>
            <input type="submit" class="formButton" value="<{$smarty.const._MD_NEWBB_SEARCH}>"/><br>
            [<a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/search.php"><{$smarty.const._MD_NEWBB_ADVSEARCH}></a>]
        </form>
        <br>
        <{if $rss_button}>
            <br>
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/rss.php?f=<{$forum_id}>" target="_blank" title="RSS FEED">
                <{$rss_button}>
            </a>
            <span style="font-size:0.7em;"><a href="https://xoops.org">NewBB Version <{$version/100}></a></span>
            <div class="clear"></div>
        <{/if}>
        <{$forum_jumpbox}>
    </div>
</div>
<div class="clear"></div>
<br>

<div>
    <div class="floatleft">
        <{foreach item=perm from=$permission_table}>
        <div><{$perm}></div>
        <{/foreach}>
    </div>
</div>
<div class="clear"></div>
<br>
<{if $online}>
    <br>
    <{includeq file="db:newbb_online.tpl"}>
<{/if}>
<{includeq file='db:newbb_notification_select.tpl'}>
<!-- end module contents -->

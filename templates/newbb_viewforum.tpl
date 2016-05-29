<div class="forum_header">
    <div class="forum_title">
        <h2><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php"><{$forum_index_title}></a></h2>
        <!-- irmtfan hardcode removed align="left" -->
        <hr class="align_left" width="50%" size="1"/>
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php"><{$smarty.const._MD_FORUMHOME}></a>
        <span class="delimiter">&raquo;</span>
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php?cat=<{$category.id}>"><{$category.title}></a>
        <{if $parentforum}>
            <{foreachq item=forum from=$parentforum}>
            <span class="delimiter">&raquo;</span>
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum.forum_id}>"><{$forum.forum_name}></a>
        <{/foreach}>
        <{/if}>
        <span class="delimiter">&raquo;</span>
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum_id}>"><{$forum_name}></a>
        <{if $forum_topictype}> <{$forum_topictype}> <{/if}>
        <{if $forum_topicstatus}> [<{$forum_topicstatus}>]
        <{else}> [
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum_id}>&amp;status=digest" title="<{$smarty.const._MD_DIGEST}>"><{$smarty.const._MD_DIGEST}></a>
            ]
        <{/if}>
    </div>
    <div style="clear:both;"></div>
</div>
<div class="clear"></div>
<br>

<{if $subforum}>
    <{includeq file="db:newbb_viewforum_subforum.tpl"}>
    <br>
<{/if}>

<{if $mode gt 1}>
<form name="form_topics_admin" action="<{$xoops_url}>/modules/<{$xoops_dirname}>/action.topic.php" method="POST" onsubmit="if(window.document.form_topics_admin.op.value &lt; 1){return false;}">
    <{/if}>

    <{if $viewer_level gt 1}>
        <div class="left" style="padding: 5px;" id="admin">
            <{$forum_addpoll}> <{$forum_post_or_register}>
        </div>
        <div class="right" style="padding: 5px;">
            <{if $mode gt 1}>
                <{$smarty.const._ALL}>:
                <input type="checkbox" name="topic_check1" id="topic_check1" value="1" onclick="xoopsCheckAll('form_topics_admin', 'topic_check1');"/>
                <select name="op">
                    <option value="0"><{$smarty.const._SELECT}></option>
                    <option value="delete"><{$smarty.const._DELETE}></option>
                    <{if $status eq "pending"}>
                        <option value="approve"><{$smarty.const._MD_APPROVE}></option>
                        <option value="move"><{$smarty.const._MD_MOVE}></option>
                    <{elseif $status eq "deleted"}>
                        <option value="restore"><{$smarty.const._MD_RESTORE}></option>
                    <{else}>
                        <option value="move"><{$smarty.const._MD_MOVE}></option>
                    <{/if}>
                </select>
                <input type="hidden" name="forum_id" value="<{$forum_id}>"/>
                <input type="submit" name="submit" value="<{$smarty.const._SUBMIT}>"/>
                |
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum_id}>" target="_self" title="<{$smarty.const._MD_TYPE_VIEW}>"><{$smarty.const._MD_TYPE_VIEW}></a>
            <{else}>
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum_id}>&amp;status=active#admin" target="_self" title="<{$smarty.const._MD_TYPE_ADMIN}>"><{$smarty.const._MD_TYPE_ADMIN}></a>
                |
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum_id}>&amp;status=pending#admin" target="_self" title="<{$smarty.const._MD_TYPE_PENDING}>"><{$smarty.const._MD_TYPE_PENDING}></a>
                |
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum_id}>&amp;status=deleted#admin" target="_self" title="<{$smarty.const._MD_TYPE_DELETED}>"><{$smarty.const._MD_TYPE_DELETED}></a>
                |
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/moderate.php?forum=<{$forum_id}>" target="_self" title="<{$smarty.const._MD_TYPE_SUSPEND}>"><{$smarty.const._MD_TYPE_SUSPEND}></a>
            <{/if}>
        </div>
    <{else}>
        <div class="right" style="padding: 5px;">
            <{$forum_addpoll}> <{$forum_post_or_register}>
        </div>
    <{/if}>
    <div class="clear"></div>
    <br>

    <div>
        <div class="dropdown">
            <{includeq file="db:newbb_viewforum_menu.tpl"}>
        </div>
        <!-- irmtfan hardcode removed style="float: right; text-align:right;" -->
        <div class="icon_right">
            <{$forum_pagenav|replace:'form':'div'|replace:'id="xo-pagenav"':''}> <!-- irmtfan to solve nested forms and id="xo-pagenav" issue -->
        </div>
    </div>
    <div class="clear"></div>
    <br>

    <table class="outer" cellpadding="6" cellspacing="1" border="0" width="100%" align="center">
        <!-- irmtfan hardcode removed align="left" -->
        <tr class="head" class="align_left">
            <td width="5%" colspan="3">
                <{if $mode gt 1}>
                    <{$smarty.const._ALL}>:
                    <input type="checkbox" name="topic_check" id="topic_check" value="1" onclick="xoopsCheckAll('form_topics_admin', 'topic_check');"/>
                <{else}>
                    &nbsp;
                <{/if}>
            </td>
            <td>&nbsp;<strong><a href="<{$h_topic_link}>"><{$smarty.const._MD_TOPICS}></a></strong></td>
            <!-- irmtfan _MD_POSTER to _MD_TOPICPOSTER -->
            <td width="10%" align="center" nowrap="nowrap"><strong><a href="<{$h_poster_link}>"><{$smarty.const._MD_TOPICPOSTER}></a></strong></td>
            <td width="10%" align="center" nowrap="nowrap"><strong><a href="<{$h_publish_link}>"><{$smarty.const._MD_TOPICTIME}></a></strong></td>
            <td width="5%" align="center" nowrap="nowrap"><strong><a href="<{$h_reply_link}>"><{$smarty.const._MD_REPLIES}></a></strong></td>
            <td width="5%" align="center" nowrap="nowrap"><strong><a href="<{$h_views_link}>"><{$smarty.const._MD_VIEWS}></a></strong></td>
            <{if $rating_enable}>
                <td width="5%" align="center" nowrap="nowrap"><strong><a href="<{$h_rating_link}>"><{$smarty.const._MD_RATINGS}></a></strong></td>
            <{/if}>
            <!-- irmtfan _MD_DATE to _MD_LASTPOSTTIME -->
            <td width="15%" align="center" nowrap="nowrap"><strong><a href="<{$h_date_link}>"><{$smarty.const._MD_LASTPOSTTIME}></a></strong></td>
        </tr>

        <{if $sticky > 0}>
            <tr class="head">
                <td colspan="3">&nbsp;</td>
                <{if $rating_enable}>
                    <td colspan="7"><strong><{$smarty.const._MD_IMTOPICS}></strong></td>
                <{else}>
                    <td colspan="6"><strong><{$smarty.const._MD_IMTOPICS}></strong></td>
                <{/if}>
            </tr>
        <{/if}>

        <!-- start forum topic -->

        <{foreachq name=loop item=topic from=$topics}>

        <{if $topic.stick AND $smarty.foreach.loop.iteration == $sticky+1}>
            <tr class="head">
                <td colspan="3">&nbsp;</td>
                <{if $rating_enable}>
                    <td colspan="7"><strong><{$smarty.const._MD_NOTIMTOPICS}></strong></td>
                <{else}>
                    <td colspan="6"><strong><{$smarty.const._MD_NOTIMTOPICS}></strong></td>
                <{/if}>
            </tr>
        <{/if}>
        <tr class="<{cycle values="even,odd"}>">
            <!-- irmtfan add topic-read/topic-new smarty variable  -->

            <td width="4%" align="center" class="<{if $topic.topic_read eq 1 }>topic-read<{else}>topic-new<{/if}>">
                <{if $mode gt 1}>
                    <input type="checkbox" name="topic_id[]" id="topic_id[<{$topic.topic_id}>]" value="<{$topic.topic_id}>"/>
                <{else}>
                    <{$topic.topic_folder}>
                <{/if}>
            </td>


            <td width="4%" align="center"><{$topic.topic_icon}></td>

            <{*mamba: add digest icon*}>
            <{*<{if $topic.topic_replies eq 1}>*}>
            <{*<td width="4%" align="center">*}>
            <{*<{$img_digest}>*}>
            <{*</td>*}>
            <{*<{/if}>*}>
            <td width="4%" align="center">
                <{if $topic.topic_digest eq 1}>
                    <{$img_digest}>
                <{/if}>
            </td>


            <{*<td width="4%" align="center">zzzz</td>*}>


            <td>&nbsp;<a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/<{$topic.topic_link}>" title="<{$topic.topic_excerpt}>">
                    <{$topic.topic_title}></a><{$topic.attachment}> <{$topic.topic_page_jump}>
            </td>


            <td align="center" valign="middle"><{$topic.topic_poster}></td>
            <td align="center" valign="middle"><{$topic.topic_time}></td>
            <td align="center" valign="middle"><{$topic.topic_replies}></td>
            <td align="center" valign="middle"><{$topic.topic_views}></td>
            <{if $rating_enable}>
                <td align="center" valign="middle"><{$topic.rating_img}></td>
            <{/if}>
            <!-- irmtfan hardcode removed align="right" -->
            <td class="align_right" valign="middle"><{$topic.topic_last_posttime}><br>
                <!-- irmtfan add $smarty.const._MD_BY -->
                <{$smarty.const._MD_BY}>&nbsp;<{$topic.topic_last_poster}>&nbsp;&nbsp;<{$topic.topic_page_jump_icon}>
            </td>
        </tr>

        <{/foreach}>

        <!-- end forum topic -->

        <{if $mode gt 1}>
</form>
<{/if}>

<tr class="foot">
    <{if $rating_enable}>
    <td colspan="9" align="center"><{else}>
    <td colspan="8" align="center"><{/if}>
        <{strip}>
            <form method="get" action="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php">
                <strong><{$smarty.const._MD_SORTEDBY}></strong>&nbsp;<{$forum_selection_sort}>&nbsp;<{$forum_selection_order}>&nbsp;<{$forum_selection_since}>&nbsp;
                <input type="hidden" name="forum" id="forum" value="<{$forum_id}>"/>
                <input type="hidden" name="status" value="<{$status}>"/>
                <!-- irmtfan remove name="submit" -->
                <input type="submit" value="<{$smarty.const._SUBMIT}>"/>
            </form>
        <{/strip}>
    </td>
</tr>
</table>
<!-- end forum main table -->

<br>

<div>
    <div class="left">
        <{$forum_addpoll}> <{$forum_post_or_register}>
    </div>
    <div class="right">
        <{$forum_pagenav|replace:'form':'div'|replace:'id="xo-pagenav"':''}> <!-- irmtfan to solve nested forms and id="xo-pagenav" issue -->
    </div>
</div>
<div class="clear"></div>

<br style="clear: both;"/>
<br>
<div>
    <!-- irmtfan hardcode style="float: left; text-align: left;" -->
    <div class="icon_left">
        <{$img_newposts}> = <{$smarty.const._MD_NEWPOSTS}> (<{$img_hotnewposts}> = <{$smarty.const._MD_MORETHAN}>) <br>
        <{$img_folder}> = <{$smarty.const._MD_NONEWPOSTS}> (<{$img_hotfolder}> = <{$smarty.const._MD_MORETHAN2}>) <br>
        <{$img_locked}> = <{$smarty.const._MD_TOPICLOCKED}> <br>
        <{$img_sticky}> = <{$smarty.const._MD_TOPICSTICKY}> <br>
        <{$img_digest}> = <{$smarty.const._MD_TOPICDIGEST}> <br>
        <{$img_poll}> = <{$smarty.const._MD_TOPICHASPOLL}>
    </div>
    <!-- irmtfan hardcode removed style="float: right; text-align:right;" -->
    <div class="icon_right">
        <form action="<{$xoops_url}>/modules/<{$xoops_dirname}>/search.php" method="get">
            <input name="term" id="term" type="text" size="15"/>
            <input type="hidden" name="forum" id="forum" value="<{$forum_id}>"/>
            <input type="hidden" name="sortby" id="sortby" value="p.post_time desc"/>
            <input type="hidden" name="since" id="since" value="<{$forum_since}>"/>
            <input type="hidden" name="action" id="action" value="yes"/>
            <input type="hidden" name="searchin" id="searchin" value="both"/>
            <input type="submit" class="formButton" value="<{$smarty.const._MD_SEARCH}>"/><br>
            [<a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/search.php"><{$smarty.const._MD_ADVSEARCH}></a>]
        </form>
        <br>
        <{if $rss_button}>
            <br>
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/rss.php?f=<{$forum_id}>" target="_blank" title="RSS FEED">
                <{$rss_button}>
            </a>
            <span style="font-size:0.7em;"><a href="http://xoops.org">NewBB Version <{$version/100}></a></span>
            <br style="clear: both;"/>
            <br>
        <{/if}>
        <{$forum_jumpbox}>
    </div>
</div>
<div class="clear"></div>
<br style="clear: both;"/>
<br>

<div>
    <!-- irmtfan hardcode removed  style="float: left; -->
    <div class="floatleft">
        <{foreachq item=perm from=$permission_table}>
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

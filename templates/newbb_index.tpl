<div class="forum_header">
    <!-- irmtfan hardcode remove style="float: left;" -->
    <div class="forum_title">
        <h2><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php"><{$index_title}></a></h2>
        <!-- irmtfan hardcode remove align="left" -->
        <hr class="align_left" width="50%" size="1"/>
        <{$index_desc}>
    </div>
</div>
<div style="clear:both;"></div>

<{if $viewer_level gt 1}>
    <br>
  <fieldset style="border:1px solid #778;margin:1em 0;text-align:left;background-color:transparent;">
    <legend>&nbsp;<{$smarty.const._MD_NEWBB_ADMINCP}>&nbsp;</legend>
    <div class="forum_stats">
        <div class="forum_stats_left">
            <{$smarty.const._MD_NEWBB_TOPIC}>:
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/list.topic.php?status=active#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_ADMIN}>"><{$smarty.const._MD_NEWBB_TYPE_ADMIN}></a> |
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/list.topic.php?status=pending#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_PENDING}>"><{if $wait_new_topic}>(
                    <span style="color: #ff0000; "><b><{$wait_new_topic}></b></span>
                    ) <{/if}><{$smarty.const._MD_NEWBB_TYPE_PENDING}></a> |
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/list.topic.php?status=deleted#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_DELETED}>"><{if $delete_topic}>(
                    <span style="color: #ff0000; "><b><{$delete_topic}></b></span>
                    ) <{/if}><{$smarty.const._MD_NEWBB_TYPE_DELETED}></a><br>
            <{$smarty.const._MD_NEWBB_POST2}>:
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewpost.php?status=active#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_ADMIN}>"><{$smarty.const._MD_NEWBB_TYPE_ADMIN}></a> |
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewpost.php?status=pending#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_PENDING}>"><{if $wait_new_post}>(
                    <span style="color: #ff0000; "><b><{$wait_new_post}></b></span>
                    ) <{/if}><{$smarty.const._MD_NEWBB_TYPE_PENDING}></a> |
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewpost.php?status=deleted#admin" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_DELETED}>"><{if $delete_post}>(
                    <span style="color: #ff0000; "><b><{$delete_post}></b></span>
                    ) <{/if}><{$smarty.const._MD_NEWBB_TYPE_DELETED}></a>
        </div>
        <div class="forum_stats_right">
            <{if $report_post}><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/admin/admin_report.php"><{$report_post}></a><{/if}>
            <br><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/moderate.php" target="_self" title="<{$smarty.const._MD_NEWBB_TYPE_SUSPEND}>"><{$smarty.const._MD_NEWBB_TYPE_SUSPEND}></a> |
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/admin/index.php" target="_self" title="<{$smarty.const._MD_NEWBB_ADMINCP}>"><{$smarty.const._MD_NEWBB_ADMINCP}></a>
        </div>
        <div style="clear:both;"></div>
    </div>
  </fieldset>    
<{/if}>
<br style="clear: both;"/>
<div class="dropdown">
    <{include file="db:newbb_index_menu.tpl"}>
</div>
<br style="clear: both;"/>
<br>

<!-- start forum categories -->
<div class="index_category">
    <!-- start forum categories -->
    <{foreach item=category from=$categories}>
        <table class="index_category" cellspacing="0" width="100%">
            <tr class="head">
                <td width="3%" valign="middle" align="center">
                    <!-- irmtfan simplify onclick method and use newbbDisplayImage(this.children[0] for IE7&8) - add alt and title"-->
                    <div class="pointer"
                         onclick="ToggleBlockCategory('<{$category.cat_element_id}>',(this.firstElementChild || this.children[0]) , '<{$category_icon.expand}>', '<{$category_icon.collapse}>','<{$smarty.const._MD_NEWBB_HIDE|escape:'quotes'}>','<{$smarty.const._MD_NEWBB_SEE|escape:'quotes'}>')">
                        <{$category.cat_displayImage}>
                    </div>
                </td>
                <{if $category.cat_image}>
                    <td width="8%"><img src="<{$category.cat_image}>" alt="<{$category.cat_title}>"/></td>
                <{/if}>
                <!-- irmtfan hardcode removed align="left" -->
                <td class="align_left">
                    <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php?cat=<{$category.cat_id}>"><{$category.cat_title}></a>
                    <{if $category.cat_description}><p class="desc"><{$category.cat_description}></p><{/if}>
                </td>
                <{if $category.cat_sponsor}>
                    <!-- irmtfan hardcode removed align="right" -->
                    <td width="15%" nowrap="nowrap" class="align_right">
                        <p class="desc"><a href="<{$category.cat_sponsor.link}>" title="<{$category.cat_sponsor.title}>" target="_blank"><{$category.cat_sponsor.title}></a></p>
                    </td>
                <{/if}>
            </tr>
        </table>
        <!-- irmtfan move semicolon -->
        <div id="<{$category.cat_element_id}>" style="display: <{$category.cat_display}>;">
            <table border="0" cellspacing="2" cellpadding="0" width="100%">
                <{if $category.forums}>
                    <tr class="head" align="center">
                        <td width="5%">&nbsp;</td>
                        <{if $subforum_display == "expand"}>
                            <!-- irmtfan hardcode removed align="left" -->
                            <td colspan="2" width="37%" nowrap="nowrap" class="align_left"><{$smarty.const._MD_NEWBB_FORUM}></td>
                        <{else}>
                            <!-- irmtfan hardcode removed align="left" -->
                            <td width="37%" nowrap="nowrap" class="align_left"><{$smarty.const._MD_NEWBB_FORUM}></td>
                        <{/if}>
                        <td width="9%" nowrap="nowrap"><{$smarty.const._MD_NEWBB_TOPICS}></td>
                        <td width="9%" nowrap="nowrap"><{$smarty.const._MD_NEWBB_POSTS}></td>
                        <td width="40%" nowrap="nowrap"><{$smarty.const._MD_NEWBB_LASTPOST}></td>
                    </tr>
                <{/if}>

                <!-- start forums -->
                <{if $subforum_display == "expand"}>
                    <{foreach item=forum from=$category.forums}>
                        <tr>
                            <!-- irmtfan add forum-read/forum-new smarty variable  -->
                            <td width="5%" class="even <{if $forum.forum_read eq 1 }>forum-read<{else}>forum-new<{/if}>" align="center" valign="middle"><{$forum.forum_folder}></td>
                            <td colspan="2" class="odd">
                                <div id="index_forum">
<span class="item"><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum.forum_id}>"><{$forum.forum_name}></a>
    <{if $rss_enable}>
        (
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/rss.php?f=<{$forum.forum_id}>" target="_blank" title="RSS feed">RSS</a>
        )
    <{/if}>
    <br><{$forum.forum_desc}>
</span>
                                    <{if $forum.forum_moderators}>
                                        <span class="extra">
<{$smarty.const._MD_NEWBB_MODERATOR}>: <{$forum.forum_moderators}>
</span>
                                    <{/if}>
                                </div>
                            </td>
                            <td class="even" align="center" valign="middle">
                                <{if $stats[$forum.forum_id].topic.day}><strong><{$stats[$forum.forum_id].topic.day}></strong>/<{/if}>
                                <{$forum.forum_topics}>
                            </td>
                            <td class="odd" align="center" valign="middle">
                                <{if $stats[$forum.forum_id].post.day}><strong><{$stats[$forum.forum_id].post.day}></strong>/<{/if}>
                                <{$forum.forum_posts}>
                            </td>
                            <!-- irmtfan hardcode removed align="right" -->
                            <td class="even" class="align_right" valign="middle">
                                <{if $forum.forum_lastpost_subject}>
                                    <{$forum.forum_lastpost_time}> <{$smarty.const._MD_NEWBB_BY}> <{$forum.forum_lastpost_user}>
                                    <br>
                                    <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewtopic.php?post_id=<{$forum.forum_lastpost_id}>">
                                        <{$forum.forum_lastpost_subject}>&nbsp;&nbsp;
                                        <!-- irmtfan remove icon_path -->
                                        <{$forum.forum_lastpost_icon}>
                                    </a>
                                <{else}>
                                    <{$smarty.const._MD_NEWBB_NOTOPIC}>
                                <{/if}>
                            </td>
                        </tr>
                        <{if $forum.subforum}>
                            <tr class="head">
                                <td width="5%">&nbsp;</td>
                                <td width="5%" align="center"><{$img_subforum}>&nbsp;</td>
                                <td width="32%" align="center"><{$smarty.const._MD_NEWBB_SUBFORUMS}>&nbsp;</td>
                                <td width="9%" nowrap="nowrap">&nbsp;</td>
                                <td width="9%" nowrap="nowrap">&nbsp;</td>
                                <td width="40%" nowrap="nowrap">&nbsp;</td>
                            </tr>
                            <{foreach item=subforum from=$forum.subforum}>
                                <tr>
                                    <td class="odd" width="5%">&nbsp;</td>
                                    <!-- irmtfan add forum-read/forum-new smarty variable  -->
                                    <td class="even <{if $subforum.forum_read eq 1 }>forum-read<{else}>forum-new<{/if}>" align="center" valign="middle" width="5%"><{$subforum.forum_folder}></td>
                                    <td width="32%" class="odd">
                                        <div id="index_forum">
<span class="item"><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$subforum.forum_id}>"><strong><{$subforum.forum_name}></strong></a>
    <{if $rss_enable}>
        (
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/rss.php?f=<{$subforum.forum_id}>" target="_blank" title="RSS feed">RSS</a>
        )
    <{/if}>
    <br><{$subforum.forum_desc}>
</span>
                                            <{if $subforum.forum_moderators}>
                                                <span class="extra">
<{$smarty.const._MD_NEWBB_MODERATOR}>: <{$subforum.forum_moderators}>
</span>
                                            <{/if}>
                                        </div>
                                    </td>
                                    <td class="even" width="9%" align="center" valign="middle">
                                        <{if $stats[$subforum.forum_id].topic.day}><strong><{$stats[$subforum.forum_id].topic.day}></strong>/<{/if}>
                                        <{$subforum.forum_topics}>
                                    </td>
                                    <td class="odd" width="9%" align="center" valign="middle">
                                        <{if $stats[$subforum.forum_id].post.day}><strong><{$stats[$subforum.forum_id].post.day}></strong>/<{/if}>
                                        <{$subforum.forum_posts}>
                                    </td>
                                    <!-- irmtfan hardcode removed align="right" -->

                                    <td class="even" width="40%" class="align_right" valign="middle">
                                        <{if $subforum.forum_lastpost_subject}>
                                            <{$subforum.forum_lastpost_time}> <{$smarty.const._MD_NEWBB_BY}> <{$subforum.forum_lastpost_user}>
                                            <br>
                                            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewtopic.php?post_id=<{$subforum.forum_lastpost_id}>">
                                                <{$subforum.forum_lastpost_subject}>&nbsp;&nbsp;
                                                <!-- irmtfan remove icon_path -->
                                                <{$subforum.forum_lastpost_icon}>
                                            </a>
                                        <{else}>
                                            <{$smarty.const._MD_NEWBB_NOTOPIC}>
                                        <{/if}>
                                    </td>
                                </tr>
                            <{/foreach}>
                        <{/if}>
                    <{/foreach}>

                <{elseif $subforum_display == "collapse"}>

                    <{foreach item=forum from=$category.forums}>
                        <tr>
                            <{if $forum.subforum}>
                                <!-- irmtfan add forum-read/forum-new smarty variable  -->
                                <td class="even <{if $forum.forum_read eq 1 }>forum-read<{else}>forum-new<{/if}>" rowspan="2" align="center" valign="middle"><{$forum.forum_folder}></td>
                            <{else}>
                                <td class="even <{if $forum.forum_read eq 1 }>forum-read<{else}>forum-new<{/if}>" align="center" valign="middle"><{$forum.forum_folder}></td>
                            <{/if}>
                            <td class="odd">
                                <div id="index_forum">
<span class="item"><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum.forum_id}>"><{$forum.forum_name}></a>
    <{if $rss_enable}>
        (
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/rss.php?f=<{$forum.forum_id}>" target="_blank" title="RSS feed">RSS</a>
        )
    <{/if}>
    <br><{$forum.forum_desc}>
</span>
                                    <{if $forum.forum_moderators}>
                                        <span class="extra">
<{$smarty.const._MD_NEWBB_MODERATOR}>: <{$forum.forum_moderators}>
</span>
                                    <{/if}>
                                </div>
                            </td>
                            <td class="even" align="center" valign="middle">
                                <{if $stats[$forum.forum_id].topic.day}><strong><{$stats[$forum.forum_id].topic.day}></strong>/<{/if}>
                                <{$forum.forum_topics}>
                            </td>
                            <td class="odd" align="center" valign="middle">
                                <{if $stats[$forum.forum_id].post.day}><strong><{$stats[$forum.forum_id].post.day}></strong>/<{/if}>
                                <{$forum.forum_posts}>
                            </td>
                            <!-- irmtfan hardcode removed align="right" -->
                            <td class="even" class="align_right" valign="middle">
                                <{if $forum.forum_lastpost_subject}>
                                    <{$forum.forum_lastpost_time}> <{$smarty.const._MD_NEWBB_BY}> <{$forum.forum_lastpost_user}>
                                    <br>
                                    <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewtopic.php?post_id=<{$forum.forum_lastpost_id}>">
                                        <{$forum.forum_lastpost_subject}>&nbsp;&nbsp;
                                        <!-- irmtfan remove icon_path -->
                                        <{$forum.forum_lastpost_icon}>
                                    </a>
                                <{else}>
                                    <{$smarty.const._MD_NEWBB_NOTOPIC}>
                                <{/if}>
                            </td>
                        </tr>
                        <{if $forum.subforum}>
                            <tr>
                                <!-- irmtfan hardcode removed align="left" -->

                                <td class="odd" colspan="4" class="align_left"><{$smarty.const._MD_NEWBB_SUBFORUMS}>&nbsp;<{$img_subforum}>&nbsp;
                                    <{foreach item=subforum from=$forum.subforum}>
                                        &nbsp;[
                                        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$subforum.forum_id}>"><{$subforum.forum_name}></a>
                                        ]
                                    <{/foreach}>
                                </td>
                            </tr>
                        <{/if}>
                    <{/foreach}>

                <{else}>

                    <{foreach item=forum from=$category.forums}>
                        <tr>
                            <!-- irmtfan add forum-read/forum-new smarty variable  -->
                            <td class="even <{if $forum.forum_read eq 1 }>forum-read<{else}>forum-new<{/if}>" align="center" valign="middle"><{$forum.forum_folder}></td>
                            <td class="odd">
                                <div id="index_forum">
<span class="item"><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum.forum_id}>"><{$forum.forum_name}></a>
    <{if $rss_enable}>
        (
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/rss.php?f=<{$forum.forum_id}>" target="_blank" title="RSS feed">RSS</a>
        )
    <{/if}>
    <br><{$forum.forum_desc}>
</span>
                                    <{if $forum.forum_moderators}>
                                        <span class="extra">
<{$smarty.const._MD_NEWBB_MODERATOR}>: <{$forum.forum_moderators}>
</span>
                                    <{/if}>
                                </div>
                            </td>
                            <td class="even" align="center" valign="middle">
                                <{if $stats[$forum.forum_id].topic.day}><strong><{$stats[$forum.forum_id].topic.day}></strong>/<{/if}>
                                <{$forum.forum_topics}>
                            </td>
                            <td class="odd" align="center" valign="middle">
                                <{if $stats[$forum.forum_id].post.day}><strong><{$stats[$forum.forum_id].post.day}></strong>/<{/if}>
                                <{$forum.forum_posts}>
                            </td>
                            <!-- irmtfan hardcode removed align="right" -->
                            <td class="even" class="align_right" valign="middle">
                                <{if $forum.forum_lastpost_subject}>
                                    <{$forum.forum_lastpost_time}> <{$smarty.const._MD_NEWBB_BY}> <{$forum.forum_lastpost_user}>
                                    <br>
                                    <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewtopic.php?post_id=<{$forum.forum_lastpost_id}>">
                                        <{$forum.forum_lastpost_subject}>&nbsp;&nbsp;
                                        <!-- irmtfan remove icon_path -->
                                        <{$forum.forum_lastpost_icon}>
                                    </a>
                                <{else}>
                                    <{$smarty.const._MD_NEWBB_NOTOPIC}>
                                <{/if}>
                            </td>
                        </tr>
                    <{/foreach}>

                <{/if}>
                <!-- end forums -->
            </table>
            <br>
        </div>
    <{/foreach}>
    <!-- end forum categories -->
</div>
<!-- irmtfan hardcode removed style="float: left; text-align: left;" -->
<div class="icon_left">
    <{$img_forum_new}> = <{$smarty.const._MD_NEWBB_NEWPOSTS}><br>
    <{$img_forum}> = <{$smarty.const._MD_NEWBB_NONEWPOSTS}><br>
</div>
<br style="clear:both;"/>
<!-- irmtfan hardcode removed style="float: right; text-align: right;" -->
<div class="icon_right">
    <form action="<{$xoops_url}>/modules/<{$xoops_dirname}>/search.php" method="post" name="search" id="search">
        <input name="term" id="term" type="text" size="20"/>
        <input type="hidden" name="forum" id="forum" value="all"/>
        <input type="hidden" name="sortby" id="sortby" value="p.post_time desc"/>
        <input type="hidden" name="searchin" id="searchin" value="both"/>
        <!-- irmtfan remove name="submit" -->
        <input type="submit" id="submit" value="<{$smarty.const._MD_NEWBB_SEARCH}>"/>
        <br>
        [ <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/search.php"><{$smarty.const._MD_NEWBB_ADVSEARCH}></a> ]
    </form>
    <{if $rss_button}>
        <br>
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/rss.php?c=<{$viewcat}>" target="_blank" title="RSS FEED">
            <{$rss_button}>
        </a>
        <br>
        <span style='font-size: 0.7em;'><a href="https://xoops.org">NewBB Version <{$version/100}></a></span>
        <br>
    <{/if}>
</div>
<br style="clear: both;"/>
<{if $currenttime}>
    <div>
        <div class="even" style="padding: 5px; line-height: 150%;">
            <span style="padding: 2px;"><{$online.statistik}></span>
            <strong><{$smarty.const._MD_NEWBB_STATS}></strong>
        </div>

        <div class="forum_stats odd" style="padding: 5px; line-height: 150%;">
            <div class="forum_stats_left odd">
                <{$currenttime}><br>
                <!-- irmtfan add lastvisit smarty variable for all users -->
                <{$lastvisit}><br>
                <{$smarty.const._MD_NEWBB_TOTALTOPICSC}>
                <strong><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/list.topic.php" title="<{$smarty.const._MD_NEWBB_ALL}>"><{$stats[0].topic.total}></a></strong>
                | <{$smarty.const._MD_NEWBB_TOTALPOSTSC}><strong><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewpost.php" title="<{$smarty.const._MD_NEWBB_ALLPOSTS}>"><{$stats[0].post.total}></a></strong>
                <{if $stats[0].digest.total}>
                    | <{$smarty.const._MD_NEWBB_TOTALDIGESTSC}><strong><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/list.topic.php?status=digest" title="<{$smarty.const._MD_TOTALDIGESTSC}>"><{$stats[0].digest.total}></a></strong>
                <{/if}>
                <{if $userstats}>
                    <br>
                    <br>
                    <!-- irmtfan userstats.lastvisit should be removed because it is for anon users too  -->
                    <{*$userstats.lastvisit*}>
                    <br>
                    <{$userstats.lastpost}>
                <{/if}>
            </div>
            <div class="forum_stats_right odd">
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewpost.php?status=new" title="<{$smarty.const._MD_NEWBB_VIEW_NEWPOSTS}>"><{$smarty.const._MD_NEWBB_VIEW_NEWPOSTS}></a><br>
                <{$smarty.const._MD_NEWBB_TODAYTOPICSC}><strong><{$stats[0].topic.day|default:0}></strong>
                | <{$smarty.const._MD_NEWBB_TODAYPOSTSC}><strong><{$stats[0].post.day|default:0}></strong>
                <{if $userstats}>
                    <br>
                    <br>
                    <{$userstats.topics}> | <{$userstats.posts}><{if $userstats.digests}><br><{$userstats.digests}><{/if}>
                <{/if}>
            </div>
        </div>
    </div>
    <br style="clear:both;"/>
<{/if}>
<br style="clear: both;"/>
<{if $online}>
    <{include file="db:newbb_online.tpl"}>
<{/if}>
<{include file='db:newbb_notification_select.tpl'}>
<!-- end module contents -->

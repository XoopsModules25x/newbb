<!-- START irmtfan assign forum_post_prefix smarty -->
<{if $forum_post_prefix === null }>
    <!-- change the value to what you prefer. even value="" (no prefix) is acceptable -->
    <{assign var=forum_post_prefix value="forumpost"}>
    <!-- it is the first time then add id=0 for recognizoing top of the topic to scroll when $post_id=0 (just $topic_id in the URL) -->
    <div id="<{$forum_post_prefix}>0"></div>
<{/if}>
<!-- END irmtfan assign forum_post_prefix smarty -->
<!-- irmtfan removed
<{*<{if $post_id == $topic_post.post_id}><div id="aktuell"></div><{/if}>*}>
-->
<table class="outer" cellpadding="0" cellspacing="0" border="0" width="100%" align="center" style="border-bottom-width: 0;">
    <tr>
        <!-- irmtfan hardcode removed align="left" -->
        <th width="20%" class="left">
            <div class="ThreadUserName"><{$topic_post.poster.link}></div>
        </th>
        <!-- irmtfan hardcode removed align="left" -->
        <th width="75%" class="left">
            <div class="comTitle"><{$topic_post.post_title}></div>
        </th>
        <!-- irmtfan hardcode removed align="right" -->
        <th class="right">
            <!-- irmtfan hardcode removed style="float: right;" -->
            <div class="ThreadTitle">
                <{if $topic_post.post_id > 0}>
                    <!-- irmtfan add id for each post -->
                    <a id="<{$forum_post_prefix}><{$topic_post.post_id}>" href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewtopic.php?post_id=<{$topic_post.post_id}>">#<{$topic_post.post_no}></a>
                <{/if}>
            </div>
        </th>
    </tr>
    <tr>
        <{if $topic_post.poster.uid gt -1}>
        <td width="20%" class="odd" rowspan="2" valign="top">
            <{if $topic_post.poster.uid != 0}>
                <!-- START hacked by irmtfan rank_title -> rank.title -->
                <div class="comUserRankText"><{if $topic_post.poster.rank.title !=""}> <{$topic_post.poster.rank.title}><br><img src="<{$xoops_upload_url}>/<{$topic_post.poster.rank.image}>" alt="<{$topic_post.poster.rank.title}>" /><{/if}></div>
                <!-- END hacked by irmtfan -->

                <{if $topic_post.poster.avatar != "blank.gif"}>
                    <br>
                    <img class="comUserImg" src="<{$xoops_upload_url}>/<{$topic_post.poster.avatar}>" alt=""/>
                <{else}>
                    <!-- irmtfan remove icon_path -->
                    <br>
                    <{$anonym_avatar}>
                <{/if}>
                <br>
                <{if $infobox.show}>
                    <!-- irmtfan simplify onclick method (this.children[0] for IE7&8) - remove hardcode style="padding:2px;"-->
                    <span class="pointer"
                          onclick="ToggleBlockCategory('<{$topic_post.post_id}>',(this.firstElementChild || this.children[0]) , '<{$infobox.icon.expand}>', '<{$infobox.icon.collapse}>','<{$smarty.const._MD_NEWBB_HIDEUSERDATA|escape:'quotes'}>','<{$smarty.const._MD_NEWBB_SEEUSERDATA|escape:'quotes'}>')">
                        <{$infobox.displayImage}>
</span>
                    <!-- irmtfan move semicolon -->
                    <div id="<{$topic_post.post_id}>" style="display: <{$infobox.style}>;">
                        <div class="comUserStat"><span class="comUserStatCaption"><{$smarty.const._MD_NEWBB_JOINED}>:</span><br><{$topic_post.poster.regdate}><br><span class="comUserStatCaption"><{$smarty.const._US_LASTLOGIN}>
                                :</span><br><{$topic_post.poster.last_login}></div>
                        <!-- irmtfan add last_login -->
                        <{if $topic_post.poster.from}>
                            <div class="comUserStat"><span class="comUserStatCaption"><{$smarty.const._MD_NEWBB_FROM}></span> <{$topic_post.poster.from}></div>
                        <{/if}>
                        <{if $topic_post.poster.groups}>
                            <div class="comUserStat"><span class="comUserStatCaption"><{$smarty.const._MD_NEWBB_GROUP}></span>
                                <{foreach item=group from=$topic_post.poster.groups}> <br><{$group}><{/foreach}>
                            </div>
                        <{/if}>
                        <div class="comUserStat">
                            <span class="comUserStatCaption"><{$smarty.const._MD_NEWBB_POSTS}>:</span>
                            <{if $topic_post.poster.posts gt 0}>
                                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewpost.php?uid=<{$topic_post.poster.uid}>" title="<{$smarty.const._ALL}>" target="_self"><{$topic_post.poster.posts}></a>
                            <{else}>
                                0
                            <{/if}>
                            <{if $topic_post.poster.digests gt 0}>
                                |
                                <span class="comUserStatCaption"><{$smarty.const._MD_NEWBB_DIGESTS}>:</span>
                                <{$topic_post.poster.digests}>
                            <{/if}>
                        </div>
                        <{if $topic_post.poster.level}>
                            <div class="comUserStat"><{$topic_post.poster.level}></div>
                        <{/if}>
                        <{if $topic_post.poster.status}>
                            <div class="comUserStatus"><{$topic_post.poster.status}></div>
                        <{/if}>
                    </div>
                <{/if}>
            <{else}>
                <div class="comUserRankText"><{$anonymous_prefix}><{$topic_post.poster.name}></div>
            <{/if}>
        </td>

        <td colspan="2" class="even">
            <{else}>
        <td colspan="3" class="even">
            <{/if}>
            <div class="comText"><{$topic_post.post_text}></div>
            <{if $topic_post.post_attachment}>
                <div class="comText"><{$topic_post.post_attachment}></div>
            <{/if}>
            <div class="clear"></div>
            <br>
            <!-- irmtfan hardcode removed style="float: right; padding: 5px; margin-top: 10px;" -->
            <div class="post_ip">
                <{if $topic_post.poster_ip}>
                    IP:
                    <a href="http://www.whois.sc/<{$topic_post.poster_ip}>" target="_blank"><{$topic_post.poster_ip}></a>
                    |
                <{/if}>
                <{if $topic_post.poster.uid gt 0}>
                <{$smarty.const._MD_NEWBB_POSTEDON}><{$topic_post.post_date}></div>
            <{/if}>
            <{if $topic_post.post_edit}>
                <div class="clear"></div>
                <br>
                <!-- irmtfan hardcode removed style="float: right; padding: 5px; margin-top: 10px; border:1px solid #000;" -->
                <div class="post_edit">
                    <!-- irmtfan hardcode removed -->
                    <{$topic_post.post_edit}>
                </div>
            <{/if}>
        </td>
    </tr>

    <tr>
        <{if $topic_post.poster.uid gt -1}>
        <td colspan="2" class="odd" valign="bottom">
            <{else}>
        <td colspan="3" class="odd" valign="bottom">
            <{/if}>
            <{if $topic_post.post_signature}>
                <div class="signature">
                    <!-- irmtfan hardcode removed hardcode ____________________<br> -->
                    <{$topic_post.post_signature}>
                </div>
            <{/if}>
        </td>
    </tr>

    <tr>
        <td colspan="3" class="foot">
            <table style="border: 0; padding: 0; margin: 0;">
                <tr>
                    <!--  irmtfan removed hardcode style="text-align:left;" -->
                    <td class="left">
                        &nbsp;<a href="#threadtop" title="<{$smarty.const._MD_NEWBB_TOP}>"><{$p_up}></a>&nbsp;
                        <{if $topic_post.thread_action}>
                            <{foreach item=btn from=$topic_post.thread_action}>
                            <!--  irmtfan add alt key -->
                            <a href="<{$btn.link}>&amp;post_id=<{$topic_post.post_id}>" alt="<{$btn.name}>" title="<{$btn.name}>" <{if $btn.target}>target="<{$btn.target}>"<{/if}>> <{$btn.image}></a>&nbsp;
                        <{/foreach}>
                        <{/if}>
                    </td>
                    <!--  irmtfan removed hardcode style="text-align:right;" -->
                    <td class="right">
                        <!--  irmtfan if the post is not advertise -->
                        <{if $mode gt 1 && $topic_post.poster.uid gt -1}>
                            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/action.post.php?post_id=<{$topic_post.post_id}>&amp;op=split&amp;mode=1" target="_self" title="<{$smarty.const._MD_NEWBB_SPLIT_ONE}>"><{$smarty.const._MD_NEWBB_SPLIT_ONE}></a>
                            |
                            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/action.post.php?post_id=<{$topic_post.post_id}>&amp;op=split&amp;mode=2" target="_self" title="<{$smarty.const._MD_NEWBB_SPLIT_TREE}>"><{$smarty.const._MD_NEWBB_SPLIT_TREE}></a>
                            |
                            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/action.post.php?post_id=<{$topic_post.post_id}>&amp;op=split&amp;mode=3" target="_self" title="<{$smarty.const._MD_NEWBB_SPLIT_ALL}>"><{$smarty.const._MD_NEWBB_SPLIT_ALL}></a>
                            |
                            <input type="checkbox" name="post_id[]" id="post_id[<{$topic_post.post_id}>]" value="<{$topic_post.post_id}>"/>
                        <{else}>
                            <{if $topic_post.thread_buttons}>
                                <{foreach item=btn from=$topic_post.thread_buttons}>
                                <!--  irmtfan add alt key -->
                                <a href="<{$btn.link}>&amp;post_id=<{$topic_post.post_id}>" alt="<{$btn.name}>" title="<{$btn.name}>"> <{$btn.image}></a>
                            <{/foreach}>
                            <{/if}>
                        <{/if}>
                        <!--<a href="#threadtop" title="<{$smarty.const._MD_NEWBB_TOP}>"> <{$p_up}></a>-->
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

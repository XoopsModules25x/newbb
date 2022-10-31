<div class="forum_header">
    <div class="forum_title">
        <h2><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php"><{$lang_forum_index}></a></h2>
        <{* irmtfan hardcode removed align="left" *}>
        <hr class="align_left" width="50%" size="1">
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
        <span class="delimiter">&raquo;</span>
        <strong><{$topic_title}></strong> <{if $topicstatus}><{$topicstatus}><{/if}>
    </div>
</div>
<div class="clear"></div>
<br>
<{if $tagbar|default:''}>
    <div class="taglist" style="padding: 5px;">
        <{include file="db:tag_bar.tpl"}>
    </div>
<{/if}>

<br>

<{if $online}>
    <div class="left" style="padding: 5px;">
        <{$smarty.const._MD_NEWBB_BROWSING}>&nbsp;
        <{foreach item=user from=$online.users|default:null}>
            <a href="<{$user.link}>">
                <{if $user.level eq 2}>
                    <span class="online_admin"><{$user.uname}></span>
                <{elseif $user.level eq 1}>
                    <span class="online_moderator"><{$user.uname}></span>
                <{else}>
                    <{$user.uname}>
                <{/if}>
            </a>
            &nbsp;
        <{/foreach}>
        <{if $online.num_anonymous}>
            &nbsp;<{$online.num_anonymous}> <{$smarty.const._MD_NEWBB_ANONYMOUS_USERS}>
        <{/if}>
    </div>
    <br>
<{/if}>
<{* only for login user // *}>
<{if $viewer_level gt 0}>

    <{* modal for rate // *}>
    <{if $rating_enable}>
    <div class="modal fade bs-example-modal-sm container" id="replyrate" tabindex="-1" role="dialog" aria-labelledby="replyrate">
        <div class="modal-dialog btn-bottom" role="document">
            <div class="modal-content btn-group" role="group">
                <button type="button" class="btn btn-default" onclick="location.href='/modules/<{$xoops_dirname}>/ratethread.php?topic_id=<{$topic_id}>&amp;forum=<{$forum_id}>&amp;rate=5';"><i class="fa fa-thumbs-o-up fa-2x" aria-hidden="true"></i><br><{$smarty.const._MD_NEWBB_RATE5}></button>
                <button type="button" class="btn btn-default" onclick="location.href='/modules/<{$xoops_dirname}>/ratethread.php?topic_id=<{$topic_id}>&amp;forum=<{$forum_id}>&amp;rate=4';"><i class="fa fa-smile-o fa-2x" aria-hidden="true"></i><br><{$smarty.const._MD_NEWBB_RATE4}></button>
                <button type="button" class="btn btn-default" onclick="location.href='/modules/<{$xoops_dirname}>/ratethread.php?topic_id=<{$topic_id}>&amp;forum=<{$forum_id}>&amp;rate=3';"><i class="fa fa-meh-o fa-2x" aria-hidden="true"></i><br><{$smarty.const._MD_NEWBB_RATE3}></button>
                <button type="button" class="btn btn-default" onclick="location.href='/modules/<{$xoops_dirname}>/ratethread.php?topic_id=<{$topic_id}>&amp;forum=<{$forum_id}>&amp;rate=2';"><i class="fa fa-frown-o fa-2x" aria-hidden="true"></i><br><{$smarty.const._MD_NEWBB_RATE2}></button>
                <button type="button" class="btn btn-default" onclick="location.href='/modules/<{$xoops_dirname}>/ratethread.php?topic_id=<{$topic_id}>&amp;forum=<{$forum_id}>&amp;rate=1';"><i class="fa fa-thumbs-o-down fa-2x" aria-hidden="true"></i><br><{$smarty.const._MD_NEWBB_RATE1}></button>
            </div>
        </div>
    </div>
    <{/if}>

    <{* modal for quickreply // *}>
    <{if $quickreply.show}>
    <div class="modal fade bs-example-modal-sm container" id="replyquick" tabindex="-1" role="dialog" aria-labelledby="replyquick">
        <div class="modal-dialog btn-bottom" role="document">
            <div class="modal-content modal-body"><button type="button btn-default" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&amp;times;</span></button>
                <{$quickreply.form|default:''}>
            </div>
        </div>
    </div>
    <{/if}>

    <{* fix bottom navbar // *}>
    <div class="navbar-fixed-bottom container" id="postnav" style="bottom:12px;">

        <{* rate button // *}>
        <{if $rating_enable}>
            <a class="btn btn-default btn-lg" style="box-shadow: 0 0 15px 0 #808080" data-toggle="modal" data-target="#replyrate"><i class="fa fa-thumbs-o-up" aria-hidden="true"></i><{$smarty.const.THEME_LIKE}></a>&nbsp;
        <{/if}>

        <{* reply button // *}>
        <{if $quickreply.show}>
            <a class="btn btn-default btn-lg" style="box-shadow: 0 0 15px 0 #808080" data-toggle="modal" data-target="#replyquick"><i class="fa fa-comment-o" aria-hidden="true"></i><{$smarty.const.THEME_FORUM_REPLY}></a>&nbsp;
        <{/if}>

        <{* modal-dialog move to bottom // *}>
        <style>.btn-bottom {position: absolute;bottom:48px;z-index:9999;} </style>

        <{* scroll hide bottom navbar // *}>
        <script>
            $(window).scroll(function(){
                const scrollBottom = $("body").height() - $(window).height() - 60;
                if (scrollBottom > 120 )
                {
                    if ($(this).scrollTop() > 60 && $(this).scrollTop() < scrollBottom)
                    { $('#postnav').fadeIn(); }
                    else {  $('#postnav').fadeOut(); }
                }
                else
                {
                    $('#postnav').fadeIn();
                }
            });
        </script>
    </div>
<{/if}>




<div class="clear"></div>
<br>
<{* irmtfan add to not show polls in admin mode *}>
<{if $mode lte 1}>
    <{if $topic_poll|default:''}>
        <{if $topic_pollresult|default:''}>
            <{include file="db:newbb_poll_results.tpl" poll=$poll|default:''}>
        <{else}>
            <{include file="db:newbb_poll_view.tpl" poll=$poll|default:''}>
        <{/if}>
    <{/if}>
<{/if}>
<div class="clear"></div>
<br>

<div style="padding: 5px;">
     <{*irmtfan hardcode removed style="float: left; text-align:left;"" *}>
    <span class="icon_left">
        <{* irmtfan correct prev and next icons *}>
<a id="threadtop"></a><{$down}><a href="#threadbottom"><{$smarty.const._MD_NEWBB_BOTTOM}></a>&nbsp;&nbsp;<{$previous}>&nbsp;<a
                href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewtopic.php?order=<{$order_current}>&amp;topic_id=<{$topic_id}>&amp;forum=<{$forum_id}>&amp;move=prev"><{$smarty.const._MD_NEWBB_PREVTOPIC}></a>&nbsp;&nbsp;<{$next}>&nbsp;<a
                href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewtopic.php?order=<{$order_current}>&amp;topic_id=<{$topic_id}>&amp;forum=<{$forum_id}>&amp;move=next"><{$smarty.const._MD_NEWBB_NEXTTOPIC}></a>
</span>
    <{* irmtfan hardcode removed style="float: right; text-align:right;"" *}>
    <span class="icon_right">
<{$forum_reply|default:''}>&nbsp;<{$forum_addpoll|default:''}>&nbsp;<{$forum_post_or_register}>
</span>
</div>
<div class="clear"></div>
<br>

<div>
    <div class="dropdown">
        <select name="topicoption" id="topicoption" onchange="if(this.options[this.selectedIndex].value.length >0 ) { window.document.location=this.options[this.selectedIndex].value;}">
            <option value=""><{$smarty.const._MD_NEWBB_TOPICOPTION}></option>
            <{if $viewer_level > 1}>
                <{foreach item=act from=$admin_actions}>
                    <option value="<{$act.link}>"><{$act.name}></option>
                <{/foreach}>
            <{/if}>
            <{if $adminpoll_actions|default:''|is_array && count($adminpoll_actions) > 0 }>
                <option value="">--------</option>
                <option value=""><{$smarty.const._MD_NEWBB_POLLOPTIONADMIN}></option>
                <{foreach item=actpoll from=$adminpoll_actions}>
                    <option value="<{$actpoll.link}>"><{$actpoll.name}></option>
                <{/foreach}>
            <{/if}>
        </select>
        <{* irmtfan user should not see rating if he dont have permission *}>
        <{if $rating_enable|default:'' && $forum_post|default:'' && $forum_reply|default:''}>
            <select
                    name="rate" id="rate"
                    onchange="if(this.options[this.selectedIndex].value.length >0 ) { window.document.location=this.options[this.selectedIndex].value;}">
                <option value=""><{$smarty.const._MD_NEWBB_RATE}></option>
                <option value="<{$xoops_url}>/modules/<{$xoops_dirname}>/ratethread.php?topic_id=<{$topic_id}>&amp;forum=<{$forum_id}>&amp;rate=5"><{$smarty.const._MD_NEWBB_RATE5}></option>
                <option value="<{$xoops_url}>/modules/<{$xoops_dirname}>/ratethread.php?topic_id=<{$topic_id}>&amp;forum=<{$forum_id}>&amp;rate=4"><{$smarty.const._MD_NEWBB_RATE4}></option>
                <option value="<{$xoops_url}>/modules/<{$xoops_dirname}>/ratethread.php?topic_id=<{$topic_id}>&amp;forum=<{$forum_id}>&amp;rate=3"><{$smarty.const._MD_NEWBB_RATE3}></option>
                <option value="<{$xoops_url}>/modules/<{$xoops_dirname}>/ratethread.php?topic_id=<{$topic_id}>&amp;forum=<{$forum_id}>&amp;rate=2"><{$smarty.const._MD_NEWBB_RATE2}></option>
                <option value="<{$xoops_url}>/modules/<{$xoops_dirname}>/ratethread.php?topic_id=<{$topic_id}>&amp;forum=<{$forum_id}>&amp;rate=1"><{$smarty.const._MD_NEWBB_RATE1}></option>
            </select>
        <{/if}>

        <select
                name="viewmode" id="viewmode"
                onchange="if(this.options[this.selectedIndex].value.length >0 ) { window.location=this.options[this.selectedIndex].value;}">
            <option value=""><{$smarty.const._MD_NEWBB_VIEWMODE}></option>
            <{foreach item=act from=$viewmode_options}>
                <option value="<{$act.link}>"><{$act.title}></option>
            <{/foreach}>
        </select>
        <{* START irmtfan add topic search *}>
        <{if $mode lte 1}>
            <form id="search-topic" action="<{$xoops_url}>/modules/<{$xoops_dirname}>/search.php" method="get">
                <fieldset>
                    <input name="term" id="term" type="text" size="15" value="<{$smarty.const._MD_NEWBB_SEARCHTOPIC}>..." onBlur="if(this.value==='') this.value='<{$smarty.const._MD_NEWBB_SEARCHTOPIC}>...'"
                           onFocus="if(this.value =='<{$smarty.const._MD_NEWBB_SEARCHTOPIC}>...' ) this.value=''">
                    <input type="hidden" name="forum" id="forum" value="<{$forum_id}>">
                    <input type="hidden" name="sortby" id="sortby" value="p.post_time desc">
                    <input type="hidden" name="topic" id="topic" value="<{$topic_id}>">
                    <input type="hidden" name="action" id="action" value="yes">
                    <input type="hidden" name="searchin" id="searchin" value="both">
                    <input type="hidden" name="show_search" id="show_search" value="post_text">
                    <input type="submit" class="formButton" value="<{$smarty.const._MD_NEWBB_SEARCH}>">
                </fieldset>
            </form>
        <{/if}>
        <{* END irmtfan add topic search *}>
    </div>
    <{* irmtfan hardcode removed style="float: right; text-align:right;" *}>
    <div class="icon_right">
        <{$forum_page_nav|replace:'form':'div'|replace:'id="xo-pagenav"':''}> <{* irmtfan to solve nested forms and id="xo-pagenav" issue *}>
    </div>
</div>
<div class="clear"></div>
<br>
<br>

<{if $viewer_level gt 1 && $topic_status == 1}>
    <div class="resultMsg"><{$smarty.const._MD_NEWBB_TOPICLOCK}></div>
    <br>
<{/if}>
<{* irmtfan remove here and move to the newbb_thread.tpl *}>
<{*<{if $post_id == 0}><div id="aktuell"></div><{/if}> *}>

<{foreach item=topic_post from=$topic_posts|default:null}>
    <{include file="db:newbb_thread.tpl" topic_post=$topic_post mode=$mode}>
    <br>
    <br>
    <{foreachelse}>
    <div style="text-align: center;width:100%;font-size:1.5em;padding:5px;"><{$smarty.const._MD_NEWBB_ERRORPOST}></div>
<{/foreach}>

<{if $mode gt 1}>
    </form>
<{/if}>

<br>
<div class="forum_header">
    <div class="forum_title">
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
        <span class="delimiter">&raquo;</span>
        <strong><{$topic_title}></strong> <{if $topicstatus}><{$topicstatus}><{/if}>
    </div>
</div>
<div class="clear"></div>
<br>

<div>
    <div class="left">
        <{* irmtfan correct prev and next icons add up*}>
        <a id="threadbottom"></a><{$p_up}><a href="#threadtop"><{$smarty.const._MD_NEWBB_TOP}></a>&nbsp;&nbsp;<{$previous}>&nbsp;<a
                href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewtopic.php?viewmode=flat&amp;order=<{$order_current}>&amp;topic_id=<{$topic_id}>&amp;forum=<{$forum_id}>&amp;move=prev"><{$smarty.const._MD_NEWBB_PREVTOPIC}></a>&nbsp;&nbsp;<{$next}>
        &nbsp;<a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewtopic.php?viewmode=flat&amp;order=<{$order_current}>&amp;topic_id=<{$topic_id}>&amp;forum=<{$forum_id}>&amp;move=next"><{$smarty.const._MD_NEWBB_NEXTTOPIC}></a>
    </div>
    <{* irmtfan hardcode removed style="float: right; text-align:right;"" *}>
    <div class="icon_right">
        <{$forum_page_nav|replace:'form':'div'|replace:'id="xo-pagenav"':''}> <{* irmtfan to solve nested forms and id="xo-pagenav" issue *}>
    </div>
</div>
<div class="clear"></div>
<br>

<div class="left" style="padding: 5px;">
    <{$forum_reply|default:''}>&nbsp;<{$forum_addpoll|default:''}>&nbsp;<{$forum_post_or_register}>
</div>
<div class="clear"></div>
<br>
<br>

<{if $quickreply.show}>
    <div>
        <{* irmtfan improve toggle method to ToggleBlockCategory (this.children[0] for IE7&8) change display to style and icon to displayImage for more comprehension *}>
        <a href="#threadbottom" onclick="ToggleBlockCategory('qr', (this.firstElementChild || this.children[0]), '<{$quickreply.icon.expand}>', '<{$quickreply.icon.collapse}>','<{$smarty.const._MD_NEWBB_HIDE|escape:'quotes'}> <{$smarty.const._MD_NEWBB_QUICKREPLY|escape:'quotes'}>','<{$smarty.const._MD_NEWBB_SEE|escape:'quotes'}> <{$smarty.const._MD_NEWBB_QUICKREPLY|escape:'quotes'}>')">
            <{$quickreply.displayImage}>
        </a>
    </div>
    <br>
    <{* irmtfan move semicolon *}>
    <div id="qr" style="display: <{$quickreply.style}>;">
        <div><{$quickreply.form}></div>
    </div>
    <br>
    <br>
<{/if}>

<div>
    <{* irmtfan hardcode removed style="float: left; text-align: left;" *}>
    <div class="icon_left">
        <{foreach item=perm from=$permission_table|default:''}>
            <div style="font-size:x-small;"><{$perm}></div>
        <{/foreach}>
    </div>
    <{* irmtfan hardcode removed style="float: right; text-align: right;" *}>
    <div class="icon_right">
        <form action="<{$xoops_url}>/modules/<{$xoops_dirname}>/search.php" method="get">
            <input name="term" id="term" type="text" size="15">
            <input type="hidden" name="forum" id="forum" value="<{$forum_id}>">
            <input type="hidden" name="sortby" id="sortby" value="p.post_time desc">
            <input type="hidden" name="since" id="since" value="<{$forum_since|default:''}>">
            <input type="hidden" name="action" id="action" value="yes">
            <input type="hidden" name="searchin" id="searchin" value="both">
            <input type="submit" class="formButton" value="<{$smarty.const._MD_NEWBB_SEARCH}>"><br>
            [<a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/search.php"><{$smarty.const._MD_NEWBB_ADVSEARCH}></a>]
        </form>
        <br>
        <{$forum_jumpbox}>
    </div>
</div>
<div class="clear"></div>
<br>

<{include file='db:newbb_notification_select.tpl'}>
<{* irmtfan remove
<script type="text/javascript">
xoopsGetElementById('aktuell').scrollIntoView(true);
</script>
*}>

<{* START irmtfan add scroll js function to scroll down to current post or top of the topic *}>
<script type="text/javascript">
    if (document.body.scrollIntoView && window.location.href.indexOf('#') == -1) {
        var el = xoopsGetElementById('<{$forum_post_prefix|default:''}><{$post_id}>');
        if (el) {
            el.scrollIntoView(true);
        }
    }
</script>
<{* END irmtfan add scroll js function to scroll down to current post or top of the topic *}>


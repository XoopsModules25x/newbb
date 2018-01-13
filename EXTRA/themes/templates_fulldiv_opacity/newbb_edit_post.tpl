<div class="forum_header">
    <div class="forum_title">
        <h2><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php"><{$lang_forum_index}></a></h2>
        <hr class="align_left"/>
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php"><{$smarty.const._MD_NEWBB_FORUMINDEX}></a>
        <span class="delimiter">&raquo;</span>
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php?cat=<{$category.id}>"><{$category.title}></a>
        <{if $parentforum}>
            <{foreach item=forum from=$parentforum}>
            <span class="delimiter">&raquo;</span>
            &nbsp;
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum.forum_id}>"><{$forum.forum_name}></a>
        <{/foreach}>
        <{/if}>
        <span class="delimiter">&raquo;</span>
        &nbsp;<a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/viewforum.php?forum=<{$forum_id}>"><{$forum_name}></a>
        <span class="delimiter">&raquo;</span>
        &nbsp;<strong><{$form_title}></strong>
    </div>
</div>
<div class="clear"></div>
<br>

<{if $disclaimer}>
    <div class="confirmMsg"><{$disclaimer}></div>
    <div class="clear"></div>
    <br>
<{/if}>

<{if $error_message}>
    <div class="errorMsg"><{$error_message}></div>
    <div class="clear"></div>
    <br>
<{/if}>

<{if $post_preview}>
    <div class='outer'>
        <div class="head"><{$post_preview.subject}></div>
        <div class=""><{$post_preview.meta}><br><br>
            <{$post_preview.content}>
        </div>
    </div>
    <div class="clear"></div>
    <br>
<{/if}>

<form name="<{$form_post.name}>" id="<{$form_post.name}>" action="<{$form_post.action}>" method="<{$form_post.method}>" <{$form_post.extra}> >
    <div class='outer'>
        <{foreach item=element from=$form_post.elements}>
        <{if $element.hidden != true}>
            <div class="edit_col1 head floatleft">
                <div class="xoops-form-element-caption<{if $element.required}>-required<{/if}>"><span class="caption-text"><{$element.caption}></span><span class="caption-marker">*</span></div>
                <{if $element.description != ''}>
                    <div class="xoops-form-element-help"><{$element.description}></div>
                <{/if}>
            </div>
            <div class="_col_end odd"><{$element.body|replace:'<tr':'<span'|replace:'<td':'<span'|replace:'</tr':'</span'|replace:'</td':'</span'}></div>
            <div class="clear"></div>
        <{/if}>
        <{/foreach}>
    </div>
    <{foreach item=element from=$form_post.elements}>
    <{if $element.hidden == true}>
        <{$element.body}>
        <div class="clear"></div>
    <{/if}>
    <{/foreach}>
</form>
<{$form_post.javascript}>
<div class="clear"></div>
<br>

<{if $posts_context}>
    <div class='outer'>
        <{foreach item=post from=$posts_context}>
        <div class="head"><{$post.subject}></div>
        <div class="<{cycle values="even,odd"}>"><{$post.meta}><br><br>
            <{$post.content}>
        </div>
        <{/foreach}>
    </div>
<{/if}>

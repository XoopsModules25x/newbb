<div class="forum_header">
    <div class="forum_title">
        <h2><a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/<{$moderate_url}>"><{$smarty.const._MD_NEWBB_SUSPEND_MANAGEMENT}></a></h2>
        <hr class="align_left" width="100%" size="1"/>
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php"><{$smarty.const._MD_NEWBB_FORUMINDEX}></a>
        <span class="delimiter">&raquo;</span>
        <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/<{$moderate_url}>"><{$smarty.const._MD_NEWBB_SUSPEND_MANAGEMENT}></a>
    </div>
</div>
<div class="clear"></div>
<br>

<{if $error_message}>
    <div class="errorMsg"><{$error_message}></div>
    <div class="clear"></div>
    <br>
<{/if}>

<h3><{$smarty.const._MD_NEWBB_SUSPEND_LIST}></h3>
<hr class="align_left" width="100%" size="1"/>
<table width="100%">
    <thead>
    <tr>
    <{foreach item=colHead from=$columnHeaders}>
        <th>
            <{if $colHead.url}>
            <a href="<{$colHead.url}>" title="<{$colHead.title}>"><{$colHead.header}></a>
            <{else}>
            <{$colHead.header}>
            <{/if}>
        </th>
    <{/foreach}>
    </tr>
    </thead>
    <tbody>
    <{foreach item=row from=$columnRows}>
    <tr class="<{cycle values='odd,even'}>">
        <td><{$row.uid}></td>
        <td><{$row.start}></td>
        <td><{$row.expire}></td>
        <td><{$row.forum}></td>
        <td><{$row.desc}></td>
        <td><{$row.options}></td>
    </tr>
    <{/foreach}>
    </tbody>
</table>
<{if $moderate_page_nav|default:false}>
<br>
<div class="icon_right">
    <{$moderate_page_nav|default:''|replace:'form':'div'|replace:'id="xo-pagenav"':''}>
</div>
<{/if}>


<br>
<h3><{$suspend_form.title}></h3>
<hr class="align_left" width="100%" size="1"/>
<form name="<{$suspend_form.name}>" id="<{$suspend_form.name}>" action="<{$suspend_form.action}>" method="<{$suspend_form.method}>" <{$suspend_form.extra}> >
    <table width='100%' class='outer' cellspacing='1'>
        <{foreach item=element from=$suspend_form.elements}>
        <{if $element.hidden != true}>
            <tr valign="top">
                <td class="head">
                    <div class="xoops-form-element-caption<{if $element.required}>-required<{/if}>"><span class="caption-text"><{$element.caption}></span><span class="caption-marker">*</span></div>
                    <{if $element.description != ''}>
                        <div class="xoops-form-element-help"><{$element.description}></div>
                    <{/if}>
                </td>
                <td class="odd" style="white-space: nowrap;"><{$element.body}></td>
            </tr>
        <{/if}>
        <{/foreach}>
    </table>
    <{foreach item=element from=$suspend_form.elements}>
    <{if $element.hidden == true}>
        <{$element.body}>
    <{/if}>
    <{/foreach}>
</form>
<{$suspend_form.javascript}>
<div class="clear"></div>
<br>

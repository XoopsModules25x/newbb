<{if $xoops_notification.show}>
    <form name="notification_select" action="<{$xoops_url}>/modules/<{$xoops_dirname}>/<{$xoops_notification.target_page}>" method="post">
        <h4 class="align_center"><{$lang_activenotifications}></h4>
        <input type="hidden" name="not_redirect" value="<{$xoops_notification.redirect_script}>" />
        <{securityToken}>
        <div class="outer"><{$lang_notificationoptions}></div>
        <div class="outer forum_table">
            <div class="forum_row">
                <div class="head"><{$lang_category}></div>
                <div class="head"><input name="allbox" id="allbox" onclick="xoopsCheckAll('notification_select','allbox');" type="checkbox" value="<{$lang_checkall}>" /></div>
                <div class="head"><{$lang_events}></div>
            </div>
            <{foreach name=outer item=category from=$xoops_notification.categories}>
                <{foreach name=inner item=event from=$category.events}>
                    <div class="forum_row">
                        <div class="even">
                            <{if $smarty.foreach.inner.first}>
                                <{$category.title}>
                            <{/if}>
                        </div>
                        <div class="odd">
                            <{counter assign=index}>
                                <input type="hidden" name="not_list[<{$index}>][params]" value="<{$category.name}>,<{$category.itemid}>,<{$event.name}>" />
                                <input type="checkbox" id="not_list[]" name="not_list[<{$index}>][status]" value="1" <{if $event.subscribed}>checked="checked"<{/if}> />
                        </div>
                        <div class="odd"><{$event.caption}></div>
                    </div>
                <{/foreach}>
            <{/foreach}>
        </div>
        <div class="align_center foot"><input type="submit" name="not_submit" value="<{$lang_updatenow}>" /></div>
        <div class="align_center">
        <{$lang_notificationmethodis}>:&nbsp;<{$user_method}>&nbsp;&nbsp;[<a href="<{$editprofile_url}>" title="<{$lang_change}>"><{$lang_change}></a>]
        </div>
    </form>
<{/if}>

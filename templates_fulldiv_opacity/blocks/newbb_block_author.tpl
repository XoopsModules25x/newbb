<div class="outer forum_block_<{$block.disp_mode+1}>">
    <{if $block.disp_mode == 0}>
        <div class="head align_center">
            <div class="block_compact_topic floatleft"><{$smarty.const._MB_NEWBB_AUTHOR}></div>
            <div class="_col_end"><{$smarty.const._MB_NEWBB_COUNT}></div>
            <div class="clear"></div>
        </div>
        <{foreach item=author key=uid from=$block.authors}>
        <div class="align_center <{cycle values="even,odd"}>">
            <div class="block_compact_topic floatleft"><a href="<{$xoops_url}>/userinfo.php?uid=<{$uid}>"><{$author.name}></a></div>
            <div class="_col_end"><{$author.count}></div>
            <div class="clear"></div>
        </div>
    <{/foreach}>
    <{elseif $block.disp_mode == 1}>
        <{foreach item=author key=uid from=$block.authors}>
        <div class="align_center <{cycle values="even,odd"}>">
            <div><a href="<{$xoops_url}>/userinfo.php?uid=<{$uid}>"><{$author.name}></a> <{$author.count}></div>
            <div class="clear"></div>
        </div>
    <{/foreach}>
    <{/if}>
</div>
<div class="clear"></div>
<{if $block.indexNav}>
    <div class="pagenav">
        <a href="<{$xoops_url}>/modules/newbb/"><{$smarty.const._MB_NEWBB_VSTFRMS}></a>
    </div>
<{/if}>

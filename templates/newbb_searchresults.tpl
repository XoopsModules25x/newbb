<div class="resultMsg"> <{$search_info}> </div>
<br>
<{if $results}>
    <table class="outer" border="0" cellpadding="0" cellspacing="0" align="center" width="95%">
        <tr>
            <td>
                <table border="0" cellpadding="4" cellspacing="1" width="100%">
                    <tr class="head" align="center">
                        <td><{$smarty.const._MD_NEWBB_FORUMC}></td>
                        <td><{$smarty.const._MD_NEWBB_SUBJECT}></td>
                        <td><{$smarty.const._MD_NEWBB_AUTHOR}></td>
                        <td nowrap="nowrap"><{$smarty.const._MD_NEWBB_POSTTIME}></td>
                    </tr>
                    <!-- start search results -->
                    <{section name=i loop=$results}>
                        <!-- start each result -->
                        <tr align="center">
                            <td class="even"><a href="<{$results[i].forum_link}>"><{$results[i].forum_name}></a></td>
                            <!-- irmtfan hardcode removed align="left" -->
                            <td class="odd" id="align_left"><a href="<{$results[i].link}>"><{$results[i].title}></a></td>
                            <td class="even"><{$results[i].poster}></a></td>
                            <td class="odd"><{$results[i].post_time}></td>
                        </tr>
                        <!-- START irmtfan add show search -->
                        <{if $results[i].post_text }>
                            <tr align="center">
                                <td class="even"></td>
                                <td class="odd">
                                    <{$results[i].post_text}>
                                </td>
                                <td class="even"></td>
                                <td class="odd"></td>
                            </tr>
                        <{/if}>
                        <!-- END irmtfan add show search -->
                        <!-- end each result -->
                    <{/section}>
                    <!-- end search results -->
                </table>
            </td>
        </tr>
        <{if $search_next or $search_prev}>
            <tr>
                <td>
                    <table border="0" cellpadding="4" cellspacing="1" width="100%">
                        <tr class="head">
                            <!-- irmtfan hardcode removed align="left" -->
                            <td class="align_left" width="50%"><{$search_prev}> </td>
                            <td class="align_right" width="50%"> <{$search_next}></td>
                        </tr>
                    </table>
                </td>
            </tr>
        <{/if}>
    </table>
    <br>
<{elseif $lang_nomatch}>
    <div class="resultMsg"> <{$lang_nomatch}> </div>
    <br>
<{/if}>

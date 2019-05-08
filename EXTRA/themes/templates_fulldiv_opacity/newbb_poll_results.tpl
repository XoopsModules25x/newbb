<{if $topic_resultform}>
    <{$topic_resultform}>
<{else}>
<div class="poll outer">
    <div>
        <{$poll.question}>
    </div>
    <div class="head right">
        <{$poll.end_text}>
    </div>
    <div class="clear"></div>
    <div class="forum_table">
        <{foreach item=option from=$poll.options}>
            <div class="forum_row">
                <div class="poll_text even"><{$option.text}></div>
                <div class="poll_col2 odd" ><{$option.image}> <{$option.percent}></div>
            </div>
        <{/foreach}>
    </div>
    <div class="foot">
        <{$poll.totalVotes}><br><{$poll.totalVoters}>
    </div>
</div>
<br>
<{/if}>

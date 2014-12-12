<script>

    {if $post}
        $(function () {
            VK.Api.call('wall.post', {
                message: "{$text|escape:'html'}",
                attachments: "{$link}"

            }, function (r) {
                ls.msg.notice(null, "{$post_good}");
            });
        });
    {/if}

</script>
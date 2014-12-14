<script>
    $(function(){
        {if $wall || $post}
        VK.init({
            apiId: {$vk_client_id}
        });
        {/if}
        {if $wall}
        $(function () {
            ls.hook.add('ls_wall_add_after', function (sText) {
                VK.Api.call('wall.post', {
                    message: sText,
                    attachments: "{$link}"

                }, function (r) {
                    ls.msg.notice(null, "{$wall_good}");
                });
            });
        });
        {/if}
    });


</script>
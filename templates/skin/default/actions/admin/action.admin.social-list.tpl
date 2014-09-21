{extends file="_index.tpl"}

{block name="layout_vars"}
    {$sMainMenuItem='content'}
{/block}

{block name="content-bar"}
    {*{include file="{$aTemplatePathPlugin.banneroid}admin.banneroid.menu.content.bar.tpl"}*}
{/block}

{block name="content-body"}
    <style>
        .social-form .control-label {
            padding-top: 8px;
        }

    </style>

    <div class="span12 social-form">
        <div class="b-wbox">
            <div class="b-wbox-header">
                <h3 class="b-wbox-header-title">
                    {$aLang.plugin.ar.admin_social_page_title}
                </h3>
            </div>
            <div class="b-wbox-content">
                <div class="b-wbox-content">
                    <form method="post" action="" enctype="multipart/form-data" id="social-setting" class="form-horizontal uniform">
                        <input type="hidden" name="security_ls_key" value="{$LIVESTREET_SECURITY_KEY}" />


                        {* ОДНОКЛАССНИКИ *}
                        <div class="control-group">
                            <label for="banner_name" class="control-label">
                                {$aLang.plugin.ar.od}:
                            </label>
                            <div class="controls">
                                <div class="col-md-4">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.client_id}" type="text" id="od_client_id" name="od_client_id" value="{$_aRequest.od_client_id}"  />
                                </div>
                                <div class="col-md-4">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.secret_key}" type="text" id="od_secret_key" name="od_secret_key" value="{$_aRequest.od_secret_key}"  />
                                </div>
                                <div class="col-md-4">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.public_key}" type="text" id="od_public_key" name="od_public_key" value="{$_aRequest.od_public_key}"  />
                                </div>
                            </div>
                        </div>

                        {* ФЕЙСБУК *}
                        <div class="control-group">
                            <label for="banner_name" class="control-label">
                                {$aLang.plugin.ar.fb}:
                            </label>
                            <div class="controls">
                                <div class="col-md-4">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.client_id}" type="text" id="fb_client_id" name="fb_client_id" value="{$_aRequest.fb_client_id}"  />
                                </div>
                                <div class="col-md-4">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.secret_key}" type="text" id="fb_secret_key" name="fb_secret_key" value="{$_aRequest.fb_secret_key}"  />
                                </div>
                                <div class="col-md-4">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.group_id}" type="text" id="fb_group_id" name="fb_group_id" value="{$_aRequest.fb_group_id}"  />
                                </div>
                            </div>
                        </div>

                        {* ГИТХАБ *}
                        <div class="control-group">
                            <label for="banner_name" class="control-label">
                                {$aLang.plugin.ar.github}:
                            </label>
                            <div class="controls">
                                <div class="col-md-4">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.client_id}" type="text" id="github_client_id" name="github_client_id" value="{$_aRequest.github_client_id}"  />
                                </div>
                                <div class="col-md-4">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.secret_key}" type="text" id="github_secret_key" name="github_secret_key" value="{$_aRequest.github_secret_key}"  />
                                </div>
                                <div class="col-md-4">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.application_name}" type="text" id="application_name" name="application_name" value="{$_aRequest.application_name}"  />
                                </div>
                            </div>
                        </div>

                        {* Вконтакт *}
                        <div class="control-group">
                            <label for="banner_name" class="control-label">
                                {$aLang.plugin.ar.vk}:
                            </label>
                            <div class="controls">
                                <div class="col-md-6">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.client_id}" type="text" id="vk_client_id" name="vk_client_id" value="{$_aRequest.vk_client_id}"  />
                                </div>
                                <div class="col-md-6">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.secret_key}" type="text" id="vk_secret_key" name="vk_secret_key" value="{$_aRequest.vk_secret_key}"  />
                                </div>
                            </div>
                        </div>

                        {* ТВИТТЕР *}
                        <div class="control-group">
                            <label for="banner_name" class="control-label">
                                {$aLang.plugin.ar.tw}:
                            </label>
                            <div class="controls">
                                <div class="col-md-6">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.client_id}" type="text" id="tw_client_id" name="tw_client_id" value="{$_aRequest.tw_client_id}"  />
                                </div>
                                <div class="col-md-6">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.secret_key}" type="text" id="tw_secret_key" name="tw_secret_key" value="{$_aRequest.tw_secret_key}"  />
                                </div>
                            </div>
                        </div>

                        {* МОЙ МИР *}
                        <div class="control-group">
                            <label for="banner_name" class="control-label">
                                {$aLang.plugin.ar.mm}:
                            </label>
                            <div class="controls">
                                <div class="col-md-6">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.client_id}" type="text" id="mm_client_id" name="mm_client_id" value="{$_aRequest.mm_client_id}"  />
                                </div>
                                <div class="col-md-6">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.secret_key}" type="text" id="mm_secret_key" name="mm_secret_key" value="{$_aRequest.mm_secret_key}"  />
                                </div>
                            </div>
                        </div>

                        {* ЯНДЕКС *}
                        <div class="control-group">
                            <label for="banner_name" class="control-label">
                                {$aLang.plugin.ar.ya}:
                            </label>
                            <div class="controls">
                                <div class="col-md-6">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.client_id}" type="text" id="ya_client_id" name="ya_client_id" value="{$_aRequest.ya_client_id}"  />
                                </div>
                                <div class="col-md-6">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.secret_key}" type="text" id="ya_secret_key" name="ya_secret_key" value="{$_aRequest.ya_secret_key}"  />
                                </div>
                            </div>
                        </div>

                        {* ГУГЛ *}
                        <div class="control-group">
                            <label for="banner_name" class="control-label">
                                {$aLang.plugin.ar.g}:
                            </label>
                            <div class="controls">
                                <div class="col-md-6">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.client_id}" type="text" id="g_client_id" name="g_client_id" value="{$_aRequest.g_client_id}"  />
                                </div>
                                <div class="col-md-6">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.secret_key}" type="text" id="g_secret_key" name="g_secret_key" value="{$_aRequest.g_secret_key}"  />
                                </div>
                            </div>
                        </div>

                        {* ЛИКЕНИД *}
                        <div class="control-group">
                            <label for="banner_name" class="control-label">
                                {$aLang.plugin.ar.li}:
                            </label>
                            <div class="controls">
                                <div class="col-md-6">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.client_id}" type="text" id="li_client_id" name="li_client_id" value="{$_aRequest.li_client_id}"  />
                                </div>
                                <div class="col-md-6">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.secret_key}" type="text" id="li_secret_key" name="li_secret_key" value="{$_aRequest.li_secret_key}"  />
                                </div>
                            </div>
                        </div>

                        {* ИНСТАГРАМ *}
                        <div class="control-group">
                            <label for="banner_name" class="control-label">
                                {$aLang.plugin.ar.i}:
                            </label>
                            <div class="controls">
                                <div class="col-md-6">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.client_id}" type="text" id="i_client_id" name="i_client_id" value="{$_aRequest.i_client_id}"  />
                                </div>
                                <div class="col-md-6">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.secret_key}" type="text" id="i_secret_key" name="i_secret_key" value="{$_aRequest.i_secret_key}"  />
                                </div>
                            </div>
                        </div>



                        {* ТЕКСТОВКА ДЛЯ ЗАПОЛНЕНИЯ РЕПОСТА ТОПИКА *}
                        <div class="control-group">
                            <label for="banner_name" class="control-label">
                                {$aLang.plugin.ar.default_text_type_text}:
                            </label>
                            <div class="controls">
                                <div class="col-md-12">
                                    <input class="input-wide" placeholder="{$aLang.plugin.ar.default_text_type_text}" type="text" id="default_text_type_text"
                                           name="default_text_type_text" value="{$_aRequest.default_text_type_text}"  />
                                </div>
                            </div>
                        </div>


                        <br/><br/>

                        <input type="submit" name="submit_social" value="{$aLang.plugin.ar.save}" />
                        <input type="submit" name="cancel" value="{$aLang.plugin.ar.cancel}"/>

                    </form>
                </div>
            </div>
        </div>
    </div>
{/block}
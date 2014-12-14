{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike   *}

{extends file="themes/$sSkinTheme/layouts/default_light.tpl"}

{block name="layout_vars"}
    {$noSidebar=true}
{/block}

{block name="layout_content"}

    <script type="text/javascript">
        jQuery(function ($) {

            var authForm = $('#auth-form');
            authForm.find('input.js-ajax-validate').blur(function (e) {
                ls.user.validateRegistrationField($(e.target).parents('form').first(), name,  $(e.target).val(), { });
            });
            $('.js-form-auth-submit').prop('disabled', false);

            authForm.find('[data-toggle="tooltip"]').tooltip({
                placement: 'left',
                container: '.js-form-auth',
                trigger: 'click'
            });

        });

    </script>

    <div class="text-center page-header">
        <h3>{$aLang.plugin.ar.next_registration}</h3>
        <br/>
        {if Config::Get('plugin.ar.auto_login') == FALSE}
            <h4>{$aLang.plugin.ar.next_registration_2}</h4>
        {else}
            <h4>{$aLang.plugin.ar.next_registration_2_1}</h4>
        {/if}
    </div>

    {hook run='auth_begin'}

    <div class="row">
        <div class="col-xs-20 col-xs-offset-2 col-sm-12 col-sm-offset-6  col-md-10 col-md-offset-7  col-lg-10 col-lg-offset-7">
            <form id="auth-form" action="{router page='auth'}confirm" method="post" class="js-form-auth">
                {hook run='form_auth_begin'}

                {if Config::Get('plugin.ar.auto_login') == FALSE}
                    <div class="form-group has-feedback">
                        <div class="input-group">
                            <label for="input-auth-login" class="input-group-addon"><i class="fa fa-user"></i></label>
                            <input placeholder="{$aLang.plugin.ar.login}" type="text" name="login" id="input-auth-login" value="{$_aRequest.login}" class="form-control js-ajax-validate js-focus-in" required/>
                            <span class="fa fa-check validate-ok-field-login form-control-feedback form-control-feedback-ok" style="display: none"></span>
                            <span  data-toggle="tooltip" data-placement="left" class="fa fa-question-circle js-tip-help form-control-feedback form-control-feedback-help" title="{$aLang.plugin.ar.login_notice}"></span>
                        </div>
                        <small class="text-danger validate-error-hide validate-error-field-login"></small>
                    </div>
                {/if}

                <div class="form-group has-feedback">
                    <div class="input-group">
                        <label for="input-auth-mail"  class="input-group-addon"><i class="fa fa-envelope"></i></label>
                        <input placeholder="{$aLang.plugin.ar.email}" type="text" name="mail" id="input-auth-mail" value="{$_aRequest.email}" class="form-control js-ajax-validate" required/>
                        <span class="fa fa-check validate-ok-field-mail form-control-feedback form-control-feedback-ok" style="display: none"></span>
                        <span data-toggle="tooltip"  class="fa fa-question-circle js-tip-help form-control-feedback form-control-feedback-help" title="{$aLang.plugin.ar.email_notice}"></span>
                    </div>
                    <small class="text-danger validate-error-hide validate-error-field-mail"></small>
                </div>


                {hook run='form_auth_end'}

                <br/>
                <br/>

                <input type="submit"
                       id="create-new-user"
                       name="create-new-user"
                       class="btn btn-blue btn-big corner-no js-form-auth-submit"
                       value="{$aLang.plugin.ar.go}">
                <a class="btn btn-light btn-big corner-no" href="{$_aRequest.return_path}">{$aLang.plugin.ar.back}</a>

            </form>

            <br/>
            <br/>

            <div class="bg bg-warning">
                <h4>{$aLang.plugin.ar.allready}</h4>
                {$aLang.plugin.ar.allready_text}
            </div>
        </div>
    </div>

    {hook run='auth_end'}

{/block}

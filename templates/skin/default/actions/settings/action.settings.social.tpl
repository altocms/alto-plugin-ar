{* Тема оформления Experience v.1.0  для Alto CMS      *}
{* @licence     CC Attribution-ShareAlike   *}

{extends file="_index.tpl"}

{block name="layout_vars"}
    {$menu="topics"}
    {*{assign var="noSidebar" value=true}*}
{/block}


{block name="layout_content"}
    <script>
        $(function(){
            $('.panel-social').find('[data-toggle="tooltip"]').tooltip({
                placement: 'right'
            }).end()

            .find('[type="checkbox"]').on('ifChanged', function (e) {
                $(this).trigger('change');
            });
        })
    </script>

    <div class="panel panel-default panel-table raised panel-social">

        <div class="panel-body">

            {hook run='settings_social_begin'}


            <h2 class="panel-header">{$aLang.plugin.ar.social_list}</h2>

            <div class="row user-info-block">
                <div class="col-lg-24">
                    {if $aToken}
                    <table class="table table-striped social-list">
                        <thead>
                            <tr>
                                <th colspan="2"></th>
                                <th><strong>{$aLang.plugin.ar.repost_post}</strong></th>
                                <th><strong>{$aLang.plugin.ar.repost_wall}</strong></th>
                                <th><strong>{$aLang.plugin.ar.repost_status}</strong></th>
                                <th><strong>{$aLang.plugin.ar.find_friends}</strong></th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach $aToken as $oToken}
                                <tr class="sid-{$oToken->getTokenProviderName()}">
                                    <td class="social-logo">
                                        <img src="{asset file="assets/img/{$oToken->getTokenProviderName()}.png" plugin="ar" theme=true}" alt="{$oToken->getTokenProviderName()}"/>
                                    </td>
                                    <td>
                                        {$aLang.plugin.ar["{$oToken->getTokenProviderName()}"]}
                                        &nbsp;&nbsp;
                                        <a href="#"
                                           data-toggle="tooltip"
                                           class="fa fa-times red"
                                           title='{$aLang.plugin.ar.remove_social|ls_lang:"social_name%%{$aLang.plugin.ar[{$oToken->getTokenProviderName()}]}"}'
                                        onclick="
                                            if (confirm('{$aLang.plugin.ar.confirm_remove|ls_lang:"social_name%%{$aLang.plugin.ar[{$oToken->getTokenProviderName()}]}"}')) {
                                                return ls.ar.removeSocial($(this), {$oToken->getTokenId()});
                                            }
                                            return false;
                                        "></a>
                                        {if isset($aProviders[$oToken->getTokenProviderName()]) && $oToken->getTokenExpire()>0 && $oToken->getTokenExpire()<time()}
                                            <a class="fa fa-repeat green"
                                               onclick="$(this).addClass('fa-times')"
                                               href="{$aProviders[$oToken->getTokenProviderName()]->sAuthUrl}"></a>
                                        {/if}
                                    </td>
                                    {$iTokenId = $oToken->getTokenId()}
                                    <td class="tac">
                                        {if isset($aProviders[$oToken->getTokenProviderName()]) &&  $aProviders[$oToken->getTokenProviderName()]->aRepostRights['post'] == true}
                                        <input type="checkbox"
                                               {if isset($aRepost[$iTokenId]['post']) && $aRepost[$iTokenId]['post']->getSettingValue() == 1}checked{/if}
                                               onchange="ls.ar.toggleRepost($(this));"
                                               data-type="post"
                                               data-id="{$iTokenId}"
                                               id="post-{$oToken->getTokenProviderName()}"/>
                                        {else}
                                            <span class="repost-no">-</span>
                                        {/if}
                                    </td>
                                    <td class="tac">
                                        {if isset($aProviders[$oToken->getTokenProviderName()]) &&  $aProviders[$oToken->getTokenProviderName()]->aRepostRights['wall'] == true}
                                        <input type="checkbox"
                                               {if isset($aRepost[$iTokenId]['wall']) && $aRepost[$iTokenId]['wall']->getSettingValue() == 1}checked{/if}
                                               onchange="ls.ar.toggleRepost($(this));"
                                               data-type="wall"
                                               data-id="{$iTokenId}"
                                               id="post-{$oToken->getTokenProviderName()}"/>
                                        {else}
                                            <span class="repost-no">-</span>
                                        {/if}
                                    </td>
                                    <td class="tac">
                                        {if isset($aProviders[$oToken->getTokenProviderName()]) &&  $aProviders[$oToken->getTokenProviderName()]->aRepostRights['status'] == true}
                                        <input type="checkbox"
                                               {if isset($aRepost[$iTokenId]['status']) && $aRepost[$iTokenId]['status']->getSettingValue() == 1}checked{/if}
                                               onchange="ls.ar.toggleRepost($(this));"
                                               data-type="status"
                                               data-id="{$iTokenId}"
                                               id="post-{$oToken->getTokenProviderName()}"/>
                                        {else}
                                            <span class="repost-no">-</span>
                                        {/if}
                                    </td>
                                    <td class="tac">
                                        {if isset($aProviders[$oToken->getTokenProviderName()]) &&  $aProviders[$oToken->getTokenProviderName()]->aRepostRights['friends'] == true}
                                            <a class="btn btn-default btn-sm"
                                               onclick="ls.ar.findFriends($(this), '{$oToken->getTokenId()}'); return false;"
                                               href="#"><i class="fa fa-plus green"></i>&nbsp;{intval({$oToken->getSearchedCount()})}</a>
                                        {else}
                                            <span class="repost-no">-</span>
                                        {/if}
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                    {/if}
                </div>
            </div>

            <h4>{$aLang.plugin.ar.social_text}</h4>

            <div class="row">
                <div class="col-md-22 col-md-offset-2">
                    <form method="post" enctype="multipart/form-data" class="form-profile">
                        <div class="form-group">
                            <div class="input-group">
                                <label class="input-group-addon" for="user_social_text">{$aLang.plugin.ar.topic_social_text}</label>
                                <input class="form-control" name="user_social_text" id="user_social_text"
                                       onblur="ls.ar.setSocialText('topic', $(this).val(), $(this));"
                                       value="{if $sSocialText}{$sSocialText}{else}{Config::Get('plugin.ar.default_text_type_text')}{/if}"/>
                            </div>
                            <small class="control-notice">{$aLang.plugin.ar.topic_social_text_notice}</small>
                        </div>
                    </form>
                </div>
            </div>

            <h4>{$aLang.plugin.ar.social_add_more}</h4>

            {hook run='settings_tuning_end'}

        </div>

        <div class="panel-footer">
            {include file='menus/menu.settings.tpl'}
        </div>

    </div>
{/block}

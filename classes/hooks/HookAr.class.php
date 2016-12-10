<?php
/* ---------------------------------------------------------------------------
 * @Plugin Name: Social Network Integration
 * @Plugin Id: ar
 * @Plugin URI:
 * @Description:
 * @Author: andreyv
 * @Author URI: http://gladcode.ru
 * ----------------------------------------------------------------------------
 */

/**
 * HookAr.class.php
 * Файл хука плагина Ar
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Ar
 */
class PluginAr_HookAr extends Hook {
    /**
     * Регистрация хуков
     */
    public function RegisterHook() {

        if (E::IsAdmin()) {
            // Репост в группу
            $this->AddHook('template_topic_show_info', 'TemplateAddRepostInGroupLink');
        }

        if (E::Module('User')->IsAuthorization()) {
            $this->AddHook('template_menu_settings_settings_item', 'TemplateAddProfileLink');
            $this->AddHook('template_settings_tuning_end', 'TemplateAddProfileSocialList');
            $this->AddHook('template_menu_people_people_item', 'TemplateAddProfileInvitedUser');
        } else {

            // Модалка
            $this->AddHook('template_pane_login_begin', 'TemplateAddProfileSocialList');
            $this->AddHook('template_pane_registration_begin', 'TemplateAddProfileSocialList');
            $this->AddHook('template_pane_reminder_begin', 'TemplateAddProfileSocialList');

            // Страницы
            $this->AddHook('template_login_begin', 'TemplateAddProfileSocialListPage');
            $this->AddHook('template_registration_begin', 'TemplateAddProfileSocialListPage');
            $this->AddHook('template_reminder_begin', 'TemplateAddProfileSocialListPage');
        }

        $this->AddHook('module_user_authorization_after', 'AfterAuth');
    }


    public function TemplateAddProfileInvitedUser() {

        if (Router::GetAction() == 'profile') {
            E::Module('Viewer')->Assign('login', Router::GetActionEvent());

            return E::Module('Viewer')->Fetch(Plugin::GetTemplatePath(__CLASS__) . 'tpls/social.invited.inject.tpl');
        }
    }


    public function TemplateAddRepostInGroupLink($aData) {

        if (!(E::IsAdmin() && !Config::Get('plugin.ar.registration_only') && Config::Get('plugin.ar.providers.fb.fb_group_id'))) {
            return;
        }

        /** @var ModuleTopic_EntityTopic $oTopic */
        if (isset($aData['topic']) && $oTopic = $aData['topic']) {
            E::Module('Viewer')->Assign('sTopicId', $oTopic->getId());
            return E::Module('Viewer')->Fetch(Plugin::GetTemplatePath(__CLASS__) . 'tpls/social.repost.in.group.inject.tpl');
        }
    }

    /**
     * Затираем сессию после успешной авторизации
     */
    public  function AfterAuth() {

        E::Module('Session')->Drop('sUserData');
        E::Module('Session')->Drop('sTokenData');
    }

    /**
     * Возвращает HTML со списком провайдеров
     *
     * @return string
     */
    private function GetSocialIcons() {

        $sMenu = '';
        foreach (Config::Get('plugin.ar.providers') as $sProviderName => $aProviderData) {
            /** @var AuthProvider $oProvider */
            $oProvider = E::Module('PluginAr\AuthProvider')->GetProviderByName($sProviderName);
            if ($oProvider) {
                E::Module('Viewer')->Assign('sAuthUrl', $oProvider->sAuthUrl);
                E::Module('Viewer')->Assign('sProviderName', $sProviderName);
                $sMenu .= E::Module('Viewer')->Fetch(Plugin::GetTemplatePath(__CLASS__) . 'tpls/social.buttons.inject.tpl');
            }
        }


        return $sMenu;
    }


    public function TemplateAddProfileLink() {

        if (Router::GetActionEvent() == 'social') {
            E::Module('Viewer')->Assign('sMenuSubItemSelect', 'social');
        }

        return E::Module('Viewer')->Fetch(Plugin::GetTemplatePath(__CLASS__) . 'tpls/social.profile.inject.tpl');
    }

    /**
     * Добавляет иконки соцюсетей на страницу профиля
     *
     * @return string
     */
    public function TemplateAddProfileSocialList() {

        E::Module('Session')->Set('return_path', Router::RealUrl());

        return '<ul class="settings-social">' . $this->GetSocialIcons() . '</ul>';
    }

    /**
     * Добавляет иконки соцюсетей на страницу профиля
     *
     * @return string
     */
    public function TemplateAddProfileSocialListPage() { //social.page.inject.tpl

        E::Module('Viewer')->Assign('sButtons', $this->TemplateAddProfileSocialList());
        return E::Module('Viewer')->Fetch(Plugin::GetTemplatePath(__CLASS__) . 'tpls/social.page.inject.tpl');
    }

}

// EOF
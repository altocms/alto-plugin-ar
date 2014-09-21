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
 * @version     0.0.1 от 30.07.2014 23:43
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

        if ($this->User_IsAuthorization()) {
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
            /** @var ModuleViewer $oLocalViewer */
            $oLocalViewer = $this->Viewer_GetLocalViewer();
            $oLocalViewer->Assign('login', Router::GetActionEvent());

            return $oLocalViewer->Fetch(Plugin::GetTemplatePath(__CLASS__) . 'social.invited.inject.tpl');
        }


    }


    public function TemplateAddRepostInGroupLink($aData) {
        if (!E::IsAdmin()) {
            return;
        }

        /** @var ModuleTopic_EntityTopic $oTopic */
        if (isset($aData['topic']) && $oTopic = $aData['topic']) {
            /** @var ModuleViewer $oLocalViewer */
            $oLocalViewer = $this->Viewer_GetLocalViewer();
            $oLocalViewer->Assign('sTopicId', $oTopic->getId());
            return $oLocalViewer->Fetch(Plugin::GetTemplatePath(__CLASS__) . 'social.repost.in.group.inject.tpl');
        }


    }

    /**
     * Затираем сессию после успешной авторизации
     */
    public  function AfterAuth() {
        $this->Session_Drop('sUserData');
        $this->Session_Drop('sTokenData');
    }

    /**
     * Возвращает HTML со списком провайдеров
     * @return string
     */
    private function GetSocialIcons() {
        /** @var ModuleViewer $oLocalViewer */
        $oLocalViewer = $this->Viewer_GetLocalViewer();

        $sMenu = '';
        foreach (Config::Get('plugin.ar.providers') as $sProviderName => $aProviderData) {
            /** @var AuthProvider $oProvider */
            $oProvider = $this->PluginAr_AuthProvider_GetProviderByName($sProviderName);
            if ($oProvider) {
                $oLocalViewer->Assign('sAuthUrl', $oProvider->sAuthUrl);
                $oLocalViewer->Assign('sProviderName', $sProviderName);
                $sMenu .= $oLocalViewer->Fetch(Plugin::GetTemplatePath(__CLASS__) . 'social.buttons.inject.tpl');
            }
        }


        return $sMenu;
    }


    public function TemplateAddProfileLink() {
        /** @var ModuleViewer $oLocalViewer */
        $oLocalViewer = $this->Viewer_GetLocalViewer();

        if (Router::GetActionEvent() == 'social') {
            $oLocalViewer->Assign('sMenuSubItemSelect', 'social');
        }

        return $oLocalViewer->Fetch(Plugin::GetTemplatePath(__CLASS__) . 'social.profile.inject.tpl');
    }

    /**
     * Добавляет иконки соцюсетей на страницу профиля
     *
     * @return string
     */
    public function TemplateAddProfileSocialList() {

        $this->Session_Set('return_path', Config::Get('path.root.web') . $_SERVER['REQUEST_URI']);

        return '<ul class="settings-social">' . $this->GetSocialIcons() . '</ul>';
    }
    /**
     * Добавляет иконки соцюсетей на страницу профиля
     *
     * @return string
     */
    public function TemplateAddProfileSocialListPage() { //social.page.inject.tpl

        /** @var ModuleViewer $oLocalViewer */
        $oLocalViewer = $this->Viewer_GetLocalViewer();
        $oLocalViewer->Assign('sButtons', $this->TemplateAddProfileSocialList());
        return $oLocalViewer->Fetch(Plugin::GetTemplatePath(__CLASS__) . 'social.page.inject.tpl');
    }

}

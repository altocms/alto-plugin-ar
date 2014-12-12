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
 * HookRepost
 * Файл хука плагина Ar
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Ar
 * @version     0.0.1 от 04.08.2014 14:25
 *
 * @method PluginAr_ModuleAuthProvider_EntityRepostSetting[] User_GetCurrentUserRepostSettingsByType
 * @method AuthProvider[] PluginAr_AuthProvider_GetProvidersByTokenIdArray
 * @method Viewer_GetLocalViewer
 * @method Session_Get
 * @method Session_Drop
 * @method Lang_Get
 * @method User_GetCurrentUserText
 * @method Session_Set
 * @method Cache_Get
 *
 */
class PluginAr_HookRepost extends Hook {

    /**
     * Регистрация хуков
     */
    public function RegisterHook() {

        if (E::IsUser()) {
            // Репост статуса
            $this->AddHook('settings_profile_save_after', 'AfterProfileSave');
            // Репост записи стены
            $this->AddHook('wall_add_after', 'AfterWallAdd');
            // Репост топика
            $this->AddHook('topic_add_after', 'AfterTopicAdd', __CLASS__, 9);


            // и специально для VK
            if (Config::Get('plugin.ar.providers.vk.vk_client_id')) {
                // Инициализация скрипта ВК и репост записи со стены
                $this->AddHook('template_layout_body_end', 'BodyEndVKWall', __CLASS__, 2);
                // Репост топика
                $this->AddHook('template_layout_body_end', 'BodyEndVKPost', __CLASS__, 1);
            }
        }
    }





    /************************************************************************
     *                  Общие методы обеспечения репостинга
     ************************************************************************/

    /**
     * Запускает метод провайдера $oProvider->RepostWall() по репосту записи стены
     *
     * @param array $aData
     * @return bool
     */
    public function AfterWallAdd($aData) {

        /** @var ModuleWall_EntityWall $oWall */
        $oWall = $aData['oWall'];
        if ($oWall) {
            // Репостим )
            // Получим социалки, для которых пользователь определи репост
            /** @var PluginAr_ModuleAuthProvider_EntityRepostSetting[] $oSettings */
            $oSettings = $this->User_GetCurrentUserRepostSettingsByType('wall');
            if ($oSettings && $oProviders = $this->PluginAr_AuthProvider_GetProvidersByTokenIdArray(array_keys($oSettings))) {

                // Провайдеров для репоста статуса получили, теперь дело за малым
                foreach ($oProviders as $oProvider) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $oProvider->RepostWall($oWall->getText(), $oWall->getUrlWall(), $oProvider->getToken());
                }

            }
        }

        return TRUE;
    }

    /**
     * Запускает метод провайдера $oProvider->RepostPost() по репосту топика
     *
     * @param array $aData
     * @return AuthProvider[]
     */
    public function AfterTopicAdd($aData) {
        /** @var ModuleTopic_EntityTopic $oTopic */
        $oTopic = $aData['oTopic'];
        if ($oTopic && $oTopic->getUserId() == E::UserId()) {
            // Репостим )
            // Получим социалки, для которых пользователь определи репост
            /** @var PluginAr_ModuleAuthProvider_EntityRepostSetting[] $oSettings */
            $oSettings = $this->User_GetCurrentUserRepostSettingsByType('post');
            if ($oSettings && $oProviders = $this->PluginAr_AuthProvider_GetProvidersByTokenIdArray(array_keys($oSettings))) {

                if (isset($oProviders['vk'])) {
                    $this->Session_Set('new_current_user_topic_title', $oTopic->getTitle());
                    $this->Session_Set('new_current_user_topic_id', $oTopic->getId());
                }

                // Провайдеров для репоста статуса получили, теперь дело за малым
                foreach ($oProviders as $oProvider) {
                    $oProvider->RepostPost($oTopic, $oProvider->getToken());
                }

            }
        }

        return TRUE;
    }

    /**
     * Запускает метод провайдера $oProvider->RepostStatus() по репосту статуса пользователя
     *
     * @param array $aData
     * @return bool
     */
    public function AfterProfileSave($aData) {
        // Сохранили пользователя

        $sOldDataUserAbout = $this->Cache_Get('current_user_profile_about', 'tmp');

        /** @var ModuleUser_EntityUser $oUser */
        // Если получили данные
        if ($oUser = (isset($aData['oUser']) ? $aData['oUser'] : FALSE)) {

            // И статус меняем у себя
            if ($oUser && E::IsUser() && $oUser->getId() == E::User()->getId()) {

                // И он все-таки поменялся, то
                if ($sOldDataUserAbout != $oUser->getProfileAbout()) {

                    // Репостим )
                    // Получим социалки, для которых пользователь определи репост
                    /** @var PluginAr_ModuleAuthProvider_EntityRepostSetting[] $oSettings */
                    $oSettings = $this->User_GetCurrentUserRepostSettingsByType('status');
                    if ($oSettings && $oProviders = $this->PluginAr_AuthProvider_GetProvidersByTokenIdArray(array_keys($oSettings))) {

                        // Провайдеров для репоста статуса получили, теперь дело за малым
                        foreach ($oProviders as $oProvider) {
                            $oProvider->RepostStatus($oUser->getProfileAbout(), $oProvider->getToken());
                        }

                        return $oProviders;
                    }

                }
            }

        }


        return TRUE;
    }






    /************************************************************************
     *                  Методы обеспечения репостинга ВК
     ************************************************************************/

    /**
     * СПЕЦИАЛЬНО ДЛЯ ВК
     *
     * Метод публикации записи стены у вконтакта, если есть необходимость
     * публиковать ссылки на новые топики на стене, то еще и инициализирует
     * скрипт ВК
     *
     * @return string
     */
    public function BodyEndVKWall() {
        // Вот так очень хитро получим настройки вконтакта по репосту
        // топика и стены
        /** @var PluginAr_ModuleAuthProvider_EntityRepostSetting[] $oSettings */
        $oSettingsWall = $this->User_GetCurrentUserRepostSettingsByType('wall');
        $oSettingsPost = $this->User_GetCurrentUserRepostSettingsByType('post');
        if (!$oSettingsWall || !is_array($oSettingsWall)) {
            $oSettingsWall = array();
        }
        if (!$oSettingsPost || !is_array($oSettingsPost)) {
            $oSettingsPost = array();
        }
        $oSettings = $oSettingsWall + $oSettingsPost;


        if ($oSettings && $aProviders = $this->PluginAr_AuthProvider_GetProvidersByTokenIdArray(array_keys($oSettings))) {
            // Если среди провайдеров, у которых разрешена публикация
            // есть вконтакте, то запускаем скрипт
            if (isset($aProviders['vk'])) {
                /** @var ModuleViewer $oLocalViewer */
                $oLocalViewer = $this->Viewer_GetLocalViewer();
                $oLocalViewer->Assign('vk_client_id', Config::Get('plugin.ar.providers.vk.vk_client_id'));
                $oLocalViewer->Assign('link', Config::Get('path.root.web') . E::User()->getLogin() . '/wall/');
                $oLocalViewer->Assign('wall', $aProviders['vk']->aRepostRights['wall']);
                $oLocalViewer->Assign('post', $aProviders['vk']->aRepostRights['post']);
                $oLocalViewer->Assign('wall_good', $this->Lang_Get('plugin.ar.wall_good'));


                return $oLocalViewer->Fetch(Plugin::GetTemplatePath(__CLASS__) . 'tpls/social.vk.inject.tpl');
            }
        }

        return '';
    }

    /**
     * СПЕЦИАЛЬНО ДЛЯ ВК
     * Отправляет ссылку на топик на стену пользователя
     *
     * @return string
     */
    public function BodyEndVKPost() {
        /** @var PluginAr_ModuleAuthProvider_EntityRepostSetting[] $oSettings */
        $oSettings = $this->User_GetCurrentUserRepostSettingsByType('post');
        $sTopicTitle = $this->Session_Get('new_current_user_topic_title', FALSE);
        $iTopicId = $this->Session_Get('new_current_user_topic_id', FALSE);
        if ($sTopicTitle && $iTopicId && $oSettings && $aProviders = $this->PluginAr_AuthProvider_GetProvidersByTokenIdArray(array_keys($oSettings))) {
            $this->Session_Drop('new_current_user_topic_title');
            $this->Session_Drop('new_current_user_topic_id');
            // Если среди провайдеров, у которых разрешена публикация
            // есть вконтакте, то запускаем скрипт
            if (isset($aProviders['vk'])) {
                /** @var ModuleViewer $oLocalViewer */
                $oLocalViewer = $this->Viewer_GetLocalViewer();
                $oLocalViewer->Assign('vk_client_id', Config::Get('plugin.ar.providers.vk.vk_client_id'));
                $oLocalViewer->Assign('post', $aProviders['vk']->aRepostRights['post']);

                $sText = $this->User_GetCurrentUserText('topic');
                $sText = str_replace('{link}', $sTopicTitle, $sText);
                $oLocalViewer->Assign('link', Config::Get('path.root.web') . "t/{$iTopicId}");
                $oLocalViewer->Assign('text', str_replace('"', "'", $sText));
                $oLocalViewer->Assign('post_good', $this->Lang_Get('plugin.ar.post_good'));

                return $oLocalViewer->Fetch(Plugin::GetTemplatePath(__CLASS__) . 'tpls/social.vk.inject.post.tpl');
            }
        }

        return '';
    }

}
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
 * ActionAdmin.class.php
 * Файл экшена плагина ab
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина ab
 * @version     0.0.1 от 19.07.2014 09:17
 */
class PluginAr_ActionAdmin extends PluginAr_Inherit_ActionAdmin {

    /**
     * Регистрация экшенов админки
     */
    protected function RegisterEvent() {

        parent::RegisterEvent();

        $this->AddEvent('social-list', 'EventAdminSocialList');

    }

    /**
     * Страница настроек плагина
     *
     * @return bool
     */
    protected function EventAdminSocialList() {
        $this->Viewer_Assign('sPageTitle', $this->Lang_Get('plugin.ar.admin_social_page_title'));
        $this->Viewer_Assign('sMainMenuItem', 'content');
        $this->Viewer_AddHtmlTitle($this->Lang_Get('plugin.ar.admin_social_page_title'));

        if (getRequest('submit_social')) {
            /** @var PluginAr_ModuleAuthProvider_EntitySetting[] $aResult */
            $aResult = $this->PluginAr_AuthProvider_GetSettingItemsAll();
            if ($aResult) {
                foreach ($aResult as $oSetting) {
                    $oSetting->Delete();
                }
            }

            // Вконтакт
            if (($sID = getRequest('vk_client_id', FALSE)) && ($sSecret = getRequest('vk_secret_key', FALSE))) {
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'vk_client_id',
                    'setting_value' => $sID
                ));
                $oSetting->Add();
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'vk_secret_key',
                    'setting_value' => $sSecret
                ));
                $oSetting->Add();
            }

            if (($sID = getRequest('tw_client_id', FALSE)) && $sSecret = getRequest('tw_secret_key', FALSE)) {
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'tw_client_id',
                    'setting_value' => $sID
                ));
                $oSetting->Add();
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'tw_secret_key',
                    'setting_value' => $sSecret
                ));
                $oSetting->Add();
            }

            if (($sID = getRequest('mm_client_id', FALSE)) && $sSecret = getRequest('mm_secret_key', FALSE)) {
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'mm_client_id',
                    'setting_value' => $sID
                ));
                $oSetting->Add();
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'mm_secret_key',
                    'setting_value' => $sSecret
                ));
                $oSetting->Add();
            }

            if (($sID = getRequest('ya_client_id', FALSE)) && $sSecret = getRequest('ya_secret_key', FALSE)) {
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'ya_client_id',
                    'setting_value' => $sID
                ));
                $oSetting->Add();
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'ya_secret_key',
                    'setting_value' => $sSecret
                ));
                $oSetting->Add();
            }

            if (($sID = getRequest('g_client_id', FALSE)) && $sSecret = getRequest('g_secret_key', FALSE)) {
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'g_client_id',
                    'setting_value' => $sID
                ));
                $oSetting->Add();
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'g_secret_key',
                    'setting_value' => $sSecret
                ));
                $oSetting->Add();
            }

            if (($sID = getRequest('li_client_id', FALSE)) && $sSecret = getRequest('li_secret_key', FALSE)) {
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'li_client_id',
                    'setting_value' => $sID
                ));
                $oSetting->Add();
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'li_secret_key',
                    'setting_value' => $sSecret
                ));
                $oSetting->Add();
            }

            if (($sID = getRequest('i_client_id', FALSE)) && $sSecret = getRequest('i_secret_key', FALSE)) {
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'i_client_id',
                    'setting_value' => $sID
                ));
                $oSetting->Add();
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'i_secret_key',
                    'setting_value' => $sSecret
                ));
                $oSetting->Add();
            }

            if (($sID = getRequest('github_client_id', FALSE)) && ($sSecret = getRequest('github_secret_key', FALSE)) && $sName = getRequest('application_name', FALSE)) {
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'github_client_id',
                    'setting_value' => $sID
                ));
                $oSetting->Add();
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'github_secret_key',
                    'setting_value' => $sSecret
                ));
                $oSetting->Add();
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'application_name',
                    'setting_value' => $sName
                ));
                $oSetting->Add();
            }

            if (($sID = getRequest('od_client_id', FALSE)) && ($sSecret = getRequest('od_public_key', FALSE)) && $sName = getRequest('od_secret_key', FALSE)) {
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'od_client_id',
                    'setting_value' => $sID
                ));
                $oSetting->Add();
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'od_public_key',
                    'setting_value' => $sSecret
                ));
                $oSetting->Add();
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'od_secret_key',
                    'setting_value' => $sName
                ));
                $oSetting->Add();
            }

            if (($sID = getRequest('fb_client_id', FALSE)) && $sSecret = getRequest('fb_secret_key', FALSE) ) {
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'fb_client_id',
                    'setting_value' => $sID
                ));
                $oSetting->Add();
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'fb_secret_key',
                    'setting_value' => $sSecret
                ));
                $oSetting->Add();

            }

            if ($sID = getRequest('fb_group_id', FALSE)) {
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'fb_group_id',
                    'setting_value' => $sID
                ));
                $oSetting->Add();

            }

            if ($sID = getRequest('default_text_type_text', 'Мой новый топик: {link}')) {
                /** @var PluginAr_ModuleAuthProvider_EntitySetting $oSetting */
                $oSetting = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySetting', array(
                    'setting_key'   => 'default_text_type_text',
                    'setting_value' => $sID
                ));
                $oSetting->Add();

            }

            
        }

        /** @var PluginAr_ModuleAuthProvider_EntitySetting $aResult */
        $aResult = $this->PluginAr_AuthProvider_GetSettingItemsAll();
        if ($aResult) {
            $bLabel = false;
            foreach ($aResult as $oSetting) {
                if ($oSetting->getSettingKey() == 'default_text_type_text') {
                    $bLabel = true;
                }
                $_REQUEST[$oSetting->getSettingKey()] = $oSetting->getSettingValue();
            }

            if (!$bLabel) {
                $_REQUEST['default_text_type_text'] = 'Мой новый топик: {link}';
            }

        }


        return FALSE;
    }


}
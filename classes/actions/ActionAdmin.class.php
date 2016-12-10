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
 *              Является частью плагина Ar
 */
class PluginAr_ActionAdmin extends PluginAr_Inherits_ActionAdmin {

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

        E::Module('Viewer')->Assign('sPageTitle', E::Module('Lang')->Get('plugin.ar.admin_social_page_title'));
        E::Module('Viewer')->Assign('sMainMenuItem', 'content');
        E::Module('Viewer')->AddHtmlTitle(E::Module('Lang')->Get('plugin.ar.admin_social_page_title'));

        // Если нажата кнопка отправки формы настроек
        if (getRequest('submit_social')) {

            // Составим массив данных для записи в хранилище
            $aData['plugin.ar.providers.od.od_secret_key'] = F::GetRequest('od_secret_key', FALSE);
            $aData['plugin.ar.providers.od.od_client_id'] = F::GetRequest('od_client_id', FALSE);
            $aData['plugin.ar.providers.od.od_public_key'] = F::GetRequest('od_public_key', FALSE);
            $aData['plugin.ar.providers.fb.fb_secret_key'] = F::GetRequest('fb_secret_key', FALSE);
            $aData['plugin.ar.providers.fb.fb_client_id'] = F::GetRequest('fb_client_id', FALSE);
            $aData['plugin.ar.providers.fb.fb_group_id'] = F::GetRequest('fb_group_id', FALSE);
            $aData['plugin.ar.providers.github.github_secret_key'] = F::GetRequest('github_secret_key', FALSE);
            $aData['plugin.ar.providers.github.github_client_id'] = F::GetRequest('github_client_id', FALSE);
            $aData['plugin.ar.providers.github.application_name'] = F::GetRequest('application_name', FALSE);
            $aData['plugin.ar.providers.vk.vk_client_id'] = F::GetRequest('vk_client_id', FALSE);
            $aData['plugin.ar.providers.vk.vk_secret_key'] = F::GetRequest('vk_secret_key', FALSE);
            $aData['plugin.ar.providers.tw.tw_client_id'] = F::GetRequest('tw_client_id', FALSE);
            $aData['plugin.ar.providers.tw.tw_secret_key'] = F::GetRequest('tw_secret_key', FALSE);
            $aData['plugin.ar.providers.mm.mm_client_id'] = F::GetRequest('mm_client_id', FALSE);
            $aData['plugin.ar.providers.mm.mm_secret_key'] = F::GetRequest('mm_secret_key', FALSE);
            $aData['plugin.ar.providers.ya.ya_client_id'] = F::GetRequest('ya_client_id', FALSE);
            $aData['plugin.ar.providers.ya.ya_secret_key'] = F::GetRequest('ya_secret_key', FALSE);
            $aData['plugin.ar.providers.g.g_client_id'] = F::GetRequest('g_client_id', FALSE);
            $aData['plugin.ar.providers.g.g_secret_key'] = F::GetRequest('g_secret_key', FALSE);
            $aData['plugin.ar.providers.li.li_client_id'] = F::GetRequest('li_client_id', FALSE);
            $aData['plugin.ar.providers.li.li_secret_key'] = F::GetRequest('li_secret_key', FALSE);
            $aData['plugin.ar.providers.i.i_client_id'] = F::GetRequest('i_client_id', FALSE);
            $aData['plugin.ar.providers.i.i_secret_key'] = F::GetRequest('i_secret_key', FALSE);
            $aData['plugin.ar.default_text_type_text'] = F::GetRequest('default_text_type_text', 'Мой новый топик: {link}');
            $aData['plugin.ar.auto_login'] = F::GetRequest('auto_login', FALSE);
            $aData['plugin.ar.registration_only'] = F::GetRequest('registration_only', FALSE);
            $aData['plugin.ar.express'] = F::GetRequest('express', FALSE);

            // Запишем настройки в хранилище
            Config::WriteCustomConfig($aData);

            // Подставим данные в запрос
            $_REQUEST['od_public_key'] = $aData['plugin.ar.providers.od.od_public_key'];
            $_REQUEST['od_secret_key'] = $aData['plugin.ar.providers.od.od_secret_key'];
            $_REQUEST['od_client_id'] = $aData['plugin.ar.providers.od.od_client_id'];
            $_REQUEST['fb_secret_key'] = $aData['plugin.ar.providers.fb.fb_secret_key'];
            $_REQUEST['fb_group_id'] = $aData['plugin.ar.providers.fb.fb_group_id'];
            $_REQUEST['fb_client_id'] = $aData['plugin.ar.providers.fb.fb_client_id'];
            $_REQUEST['github_secret_key'] = $aData['plugin.ar.providers.github.github_secret_key'];
            $_REQUEST['github_client_id'] = $aData['plugin.ar.providers.github.github_client_id'];
            $_REQUEST['application_name'] = $aData['plugin.ar.providers.github.application_name'];
            $_REQUEST['vk_client_id'] = $aData['plugin.ar.providers.vk.vk_client_id'];
            $_REQUEST['vk_secret_key'] = $aData['plugin.ar.providers.vk.vk_secret_key'];
            $_REQUEST['tw_client_id'] = $aData['plugin.ar.providers.tw.tw_client_id'];
            $_REQUEST['tw_secret_key'] = $aData['plugin.ar.providers.tw.tw_secret_key'];
            $_REQUEST['mm_client_id'] = $aData['plugin.ar.providers.mm.mm_client_id'];
            $_REQUEST['mm_secret_key'] = $aData['plugin.ar.providers.mm.mm_secret_key'];
            $_REQUEST['ya_client_id'] = $aData['plugin.ar.providers.ya.ya_client_id'];
            $_REQUEST['ya_secret_key'] = $aData['plugin.ar.providers.ya.ya_secret_key'];
            $_REQUEST['g_client_id'] = $aData['plugin.ar.providers.g.g_client_id'];
            $_REQUEST['g_secret_key'] = $aData['plugin.ar.providers.g.g_secret_key'];
            $_REQUEST['li_client_id'] = $aData['plugin.ar.providers.li.li_client_id'];
            $_REQUEST['li_secret_key'] = $aData['plugin.ar.providers.li.li_secret_key'];
            $_REQUEST['i_client_id'] = $aData['plugin.ar.providers.i.i_client_id'];
            $_REQUEST['i_secret_key'] = $aData['plugin.ar.providers.i.i_secret_key'];
            $_REQUEST['default_text_type_text'] = $aData['plugin.ar.default_text_type_text'];
            $_REQUEST['auto_login'] = $aData['plugin.ar.auto_login'];
            $_REQUEST['registration_only'] = $aData['plugin.ar.registration_only'];
            $_REQUEST['express'] = $aData['plugin.ar.express'];

            return FALSE;
        }

        // Если сохранения не было, то возьмем настройки из конфига
        $_REQUEST['od_public_key'] = Config::Get('plugin.ar.providers.od.od_public_key');
        $_REQUEST['od_secret_key'] = Config::Get('plugin.ar.providers.od.od_secret_key');
        $_REQUEST['od_client_id'] = Config::Get('plugin.ar.providers.od.od_client_id');
        $_REQUEST['fb_secret_key'] = Config::Get('plugin.ar.providers.fb.fb_secret_key');
        $_REQUEST['fb_group_id'] = Config::Get('plugin.ar.providers.fb.fb_group_id');
        $_REQUEST['fb_client_id'] = Config::Get('plugin.ar.providers.fb.fb_client_id');
        $_REQUEST['github_secret_key'] = Config::Get('plugin.ar.providers.github.github_secret_key');
        $_REQUEST['github_client_id'] = Config::Get('plugin.ar.providers.github.github_client_id');
        $_REQUEST['application_name'] = Config::Get('plugin.ar.providers.github.application_name');
        $_REQUEST['vk_client_id'] = Config::Get('plugin.ar.providers.vk.vk_client_id');
        $_REQUEST['vk_secret_key'] = Config::Get('plugin.ar.providers.vk.vk_secret_key');
        $_REQUEST['tw_client_id'] = Config::Get('plugin.ar.providers.tw.tw_client_id');
        $_REQUEST['tw_secret_key'] = Config::Get('plugin.ar.providers.tw.tw_secret_key');
        $_REQUEST['mm_client_id'] = Config::Get('plugin.ar.providers.mm.mm_client_id');
        $_REQUEST['mm_secret_key'] = Config::Get('plugin.ar.providers.mm.mm_secret_key');
        $_REQUEST['ya_client_id'] = Config::Get('plugin.ar.providers.ya.ya_client_id');
        $_REQUEST['ya_secret_key'] = Config::Get('plugin.ar.providers.ya.ya_secret_key');
        $_REQUEST['g_client_id'] = Config::Get('plugin.ar.providers.g.g_client_id');
        $_REQUEST['g_secret_key'] = Config::Get('plugin.ar.providers.g.g_secret_key');
        $_REQUEST['li_client_id'] = Config::Get('plugin.ar.providers.li.li_client_id');
        $_REQUEST['li_secret_key'] = Config::Get('plugin.ar.providers.li.li_secret_key');
        $_REQUEST['i_client_id'] = Config::Get('plugin.ar.providers.i.i_client_id');
        $_REQUEST['i_secret_key'] = Config::Get('plugin.ar.providers.i.i_secret_key');
        $_REQUEST['default_text_type_text'] = Config::Get('plugin.ar.default_text_type_text');
        $_REQUEST['auto_login'] = Config::Get('plugin.ar.auto_login');
        $_REQUEST['registration_only'] = Config::Get('plugin.ar.registration_only');
        $_REQUEST['default_text_type_text'] = Config::Get('plugin.ar.default_text_type_text');
        $_REQUEST['auto_login'] = Config::Get('plugin.ar.auto_login');
        $_REQUEST['registration_only'] = Config::Get('plugin.ar.registration_only');
        $_REQUEST['express'] = Config::Get('plugin.ar.express');

        return FALSE;
    }

}

// EOF
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
 * HookAdmin.class.php
 * Файл хука плагина ar
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина ab
 * @version     0.0.1 от 09.08.2014 19:56
 */
class PluginAr_HookAdmin extends Hook {

    /**
     * Регистрация хуков
     *
     * @return void
     */
    public function RegisterHook() {
        $this->AddHook('template_admin_menu_content', 'AdminMenuInject', __CLASS__);

    }

    /**
     * Доабвление ссылки в меню админки
     *
     * @return string
     */
    public function AdminMenuInject() {

        if (E::IsAdmin()) {
            return $this->Viewer_Fetch(Plugin::GetTemplatePath(__CLASS__) . 'admin.menu.social.inject.tpl');
        }



        return '';
    }

}
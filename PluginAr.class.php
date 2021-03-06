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

/** Запрещаем напрямую через браузер обращение к этому файлу.  */
if (!class_exists('Plugin')) {
    die('Hacking attempt!');
}

/**
 * PluginAr.class.php
 * Файл основного класса плагина Ar
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Ar        
 *
 * @method void Viewer_AppendStyle
 * @method void Viewer_AppendScript
 * @method void Viewer_Assign
 *
 */
class PluginAr extends Plugin {

    /** @var array $aDelegates Объявление делегирований */
    protected $aDelegates = array(
        'template' => array(),
    );

    /** @var array $aInherits Объявление переопределений (модули, мапперы и сущности) */
    protected $aInherits = array(
        'actions' => array(
            'ActionSettings',
            'ActionProfile',
            'ActionAdmin',
        ),
        'modules' => array(
            'ModuleUploader',
            'ModuleUser',
        ),
        'mapper' => array(
            'ModuleUser_MapperUser'
        ),
    );

    /**
     * Активация плагина
     * @return bool
     */
    public function Activate() {

        if (!$this->isTableExists('prefix_user_token')) {
            $this->ExportSQL(__DIR__ . '/install/db/init.sql');
        }
        return TRUE;
    }

    /**
     * Деактивация плагина
     * @return bool
     */
    public function Deactivate() {

        return TRUE;
    }

    /**
     * Инициализация плагина
     */
    public function Init() {

        E::Module('Viewer')->AppendStyle(Plugin::GetTemplatePath(__CLASS__) . 'assets/css/plugin.ar.css'); // Добавление своего CSS
        E::Module('Viewer')->AppendScript(Plugin::GetTemplatePath(__CLASS__) . 'assets/js/plugin.ar.js'); // Добавление своего JS
        E::Module('Viewer')->AppendScript('https://vk.com/js/api/openapi.js'); // API Вконтакта

        return TRUE;
    }

}

// EOF
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
 * config.php
 * Файл конфигурационных параметров плагина Ar
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Ar
 * @version     0.0.1 от 30.07.2014 21:13
 */

/**
 * Таблицы БД плагина
 */
Config::Set('db.table.auth_provider_user_token', '___db.table.prefix___user_token');
Config::Set('db.table.auth_provider_repost_setting', '___db.table.prefix___repost_setting');
Config::Set('db.table.auth_provider_text', '___db.table.prefix___repost_text');
Config::Set('db.table.auth_provider_searched_user', '___db.table.prefix___searched_user');
Config::Set('db.table.auth_provider_setting', '___db.table.prefix___social_setting');

/**
 * Роутеры плагина
 */
Config::Set('router.page.auth', 'PluginAr_ActionAr'); // Админка

/**
 * Параметры плагина
 */
$aConfig = array(

    // Ключи к приложениям
    'providers' => array(

        // Параметры авторизации через Vkontakte
        'vk'     => array(
            'name'           => 'vkontakte',
            'version'        => 2,
            'vk_client_id'   => '',
            'vk_secret_key'  => '',
            'vk_permissions' => array(
                'friends',
                'photos',
                'wall',
                'offline',
                'status',
            ),
        ),

        // Параметры авторизации через Одноклассники
        // http://apiok.ru/wiki/pages/viewpage.action?pageId=42476652
        // Мои приложения - http://www.odnoklassniki.ru/dk?st.cmd=appsInfoMyDevList
        'od'     => array(
            'name'           => 'odnoklassniki',
            'version'        => 2,
            'od_client_id'   => '',
            'od_public_key'  => '',
            'od_secret_key'  => '',
            'od_permissions' => array(
            ),
        ),

        // Параметры авторизации через Facebook
        'fb'     => array(
            'name'           => 'facebook',
            'version'        => 2,
            'fb_client_id'   => '',
            'fb_secret_key'  => '',
            'fb_group_id' => '',
            'fb_permissions' => array(
                'user_birthday',
                'user_website',
                'user_friends',
                'email',
                'user_about_me',
                'publish_actions',
                'user_groups',
            ),
        ),

        // Параметры авторизации через Twitter
        'tw'     => array(
            'name'           => 'twitter',
            'version'        => 1,
            'tw_client_id'   => '',
            'tw_secret_key'  => '',
            'tw_permissions' => array(),
        ),

        // Параметры авторизации через Мой Мир
        'mm'     => array(
            'name'           => 'mm',
            'version'        => 2,
            'has-receiver'   => TRUE,
            'mm_client_id'   => '',
            'mm_secret_key'  => '',
            'mm_permissions' => array(
                'stream'
            ),
        ),

        // Параметры авторизации через Yandex
        'ya'     => array(
            'name'           => 'ya',
            'version'        => 2,
            'ya_client_id'   => '',
            'ya_secret_key'  => '',
            'ya_permissions' => array(),
        ),

        // Параметры авторизации через Google
        'g'      => array(
            'name'          => 'google',
            'version'       => 2,
            'g_client_id'   => '',
            'g_secret_key'  => '',
            'g_permissions' => array(
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile',
            ),
        ),

        // Параметры авторизации через Github
        'github' => array(
            'name'               => 'github',
            'version'            => 2,
            'application_name'   => '',
            'github_client_id'   => '',
            'github_secret_key'  => '',
            'github_permissions' => array(
                'user:email',
            ),

        ),

        // Параметры авторизации через linkedin
        'li'     => array(
            'name'           => 'linkedin',
            'version'        => 2,
            'li_client_id'   => '',
            'li_secret_key'  => '',
            'li_permissions' => array(
                'r_fullprofile',
                'r_emailaddress',
                'r_network',
                'r_contactinfo',
            ),
        ),

        // Параметры авторизации через instagramm
        'i'     => array(
            'name'           => 'instagram',
            'version'        => 2,
            'i_client_id'   => '',
            'i_secret_key'  => '',
            'i_permissions' => array(
                'basic',
            ),
        ),

    ),

    'default_text_type_text' => 'Мой новый топик: {link}',

    'use_curl'  => TRUE,

    // Лимит времени между поисками друзей в секундах
    'friends_search_limit' => 60*60*24,

    // Формировать логин автоматически или запрашивать у пользователя
    // TRUE - автоматически
    // FALSE - запрашивать у пользователя
    'auto_login' => FALSE,

);

return $aConfig;
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
 * Text.entity.class.php
 * Файл сущности для модуля AuthProvider плагина Ar
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Ar
 * @version     0.0.1 от 31.07.2014 2:48
 */
class PluginAr_ModuleAuthProvider_EntityText extends EntityORM {
    protected $aRelations = array(
        'user' => array(EntityORM::RELATION_TYPE_BELONGS_TO, 'ModuleUser_EntityUser', 'text_user_id'),
    );

    public function getUserByTokenUserId($iUserId) {
        return $this->User_GetUserById($iUserId);
    }
}
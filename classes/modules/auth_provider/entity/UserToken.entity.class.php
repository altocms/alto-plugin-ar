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
 * Token.entity.class.php
 * Файл сущности для модуля AuthProvider плагина Ar
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Ar
 *
 * @method int getTokenId
 * @method string getTokenData
 * @method string getTokenExpire
 * @method string getTokenUserId
 * @method string getTokenProviderName
 * @method string getTokenProviderUserId
 * @method string getTokenDataSecret
 *
 * @method void setTokenId
 * @method void setTokenData
 * @method void setTokenExpire
 * @method void setTokenUserId
 * @method void setTokenProviderName
 * @method void setTokenProviderUserId
 * @method void setTokenDataSecret
 */
class PluginAr_ModuleAuthProvider_EntityUserToken extends EntityORM {

    protected $aRelations = array(
        'user' => array(EntityORM::RELATION_TYPE_BELONGS_TO, 'ModuleUser_EntityUser', 'token_user_id'),
    );

    public function getUserByTokenUserId($iUserId) {

        return E::Module('User')->GetUserById($iUserId);
    }

    /**
     * Специально для вконтакта, который email отдает в токене, а не в
     * дополнительных данный
     */
    public function getKeyProps() {

        unset($this->_aData['token_email']);
        return parent::getKeyProps();
    }

    public function getValProps() {

        unset($this->_aData['token_email']);
        return parent::getValProps();
    }

}

// EOF
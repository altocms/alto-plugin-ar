<?php

require_once __DIR__ . '/../AuthProvider.class.php';

class GProvider extends AuthProvider {

    public $sName = 'g';
    public $sPermissionsGutter = ' ';
    public $sAuthUrl = 'https://accounts.google.com/o/oauth2/auth?redirect_uri=%%redirect%%&response_type=code&client_id=%%client_id%%&scope=%%permissions%%';
    public $sTokenUrl = 'https://accounts.google.com/o/oauth2/token?grant_type=authorization_code&code=%%code%%&client_id=%%client_id%%&client_secret=%%secret_key%%&redirect_uri=%%redirect%%';
    public $sUserInfoUrl = 'https://www.googleapis.com/oauth2/v1/userinfo?format=json&oauth_token=%%access_token%%';

    /**
     * Получение токена пользователя
     *
     * @throws Exception
     *
     * @return bool|PluginAr_ModuleAuthProvider_EntityUserToken
     */
    public function GetUserToken() {

        if (!$aData = $this->LoadTokenData(TRUE, 'code', array('Content-type:application/x-www-form-urlencoded'))) {
            return FALSE;
        }

        /**
         * Возвратим объект токена
         */
        $oToken = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityUserToken', array(
            'token_provider_name'    => $this->sName,
            'token_data'             => $aData->access_token,
            'token_data_secret'      => @$aData->id_token,
            'token_expire'           => @$aData->expires_in?time()+@$aData->expires_in:0,
            'token_provider_user_id' => 0,
        ));

        return $oToken;
    }

    /**
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     *
     * @return bool|Entity
     */
    public function GetUserData($oToken) {

        if (!$sData = $this->LoadAdditionalData(
            $oToken,
            array(
                '%%access_token%%' => $oToken->getTokenData(),
            ), FALSE)
        ) {
            return FALSE;
        }

        // Раскодируем
        $oData = json_decode($sData);

        // * Получили дополнительные данные. Заполним профиль из того, что есть
        return Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityData', array(
            'data_provider_name' => $this->sName,
            // В идентификаторе от гугла могут содержаться символы, которые не
            // разрешены в логине пользователя, потому будем брать хэш от этого
            // значения и, чтобы уж наверняка, с примесью рандомной строки.
            'data_login'         => $this->sName . '_' . F::TruncateText(F::DoHashe($oData->id . F::RandomStr()), 20),
            'data_name'          => @$oData->given_name,
            'data_surname'       => @$oData->family_name,
            'data_sex'           => 'other',
            'data_about'         => '',
            'data_page'          => $oData->id,
            'data_birthday'      => null,
            'data_mail'          => @$oData->email,
            'data_photo'         => @$oData->picture,
        ));
    }

}

// EOF
<?php

require_once __DIR__ . "/../AuthProvider.class.php";

class GithubProvider extends AuthProvider {

    public $sName = 'github';
    public $sAuthUrl = 'https://github.com/login/oauth/authorize?redirect_uri=%%redirect%%&state=%%state%%&client_id=%%client_id%%&scope=%%permissions%%';
    public $sTokenUrl = 'https://github.com/login/oauth/access_token?code=%%code%%&client_id=%%client_id%%&client_secret=%%secret_key%%&redirect_uri=%%redirect%%';
    public $sUserInfoUrl = 'https://api.github.com/user?access_token=%%access_token%%';

    public function Init() {
        parent::Init();

        $sState = Engine::getInstance()->Session_Get('github_state');
        if (!$sState) {
            $sState = base64_encode(func_generator(20));
            Engine::getInstance()->Session_Set('github_state', $sState);
        }

        $this->sAuthUrl = $this->EvalUrl($this->sAuthUrl, array('%%state%%' => $sState));
    }

    /**
     * Получение токена пользователя
     *
     * @return PluginAr_ModuleAuthProvider_EntityUserToken
     * @throws Exception
     */
    public function GetUserToken() {

        // К нам пришли не с гитхаба
        if (getRequest('state') != Engine::getInstance()->Session_Get('github_state')) {
            return FALSE;
        }

        Engine::getInstance()->Session_Set('github_state', '');

        if (!$aData = $this->LoadTokenData(TRUE)) {
            return FALSE;
        }



        /**
         * Возвратим объект токена
         */
        $oToken = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityUserToken', array(
            'token_provider_name'    => $this->sName,
            'token_data'             => $aData->access_token,
            'token_expire'           => 0,
            'token_provider_user_id' => 0,
        ));

        return $oToken;
    }

    public function GetUserData(PluginAr_ModuleAuthProvider_EntityUserToken $oToken) {

        if (!$aData = $this->LoadAdditionalData(
            $oToken,
            array(
                '%%access_token%%' => $oToken->getTokenData(),
            ),
            FALSE,
            array('User-Agent: ' . Config::Get('plugin.ar.providers.github.application_name')))
        ) {
            return FALSE;
        }

        // Раскодируем
        $oData = json_decode($aData);

        if (is_null($oData)) {
            return false;
        }

        /**
         * Получили дополнительные данные. Заполним профиль из того, что есть
         */

        return Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityData', array(
            'data_provider_name' => $this->sName,
            'data_login'         => $this->sName . '_' . $oData->id,
            'data_name'          => @$oData->name,
            'data_surname'       => '',
            'data_sex'           => 'other',
            'data_about'         => $oData->bio,
            'data_page'          => $oData->login,
            'data_birthday'      => '',
            'data_mail'          => @$oData->email,
            'data_photo'         => @$oData->avatar_url,
        ));

    }


}
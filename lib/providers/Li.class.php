<?php

require_once __DIR__ . '/../AuthProvider.class.php';

class LiProvider extends AuthProvider {

    public $sName = 'li';
    public $sPermissionsGutter = ',';
    public $sAuthUrl = 'https://www.linkedin.com/uas/oauth2/authorization?redirect_uri=%%redirect%%&state=%%state%%&client_id=%%client_id%%&scope=%%permissions%%&response_type=code';
    public $sTokenUrl = 'https://www.linkedin.com/uas/oauth2/accessToken?grant_type=authorization_code&scope=%%permissions%%&code=%%code%%&client_id=%%client_id%%&client_secret=%%secret_key%%&redirect_uri=%%redirect%%';
    public $sUserInfoUrl = 'https://api.linkedin.com/v1/people/~:(id,first-name,last-name,date-of-birth,picture-url,email-address)?oauth2_access_token=%%access_token%%&format=json';

    public $aRepostRights = array(
        AuthProvider::REPOST_RIGHT_WALL => FALSE, // Репост записей стены
        AuthProvider::REPOST_RIGHT_STATUS => FALSE, // Репост статуса
        AuthProvider::REPOST_RIGHT_POST   => FALSE, // Репост топиков
    );

    public function Init() {

        parent::Init();

        $sState = Engine::getInstance()->Session_Get('li_state');
        if (!$sState) {
            $sState = base64_encode(func_generator(20));
            Engine::getInstance()->Session_Set('li_state', $sState);
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

        // К нам пришли не с li
        if (getRequest('state') != Engine::getInstance()->Session_Get('li_state')) {
            return FALSE;
        }

        Engine::getInstance()->Session_Set('li_state', '');

        if (!$aData = $this->LoadTokenData(TRUE)) {
            return FALSE;
        }

        // * Возвратим объект токена
        $oToken = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityUserToken', array(
            'token_provider_name'    => $this->sName,
            'token_data'             => $aData->access_token,
            'token_expire'           => $aData->expires_in ? time() + $aData->expires_in : 0,
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
            ),
            FALSE
            )
        ) {
            return FALSE;
        }

        // Раскодируем
        $oData = json_decode($sData);

        // * Получили дополнительные данные. Заполним профиль из того, что есть
        return Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityData', array(
            'data_provider_name' => $this->sName,
            'data_login'         => $this->sName . '_' . $oData->id,
            'data_name'          => @$oData->firstName,
            'data_surname'       => @$oData->lastName,
            'data_sex'           => 'other',
            'data_about'         => '',
            'data_page'          => $oData->id,
            'data_birthday'      => null,
            'data_mail'          => @$oData->emailAddress,
            'data_photo'         => @$oData->pictureUrl,
        ));
    }

}

// EOF
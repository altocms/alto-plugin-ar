<?php

require_once __DIR__ . "/../AuthProvider.class.php";

class MmProvider extends AuthProvider {

    public $sName = 'mm';
    public $sAuthUrl = 'https://connect.mail.ru/oauth/authorize?client_id=%%client_id%%&response_type=code&redirect_uri=%%redirect%%&scope=%%permissions%%';
    public $sTokenUrl = 'https://connect.mail.ru/oauth/token?client_id=%%client_id%%&redirect_uri=%%redirect%%&client_secret=%%secret_key%%&code=%%code%%&grant_type=authorization_code';
    public $sUserInfoUrl = 'http://www.appsmail.ru/platform/api?method=users.getInfo&secure=1&app_id=%%client_id%%&session_key=%%access_token%%&sig=%%signature%%';

    public $sPermissionsGutter = ' ';

    private $sRepostWallUrl = 'http://www.appsmail.ru/platform/api';

    public $aRepostRights = array(
        AuthProvider::REPOST_RIGHT_WALL   => TRUE, // Репост записей стены
        AuthProvider::REPOST_RIGHT_STATUS => TRUE, // Репост статуса
        AuthProvider::REPOST_RIGHT_POST   => TRUE, // Репост топиков
        AuthProvider::REPOST_RIGHT_FRIENDS => TRUE, // Поиск друзей по сайту
    );

    /**
     * Получает идентфикаторы друзей пользователя из социальной сети
     *
     * @param $oToken
     * @return bool|string[]
     */
    public function GetFriendsId($oToken) {
        $this->RefreshToken($oToken);

        $aParams = array(
            // Параметры защиты
            'method'      => 'friends.get',
            'secure'      => '1',
            'app_id'      => $this->sClientId,
            'session_key' => $oToken->getTokenData(),

            // Параметы метода
            'ext'         => 0,
        );

        $aData = $this->SendRequest(
            $this->sRepostWallUrl . $this->BuildParamsString($aParams, $this->GetSignature($aParams, $this->sSecretKey)),
            TRUE
        );

        // Раскодируем
        $aData = json_decode($aData);

        if (is_array($aData)) {
            return $aData;
        }

        return FALSE;
    }

    /**
     * Расчет сигнатуры
     *
     * @see http://api.mail.ru/docs/guides/restapi/
     * @param array $request_params
     * @param       $secret_key
     * @return string
     */
    protected function GetSignature(array $request_params, $secret_key) {
        ksort($request_params);
        $params = '';
        foreach ($request_params as $key => $value) {
            $params .= "$key=$value";
        }

        return md5($params . $secret_key);
    }

    /**
     * Формирует строку параметров из массива
     *
     * @param array $request_params
     * @param       $sSignature
     * @return string
     */
    protected function BuildParamsString(array $request_params, $sSignature) {
        ksort($request_params);
        $params = '?';
        foreach ($request_params as $key => $value) {
            $params .= "$key=$value&";
        }

        return $params . 'sig=' . $sSignature;
    }

    /**
     * Репост топика
     *
     * @param ModuleTopic_EntityTopic                     $oTopic
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     * @return bool|void
     */
    public function RepostPost($oTopic, $oToken) {

        $this->RefreshToken($oToken);

        $sText = strip_tags($oTopic->getTitle());
        $sText = $this->CropText($sText, '...', 400);

        $aParams = array(
            'method'      => 'stream.post',
            'secure'      => '1',
            'app_id'      => $this->sClientId,
            'session_key' => $oToken->getTokenData(),
            'text'        => $sText,
            'title'       => str_replace('{link}', '', Engine::getInstance()->User_GetCurrentUserText('topic')),
            'link1_href'  => Config::Get('path.root.web') . "t/{$oTopic->getId()}",
            'link1_text'  => Engine::getInstance()->Lang_Get('plugin.ar.read_more_topic'),
        );

        $this->SendRequest(
            $this->sRepostWallUrl . $this->BuildParamsString($aParams, $this->GetSignature($aParams, $this->sSecretKey)),
            TRUE
        );

    }

    /**
     * Репост статуса
     *
     * @param string                                      $sStatus
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     * @return bool|void
     */
    public function RepostStatus($sStatus, $oToken) {

        $this->RefreshToken($oToken);

        $sStatus = strip_tags($sStatus);
        $sStatus = $this->CropText($sStatus, '...', 400);

        $aParams = array(
            'method'      => 'stream.post',
            'secure'      => '1',
            'app_id'      => $this->sClientId,
            'session_key' => $oToken->getTokenData(),
            'text'        => $sStatus,
            'title'       => Engine::getInstance()->Lang_Get('plugin.ar.new_status'),
        );

        $this->SendRequest(
            $this->sRepostWallUrl . $this->BuildParamsString($aParams, $this->GetSignature($aParams, $this->sSecretKey)),
            TRUE
        );

    }

    /**
     * Проводит репост записи стены
     *
     * @param                                             $sStatus
     * @param                                             $sUrl
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     * @return bool|void
     */
    public function RepostWall($sStatus, $sUrl, $oToken) {

        $this->RefreshToken($oToken);

        $sStatus = strip_tags($sStatus);
        $sStatus = $this->CropText($sStatus, '...', 400);

        $aParams = array(
            'method'      => 'stream.post',
            'secure'      => '1',
            'app_id'      => $this->sClientId,
            'session_key' => $oToken->getTokenData(),
            'text'        => $sStatus,
            'title'       => Engine::getInstance()->Lang_Get('plugin.ar.on_wall'),
            'link1_href'  => $sUrl,
            'link1_text'  => Engine::getInstance()->Lang_Get('plugin.ar.read_more'),
        );

        $this->SendRequest(
            $this->sRepostWallUrl . $this->BuildParamsString($aParams, $this->GetSignature($aParams, $this->sSecretKey)),
            TRUE
        );

    }

    /**
     * Получение токена пользователя
     *
     * @return PluginAr_ModuleAuthProvider_EntityUserToken
     * @throws Exception
     */
    public function GetUserToken() {

        if (!$aData = $this->LoadTokenData(TRUE)) {
            return FALSE;
        }

        /**
         * Возвратим объект токена
         */
        $oToken = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityUserToken', array(
            'token_provider_name'    => $this->sName,
            'token_data'             => $aData->access_token,
            'token_expire' => $aData->expires_in ? time() + $aData->expires_in : 0,
            'token_provider_user_id' => 0,
        ));

        return $oToken;
    }

    public function GetUserData(PluginAr_ModuleAuthProvider_EntityUserToken $oToken) {

        if (!$aData = $this->LoadAdditionalData(
            $oToken,
            array(
                '%%access_token%%' => $oToken->getTokenData(),
                '%%signature%%'    => md5("app_id={$this->sClientId}method=users.getInfosecure=1session_key={$oToken->getTokenData()}{$this->sSecretKey}")
            ), FALSE)
        ) {
            return FALSE;
        }

        // Раскодируем
        $oData = json_decode($aData);

        $oData = $oData[0];

        /**
         * Получили дополнительные данные. Заполним профиль из того, что есть
         */

        return Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityData', array(
            'data_provider_name' => $this->sName,
            'data_login'         => $this->sName . '_' . $oData->uid,
            'data_name'          => @$oData->first_name,
            'data_surname'       => @$oData->last_name,
            'data_sex'           => ((@$oData->sex == '0') ? 'man' : ($oData->sex == '1' ? 'woman' : 'other')),
            'data_about'         => @$oData->status_text ? @$oData->status_text : '',
            'data_page'          => str_replace('/', '', str_replace('http://my.mail.ru/mail/', '', $oData->link)),
            'data_birthday'      => date('Y-m-d H:i:s', strtotime(@$oData->birthday)),
            'data_mail'          => @$oData->email,
            'data_photo' => @$oData->has_pic ? @$oData->pic_big : '',
        ));

    }


}
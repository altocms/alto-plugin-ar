<?php

require_once __DIR__ . "/../AuthProvider.class.php";

/**
 * А у твиттера друзья - это те, которых ты читаешь
 * Class TwProvider
 */
class TwProvider extends AuthProvider {

    public $sName = 'tw';

    // Количество апдейтов (сообщений) – не боле 1000 в сутки.
    // Количество прямых сообщений другим пользователям – не более 1000 в сутки.
    // Ограничение на операции с акаунтом (ограничение API Twitter) – не более 100 в час.

    // Формируется в таком виде родительским классом
    // $this->sAuthUrl = Config::Get('path.root.web') . 'auth/1/tw';

    // Одобрение пользователя
    public $sUserInfoUrl = 'https://twitter.com/oauth/authorize?oauth_token=%%token%%';
    // Получение токена
    public $sTokenUrl = 'https://twitter.com/oauth/request_token';
    // Получение первичных данных
    private $sPrimaryDataUrl = 'https://api.twitter.com/oauth/access_token';
    // Получение дополнительных данных от твиттера
    private $sAdditionalDataUrl = 'https://api.twitter.com/1.1/users/lookup.json';


    private $sRepostWallUrl = 'https://api.twitter.com/1.1/statuses/update.json';

    private $bPostMethod = FALSE;
    private $aHeaders = FALSE;

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
        $this->bPostMethod = FALSE;
        $this->aHeaders = array('Content-Type: application/x-www-form-urlencoded');

        $aData = $this->SendRequestToTwitter(
            "https://api.twitter.com/1.1/friends/ids.json",
            array(
                "oauth_consumer_key"     => $this->sClientId,
                "oauth_nonce"            => time(),
                "oauth_signature_method" => "HMAC-SHA1",
                "oauth_timestamp"        => time(),
                "oauth_token"            => $oToken->getTokenData(),
                "oauth_version"          => "1.0",
            ),
            $oToken->getTokenDataSecret()
        );

        if (isset($aData->ids) && $aFriendId = @$aData->ids) {
            return $aFriendId;
        }

        return FALSE;
    }


    /**
     * Отправляет твит
     *
     * @param string $sStatus Текст твита
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken Токен
     * @param string $sUrl Ссылка на текстовку на сайте
     */
    public function SendTweet($sStatus, $oToken, $sUrl = '') {

        $sStatus = $this->CropText($sStatus);


        parent::RepostWall($sStatus, $sUrl, $oToken);

        $this->bPostMethod = TRUE;
        $this->aHeaders = array('Content-Type: application/x-www-form-urlencoded');

        $this->SendRequestToTwitter(
            $this->sRepostWallUrl,
            array(
                "oauth_consumer_key"     => $this->sClientId,
                "oauth_nonce"            => time(),
                "oauth_signature_method" => "HMAC-SHA1",
                "oauth_timestamp"        => time(),
                "oauth_token"            => $oToken->getTokenData(),
                "oauth_version"          => "1.0",
                "status"                 => $sStatus,
            ),
            $oToken->getTokenDataSecret()
        );

    }

    public function RepostPost($oTopic, $oToken) {
        $sText = Engine::getInstance()->User_GetCurrentUserText('topic');

        $sTweet = str_replace('{link}', Config::Get('path.root.web') . "t/{$oTopic->getId()}", $sText);

        $this->SendTweet($sTweet, $oToken);

    }

    public function RepostWall($sStatus, $sUrl, $oToken) {
        $sStatus = strip_tags($sStatus);
        $this->SendTweet($sStatus, $oToken);
    }

    public function RepostStatus($sStatus, $oToken) {
        $sStatus = strip_tags($sStatus);
        $this->SendTweet($sStatus, $oToken);
    }

    /**
     * Отправка запроса свитеру
     *
     * @param        $Url
     * @param        $aData
     * @param string $sSecretToken
     * @return bool|mixed|object|stdClass
     */
    protected function SendRequestToTwitter($Url, $aData, $sSecretToken = '') {
        $params = $aData;

        // Строим параметры
        $keys = $this->_urlencode_rfc3986(array_keys($params));
        $values = $this->_urlencode_rfc3986(array_values($params));
        $params = array_combine($keys, $values);
        uksort($params, 'strcmp');

        // переводим параметры в строку
        $pairs = array();
        foreach ($params as $k => $v) {
//            $pairs[] = $this->_urlencode_rfc3986($k) . '=' . $this->_urlencode_rfc3986($v);
            $pairs[] = $k . '=' . $v;
        }
        $concatenatedParams = implode('&', $pairs);

        // Формируем строку для получения подписи
        $baseString = ($this->bPostMethod ? 'POST' : 'GET') . "&" . $this->_urlencode_rfc3986($Url) . "&" . $this->_urlencode_rfc3986($concatenatedParams);
        // Секретный ключ
        $secret = $this->_urlencode_rfc3986($this->sSecretKey) . "&" . $sSecretToken;
        // Получаем сигнатуру
        $params['oauth_signature'] = $this->_urlencode_rfc3986(base64_encode(hash_hmac('sha1', $baseString, $secret, TRUE)));

        // Получаем УРЛ
        uksort($params, 'strcmp');
        // Конвертим в строку
        $urlPairs = array();
        foreach ($params as $k => $v) {
            $urlPairs[] = $k . "=" . $v;
        }
        $concatenatedUrlParams = implode('&', $urlPairs);
        // form url
        $url = $Url . "?" . $concatenatedUrlParams;

        // Send to cURL
        $aData = $this->SendRequest($url, $this->bPostMethod, $this->aHeaders);

        if ($this->isJson($aData)) {
            $aData = json_decode($aData);
        } else {
            $aData = $this->DecodeGetString($aData);
        }


        if (isset($aData->error)) {
            if (!(isset($aData->oauth_token) || (is_array($aData) && isset($aData[0])))) {
                $this->setLastErrorCode(3);

                return FALSE;
            }
        }


        return $aData;
    }


    /**
     * Получение токена пользователя
     * http://stackoverflow.com/questions/3295466/another-twitter-oauth-curl-access-token-request-that-fails
     *
     * @return PluginAr_ModuleAuthProvider_EntityUserToken
     * @throws Exception
     */
    public function GetUserToken() {
        $aData = $this->SendRequestToTwitter($this->sTokenUrl, array(
            "oauth_version"          => "1.0",
            "oauth_nonce"            => time(),
            "oauth_timestamp"        => time(),
            "oauth_consumer_key"     => $this->sClientId,
            "oauth_signature_method" => "HMAC-SHA1"
        ));

        if ($aData) {
            $oToken = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityUserToken', array(
                'token_provider_name'    => $this->sName,
                'token_data'             => $aData->oauth_token,
                'token_data_secret'      => $aData->oauth_token_secret,
                'token_expire'           => 0,
                'token_provider_user_id' => 0,
            ));

            return $oToken;
        }

        return FALSE;
    }

    public function GetUserData(PluginAr_ModuleAuthProvider_EntityUserToken $oToken) {

        // Проверяем входящий токен
        if ($oToken->getTokenData() != getRequest('oauth_token', FALSE)) {
            return FALSE;
        }

        $aData = $this->SendRequestToTwitter($this->sPrimaryDataUrl, array(
            "oauth_consumer_key"     => $this->sClientId,
            "oauth_token"            => $oToken->getTokenData(),
            "oauth_signature_method" => "HMAC-SHA1",
            "oauth_timestamp"        => time(),
            "oauth_version"          => "1.0",
            "oauth_nonce"            => time(),
            "oauth_verifier"         => getRequest('oauth_verifier', ''),
        ), $oToken->getTokenDataSecret());

        if ($aData) {

            // Обновим токен
            $oToken->setTokenData($aData->oauth_token);
            $oToken->setTokenDataSecret($aData->oauth_token_secret);

            $oUserData = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityData', array(
                'data_provider_name' => $this->sName,
                'data_login'         => $this->sName . '_' . $aData->user_id,
                'data_name'          => @$aData->screen_name,
                'data_surname'       => '',
                'data_sex'           => '',
                'data_about'         => '',
                'data_page'          => $aData->screen_name,
                'data_birthday'      => '',
                'data_mail'          => '',
                'data_photo'         => '',
            ));


            $aAdditionalData = $this->SendRequestToTwitter(
                $this->sAdditionalDataUrl,
                array(
                    "oauth_consumer_key"     => $this->sClientId,
                    "oauth_nonce"            => time(),
                    "oauth_signature_method" => "HMAC-SHA1",
                    "oauth_timestamp"        => time(),
                    "oauth_token"            => $aData->oauth_token,
                    "oauth_version"          => "1.0",
                    "user_id"                => $aData->user_id,
                ),
                $aData->oauth_token_secret
            );

            if ($aAdditionalData) {
                $aAdditionalData = $aAdditionalData[0];
            }

            $oUserData->setDataPhoto($aAdditionalData->profile_image_url);
            $oUserData->setDataAbout($aAdditionalData->description);


            return $oUserData;
        }


        return FALSE;

    }


}
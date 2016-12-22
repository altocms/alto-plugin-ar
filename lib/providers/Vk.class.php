<?php

require_once __DIR__ . '/../AuthProvider.class.php';

class VkProvider extends AuthProvider {

    public $sName = 'vk';
    public $sAuthUrl = 'https://oauth.vk.com/authorize?client_id=%%client_id%%&scope=%%permissions%%&redirect_uri=%%redirect%%&response_type=code&v=5.23';
    public $sTokenUrl = 'https://oauth.vk.com/access_token?client_id=%%client_id%%&client_secret=%%secret_key%%&code=%%code%%&redirect_uri=%%redirect%%';
    public $sUserInfoUrl = 'https://api.vk.com/method/getProfiles?uid=%%user_id%%&fields=sex,status,domain,bdate,photo_big&access_token=%%access_token%%';

    public $aRepostRights = array(
        AuthProvider::REPOST_RIGHT_WALL     => TRUE, // Репост записей стены, только через javascript
        AuthProvider::REPOST_RIGHT_STATUS   => FALSE, // Репост статуса запрещен, у сайта к такому API нет доступа
        AuthProvider::REPOST_RIGHT_POST     => TRUE, // Репост топиков, запрещен JS идет get-запросом, а там длина ограничена.
        AuthProvider::REPOST_RIGHT_FRIENDS  => TRUE, // Поиск друзей по сайту
    );

    /**
     * Получает идентфикаторы друзей пользователя из социальной сети
     *
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     *
     * @return bool
     */
    public function GetFriendsId($oToken) {

        $this->sUserInfoUrl = $this->EvalUrl(
            'https://api.vk.com/method/friends.get?uid=%%user_id%%&access_token=%%access_token%%',
            array(
                '%%user_id%%'      => $oToken->getTokenProviderUserId(),
                '%%access_token%%' => $oToken->getTokenData())
        );

        $sData = $this->SendRequest($this->sUserInfoUrl, FALSE);
        if (!$sData || !$this->isJson($sData)) {
            return FALSE;
        }
        // Раскодируем
        $aData = json_decode($sData);

        if (isset($aData->response) && $aFriendId = @$aData->response) {
            return $aFriendId;
        }

        return FALSE;
    }


    /**
     * Получение токена пользователя
     *
     * @return bool|PluginAr_ModuleAuthProvider_EntityUserToken
     *
     * @throws Exception
     */
    public function GetUserToken() {

        if (!$aData = $this->LoadTokenData(FALSE)) {
            return FALSE;
        }

        /**
         * Возвратим объект токена
         */
        $oToken = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityUserToken', array(
            'token_provider_name'    => $this->sName,
            'token_data'             => $aData->access_token,
            'token_email'            => @$aData->email,
            'token_expire'           => intval($aData->expires_in),
            'token_provider_user_id' => $aData->user_id,
        ));

        return $oToken;
    }

    /**
     * Получение дополнительных данных пользователя
     *
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     *
     * @throws Exception
     *
     * @return bool|PluginAr_ModuleAuthProvider_EntityData
     */
    public function GetUserData($oToken) {

        if (!$sData = $this->LoadAdditionalData(
            $oToken,
            array(
                '%%user_id%%'      => $oToken->getTokenProviderUserId(),
                '%%access_token%%' => $oToken->getTokenData()),
            FALSE)
        ) {
            return FALSE;
        }

        // Раскодируем
        $aData = json_decode($sData);

        // Сократим путь к данным и проверим его, в смысле путь
        if (!isset($aData->response[0]) || !($oData = @$aData->response[0])) {
            $this->setLastErrorCode(3);

            return FALSE;
        }

        // * Получили дополнительные данные. Заполним профиль из того, что есть
        return Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityData', array(
            'data_provider_name' => $this->sName,
            'data_login'         => $this->sName . '_' . $oToken->getTokenProviderUserId(),
            'data_name'          => @$oData->first_name,
            'data_surname'       => @$oData->last_name,
            'data_sex'           => ((@$oData->sex && $oData->sex > 0) ? ($oData->sex == 1 ? 'woman' : 'man') : 'other'),
            'data_about'         => @$oData->status,
            'data_page'          => @$oData->domain,
            'data_birthday'      => @$oData->bdate ? date('Y-m-d H:i:s', strtotime(@$oData->bdate)) : null,
            'data_mail'          => @$oToken->getTokenEmail(),
            'data_photo'         => @$oData->photo_big,
        ));
    }

    /**
     * Прводит репост записи стены у провайдера.
     *
     * @param string $sStatus
     * @param $sUrl
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     *
     * @return bool
     */
    public function RepostWall($sStatus, $sUrl, $oToken) {
        // У вконтакта репостим только через javascript, поэтому
        // Этот метод есть, но он ни чего не делает. Все в хуке
        // репоста этого плагина
        return FALSE;
    }

    /**
     * Проводит репост топика у провайдера.
     *
     * @param ModuleTopic_EntityTopic $oTopic
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     * @return bool
     */
    public function RepostPost($oTopic, $oToken) {
        // У вконтакта репостим только через javascript, поэтому
        // Этот метод есть, но он ни чего не делает. Все в хуке
        // репоста этого плагина
        return FALSE;
    }

}

// EOF
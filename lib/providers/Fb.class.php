<?php

require_once __DIR__ . "/../AuthProvider.class.php";

class FbProvider extends AuthProvider {

    public $sName = 'fb';
    public $sAuthUrl = 'https://www.facebook.com/dialog/oauth?client_id=%%client_id%%&redirect_uri=%%redirect%%&response_type=code&scope=%%permissions%%';
    public $sTokenUrl = 'https://graph.facebook.com/oauth/access_token?client_id=%%client_id%%&redirect_uri=%%redirect%%&client_secret=%%secret_key%%&code=%%code%%';
    public $sUserInfoUrl = 'https://graph.facebook.com/me?access_token=%%access_token%%';

    public $aRepostRights = array(
        AuthProvider::REPOST_RIGHT_WALL   => TRUE, // Репост записей стены
        AuthProvider::REPOST_RIGHT_STATUS => TRUE, // Репост статуса
        AuthProvider::REPOST_RIGHT_POST   => TRUE, // Репост топиков
        AuthProvider::REPOST_RIGHT_GROUP => TRUE, // Репост топиков
        AuthProvider::REPOST_RIGHT_FRIENDS => TRUE, // Поиск друзей по сайту
    );

    /**
     * Получает идентфикаторы друзей пользователя из социальной сети
     *
     * @param $oToken
     * @return bool|string[]
     */
    public function GetFriendsId($oToken) {
        $sRepostUrl = "https://graph.facebook.com/me/friends?access_token={$oToken->getTokenData()}";

        // Посылаем
        $aData = $this->SendRequest($sRepostUrl, FALSE);

        $oData = json_decode($aData);

        if (isset($oData->data) && $aFriends = @$oData->data) {
            $aFriendId = array();
            if (is_array($aFriends)) {
                foreach ($aFriends as $oFriend) {
                    $aFriendId[] = $oFriend->id;
                }
            }
            return $aFriendId;
        }

        return FALSE;
    }


    /**
     * Публикация топика в группу
     *
     * @param ModuleTopic_EntityTopic                     $oTopic
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     * @return bool
     */
    public function PostInGroup($oTopic, $oToken) {

        $sText = $oTopic->getTitle();
        $sText .= '   ' . Config::Get('path.root.web') . "t/{$oTopic->getId()}";

        $sRepostUrl = "https://graph.facebook.com/{$this->sGroupId}/feed?access_token={$oToken->getTokenData()}&message={$sText}";

        // Посылаем
        $this->SendRequest($sRepostUrl, TRUE);

    }

    /**
     * Репост статуса
     *
     * @param string                                      $sStatus
     * @param                                             $sUrl
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     * @return bool|void
     */
    public function RepostWall($sStatus, $sUrl, $oToken) {

        // Нормализуем статус
        $sStatus = strip_tags($sStatus);
        $sStatus = $this->CropText($sStatus, '...', 420);


        // Посылаем
        $this->SendRequest($this->GetRepostUrl($oToken, $sStatus), TRUE);

    }

    /**
     * Репост статуса
     *
     * @param string                                      $sStatus
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     * @return bool|void
     */
    public function RepostStatus($sStatus, $oToken) {

        // Нормализуем статус
        $sStatus = strip_tags($sStatus);
        $sStatus = $this->CropText($sStatus, '...', 420);


        // Посылаем
        $this->SendRequest($this->GetRepostUrl($oToken, $sStatus), TRUE);

    }

    public function RepostPost($oTopic, $oToken) {
        $sStatus = Engine::getInstance()->User_GetCurrentUserText('topic');
        $sStatus = str_replace('{link}', $oTopic->getTitle(), $sStatus);

        // Посылаем
        $this->SendRequest($this->GetRepostUrl($oToken, $sStatus . '   ' . Config::Get('path.root.web') . "t/{$oTopic->getId()}"), TRUE);

    }

    /**
     * Формируем урл репостинга, у фейсбука с этим проще всего )
     *
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     * @param                                             $sText
     * @return string
     */
    private function GetRepostUrl($oToken, $sText) {
        return "https://graph.facebook.com/{$oToken->getTokenProviderUserId()}/feed?access_token={$oToken->getTokenData()}&message={$sText}";
    }


    /**
     * Получение токена пользователя
     *
     * @return PluginAr_ModuleAuthProvider_EntityUserToken
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
            'token_expire'           => $aData->expires?time()+$aData->expires:0,
            'token_provider_user_id' => 0,
        ));

        return $oToken;
    }

    public function GetUserData(PluginAr_ModuleAuthProvider_EntityUserToken $oToken) {

        if (!$aData = $this->LoadAdditionalData(
            $oToken,
            array(
                '%%public_key%%'   => Config::Get('plugin.ar.providers.od.od_public_key'),
                '%%access_token%%' => $oToken->getTokenData(),
                '%%signature%%'    => md5("application_key=" . Config::Get('plugin.ar.providers.od.od_public_key') . "method=users.getCurrentUser" . md5($oToken->getTokenData() . Config::Get('plugin.ar.providers.od.od_secret_key')))
            ), FALSE)
        ) {
            return FALSE;
        }

        // Раскодируем
        $oData = json_decode($aData);

        /**
         * Получили дополнительные данные. Заполним профиль из того, что есть
         */

        return Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityData', array(
            'data_provider_name' => $this->sName,
            'data_login'         => $this->sName . '_' . $oData->id,
            'data_name'          => @$oData->first_name,
            'data_surname'       => @$oData->last_name,
            'data_sex'           => ((@$oData->gender == 'male') ? 'man' : ($oData->gender == 'female' ? 'woman' : 'other')),
            'data_about'         => @$oData->bio ? @$oData->bio : '',
            'data_page'          => @$oData->id,
            'data_birthday'      => date('Y-m-d H:i:s', strtotime(@$oData->birthday)),
            'data_mail'          => @$oData->email,
            'data_photo'         => "https://graph.facebook.com/{$oData->id}/picture?type=large",
        ));

    }


}
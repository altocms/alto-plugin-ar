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
 * AuthProvider
 * Файл модуля AuthProvider.class.php плагина Ar
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Ar
 * @version     0.0.1 от 30.07.2014 23:55
 */
class PluginAr_ModuleAuthProvider extends ModuleORM {

    /**
     * провайдеры авторизации
     *
     * @var AuthProvider[]
     */
    protected $aProviders = array();

    /**
     * Инициализация модуля
     */
    public function Init() {

        parent::Init();

        foreach (Config::Get('plugin.ar.providers') as $sProviderName => $aProviderData) {
            /** @noinspection PhpIncludeInspection */
            include_once __DIR__ . '/../../../lib/providers/' . ucwords($sProviderName) . '.class.php';

            /** @var string $sProviderClassName Имя класса провайдера */
            $sProviderClassName = ucwords($sProviderName) . 'Provider';

            if (isset($this->aProviders[$sProviderName]))
                continue;

            if (!isset($aProviderData[$sProviderName . '_client_id']) || !$aProviderData[$sProviderName . '_client_id']) {
                continue;
            }

            // Проверим секретный ключ
            if (!isset($aProviderData[$sProviderName . '_secret_key']) || !$aProviderData[$sProviderName . '_secret_key']) {
                continue;
            }

            $this->aProviders[$sProviderName] = new $sProviderClassName(
                $sProviderName,
                $aProviderData,
                Config::Get('plugin.ar.use_curl')
            );

            /**
             * Если была ошибка создания объекта авторизации
             */
            if ($this->aProviders[$sProviderName]->getLastError()) {
                unset($this->aProviders[$sProviderName]);
            }
        }

    }

    /**
     * Есть ли вообще провайдеры для репоста в группы
     * @return bool
     */
    public function HasEnabledRepostInGroupsProviders() {

        // Если не администратор, то нету
        if (!E::IsAdmin()) {
            return FALSE;
        }

        $bResult = FALSE;
        foreach ($this->aProviders as $oProvider) {
            if ($oProvider->sGroupId != FALSE && $oProvider->aRepostRights['group'] == TRUE) {
                $bResult = TRUE;
                break;
            }
        }

        return $bResult;
    }


    /**
     * Получение пользователя по токену
     *
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     * @return bool|ModuleUser_EntityUser
     */
    public function GetUserByToken($oToken) {

        /** @var PluginAr_ModuleAuthProvider_EntityUserToken|array $oResult */
        $oResult = $this->PluginAr_AuthProvider_GetUserTokenItemsByFilter(array(
            'token_provider_user_id' => $oToken->getTokenProviderUserId(),
            'token_provider_name'    => $oToken->getTokenProviderName(),
        ));

        if ($oResult) {
            $oResult = array_shift($oResult);

            // Обновим значение токена, если необходимо
            if ($oResult->getTokenData() != $oToken->getTokenData()) {
                $oResult->setTokenData($oToken->getTokenData());
                $oResult->setTokenExpire($oToken->getTokenExpire());
                $oResult->Update();
            }

            return $oResult->getUser();
        }

        return FALSE;
    }

    /**
     * Получение пользователя по токену
     *
     * @param $sProviderName
     * @internal param \PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     * @return bool|ModuleUser_EntityUser
     */
    public function GetTokensByProviderName($sProviderName) {

        /** @var PluginAr_ModuleAuthProvider_EntityUserToken|array $oResult */
        return $this->PluginAr_AuthProvider_GetUserTokenItemsByFilter(array(
            'token_user_id'       => E::UserId(),
            'token_provider_name' => $sProviderName,
        ));
    }


    /**
     * Получение пользователя по данным токена
     *
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     * @return bool|ModuleUser_EntityUser
     */
    public function GetUserByTokenData($oToken) {

        /** @var array|PluginAr_ModuleAuthProvider_EntityUserToken $oResult */
        $oResult = $this->PluginAr_AuthProvider_GetUserTokenItemsByFilter(array(
            'token_data' => $oToken->getTokenData(),
        ));

        if ($oResult) {
            $oResult = array_shift($oResult);

            // Обновим значение токена, если необходимо
            if ($oResult->getTokenExpire() != $oToken->getTokenExpire()) {
                $oResult->setTokenExpire($oToken->getTokenExpire());
                $oResult->Update();
            }
            return $oResult->getUser();
        }

        return FALSE;
    }

    public function UploadUserImageByUrl($sUrl, $oUser) {

        if ($sFileTmp = $this->Uploader_UploadRemote($sUrl)) {
            if ($sFileUrl = $this->User_UploadAvatar($sFileTmp, $oUser, array())) {
                return $sFileUrl;
            }
        }

        return FALSE;
    }

    /**
     * Берет токен из сессии
     *
     * @return bool|PluginAr_ModuleAuthProvider_EntityUserToken
     */
    public function GetTokenFromSession() {

        $sProviderName = $this->Session_Get('provider_name');
        $sToken = $this->Session_Get($sProviderName . '_token');
        $sTokenSecret = $this->Session_Get($sProviderName . '_token_secret');

        if (!($sProviderName && $sToken && $sTokenSecret)) {
            return FALSE;
        }

        $oToken = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityUserToken', array(
            'token_provider_name'    => $sProviderName,
            'token_data'             => $sToken,
            'token_data_secret'      => $sTokenSecret,
            'token_expire'           => 0,
            'token_provider_user_id' => 0,
        ));

        return $oToken;
    }

    /**
     * Создает и возвращает нового пользователя по данным от провайдера
     *
     * @param PluginAr_ModuleAuthProvider_EntityData $oUserData
     * @return ModuleUser_EntityUser
     */
    public function CreateNewUserByUserData($oUserData) {

        /**
         * Если пользователь авторизован, то только привязываем его к новой соц.сети,
         * но ни как не создаем повторно
         */
        if (E::IsUser()) {
            $aType = array('social');
            $aFields = $this->User_getUserFields($aType);
            foreach ($aFields as $oField) {
                if (Config::Get("plugin.ar.providers.{$oUserData->getDataProviderName()}.name") == $oField->getName()) {
                    $this->User_setUserFieldsValues(E::User()->getId(), array($oField->getId() => $oUserData->getDataPage()), 1);
                    break;
                }
            }

            return TRUE;
        }

        /**
         * Создание новой учетки
         */
        // Логин валидный
        $bError = $this->User_InvalidLogin($oUserData->getDataLogin());
        if ($bError > 0) return 1;
        // И пользователя такого нет
        $bError = $this->User_GetUserByLogin($oUserData->getDataLogin());
        if ($bError) return 2;
        // И емайл, что ни удивительно правильный или пустой
        $bError = F::CheckVal($oUserData->getDataMail(), 'mail');
        if (!$bError) return 3;
        // И его еще нет в базе
        $bError = $this->User_GetUserByMail($oUserData->getDataMail());
        if ($bError) return 4;

        // Вот теперь пользователь правильный, нужно его создать
        $sPassword =  func_generator(10);
        $oUser = Engine::GetEntity('ModuleUser_EntityUser', array(
            'user_login'         => $oUserData->getDataLogin(),
            'user_password'      => $sPassword,
            'user_mail'          => is_null($oUserData->getDataMail()) ? '' : $oUserData->getDataMail(),
            'user_date_register' => date("Y-m-d H:i:s"),
            'user_ip_register'   => func_getIp(),
            'user_activate'      => 1,
            'user_activate_key'  => NULL,
        ));

        // Тут два варианта, либо пользователь сам придумал логин, либо он автоматичский
        // Логин создавался автоматически и содержит идентфикаторы социалок,
        // уберем. что бы не светить ими
        if (($oUserData->getDataLogin() != getRequest('login')) || Config::Get('plugin.ar.auto_login') == TRUE) {
            $oUser->setUserLogin(md5($oUserData->getDataLogin()));
        }

        if ($this->User_Add($oUser)) {

            $sDateActivate = date("Y-m-d H:i:s");
            if (Config::Get('general.reg.activation') == TRUE) {
                $sDateActivate = NULL;
                $oUser->setActivateKey(F::RandomStr());
                $this->Notify_SendRegistrationActivate($oUser, $sPassword);
                $oUser->setActivate(0);
            } else {
                $oUser->setActivate(1);
            }

            $oUser->setUserDateActivate($sDateActivate);
            $oUser->setUserProfileName($oUserData->getDataName() . ' ' . $oUserData->getDataSurname());
            $oUser->setUserProfileSex($oUserData->getDataSex());
            $oUser->setUserProfileBirthday($oUserData->getDataBirthday());
            $oUser->setUserProfileAbout($oUserData->getDataAbout());
            $oUser->setUserSettingsNoticeNewTopic(TRUE);
            $oUser->setUserSettingsNoticeNewComment(TRUE);
            $oUser->setUserSettingsNoticeNewTalk(TRUE);
            $oUser->setUserSettingsNoticeReplyComment(TRUE);
            $oUser->setUserSettingsNoticeNewFriend(TRUE);

            if ($oUserData->getDataPhoto() && $sUserLogoUrl = $this->UploadUserImageByUrl($oUserData->getDataPhoto(), $oUser)) {
                $oUser->setProfileAvatar($sUserLogoUrl);
            }

            $aType = array('social');
            $aFields = $this->User_getUserFields($aType);
            foreach ($aFields as $oField) {
                if (Config::Get("plugin.ar.providers.{$oUserData->getDataProviderName()}.name") == $oField->getName()) {
                    $this->User_setUserFieldsValues($oUser->getId(), array($oField->getId() => $oUserData->getDataPage()), 1);
                    break;
                }
            }

            $this->User_Update($oUser);

            return $oUser;
        }


        return FALSE;
    }

    /**
     * Получение провайдера по его имени
     *
     * @param $sProviderName
     * @return AuthProvider|bool
     */
    public function GetProviderByName($sProviderName) {

        return isset($this->aProviders[$sProviderName]) ? $this->aProviders[$sProviderName] : FALSE;
    }

    /**
     * Возвращает массив провайдеров
     *
     * @return AuthProvider[]
     */
    public function GetProviders() {

        return $this->aProviders;
    }

    public function GetProvidersByTokenIdArray($aToken) {

        $oResult = $this->PluginAr_AuthProvider_GetUserTokenItemsByTokenIdIn($aToken);

        if ($oResult) {
            $aProviders = array();
            foreach ($oResult as $oToken) {
                $oProvider = $this->aProviders[$oToken->getTokenProviderName()];
                $oProvider->setToken($oToken);
                $aProviders[$oToken->getTokenProviderName()] = $oProvider;
            }

            return $aProviders ? $aProviders : FALSE;
        }

        return FALSE;
    }

    /**
     * Добавление в список найденных друзей по данному провайдеру
     *
     * @param ModuleUser_EntityUser[]                     $aFriends
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     */
    public function AddSearchedFriends($aFriends, $oToken) {

        foreach ($aFriends as $oUser) {

            /** @var PluginAr_ModuleAuthProvider_EntitySearchedUser $oSearchedUser */
            $oSearchedUser = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntitySearchedUser', array(
                'searched_user_id'  => $oUser->getId(),
                'searched_token_id' => $oToken->getTokenId(),
                'searched_time'     => time(),
            ));

            $oSearchedUser->Add();
        }
    }

    /**
     * Количество найденных черехз социальные сети друзей
     *
     * @param $iTokenId
     * @return int
     */
    public function GetCountSearchedFriendsByTokenId($iTokenId) {

        $aResult = $this->PluginAr_AuthProvider_GetSearchedUserItemsByFilter(array(
            'searched_token_id' => $iTokenId,
        ));

        if ($aResult) {
            return count($aResult);
        }

        return 0;
    }

}

// EOF
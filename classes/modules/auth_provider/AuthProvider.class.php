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

            // * Если была ошибка создания объекта авторизации
            if ($this->aProviders[$sProviderName]->getLastError()) {
                unset($this->aProviders[$sProviderName]);
            }
        }
    }

    /**
     * Есть ли вообще провайдеры для репоста в группы
     *
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
     *
     * @return bool|ModuleUser_EntityUser
     */
    public function GetUserByToken($oToken) {

        /** @var PluginAr_ModuleAuthProvider_EntityUserToken|array $oResult */
        $oResult = E::Module('PluginAr\AuthProvider')->GetUserTokenItemsByFilter(array(
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
     *
     * @return bool|ModuleUser_EntityUser
     */
    public function GetTokensByProviderName($sProviderName) {

        /** @var PluginAr_ModuleAuthProvider_EntityUserToken|array $oResult */
        return E::Module('PluginAr\AuthProvider')->GetUserTokenItemsByFilter(array(
            'token_user_id'       => E::UserId(),
            'token_provider_name' => $sProviderName,
        ));
    }

    /**
     * Получение пользователя по данным токена
     *
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     *
     * @return bool|ModuleUser_EntityUser
     */
    public function GetUserByTokenData($oToken) {

        /** @var array|PluginAr_ModuleAuthProvider_EntityUserToken $oResult */
        $oResult = E::Module('PluginAr\AuthProvider')->GetUserTokenItemsByFilter(array(
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

    /**
     * @param $sUrl
     * @param $oUser
     *
     * @return bool|string
     */
    public function UploadUserImageByUrl($sUrl, $oUser) {

        if ($sFileTmp = E::Module('Uploader')->UploadRemote($sUrl)) {
            if ($sFileUrl = E::Module('User')->UploadAvatar($sFileTmp, $oUser, array())) {
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

        $sProviderName = E::Module('Session')->Get('provider_name');
        $sToken = E::Module('Session')->Get($sProviderName . '_token');
        $sTokenSecret = E::Module('Session')->Get($sProviderName . '_token_secret');

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
     * Возвращает автоматически сформированный логин пользователя
     *
     * @param PluginAr_ModuleAuthProvider_EntityData $oUserData
     *
     * @return string
     */
    public function GetAutoLogin($oUserData) {

        $sLogin = FALSE;
        if ($oUserData->getDataName() && $oUserData->getDataSurname()) {
            $sLogin = $oUserData->getDataName() . Config::Get('plugin.ar.express_comma') . $oUserData->getDataSurname();
        }
        if ($oUserData->getDataName() && !$oUserData->getDataSurname()) {
            $sLogin = $oUserData->getDataName();
        }
        if (!$oUserData->getDataName() && $oUserData->getDataSurname()) {
            $sLogin = $oUserData->getDataSurname();
        }
        if (!$oUserData->getDataName() && !$oUserData->getDataSurname()) {
            return md5($oUserData->getDataLogin());
        }

        /** @var string $sValidateRegexp Регулярка валидации по логину */
        $sValidateRegexp = '/^['
            . Config::Get('module.user.login.charset') . ']{'
            . Config::Get('module.user.login.min_size') . ','
            . Config::Get('module.user.login.max_size') . '}$/i';

        // Проверим логин на допустимые символы, если нет, то переводим в транслит
        if (!preg_match($sValidateRegexp, $sLogin)) {
            $sLogin = F::Translit($sLogin);
        }
        // Ещё раз, если всё равно нет, то заменим "плохие символы" на подчеркивание
        if (!preg_match($sValidateRegexp, $sLogin)) {
            $sLogin = preg_replace('~([^'. Config::Get('module.user.login.charset') .'])~i', '_', $sLogin);
        }
        // Если, почему-то, всё равно не подходит логин, тогда уходим в стандартную регистрацию
        if (!preg_match($sValidateRegexp, $sLogin)) {
            return md5($oUserData->getDataLogin());
        }
        if (E::Module('User')->GetUserByLogin($sLogin)) {
            return substr(md5($oUserData->getDataLogin()), 0, Config::Get('module.user.login.max_size'));
        }

        return $sLogin;
    }

    /**
     * Создает и возвращает нового пользователя по данным от провайдера
     *
     * @param PluginAr_ModuleAuthProvider_EntityData $oUserData
     *
     * @return ModuleUser_EntityUser|bool|int
     */
    public function CreateNewUserByUserData($oUserData) {

        // * Если пользователь авторизован, то только привязываем его к новой соц.сети,
        // * но ни как не создаем повторно
        if (E::IsUser()) {
            $aType = array('social');
            $aFields = E::Module('User')->getUserFields($aType);
            foreach ($aFields as $oField) {
                if (Config::Get("plugin.ar.providers.{$oUserData->getDataProviderName()}.name") == $oField->getName()) {
                    E::Module('User')->setUserFieldsValues(E::User()->getId(), array($oField->getId() => $oUserData->getDataPage()), 1);
                    break;
                }
            }

            return TRUE;
        }

        // * Создание новой учетки
        // Логин валидный
        $bError = E::Module('User')->InvalidLogin($oUserData->getDataLogin());
        if ($bError > 0) {
            $sErrorMessage = E::ModuleUser()->GetLoginErrorMessage($bError);
            F::SysWarning($sErrorMessage . ' (login:' . $oUserData->getDataLogin() . ')');
            return 1;
        }
        // И пользователя такого нет
        $bError = E::Module('User')->GetUserByLogin($oUserData->getDataLogin());
        if ($bError) {
            return 2;
        }
        // И емайл, что ни удивительно правильный или пустой
        $bError = F::CheckVal($oUserData->getDataMail(), 'mail');
        if (!$bError) {
            return 3;
        }
        // И его еще нет в базе
        $bError = E::Module('User')->GetUserByMail($oUserData->getDataMail());
        if ($bError) {
            return 4;
        }

        // Вот теперь пользователь правильный, нужно его создать
        $sPassword = F::RandomStr(10);
        /** @var ModuleUser_EntityUser $oUser */
        $oUser = Engine::GetEntity('ModuleUser_EntityUser', array(
            'user_login'         => $oUserData->getDataLogin(),
            'user_password'      => $sPassword,
            'user_mail'          => is_null($oUserData->getDataMail()) ? '' : $oUserData->getDataMail(),
            'user_date_register' => date("Y-m-d H:i:s"),
            'user_ip_register'   => F::GetUserIp(),
            'user_activate'      => 1,
            'user_activate_key'  => NULL,
        ));

        $oUser->setUserLogin($oUserData->getDataLogin());

        if (E::Module('User')->Add($oUser)) {

            $sDateActivate = F::Now();
            if (Config::Get('general.reg.activation') == TRUE && !Config::Get('plugin.ar.express')) {
                $sDateActivate = NULL;
                $oUser->setActivationKey(F::RandomStr());
                E::Module('Notify')->SendRegistrationActivate($oUser, $sPassword);
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
            $aFields = E::Module('User')->getUserFields($aType);
            foreach ($aFields as $oField) {
                if (Config::Get("plugin.ar.providers.{$oUserData->getDataProviderName()}.name") == $oField->getName()) {
                    E::Module('User')->setUserFieldsValues($oUser->getId(), array($oField->getId() => $oUserData->getDataPage()), 1);
                    break;
                }
            }

            E::Module('User')->Update($oUser);

            return $oUser;
        }

        return FALSE;
    }

    /**
     * Получение провайдера по его имени
     *
     * @param $sProviderName
     *
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

    /**
     * @param $aToken
     *
     * @return array|bool
     */
    public function GetProvidersByTokenIdArray($aToken) {

        $oResult = E::Module('PluginAr\AuthProvider')->GetUserTokenItemsByTokenIdIn($aToken);

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
     *
     * @return int
     */
    public function GetCountSearchedFriendsByTokenId($iTokenId) {

        $aResult = E::Module('PluginAr\AuthProvider')->GetSearchedUserItemsByFilter(array(
            'searched_token_id' => $iTokenId,
        ));

        if ($aResult) {
            return count($aResult);
        }

        return 0;
    }

}

// EOF
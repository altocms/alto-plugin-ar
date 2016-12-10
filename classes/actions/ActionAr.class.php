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
 * ActionAr.class.php
 * Файл экшена плагина Ar
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Ar
 *
 * @method PluginAr_AuthProvider_GetProviderByName
 * @method User_IsAuthorization
 * @method PluginAr_AuthProvider_GetUserByToken
 * @method Session_Get
 * @method Session_Set
 * @method Message_AddErrorSingle
 * @method Lang_Get
 * @method User_Authorization
 * @method PluginAr_AuthProvider_CreateNewUserByUserData
 * @method PluginAr_AuthProvider_GetTokenFromSession
 * @method Session_Drop
 * @method PluginAr_AuthProvider_GetUserByTokenData
 * @method Viewer_SetResponseAjax
 * @method User_GetCurrentUserTokenById
 * @method Message_AddNoticeSingle
 * @method PluginAr_AuthProvider_GetProvidersByTokenIdArray
 * @method User_GetCurrentUserRepostSettingsByType
 * @method Topic_GetTopicById
 */
class PluginAr_ActionAr extends Action {

    /**
     * Инициализация экшена
     */
    public function Init() {

//        // Только авторизованные пользователи рабоатбт с этим экшеном
//        if (E::Module('User')->IsAuthorization()) {
//            return $this->_NotFound();
//        }
//
//        return TRUE;
    }

    /**
     * Регистрация евентов
     */
    protected function RegisterEvent() {

        // Перенаправим продолжение авторизации в класс провайдера
        foreach (Config::Get('plugin.ar.providers') as $sProviderName => $aProviderData) {
            $this->AddEvent($sProviderName, 'EventAuth');
            if (isset($aProviderData['has-receiver']) && $aProviderData['has-receiver']) {
                $this->AddEvent($sProviderName . '-receiver', 'EventReceiver');
            }
        }

        // Пустая страница-заглушка
        $this->AddEvent('about', 'EventAuthAbout');

        // Подтверждение регистрации
        $this->AddEvent('confirm', 'EventConfirm');

        // Инициализация работы по первому протоколу
        $this->AddEvent('1', 'EventAuthVersion1Init');

        // Инициализация работы по первому протоколу
        $this->AddEvent('remove', 'EventRemoveSocial');

        // Инициализация работы по первому протоколу
        $this->AddEvent('toggle', 'EventToggleRepost');

        // Инициализация работы по первому протоколу
        $this->AddEvent('text', 'EventText');

        // Постинг от имени администатора в группу
        $this->AddEvent('repost', 'EventRepostInGroup');

        // Поиск друзей из социальных сетей по сайту
        $this->AddEvent('find', 'EventFindFriends');
    }


    /////////////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////      ОСНОВНЫЕ МЕТОДЫ КЛАССА      /////////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Обработка добавления в друзья
     *
     * @param  ModuleUser_EntityUser $oUser
     *
     * @return bool
     */
    protected function SubmitAddFriend($oUser) {

        // Ограничения на добавления в друзья, т.к. приглашение отправляется в личку, то и ограничиваем по ней
        if (!$this->ACL_CanSendTalkTime(E::User())) return FALSE;

        // Создаем связь с другом
        $oFriendNew = Engine::GetEntity('User_Friend');
        $oFriendNew->setUserTo($oUser->getId());
        $oFriendNew->setUserFrom(E::UserId());

        // Добавляем заявку в друзья
        $oFriendNew->setStatusFrom(ModuleUser::USER_FRIEND_OFFER);
        $oFriendNew->setStatusTo(ModuleUser::USER_FRIEND_NULL);

        // Добавляем друга
        if (E::Module('User')->AddFriend($oFriendNew)) {

            // Сформируем заголовок письма
            $sTitle = E::Module('Lang')->Get(
                'user_friend_offer_title',
                array(
                    'login'  => E::User()->getLogin(),
                    'friend' => $oUser->getLogin()
                )
            );

            // Сделаем хитрый-хитрый код
            F::IncludeLib('XXTEA/encrypt.php');
            $sCode = E::UserId() . '_' . $oUser->getId();
            $sCode = rawurlencode(base64_encode(xxtea_encrypt($sCode, Config::Get('module.talk.encrypt'))));

            // Сам текст письма
            $sText = E::Module('Lang')->Get(
                'user_friend_offer_text',
                array(
                    'login'       => E::User()->getLogin(),
                    'accept_path' => Router::GetPath('profile') . 'friendoffer/accept/?code=' . $sCode,
                    'reject_path' => Router::GetPath('profile') . 'friendoffer/reject/?code=' . $sCode,
                    'user_text'   => ''
                )
            );

            // Отправим по внутренней почте
            /** @var ModuleTalk_EntityTalk $oTalk */
            $oTalk = E::Module('Talk')->SendTalk($sTitle, $sText, E::User(), array($oUser), FALSE, FALSE);

            // По почте тоже отправим
            E::Module('Notify')->SendUserFriendNew($oUser, E::User(), '', Router::GetPath('talk') . 'read/' . $oTalk->getId() . '/');

            // Удаляем отправляющего юзера из переписки
            E::Module('Talk')->DeleteTalkUserByArray($oTalk->getId(), E::UserId());

            // Подписываемся на запрашивающего дружбу
            E::Module('Stream')->SubscribeUser(E::UserId(), $oUser->getId());

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Добавляем друзей по массиву
     *
     * @param ModuleUser_EntityUser[] $aFriendsId
     * @param AuthProvider            $oProvider
     *
     * @return \ModuleUser_EntityUser[]
     */
    private function AddFriends($aFriendsId, $oProvider) {

        /** @var ModuleUser_EntityUser[] $aNewFriends Массив новых друзей */
        $aNewFriends = array();

        foreach ($aFriendsId as $iNewFriendSocialId) {

            // а это кто ещё такой?
            /** @var ModuleUser_EntityUser $oUser */
            if (!$oUser = E::Module('User')->GetUserBySocialId($iNewFriendSocialId, $oProvider)) continue;

            // Себя не добавляем
            if ($oUser->getId() == E::UserId()) continue;

            // В каких мы с ним отношениях?
            if (!$oFriendState = E::Module('User')->GetFriend($oUser->getId(), E::UserId())) {
                if ($bResult = $this->SubmitAddFriend($oUser)) {
                    $aNewFriends[] = $oUser;
                }
            }

        }

        return $aNewFriends;
    }

    /**
     * Установка тектовок
     */
    protected function EventFindFriends() {

        E::Module('Viewer')->SetResponseAjax('json');

        if (!E::IsUser()) {
            E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.ar.friends_find_error'));

            return FALSE;
        }

        // Параметры репоста
        $sTokenId = F::GetRequest('token_id', FALSE);


        /** @var PluginAr_ModuleAuthProvider_EntityUserToken $oToken */
        if ($oToken = E::Module('User')->GetCurrentUserTokenById($sTokenId)) {

            // Ищем только по своему токену
            if ($oToken->getTokenUserId() != E::UserId()) {
                E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.ar.friends_find_error'));

                return FALSE;
            }

            // а не слишком ли часто пользователь друзей ищет
            $aResult = E::Module('PluginAr\AuthProvider')->GetSearchedUserItemsByFilter(array(
                'searched_token_id' => $sTokenId,
                '#order' => array('searched_time' => 'desc'),
                '#limit' => 1,
            ));

            if ($aResult) {
                $oSearchLast = array_shift($aResult);
                if ($oSearchLast->getSearchedTime() + Config::Get('plugin.ar.friends_search_limit') > time() ) {
                    E::Module('Message')->AddNoticeSingle(E::Module('Lang')->Get('plugin.ar.friends_find_no', array('social_name', E::Module('Lang')->Get('plugin.ar.' . $oToken->getTokenProviderName()))));

                    return FALSE;
                }
            }

            // Поищем и, наверное даже найдем
            $aProviders = E::Module('PluginAr\AuthProvider')->GetProvidersByTokenIdArray(array($oToken->getTokenId()));
            if ($aProviders) {
                /** @var AuthProvider $oProvider */
                $oProvider = array_shift($aProviders);
                // Ищем друзей
                $aFriendsId = $oProvider->GetFriendsId($oToken);
                // Если нашли
                if ($aFriendsId) {

                    /** @var ModuleUser_EntityUser[] $aNewFriends */
                    $aNewFriends = $this->AddFriends($aFriendsId, $oProvider);

                    E::Module('PluginAr\AuthProvider')->AddSearchedFriends($aNewFriends, $oToken);

                    // Сколько уже было приглашенных?
                    /** @var array|PluginAr_ModuleAuthProvider_EntityUserToken $oResult */
                    $iFriendsCount = E::Module('PluginAr\AuthProvider')->GetCountSearchedFriendsByTokenId($oToken->getTokenId());
                    $iFriendsCount += count($aNewFriends);

                    // Определим, что показываем ползователю
                    if (count($aNewFriends)>0) {
                        E::Module('Message')->AddNoticeSingle(E::Module('Lang')->Get('plugin.ar.friends_find', array('social_name', E::Module('Lang')->Get('plugin.ar.' . $oToken->getTokenProviderName()))));
                    } else {
                        E::Module('Message')->AddNoticeSingle(E::Module('Lang')->Get('plugin.ar.friends_find_no', array('social_name', E::Module('Lang')->Get('plugin.ar.' . $oToken->getTokenProviderName()))));
                    }

                    E::Module('Viewer')->AssignAjax('count', $iFriendsCount);

                    return FALSE;
                }

                E::Module('Message')->AddNoticeSingle(E::Module('Lang')->Get('plugin.ar.friends_find_no', array('social_name', E::Module('Lang')->Get('plugin.ar.' . $oToken->getTokenProviderName()))));
            }

            E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.ar.friends_find_error'));
        }

        return FALSE;
    }


    /**
     * Репостинг топика в группу (Только администратор!!!)
     *
     * @return bool
     */
    protected function EventRepostInGroup() {

        E::Module('Viewer')->SetResponseAjax('json');

        if (!(E::IsUser() && E::IsAdmin())) {
            E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.ar.repost_error_in_group'));

            return FALSE;
        }

        // Параметры репоста
        $sTopicId = F::GetRequest('topic_id', FALSE);
        /** @var ModuleTopic_EntityTopic $oTopic */
        $oTopic = E::Module('Topic')->GetTopicById($sTopicId);

        if (!$oTopic) {
            E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.ar.repost_error_in_group'));

            return FALSE;
        }


        /** @var AuthProvider $oProvider */
        if ($oProviders = E::Module('PluginAr\AuthProvider')->GetProviders()) {

            // Провайдеров для репоста статуса получили, теперь дело за малым -
            // публикнуть по всем провайдерам
            foreach ($oProviders as $oProvider) {
                if ($oProvider->aRepostRights['group'] == TRUE) {
                    $aToken = E::Module('PluginAr\AuthProvider')->GetTokensByProviderName($oProvider->sName);
                    if ($aToken) {
                        foreach ($aToken as $oToken) {
                            $oProvider->PostInGroup($oTopic, $oToken);
                        }
                    }
                }
            }

        }

        E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.ar.repost_in_group_good'));

        return FALSE;
    }

    /**
     * Установка тектовок
     */
    protected function EventText() {

        E::Module('Viewer')->SetResponseAjax('json');

        if (!E::IsUser()) {
            E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.ar.repost_error_text'));

            return FALSE;
        }

        // Параметры репоста
        $sType = F::GetRequest('text_type', FALSE);
        $sVal = F::GetRequest('text_val', FALSE);

        if (!in_array($sType, array('topic'))) {
            $sType = 'topic';
        }

        // Проверяем их
        if (!($sType && $sVal)) {
            E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.ar.repost_error_text'));

            return FALSE;
        }

        if (mb_strlen($sVal, 'utf-8')< 2) {
            E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.ar.repost_error_text_short'));

            return FALSE;
        }

        if (mb_strpos($sVal, '{link}') === false) {
            E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.ar.repost_error_text_no'));

            return FALSE;
        }

        if (E::Module('User')->SetCurrentUserText($sType, $sVal)) {
            E::Module('Message')->AddNoticeSingle(E::Module('Lang')->Get('plugin.ar.repost_text_good'));
            return TRUE;
        };

        E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.ar.repost_error'));

        return FALSE;
    }

    /**
     * Переключение параметров репоста
     */
    protected function EventToggleRepost() {

        E::Module('Viewer')->SetResponseAjax('json');

        if (!E::IsUser()) {
            E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.ar.repost_error'));

            return FALSE;
        }

        // Параметры репоста
        $sTokenProviderId = F::GetRequest('id', FALSE);
        $sRepostType = F::GetRequest('type', FALSE);

        // Проверяем их
        if (!($sTokenProviderId && $sRepostType)) {
            E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.ar.repost_error'));

            return FALSE;
        }

        if (E::Module('User')->ToggleRepostSetting($sTokenProviderId, $sRepostType)) {
            E::Module('Message')->AddNoticeSingle(E::Module('Lang')->Get('plugin.ar.repost_good'));
            return TRUE;
        };

        E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.ar.repost_error'));

        return FALSE;
    }

    /**
     * Удаление связи с социальной сетью
     */
    protected function EventRemoveSocial() {

        E::Module('Viewer')->SetResponseAjax('json');

        $iTokenId = F::GetRequest('token_id', FALSE);

        /** @var PluginAr_ModuleAuthProvider_EntityUserToken $oToken */
        if ($oToken = E::Module('User')->GetCurrentUserTokenById($iTokenId)) {
            // Удалим настройки по токену
            E::Module('User')->RemoveCurrentUserSettingsByTokenId($iTokenId);
            // А затем и сам токен
            $oToken->Delete();
            E::Module('Viewer')->AssignAjax('sid', '.' . 'sid-' . $oToken->getTokenProviderName());
            E::Module('Message')->AddNoticeSingle(E::Module('Lang')->Get('plugin.ar.removed', array('social_name', E::Module('Lang')->Get('plugin.ar.' . $oToken->getTokenProviderName()))));

        } else {
            E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.ar.not_removed'));
        }
    }

    /**
     * Пустая страница
     * Некотоые соц.сети, например одноклассники отображают весь сайт у себя как внешнее приложение
     * Можно таким сется указать путь к этой пустышке, если очень хочется
     */
    protected function EventAuthAbout() {

    }

    /**
     * Продолжение авторизации пользователя
     * Сюда попадаем из соцсети и определемся по протоколу куда дальше
     * по первой версии или по второй
     */
    protected function EventAuth() {

        /** @var string $sProviderName Наименование провайдера авторизации */
        $sProviderName = Router::GetActionEvent();

        /** @var AuthProvider $oProvider Текущий провайдер */
        if (!($sProviderName && $oProvider = E::Module('PluginAr\AuthProvider')->GetProviderByName($sProviderName))) {
            return $this->_NotFound();
        }

        /**
         * Здесь провайдер определён, получим данные от него
         */
        if ($oProvider->iVersion == 1) {
            $this->EventAuth1($oProvider);
        }
        if ($oProvider->iVersion == 2) {
            $this->EventAuth2($oProvider);
        }

        return TRUE;
    }

    /**
     * Обрабатываем провайдера первой версии.
     * Нужно сначала рассчитать хитрый токен, а потом перевети пользователя на страницу соц.сети
     *
     * @return bool
     */
    protected function EventAuthVersion1Init() {

        /** @var string $sProviderName Наименование провайдера авторизации */
        $sProviderName = Router::GetParam('0');

        /** @var AuthProvider $oProvider Текущий провайдер */
        if (!($sProviderName && $oProvider = E::Module('PluginAr\AuthProvider')->GetProviderByName($sProviderName))) {
            return $this->_NotFound();
        }

        /** @var PluginAr_ModuleAuthProvider_EntityUserToken $oToken */
        $oToken = $oProvider->GetUserToken();
        if (!$oToken) {
            return $this->_NotFound();
        }


        if ($oToken->getTokenProviderUserId() && $oUser = E::Module('PluginAr\AuthProvider')->GetUserByToken($oToken)) {
            $this->_AuthUser($oUser);
            $sReturnPath = E::Module('Session')->Get('return_path');
            Router::Location($sReturnPath ? $sReturnPath : '');

        } else {
            E::Module('Session')->Set('tw_token', $oToken->getTokenData());
            E::Module('Session')->Set('tw_token_secret', $oToken->getTokenDataSecret());
            E::Module('Session')->Set('provider_name', $oToken->getTokenProviderName());
            $oProvider->sUserInfoUrl = $oProvider->EvalUrl($oProvider->sUserInfoUrl, array('%%token%%' => $oToken->getTokenData()));
            Router::Location($oProvider->sUserInfoUrl);
        }

        return FALSE;
    }

    /**
     * Авторизация по протоколу OAuth1.
     * Изначально данные всех токенов мы сохранили в сессию пользователя и после
     * того, как социалка переведет пользвателя назад, нужно поднять эти данные,
     * проверить их и получить уже, наконеч, инфу об этом пользователе
     *
     * @param AuthProvider $oProvider
     */
    protected function EventAuth1($oProvider) {

        $sReturnPath = E::Module('Session')->Get('return_path');

        // Токен есть
        /** @var PluginAr_ModuleAuthProvider_EntityUserToken $oToken */
        $oToken = E::Module('PluginAr\AuthProvider')->GetTokenFromSession();
        if (!$oToken) {
            Router::Location($sReturnPath ? $sReturnPath : '');

            return;
        }

        // Провайдер есть
        if (!$oProviderTmp = E::Module('PluginAr\AuthProvider')->GetProviderByName($oToken->getTokenProviderName())) {
            Router::Location($sReturnPath ? $sReturnPath : '');

            return;
        }

        // И именно тот, с которого начинали )
        if ($oProvider->sName != $oProviderTmp->sName) {
            Router::Location($sReturnPath ? $sReturnPath : '');

            return;
        }

        // Получим доп данные о пользователе и закончим регистрацию
        $this->UserData($oProvider, $oToken);

        return;
    }

    /**
     * Авторизация по протоколу OAuth2
     * Здесь пользователь по ссылке ушел с нашего сайта, соцсеть авторизовала его
     * и вернула на эту страниу. Тут получим токен, данные и уйдем в регистрацию
     *
     * @param AuthProvider $oProvider
     *
     * @return bool
     */
    protected function EventAuth2($oProvider) {

        // Куда возвращаемся?
        $sReturnPath = E::Module('Session')->Get('return_path');

        /** @var PluginAr_ModuleAuthProvider_EntityUserToken $oToken */
        $oToken = $oProvider->getToken();
        if (!$oToken || !@$oToken->getTokenData()) {
//            return $this->_NotFound();
            // Пользователь отказался (
            Router::Location($sReturnPath ? $sReturnPath : '');

            return TRUE;
        }

        // Если пользователь есть, авторизуем его и уходим. Но здесь может быть два варианта:
        // Если ид. пользователя отдается с токеном, то второй запрос не формируем, для проверки
        // пользователя хватит и одного. Если же ид. не получили, например от одноклассников, то
        // здесь считаем, что пользователя нет и проверку на его наличие будем делать только
        // после получения полных данных от социальной сети

        // Сначала ищем пользователя по токену
        if ($oUserFindByToken = E::Module('PluginAr\AuthProvider')->GetUserByTokenData($oToken)) {
            $this->_AuthUser($oUserFindByToken);

        } // Теперь по идентификатору пользователя, который может быть в токене
        elseif ($oToken->getTokenProviderUserId() && $oUser = E::Module('PluginAr\AuthProvider')->GetUserByToken($oToken)) {
            // Вот и всё
            $this->_AuthUser($oUser);

        } else {
            // Пользователь первый раз авторизуется на нашем сайте и его необходимо создать
            // Или в токене не было ссылок ид пользователя для поиска.
            $this->UserData($oProvider, $oToken);

        }

        Router::Location($this->_ReturnPath());

        return TRUE;
    }

    /**
     * Получение данных о пользователе, проверка их и авторизация либо
     * создание и авторизация пользователя
     *
     * @param AuthProvider $oProvider
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     */
    protected function UserData($oProvider, $oToken) {

        $sReturnPath = E::Module('Session')->Get('return_path');

        $oUserData = $oProvider->GetUserData($oToken);

        // Обновим токен и поищем пользователя
        if ($oUserData && !$oToken->getTokenProviderUserId()) {
            $oToken->setTokenProviderUserId(str_replace($oUserData->getDataProviderName() . '_', '', $oUserData->getDataLogin()));

            if ($oUser = E::Module('PluginAr\AuthProvider')->GetUserByToken($oToken)) {
                $this->_AuthUser($oUser);

                Router::Location($sReturnPath ? $sReturnPath : '');

                return;
            }
        }

        // А вот здесь может быть двоякая ситуация
        // С одной стороны пользователь может регистрироваться заново, а может
        // у него уже есть учетка и он просто хоче войти на сайт с соц.сети, поскольку
        // Пароль набирать ему в лом. Поэтому переведем пользователя на страницу, где он
        // сам решит что нужно сделать с полученными от провайдера данными. Создать нового
        // пользователя или не стоит. Здесь уложим в сессию токен и пришедшие данные и пойдем
        // на страницу решения этой проблемы
        if (E::User()) {
            E::Module('PluginAr\AuthProvider')->CreateNewUserByUserData($oUserData);
            $oToken->setTokenUserId(E::User()->getId());
            $oToken->Add();
        } else {
            if ($oUserData && $oToken) {

                $sUserData = serialize($oUserData);
                $sTokenData = serialize($oToken);

                E::Module('Session')->Set('sUserData', $sUserData);
                E::Module('Session')->Set('sTokenData', $sTokenData);

                Router::Location('auth/confirm');

                return;

            }
        }

        Router::Location($this->_ReturnPath());
    }

    /**
     * Стандартная регистрация с запросом данных у пользователя
     *
     * @param PluginAr_ModuleAuthProvider_EntityData $oUserData
     * @param PluginAr_ModuleAuthProvider_EntityUserToken$oToken
     *
     * @return bool
     */
    protected function StandardRegistration($oUserData, $oToken) {

        // Нажата кнопка создания пользователя
        if (getRequest('create-new-user', FALSE)) {

            $oUserData->setDataMail(getRequest('mail', FALSE));
            if (Config::Get('plugin.ar.auto_login') == FALSE && Config::Get('plugin.ar.express') == FALSE){
                $oUserData->setDataLogin(getRequest('login', FALSE));
            } else {
                $sLogin = E::Module('PluginAr\AuthProvider')->GetAutoLogin($oUserData);
                $oUserData->setDataLogin($sLogin);
            }

            /** @var ModuleUser_EntityUser|int $oUser */
            if (($oUser = E::Module('PluginAr\AuthProvider')->CreateNewUserByUserData($oUserData)) && !is_int($oUser)) {
                $oToken->setTokenUserId($oUser->getId());
                $oToken->Add();
                $this->_AuthUser($oUser);
            } else {
                if (is_int($oUser)) {

                    $sErrorText = E::Module('Lang')->Get('plugin.ar.error_create_user_' . $oUser) . '<br>';
                    switch ($oUser) {
                        case 1: $sErrorText .= "Login: {$oUserData->getDataLogin()}"; break;
                        case 2: $sErrorText .= "Login: {$oUserData->getDataLogin()}"; break;
                        case 3: $sErrorText .= "Email: {$oUserData->getDataMail()}"; break;
                        case 4: $sErrorText .= "Email: {$oUserData->getDataMail()}"; break;
                    }

                    E::Module('Message')->AddErrorSingle($sErrorText, NULL, TRUE);
                } else {
                    E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.ar.error_create_user'), NULL, TRUE);
                }

            }

            E::Module('Session')->Drop('sUserData');
            E::Module('Session')->Drop('sTokenData');

            Router::Location($this->_ReturnPath());

            return FALSE;
        }

        // Страничка с подтверждением регистрации
        $this->SetTemplateAction('confirm.tpl');

        $_REQUEST['login'] = $oUserData->getDataLogin();
        $_REQUEST['email'] = $oUserData->getDataMail();
        $_REQUEST['return_path'] = $this->_ReturnPath();

        return FALSE;
    }

    /**
     * Экспресс-регистрация
     *
     * @param PluginAr_ModuleAuthProvider_EntityData $oUserData
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken
     *
     * @return bool
     */
    protected function ExpressRegistration($oUserData, $oToken) {

        /**
         * Проверим логин
         * Логика похожая. Если логин не валиден - уходим на страницу стандартной регистрации
         * иначе проверяем на присутствие в бд. Если есть, то уходим.
         */
        /** @var string $sLogin Логин пользователя */
        $sLogin = E::Module('PluginAr\AuthProvider')->GetAutoLogin($oUserData);
        $oUserData->setDataLogin($sLogin);

        /**
         * Проверим email
         * Логика такая: если email по каким-то параметрам не подходит, то переходим
         * на процедуру стандартной регистрации пользователя
         */
        // Его просто нет
        /** @var string $sEmail */
        if (!($sEmail = $oUserData->getDataMail())) {
            return $this->StandardRegistration($oUserData, $oToken);
        }

        // Он НЕ валидный
        if (!F::CheckVal($sEmail, 'mail')) {
            return $this->StandardRegistration($oUserData, $oToken);
        }

        // Уже занят?
        if (E::Module('User')->GetUserByMail($sEmail)) {
            return $this->StandardRegistration($oUserData, $oToken);
        }


        // Вот здесь у нас есть валидные логин и email. Будем создаваться
        /** @var ModuleUser_EntityUser|int $oUser */
        if (($oUser = E::Module('PluginAr\AuthProvider')->CreateNewUserByUserData($oUserData)) && !is_int($oUser)) {
            $oToken->setTokenUserId($oUser->getId());
            $oToken->Add();
            $this->_AuthUser($oUser);
        } else {
            if (is_int($oUser)) {
                E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.ar.error_create_user_' . $oUser), NULL, TRUE);
            } else {
                E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('plugin.ar.error_create_user'), NULL, TRUE);
            }

        }

        E::Module('Session')->Drop('sUserData');
        E::Module('Session')->Drop('sTokenData');

        Router::Location($this->_ReturnPath());

        return FALSE;
    }

    /**
     * Страница подтверждения регистрации
     *
     * @return bool|string
     */
    protected function EventConfirm() {

        $sUserData = E::Module('Session')->Get('sUserData');
        $sTokenData = E::Module('Session')->Get('sTokenData');

        if ($sUserData && $sTokenData) {

            /** @var PluginAr_ModuleAuthProvider_EntityData $oUserData */
            $oUserData = unserialize($sUserData);
            /** @var PluginAr_ModuleAuthProvider_EntityUserToken $oToken */
            $oToken = unserialize($sTokenData);

            if (Config::Get('plugin.ar.express') && !getRequest('create-new-user', FALSE)) {
                return $this->ExpressRegistration($oUserData, $oToken);
            } else {
                return $this->StandardRegistration($oUserData, $oToken);
            }
        }

        E::Module('Session')->Drop('sUserData');
        E::Module('Session')->Drop('sTokenData');

        return Router::Action('error');
    }


    protected function EventReceiver() {

        $this->SetTemplateAction(Router::GetActionEvent() . '.tpl');
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////      ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ КЛАССА      /////////////////////////////////
    /////////////////////////////////////////////////////////////////////////////////////////////////////

    private function _ReturnPath() {

        $sReturnPath = E::Module('Session')->Get('return_path');

        return $sReturnPath ? $sReturnPath : '';
    }

    /**
     * Экстренное завершение экшена
     *
     * @return string
     */
    private function  _NotFound() {

        E::Module('Message')->AddErrorSingle(E::Module('Lang')->Get('not_access'), E::Module('Lang')->Get('error'));

        return Router::Action('error');
    }

    /**
     * Авторизация пользователя
     *
     * @param ModuleUser_EntityUser $oUser
     *
     * @return string
     */
    private function _AuthUser(ModuleUser_EntityUser $oUser) {

        if (Config::Get('general.reg.activation') == TRUE && !Config::Get('plugin.ar.express')) {
            // Нужна активация пользователя
            if (!E::User() && $oUser && $oUser->getUserActivate()) {
                E::Module('User')->Authorization($oUser);
                Router::Location($this->_ReturnPath());
            } else {
                Router::Location('registration/confirm');
            }
        } else {
            // Не нужна активация
            if (!E::User() && $oUser) {
                E::Module('User')->Authorization($oUser);
                Router::Location($this->_ReturnPath());
            }
        }
    }

}

// EOF
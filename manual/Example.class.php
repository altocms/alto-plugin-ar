<?php

// Защита от случайного выполнения файла.
// В реализации своего провайдера этой строки быть не должно.
// Если вы не собираетесь разрабатывать класс нового провайдера - удалите этот файл
die();

require_once __DIR__ . "/../AuthProvider.class.php";

class ExampleProvider extends AuthProvider {

    /*************************************************************************************************
     *************************************************************************************************
     *
     *  ОСНОВНЫЕ МЕТОДЫ И СВОЙСТВА, КОТОРЫЕ НУЖНО РЕАЛИЗОВАТЬ ДЛЯ АВТОРИЗАЦИИ ЧЕРЕЗ ПРОВАЙДЕРА
     *
     *************************************************************************************************
     *************************************************************************************************/

    /**
     * Имя провайдера.
     * Используется в качестве ключа массива доступных провайдеров. Также, в файле
     * конфига *common/plugins/ar/config/config.php* указываются настройки для этого
     * провайдера в виде массива:
     * ```php
     * // Параметры авторизации через Vkontakte
     * ...
     *  'example' => array(
     *                  'name'           => 'example',        // Имя провайдера
     *                  'version'        => 2,                // Версия OAuth протокола
     *                  'example_client_id'   => '',          // Ид. приложения - берётся из настроек приложения
     *                  'example_secret_key'  => '',          // Секретный ключ - берётся из настроек приложения
     *                  'example_permissions' => array(       // Массив прав так, как он указан в мануале у провайдера
     *                                          'friends',
     *                                          'photos',
     *                                          'wall',
     *                                          'offline',
     *                                          'status',
     *                                      ),
     *              ),
     *  ...
     * ```
     *
     * Кроме этого, картнка провайдера, которая должна лежать в
     * папке *common/plugins/ar/templates/skin/default/assets/img* и назваться как
     * *example.png*
     *
     * Название класса, также содержит это значение как *ExampleProvider*
     *
     * Значение содержится и в имени файла класса - *Example.class.php*
     *
     * @var string
     */
    public $sName = 'example';


    /**
     * Урл запроса на авторизацию.
     *
     * По этому урл возвращается код, который нужен для получения токена авторизации
     *
     * Берется из описания API провайдера. В строке урл содержатся спциальные значения параметров,
     * которые подставляются автоматически родительским классом. Это параметры:
     *  - %%redirect%% - урл редиректа с сайта провайдера, обычно это параметр redirect_uri
     *  - %%permissions%% - набор прав, запрашиваемый от провайдера, обычно это параметр scope
     *  - %%client_id%% - идентификатор приложения, обычно это параметр client_id
     *
     * Пример для провайдера МойМир:
     *  'https://connect.mail.ru/oauth/authorize?client_id=%%client_id%%&response_type=code&redirect_uri=%%redirect%%&scope=%%permissions%%';
     *
     * @var string
     */
    public $sAuthUrl = '';

    /**
     * Урл получения токена авторизации
     *
     * Если пользователь согласился на условия авторизации, то по этому урл будет
     * запрашиваться секретный токен для операций у провайдера от имени пользователя
     * например для отправки репоста.
     *
     * В строке урл содержатся спциальные значения параметров, которые подставляются
     * автоматически родительским классом. Это параметры:
     *  - %%code%% - Полученый ранее из урла $sAuthUrl код,
     *  - %%client_id%% - идентификатор приложения, обычно это параметр client_id
     *  - %%client_secret%% - Секретный код приложения, обычно это параметр secret_key
     *  - %%redirect%% - урл редиректа с сайта провайдера, обычно это параметр redirect_uri
     *
     * Пример для провайдера Github
     *  'https://example.com/login/oauth/access_token?code=%%code%%&client_id=%%client_id%%&client_secret=%%secret_key%%&redirect_uri=%%redirect%%';
     *
     * @var string
     */
    public $sTokenUrl = '';

    /**
     * Урл запроса к API провайдера для получения дополнительных данных пользователя
     *
     * Пример для провайдера Github:
     *  https://api.github.com/user?access_token=%%access_token%%
     *
     * @var string
     */
    public $sUserInfoUrl = '';

    /**
     * Инициализация класса провайдера. Здесь могут подготавливаться какие-либо дополнительные
     * данны. При авторизации этот метод выполняется дважды - первый раз при отправке запроса на
     * разрешение авторизации по урл {@see $sAuthUrl}, а второй раз при редиректе со стороны провайдера
     * на наш сайт.
     *
     * Например, некоторые провайдеры для обеспечения большей безопасности требуют передавать с запросом
     * произвольный хэш, а при редиректе на наш сайт эту же строку нам возвращает и мы можем точно знать,
     * что запрос пришел именно от провайдера. В таком случае здесь можно формировать хэш и добавлять
     * его к url авторизации, а после проверять пришедшую.
     *
     * Метод вызывается автоматически конструктором родительского класса
     *
     */
    public function Init() {

        // Вызовем родительский метод. !!Обязательно!! А уж после наш код.
        parent::Init();

    }

    /**
     * Получение токена пользователя
     *
     * Метод обязательно использует родительский {@see LoadTokenData} который запрашивает
     * разрешение на авторизацию и получает токен. После из полученных от провайдера данны
     * нужно сформировать токен типа {@see PluginAr_ModuleAuthProvider_EntityUserToken}
     *
     * Метод должен вернуть false, если токен не получен!
     *
     * Типовой код приведен в примере
     *
     * @return PluginAr_ModuleAuthProvider_EntityUserToken|bool
     * @throws Exception
     */
    public function GetUserToken() {

        // Получение токена от провайдера
        if (!$aData = $this->LoadTokenData(TRUE)) {
            return FALSE;
        }

        // Сформируем токен
        $oToken = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityUserToken', array(
            'token_provider_name'    => $this->sName,
            'token_data'             => $aData->access_token,
            'token_expire'           => 0,
            'token_provider_user_id' => 0,
        ));

        // Возвратим объект токена
        return $oToken;
    }

    /**
     * Получение дополнительных данных о пользовтаеле - email, имя и т.д.
     * Используется родительский {@LoadAdditionalData} для получения этой информации.
     *
     * Метод должен вернуть false, если данные не получены!
     *
     * В случае успеха метод должен вернуть объект типа PluginAr_ModuleAuthProvider_EntityData
     * с дополнительной информацией о пользователе
     *
     * Типовой код приведен в примере
     *
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken Полученый токен
     * @return bool|Entity
     */
    public function GetUserData(PluginAr_ModuleAuthProvider_EntityUserToken $oToken) {

        // Получаем дополнительные данные
        if (!$aData = $this->LoadAdditionalData($oToken, array('%%access_token%%' => $oToken->getTokenData()), FALSE)) {
            return FALSE;
        }

        // Раскодируем их
        $oData = json_decode($aData);

        // Проверим что пришло
        if (is_null($oData)) {
            return FALSE;
        }

        // Сформируем данные для возврата
        $oAdditionalData = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityData', array(
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

        return $oAdditionalData;

    }

    /*************************************************************************************************
     *************************************************************************************************
     *
     *  ДОПОЛНИТЕЛЬНЫ РОДИТЕЛЬСКИЕ МЕТОДЫ И СВОЙСТВА, КОТОРЫЕ МОЖНО ИСПОЛЬЗОВАТЬ В ДОЧЕРНИХ КЛАССАХ.
     *
     *************************************************************************************************
     *************************************************************************************************/

    // Константы, определяющие права репоста. Переопределять их
    // в дочерних классах не нужно - только использовать
    const REPOST_RIGHT_POST = 'post';
    const REPOST_RIGHT_WALL = 'wall';
    const REPOST_RIGHT_STATUS = 'status';
    const REPOST_RIGHT_GROUP = 'group';
    const REPOST_RIGHT_FRIENDS = 'friends';

    /**
     * Права репоста, изначально всё запрещено. Если разрешить какой-то вид репоста,
     * то нужно реализовать соответствующий метод в теле класса. При разрешенном виде
     * в настройках у пользователя доступна станет галочка этого виде репоста
     *
     * @var array
     */
    public $aRepostRights = array(
        AuthProvider::REPOST_RIGHT_WALL    => FALSE, // Репост записей стены
        AuthProvider::REPOST_RIGHT_STATUS  => FALSE, // Репост статуса
        AuthProvider::REPOST_RIGHT_POST    => FALSE, // Репост топиков
        AuthProvider::REPOST_RIGHT_GROUP   => FALSE, // Репост топиков
        AuthProvider::REPOST_RIGHT_FRIENDS => FALSE, // Поиск друзей по сайту
    );

    /**
     * Прводит репост статуса у провайдера.
     * Работает только если $aRepostRights[AuthProvider::REPOST_RIGHT_STATUS] установлен в TRUE
     * РЕАЛИЗУЕТСЯ САМОМТОЯТЕЛЬНО
     *
     * @param string                                      $sStatus Текст статуса
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken  Токен пользователя
     * @return bool
     */
    public function RepostStatus($sStatus, $oToken) {
        return TRUE;
    }

    /**
     * Прводит репост записи на стене
     * Работает только если $aRepostRights[AuthProvider::REPOST_RIGHT_WALL] установлен в TRUE
     * РЕАЛИЗУЕТСЯ САМОМТОЯТЕЛЬНО
     *
     * @param string                                      $sStatus Текст со стены
     * @param       string                                $sUrl    Урл репоста
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken  Токен пользователя
     * @internal param string $sText
     * @return bool
     */
    public function RepostWall($sStatus, $sUrl, $oToken) {
        return TRUE;
    }

    /**
     * Прводит репост топика
     * Работает только если $aRepostRights[AuthProvider::REPOST_RIGHT_POST] установлен в TRUE
     * РЕАЛИЗУЕТСЯ САМОМТОЯТЕЛЬНО
     *
     * @param ModuleTopic_EntityTopic                     $oTopic Топик для репоста
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken Токен пользователя
     * @return bool
     */
    public function RepostPost($oTopic, $oToken) {
        return TRUE;
    }

    /**
     * Публикация топика в группу
     * Работает только если $aRepostRights[AuthProvider::REPOST_RIGHT_GROUP] установлен в TRUE
     * РЕАЛИЗУЕТСЯ САМОМТОЯТЕЛЬНО
     *
     * @param ModuleTopic_EntityTopic                     $oTopic Топик для репоста
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken Токен пользователя
     * @return bool
     */
    public function PostInGroup($oTopic, $oToken) {
        return TRUE;
    }

    /**
     * Получает идентфикаторы друзей пользователя из социальной сети
     * Работает только если $aRepostRights[AuthProvider::REPOST_RIGHT_FRIENDS] установлен в TRUE
     * РЕАЛИЗУЕТСЯ САМОМТОЯТЕЛЬНО
     *
     * @param $oToken
     * @return bool|string[]
     */
    public function GetFriendsId($oToken) {
        return FALSE;
    }

    /**
     * Разделитель прав.
     *
     * Вообще, запрашиваемые права передаются провайдеру через запятую по url {@see $sAuthUrl},
     * но некоторые требуют другой разделитель, например пробел. Если так, то это свойство
     * нужно в дочернем классе переопределить.
     *
     * @default ','
     * @var string
     */
    public $sPermissionsGutter = ',';

    /**
     * Отрезает часть от текста. По умолчанию - 140 символов. Используется для подготовки данны
     * к репосту, поскольку многие провайдеры ограничивают длину текста репоста
     *
     * @param string $sText    Сам текст
     * @param string $sPostfix Добавляемый после обрезки текст, по умолчнию три точки
     * @param int    $iLength  Требуемая длина текста, по умолчанию - 140 символов
     *
     * @return string
     */
    protected function CropText($sText, $sPostfix = '...', $iLength = 140) {
        parent::CropText($sText, $sPostfix, $iLength);
    }

    /**
     * Подставляет параметры в url.
     *
     * Автоматически заменяет сотящие в урл параметры '%%client_id%%', '%%secret_key%%',
     * '%%permissions%%', '%%redirect%%' на значения из конфига. Если нужно добавить другие параметры,
     * то их следует указать в массиве вторым параметром этого метода.
     *
     * Пример использования:
     *  В урл {@see $sTokenUrl} все 4 параметр будут заменены значениями из конфига, плюс
     *  параметр %%salt%% заменится на значение '12345'
     *  <code>
     *      $this->sTokenUrl = $this->EvalUrl($this->sTokenUrl, array('%%salt%%' => '12345'));
     *  </code>
     *
     * @param string $sUrl
     * @param array  $aAdditionalData
     * @return mixed
     */
    public function EvalUrl($sUrl, $aAdditionalData = array()) {
        return parent::EvalUrl($sUrl, $aAdditionalData);
    }

    /**
     * Отправляет запрос с помощью CURL серверу
     *
     * Возвращает ответ провайдера в специфичном для него формате или FALSE,
     * если запрос завершился ошибкой.
     *
     * Пример:
     * <code>
     *  $this->SendRequest($this->sUserInfoUrl, TRUE, array('User-Agent: My App'));
     * </code>
     *
     * @param string     $sUrl     Урл запроса, параметры должны уже быть в троке запроса
     * @param bool       $bPost    Это post запрос? TRUE, если да, иначе FALSE
     * @param bool|array $aHeaders Дополнительные заголовки.
     *
     * @return bool|stdClass
     */
    protected function SendRequest($sUrl, $bPost = TRUE, $aHeaders = FALSE) {
        return parent::SendRequest($sUrl, $bPost, $aHeaders);
    }

    /**
     * Проверяет, является ли переданная строка строкой в формате json,
     * возвращает TRUE или FALSE, в зависимости от результата
     *
     * @param string $string Проверяемая строка
     * @return bool
     */
    protected function isJson($string) {
        return parent::isJson($string);
    }

    /**
     * Получение данных о токене от провайдера
     *
     * Сначала метод отправляет запрос на урл {@see $sAuthUrl} и получает ответ. Ищет в этом ответе
     * параметр $sCodeParamName (для большинства провайдеров это 'code'), а затем
     * метод отправляет способом *$bPost* запрос на урл {@see $sTokenUrl}. Дополнительно метод
     * передает заголовки http запроса, указанные в параметре $aHeaders. Если в урл запроса есть
     * специфичные поля для замены, то они передаются в параметре $aAdditionalData. Сам урл
     * обрабатывается методом {@see EvalUrl}.
     *
     * @param bool       $bPost           Это post запрос? TRUE, если да, иначе FALSE
     * @param string     $sCodeParamName  Имя параметра, содержащего код в ответе провайдара
     * @param bool|array $aHeaders        Массив строк дополнительных заголовков
     * @param bool|array $aAdditionalData Допонительные параметры для замены в урл
     *
     * @return bool|PluginAr_ModuleAuthProvider_EntityUserToken
     */
    protected function LoadTokenData($bPost = TRUE, $sCodeParamName = 'code', $aHeaders = FALSE, $aAdditionalData = FALSE) {
        return parent::LoadTokenData($bPost, $sCodeParamName, $aHeaders, $aAdditionalData);
    }

    /**
     * Получение дополнительных данных от провайдера по урл {@see $sUserInfoUrl}
     *
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken   Объект токена пользователя
     * @param string[]                                    $aParam   Допонительные параметры для замены в урл
     * @param bool                                        $bPost    Это post запрос? TRUE, если да, иначе FALSE
     * @param bool|array                                  $aHeaders Массив строк дополнительных заголовков
     * @return bool
     */
    function LoadAdditionalData($oToken, $aParam, $bPost = TRUE, $aHeaders = FALSE) {
        return parent::LoadAdditionalData($oToken, $aParam, $bPost, $aHeaders);
    }

    /**
     * Обновляет токен, если это нужно.
     * Обновление произойдет только в том случае, если токен устарел
     *
     * @param PluginAr_ModuleAuthProvider_EntityUserToken $oToken Токен пользователя
     */
    public function RefreshToken($oToken) {
        return parent::RefreshToken($oToken);
    }

}
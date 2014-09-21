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
 * User.class.php
 * Файл модуля User плагина Ar
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Ar
 * @version     0.0.1 от 01.08.2014 13:48
 */
class PluginAr_ModuleUser extends PluginAr_Inherit_ModuleUser {

    public function UploadAvatar($sFile, $oUser, $aSize = array()) {

        if (!F::File_Exists($sFile)) {
            return FALSE;
        }
        if (!$aSize) {
            $oImg = $this->Img_CropSquare($sFile, TRUE);
        } else {
            if (!isset($aSize['w'])) {
                $aSize['w'] = $aSize['x2'] - $aSize['x1'];
            }
            if (!isset($aSize['h'])) {
                $aSize['h'] = $aSize['y2'] - $aSize['y1'];
            }
            $oImg = $this->Img_Crop($sFile, $aSize['w'], $aSize['h'], $aSize['x1'], $aSize['y1']);
        }
//        $sExtension = strtolower(pathinfo($sFile, PATHINFO_EXTENSION));
        $sExtension = $this->Uploader_GetExtension($sFile);

        $sName = pathinfo($sFile, PATHINFO_FILENAME);

        // Сохраняем аватар во временный файл
        if ($sTmpFile = $oImg->Save(F::File_UploadUniqname($sExtension))) {

            // Файл, куда будет записан аватар
            $sAvatar = $this->Uploader_GetUserAvatarDir($oUser->GetId()) . $sName . '.' . $sExtension;

            // Окончательная запись файла только через модуль Uploader
            if ($xStoredFile = $this->Uploader_Store($sTmpFile, $sAvatar)) {
                if (is_object($xStoredFile)) {
                    return $xStoredFile->GetUrl();
                } else {
                    return $this->Uploader_Dir2Url($xStoredFile);
                }
            }
        }

        // * В случае ошибки, возвращаем false
        $this->Message_AddErrorSingle($this->Lang_Get('system_error'));

        return FALSE;
    }

    /**
     * Получение всех токенов социальных сетей текущего пользователя
     *
     * @return bool|PluginAr_ModuleAuthProvider_EntityUserToken[]
     */
    public function GetCurrentUserTokens() {

        if (!E::IsUser()) {
            return FALSE;
        }

        /** @var PluginAr_ModuleAuthProvider_EntityUserToken[] $oResult */
        $oResult = $this->PluginAr_AuthProvider_GetUserTokenItemsByFilter(array(
            'token_user_id' => E::UserId(),
            '#order'        => array('token_provider_name' => 'ASC')
        ));

        return $oResult;
    }

    /**
     * Получение токена социальной сети текущего пользователя по его идентификатору
     *
     * @param $iTokenId
     * @return bool|PluginAr_ModuleAuthProvider_EntityUserToken[]
     */
    public function GetCurrentUserTokenById($iTokenId) {

        if (!E::IsUser()) {
            return FALSE;
        }

        /** @var PluginAr_ModuleAuthProvider_EntityUserToken[] $oResult */
        $oResult = $this->PluginAr_AuthProvider_GetUserTokenItemsByFilter(array(
            'token_user_id' => E::UserId(),
            'token_id'      => $iTokenId,
        ));

        if ($oResult) {
            return array_shift($oResult);
        }

        return FALSE;

    }


    /**
     * Получение параметров репоста пользователя
     * @return bool|mixed
     */
    public function GetCurrentUserRepostSettings() {
        if (!E::IsUser()) {
            return FALSE;
        }

        /** @var PluginAr_ModuleAuthProvider_EntityUserToken[] $oResult */
        $oResult = $this->PluginAr_AuthProvider_GetRepostSettingItemsByFilter(array(
            'setting_user_id' => E::UserId(),
        ));

        $aResult = array();
        if ($oResult) {
            foreach ($oResult as $oSetting) {
                $aResult[$oSetting->getSettingTokenId()][$oSetting->getSettingTypeId()] = $oSetting;
            }

            return $aResult;
        }

        return FALSE;
    }

    /**
     * Получение параметров репоста пользователя по типу
     *
     * @param $sSettingType
     * @return string $sSettingType
     */
    public function GetCurrentUserRepostSettingsByType($sSettingType) {
        if (!E::IsUser()) {
            return FALSE;
        }

        /** @var PluginAr_ModuleAuthProvider_EntityUserToken[] $oResult */
        $oResult = $this->PluginAr_AuthProvider_GetRepostSettingItemsByFilter(array(
            'setting_user_id' => E::UserId(),
            'setting_type_id' => $sSettingType,
            'setting_value'   => 1,
        ));

        $aResult = array();
        if ($oResult) {
            foreach ($oResult as $oSetting) {
                $aResult[$oSetting->getSettingTokenId()] = $oSetting;
            }

            return $aResult;
        }

        return FALSE;
    }

    /**
     * Удаляет настройки репоста по имени провайдера у текущего пользователя
     *
     * @param string $sProviderName
     */
    public function RemoveCurrentUserSettings($sProviderName) {
        if (!E::IsUser()) {
            return;
        }


        /** @var PluginAr_ModuleAuthProvider_EntityRepostSetting[] $oResult */
        $oResult = $this->PluginAr_AuthProvider_GetUserTokenItemsByFilter(array(
            'token_user_id'       => E::UserId(),
            'token_provider_name' => $sProviderName,
        ));
        if ($oResult) {
            $aTokenId = array_keys($oResult);

            /** @var PluginAr_ModuleAuthProvider_EntityRepostSetting[] $oResult */
            $oResult = $this->PluginAr_AuthProvider_GetRepostSettingItemsBySettingTokenIdIn($aTokenId);

            if ($oResult) {
                /** @var PluginAr_ModuleAuthProvider_EntityRepostSetting $oSetting */
                foreach ($oResult as $oSetting) {
                    $oSetting->Delete();
                }
            }
        }

        return;
    }

    /**
     * Удаляет настройки репоста по имени провайдера у текущего пользователя
     *
     * @param int $iTokenId
     */
    public function RemoveCurrentUserSettingsByTokenId($iTokenId) {
        if (!E::IsUser()) {
            return;
        }


        /** @var PluginAr_ModuleAuthProvider_EntityRepostSetting[] $oResult */
        $oResult = $this->PluginAr_AuthProvider_GetRepostSettingItemsBySettingTokenId($iTokenId);

        if ($oResult) {
            /** @var PluginAr_ModuleAuthProvider_EntityRepostSetting $oSetting */
            foreach ($oResult as $oSetting) {
                $oSetting->Delete();
            }
        }


        return;
    }

    public function ToggleRepostSetting($sTokenProviderId, $sRepostType) {
        if (!E::IsUser()) {
            return FALSE;
        }

        /** @var PluginAr_ModuleAuthProvider_EntityRepostSetting[] $oResult */
        $oResult = $this->PluginAr_AuthProvider_GetRepostSettingItemsByFilter(array(
            'setting_user_id'  => E::UserId(),
            'setting_token_id' => $sTokenProviderId,
            'setting_type_id'  => $sRepostType,
        ));

        if ($oResult) {
            /** @var PluginAr_ModuleAuthProvider_EntityRepostSetting $oSettingData */
            $oSettingData = array_shift($oResult);

            if ($oSettingData->getSettingValue() == 1) {
                $oSettingData->Delete();

                return TRUE;
            }
        } else {
            /** @var PluginAr_ModuleAuthProvider_EntityRepostSetting $oSettingData */
            $oSettingData = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityRepostSetting', array(
                'setting_user_id'  => E::UserId(),
                'setting_token_id' => $sTokenProviderId,
                'setting_type_id'  => $sRepostType,
                'setting_value'    => 1,
            ));
            $oSettingData->Add();

            return TRUE;
        }

        return FALSE;
    }


    /**
     * Получение параметров репоста пользователя
     * @param $sType
     * @return bool|mixed
     */
    public function GetCurrentUserText($sType) {
        if (!E::IsUser()) {
            return FALSE;
        }

        /** @var PluginAr_ModuleAuthProvider_EntityUserToken[] $oResult */
        $oResult = $this->PluginAr_AuthProvider_GetTextItemsByFilter(array(
            'text_user_id' => E::UserId(),
            'text_type_id' => $sType,
        ));

        if ($oResult) {
            $oResult = array_shift($oResult);

            return $oResult->getTextData();

        }

        return Config::Get('plugin.ar.default_text_type_text');
    }

    /**
     * Устанавливает текстовку ползователя
     *
     * @param $sType
     * @param $sText
     * @return mixed
     */
    public function SetCurrentUserText($sType, $sText) {
        if (!E::IsUser()) {
            return FALSE;
        }

        /** @var PluginAr_ModuleAuthProvider_EntityUserToken[] $oResult */
        $oResult = $this->PluginAr_AuthProvider_GetTextItemsByFilter(array(
            'text_user_id' => E::UserId(),
            'text_type_id' => $sType,
        ));

        if ($oResult) {
            $oResult = array_shift($oResult);
            $oResult->setTextData($sText);
            $oResult->Update();

            return TRUE;
        }

        $oResult = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityText', array(
            'text_user_id' => E::UserId(),
            'text_data'    => $sText,
            'text_type_id' => $sType,
        ));

        $oResult->Add();

        return TRUE;
    }


    /**
     * Получение пользователя по идентфикатору социальной сети
     *
     * @param string $sNewFriendSocialId
     * @param AuthProvider $oProvider
     * @return bool|ModuleUser_EntityUser
     */
    public function GetUserBySocialId($sNewFriendSocialId, $oProvider) {
        /** @var PluginAr_ModuleAuthProvider_EntityUserToken|array $oResult */
        $oResult = $this->PluginAr_AuthProvider_GetUserTokenItemsByFilter(array(
            'token_provider_user_id' => $sNewFriendSocialId,
            'token_provider_name'    => $oProvider->sName,
        ));

        if ($oResult) {
            $oResult = array_shift($oResult);

            return $oResult->getUser();
        }

        return FALSE;
    }

    /**
     * Получает список друзей
     *
     * @param  int $nUserId     ID пользователя
     * @param  int $iPage       Номер страницы
     * @param  int $iPerPage    Количество элементов на страницу
     *
     * @return array
     */
    public function GetUsersInvitedFriend($nUserId, $iPage = 1, $iPerPage = 10) {

        $sCacheKey = "user_friend_{$nUserId}_{$iPage}_{$iPerPage}";
        if (false === ($data = $this->Cache_Get($sCacheKey))) {
            $data = array(
                'collection' => $this->oMapper->GetUsersInvitedFriend($nUserId, $iCount, $iPage, $iPerPage),
                'count'      => $iCount
            );
            $this->Cache_Set($data, $sCacheKey, array("friend_change_user_{$nUserId}"), 'P2D');
        }
        if ($data['collection']) {
            $data['collection'] = $this->GetUsersAdditionalData($data['collection']);
        }
        return $data;
    }
}
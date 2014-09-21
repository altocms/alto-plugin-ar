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

class PluginAr_ActionProfile extends PluginAr_Inherit_ActionProfile {

    protected function RegisterEvent() {
        parent::RegisterEvent();
        $this->AddEventPreg('/^.+$/i', '/^invited/i', '/^(page([1-9]\d{0,5}))?$/i', 'EventInvitedFriends');
    }

    /**
     * Список друзей пользователей
     */
    protected function EventInvitedFriends() {

        if (!$this->CheckUserProfile()) {
            return parent::EventNotFound();
        }
        /**
         * Передан ли номер страницы
         */
        $iPage = $this->GetParamEventMatch(1, 2) ? $this->GetParamEventMatch(1, 2) : 1;
        /**
         * Получаем список комментов
         */
        $aResult = $this->User_GetUsersInvitedFriend(
            $this->oUserProfile->getId(), $iPage, Config::Get('module.user.per_page')
        );
        $aFriends = $aResult['collection'];
        /**
         * Формируем постраничность
         */
        $aPaging = $this->Viewer_MakePaging(
            $aResult['count'], $iPage, Config::Get('module.user.per_page'), Config::Get('pagination.pages.count'),
            $this->oUserProfile->getUserUrl() . 'friends'
        );
        /**
         * Загружаем переменные в шаблон
         */
        $this->Viewer_Assign('aPaging', $aPaging);
        $this->Viewer_Assign('aFriends', $aFriends);
        $this->Viewer_AddHtmlTitle(
            $this->Lang_Get('user_menu_profile_friends') . ' ' . $this->oUserProfile->getLogin()
        );

        $this->sMenuSubItemSelect = 'invited';
        $this->SetTemplateAction('friends');
    }

}

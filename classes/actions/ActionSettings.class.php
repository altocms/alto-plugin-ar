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
 * Class PluginAr_ActionSettings
 *
 * @property ModuleUser_EntityUser $oUserProfile
 */
class PluginAr_ActionSettings extends PluginAr_Inherit_ActionSettings {

    protected function RegisterEvent() {
        parent::RegisterEvent();
        $this->AddEvent('social', 'EventSocial');
    }

    protected function EventSocial() {

        // Менюшки )
        $this->sMenuItemSelect = 'settings';
        $this->sMenuSubItemSelect = 'social';

        $aProviders = $this->PluginAr_AuthProvider_GetProviders();

        $aToken = $this->User_GetCurrentUserTokens();
        /** @var PluginAr_ModuleAuthProvider_EntityUserToken $v */
        foreach ($aToken as $k => $v) {
            $aToken[$k]->setSearchedCount($this->PluginAr_AuthProvider_GetCountSearchedFriendsByTokenId($v->getTokenId()));
        }

        $this->Viewer_Assign('aToken', $aToken);
        $this->Viewer_Assign('aRepost', $this->User_GetCurrentUserRepostSettings());
        $this->Viewer_Assign('aProviders', $aProviders);
        $this->Viewer_Assign('sSocialText', $this->User_GetCurrentUserText('topic'));

    }


    protected function EventProfile() {

        if (E::IsUser() && E::UserId() == $this->oUserCurrent->getId()) {
            $this->Cache_Set(
                $this->oUserCurrent->getProfileAbout(),
                'current_user_profile_about',
                $aTags = array(),
                $nTimeLife = false,
                'tmp');
        }

        parent::EventProfile();
    }

}
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
class PluginAr_ActionSettings extends PluginAr_Inherits_ActionSettings {

    protected function RegisterEvent() {

        parent::RegisterEvent();
        $this->AddEvent('social', 'EventSocial');
    }


    protected function EventSocial() {

        // Менюшки )
        $this->sMenuItemSelect = 'settings';
        $this->sMenuSubItemSelect = 'social';

        $aProviders = E::Module('PluginAr\AuthProvider')->GetProviders();

        $aToken = E::Module('User')->GetCurrentUserTokens();
        /** @var PluginAr_ModuleAuthProvider_EntityUserToken $v */
        foreach ($aToken as $k => $v) {
            $aToken[$k]->setSearchedCount(E::Module('PluginAr\AuthProvider')->GetCountSearchedFriendsByTokenId($v->getTokenId()));
        }

        E::Module('Viewer')->Assign('aToken', $aToken);
        E::Module('Viewer')->Assign('aRepost', E::Module('User')->GetCurrentUserRepostSettings());
        E::Module('Viewer')->Assign('aProviders', $aProviders);
        E::Module('Viewer')->Assign('sSocialText', E::Module('User')->GetCurrentUserText('topic'));

    }


    protected function EventProfile() {

        if (E::IsUser() && E::UserId() == $this->oUserCurrent->getId()) {
            E::Module('Cache')->Set(
                $this->oUserCurrent->getProfileAbout(),
                'current_user_profile_about',
                $aTags = array(),
                $nTimeLife = false,
                'tmp');
        }

        parent::EventProfile();
    }

}

// EOF
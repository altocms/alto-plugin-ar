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
 * User.mapper.class.php
 * Файл маппера для модуля User плагина Ar
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Ar
 */
class PluginAr_ModuleUser_MapperUser extends PluginAr_Inherits_ModuleUser_MapperUser {

    /**
     * Получает список друзей
     *
     * @param  int $nUserId      ID пользователя
     * @param  int $iCount       Возвращает общее количество элементов
     * @param  int $iCurrPage    Номер страницы
     * @param  int $iPerPage     Количество элементов на страницу
     *
     * @return array
     */
    public function GetUsersInvitedFriend($nUserId, &$iCount, $iCurrPage, $iPerPage) {

        $sql
            = "SELECT
					uf.user_from,
					uf.user_to
				FROM
					?_friend as uf
				WHERE
					( uf.user_from = ?d
					OR
					uf.user_to = ?d )
					AND
					( 	uf.status_from + uf.status_to = ?d
					OR
						(uf.status_from = ?d AND uf.status_to = ?d )
					)
				LIMIT ?d, ?d ;";
        $aUsers = array();
        $aRows = $this->oDb->selectPage(
            $iCount,
            $sql,
            $nUserId,
            $nUserId,
            ModuleUser::USER_FRIEND_NULL + ModuleUser::USER_FRIEND_OFFER,
            ModuleUser::USER_FRIEND_ACCEPT,
            ModuleUser::USER_FRIEND_ACCEPT,
            ($iCurrPage - 1) * $iPerPage, $iPerPage
        );
        if ($aRows) {
            foreach ($aRows as $aUser) {
                $aUsers[] = ($aUser['user_from'] == $nUserId)
                    ? $aUser['user_to']
                    : $aUser['user_from'];
            }
        }
        rsort($aUsers, SORT_NUMERIC);

        return array_unique($aUsers);
    }

    public function DeleteTokensByUsersId($aUsersId) {

        $sql = "
          DELETE FROM ?_user_token
          WHERE token_user_id IN (?a)
          ";
        return $this->oDb->query($sql, $aUsersId) !== false;
    }

}

// EOF
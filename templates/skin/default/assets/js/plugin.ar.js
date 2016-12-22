/*!
 * script.js
 * Файл скриптов плагина Ar
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Ar
 * @version     0.0.1 от 30.07.2014 21:12
 */

var ls = ls || {};

ls.ar = (function ($) {
    "use strict";
    /**
     * Поиск друзей по сайту
     *
     * @param $this
     * @returns {boolean}
     * @param iSocialId
     */
    this.findFriends = function($this, iSocialId) {
        var url = aRouter.auth + 'find/';

        $this.find('i').addClass('fa-spin');

        ls.ajax(url, {token_id: iSocialId}, function (result) {
            $this.find('i').removeClass('fa-spin');

            if (typeof result.sMsg !== 'undefined') {
                ls.msg.notice(null, result.sMsg);
            }

            if (typeof result.count !== 'undefined') {
                $this.find('i').remove();
                $this.text(result.count);
            }

            $this.attr('onclick', '');
            $this.off('click');

        });

        return false;
    };

    /**
     * Репостинг топика в группы
     *
     * @param $this
     * @returns {boolean}
     */
    this.repostInGroup = function($this) {
        var url = aRouter.auth + 'repost/';

        $this.find('i').addClass('fa-spin');

        ls.ajax(url, {topic_id: $this.data('topic_id')}, function (result) {
            $this.find('i').removeClass('fa-spin');

            if (typeof result.sMsg !== 'undefined') {
                ls.msg.notice(null, result.sMsg);
            }

            $this.parent().remove();

        });

        return false;
    };

    /**
     * Удаление связи с провайдером
     *
     * @param $this
     * @param $iSocialId
     * @returns {boolean}
     */
    this.removeSocial = function($this, $iSocialId) {
        var url = aRouter.auth + 'remove/';

        $this.addClass('fa-spin');

        ls.ajax(url, {token_id: $iSocialId}, function (result) {
            $this.removeClass('fa-spin');

            if (typeof result.sMsg !== 'undefined') {
                ls.msg.notice(null, result.sMsg);
            }
            if (typeof result.sid !== 'undefined') {
                $(result.sid).remove();
            }
        });

        return false;
    };

    /**
     * Переключение статуса репостинга по конкретному провайдеру
     *
     * @param $this
     * @returns {boolean}
     */
    this.toggleRepost = function($this) {
        var url = aRouter.auth + 'toggle/';

        ls.ajax(url, {id: $this.data('id'), 'type': $this.data('type')}, function (result) {

            if (typeof result.sMsg !== 'undefined') {
                ls.msg.notice(null, result.sMsg);
            }

        });

        return false;
    };

    /**
     * Установка текстовки постинга топика
     *
     * @param sType
     * @param sText
     * @param $this
     * @returns {boolean}
     */
    this.setSocialText = function(sType, sText, $this) {
        var url = aRouter.auth + 'text/';

        $this.addClass('loading');
        ls.ajax(url, {text_type: sType, text_val: sText}, function (result) {
            $this.removeClass('loading');
            if (typeof result.sMsg !== 'undefined') {
                ls.msg.notice(null, result.sMsg);
            }

        });

        return false;
    };

    return this;

}).call(ls.ar || {}, jQuery);

// Фейсбук добавляет некрасивый хэш в колбэк-урл, уберем его
// В принципе, не очень обязательно, но мне не нравится )
if (window.location.hash && window.location.hash == '#_=_') {
    window.location.hash = '';
}



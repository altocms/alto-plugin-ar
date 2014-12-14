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
        ls.progressStart();
        ls.ajax(url, {token_id: iSocialId}, function (result) {
            $this.find('i').removeClass('fa-spin');
            ls.progressDone();
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
        ls.progressStart();
        ls.ajax(url, {topic_id: $this.data('topic_id')}, function (result) {
            $this.find('i').removeClass('fa-spin');
            ls.progressDone();
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
        ls.progressStart();
        ls.ajax(url, {token_id: $iSocialId}, function (result) {
            $this.removeClass('fa-spin');
            ls.progressDone();
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
        ls.progressStart();
        ls.ajax(url, {id: $this.data('id'), 'type': $this.data('type')}, function (result) {
            ls.progressDone();
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
        ls.progressStart();
        $this.addClass('loading');
        ls.ajax(url, {text_type: sType, text_val: sText}, function (result) {
            ls.progressDone();
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





tinymce.PluginManager.add('spoiler5', function(editor, url) {
    editor.addButton('spoiler5', {
        text: 'S',
        icon: false,
        onclick: function() {
            editor.windowManager.open({
                title: function() { return 'fffff'; },
                body: [
                    {type: 'textbox', name: 'title', label: 'Заголовок'},
                    {type: 'textbox', name: 'insert', label: 'Содержимое', multiline: !0, minHeight: 300, minWidth: 500, style: "direction: ltr; text-align: left"}
                ],
                onsubmit: function(e) {
                    editor.insertContent('<spoiler title="' + e.data.title + '":>' + e.data.insert + '</spoiler>');
                }
            });
        }
    });
});

var p = ls.settings.presets.tinymce['default']();
p.plugins = p.plugins + ' spoiler5';
p.toolbar = p.toolbar + ' | spoiler5';
ls.settings.presets.tinymce['default'] = function() { return p; };
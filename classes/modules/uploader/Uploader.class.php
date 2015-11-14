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
 * Uploader.class.php
 * Файл модуля Uploader плагина ar
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина ar
 * @version     0.0.1 от 01.08.2014 13:07
 */
class PluginAr_ModuleUploader extends PluginAr_Inherit_ModuleUploader {

    public function GetExtension($sFile) {
        $sExtension = strtolower(pathinfo($sFile, PATHINFO_EXTENSION));
        if (!$sExtension) {
            list($iWidth, $iHheight, $sFileType) = getimagesize($sFile);
            $sFileType = image_type_to_extension($sFileType, true);
            $sExtension = str_replace('.', '', $sFileType);
        }
        return $sExtension;
    }

    protected function _checkUploadedFile($sFile, $sConfigKey = 'default') {

        $sExtension = $this->GetExtension($sFile);

        // Check allow extensions
        if ($this->aModConfig['file_extensions']
            && !in_array($sExtension, $this->aModConfig['file_extensions'])
        ) {
            $this->nLastError = self::ERR_NOT_ALLOWED_EXTENSION;

            return FALSE;
        }
        // Check filesize
        if ($this->aModConfig['max_filesize'] && filesize($sFile) > $this->aModConfig['max_filesize']) {
            $this->nLastError = self::ERR_FILE_TOO_LARGE;

            return FALSE;
        }
        // Check images
        if (in_array($sExtension, array('gif', 'png', 'jpg', 'jpeg', ''))) {
            if (!$this->_checkUploadedImage($sFile)) {
                return FALSE;
            }
        }

        return TRUE;
    }

}
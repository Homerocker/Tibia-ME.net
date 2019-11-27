<?php

/**
 * Handles HTTP uploads ($_FILES)
 *
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2012, Tibia-ME.net
 */
class Uploader {

    const HASH_ALGO = 'SHA3-256';

    /**
     * Validates file before uploading.
     */
    public static function validate($files, $options) {
        switch ($files['error']) {
            case 4:
                return array('error' => _('No file specified.'));
            case 0:
                if (isset($options['type'])) {
                    $mime = get_mime_type($files['tmp_name']);
                    switch ($options['type']) {
                        case 'image':
                            $extensions = array(
                                'image/png' => 'png',
                                'image/jpeg' => 'jpg',
                                'image/bmp' => 'bmp',
                                'image/x-ms-bmp' => 'bmp',
                                'image/gif' => 'gif'
                            );
                            break;
                        case 's60':
                            $extensions = array(
                                'application/vnd.symbian.install' => 'sis'
                            );
                            break;
                        default:
                            log_error('Invalid type  \'' . $options['type'] . '\'');
                            return array('error' => _('Unknown error.'));
                    }
                    if (!array_key_exists($mime, $extensions)) {
                        log_error('Unsupported MIME type \'' . $mime . '\' (' . $files['name'] . ')');
                        return array('error' => sprintf(_('Invalid MIME type (%s).'),
                                    $mime));
                    }
                    if (isset($options['type']) && $options['type'] === 'image') {
                        if (@Images::imagecreate($files['tmp_name']) === false) {
                            return array('error' => _('Corrupt image data.'));
                        }
                        $size = getimagesize($files['tmp_name']);
                        if (isset($options['resolution']) && $options['resolution']
                                != ($size[0] . 'x' . $size[1])) {
                            return array('error' => sprintf(_('Image resolution should be %s.'),
                                        $options['resolution']));
                        } else {
                            $resolution = $size[0] . 'x' . $size[1];
                        }
                    }
                    $ext = $extensions[$mime];
                } else {
                    $ext = pathinfo($files['name'], PATHINFO_EXTENSION);
                }
                
                if ($ext == '') {
                    // @todo allow this
                    return ['error' => _('File extension cannot be empty.')];
                }

                if (isset($resolution)) {
                    return array('error' => false, 'mime' => $mime, 'extension' => $ext,
                        'resolution' => $resolution);
                }
                return array('error' => false, 'mime' => $mime, 'extension' => $ext);
            case 1:
            case 2:
                return array('error' => _('File is too large.'));
            default:
                log_error('Unknown error code \'' . $files['error'] . '\'');
                return array('error' => sprintf(_('Unknown error (errorcode: %d).'),
                            $files['error']));
        }
    }

    /**
     * Handles files uploads.
     *
     * @param string $field_name html form field name to parse
     * @param array $options optional array with options:
     * 'index' => (int) specify uploaded file index in $_POST[$field_name] array, used when uploading more than one file
     * 'type' => (string) image/s60 validates file type
     * 'resolution' => (string) 120x60 validates image resolution
     * 'force' => (boolean) if true file existance check will be skipped
     * 'ignore' => (boolean) do not return any error if file is not specified
     * @return array on success returns an array with 3 elements:
     * 'error' => (boolean) false
     * 'hash' => (string) file hash returned by hash_file()
     * 'extension' => (string) file extension without dot (e.g. "jpg")
     * 'resolution' => (string) image resolution, only if type is set to 'image' and 'resolution' is not specified (e.g. "160x120")
     *
     * on failture returns an array with 1 element:
     * 'error' => (string) error message (depends on current user locale)
     */
    public static function upload($field_name, $options = array()) {
        if (isset($options['index']) && !isset($_FILES[$field_name]['name'][$options['index']])) {
            return array('error' => empty($options['ignore']) ? _('No file specified.')
                    : false);
        } elseif (!isset($_FILES[$field_name])) {
            return array('error' => _('No file specified.'));
        }

        $files['error'] = $_FILES[$field_name]['error'];
        $files['tmp_name'] = $_FILES[$field_name]['tmp_name'];
        $files['name'] = $_FILES[$field_name]['name'];
        if (isset($options['index'])) {
            foreach (array_keys($files) as $key) {
                $files[$key] = $files[$key][$options['index']];
            }
        }

        $validation = self::validate($files, $options);
        if ($validation['error'] !== false) {
            return array('error' => $validation['error']);
        }

        if (isset($options['upload_dir'])) {
            $upload_dir = $options['upload_dir'];
        } else {
            switch ($field_name) {
                case 'photo':
                    $upload_dir = '/photos';
                    break;
                case 'theme':
                    $upload_dir = '/themes';
                    break;
                case 'screenshot':
                    $upload_dir = '/screenshots';
                    break;
                case 'artwork':
                    $upload_dir = '/artworks';
                    break;
                default:
                    log_error('unknown upload type and no upload_dir specified');
                    return array('error' => _('Server error.'));
            }
        }

        $temp = tempnam(sys_get_temp_dir(), 'UPLOAD');
        if (!move_uploaded_file($files['tmp_name'], $temp)) {
            return ['error' => _('Unknown error.')];
        }
        
        if (isset($options['type']) && $options['type'] == 'image') {
            $temp = Images::compress($temp, ($upload_dir == '/artworks'));
            $ext = Images::get_image_ext($temp);
        } else {
            $ext = $validation['extension'];
        }
        $hash = hash_file(self::HASH_ALGO, $temp);

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . $upload_dir . '/' . $hash . '.' . $ext)) {
            return array('error' => sprintf(_('File %s already exists.'),
                        htmlspecialchars($files['name'], ENT_COMPAT, 'UTF-8')));
        }

        if (!rename($temp,
                        $_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . $upload_dir . '/' . $hash . '.' . $ext)) {
            unlink($temp);
            return ['error' => _('Unknown error.')];
        }

        $metadata = array(
            'error' => false,
            'hash' => $hash,
            'extension' => $ext,
            'filesize' => filesize($_SERVER['DOCUMENT_ROOT'] . UPLOAD_DIR . $upload_dir . '/' . $hash . '.' . $ext)
        );
        if (isset($validation['resolution'])) {
            $metadata['resolution'] = $validation['resolution'];
        }
        return $metadata;
    }

    /**
     * @deprecated
     * @param string $path
     * @return string
     */
    public static function image_hash_update($path) {
        $pathinfo = pathinfo($path);
        $hash = hash_file(self::HASH_ALGO, $path);
        $new_path = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $hash . '.' . $pathinfo['extension'];
        if (realpath($path) != realpath($new_path)) {
            rename($path,
                    $pathinfo['dirname'] . '/' . $hash . '.' . $pathinfo['extension']);
        }
        return $pathinfo['dirname'] . '/' . $hash . '.' . $pathinfo['extension'];
    }

}

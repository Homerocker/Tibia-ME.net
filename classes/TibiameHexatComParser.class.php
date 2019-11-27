<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2016, Tibia-ME.net
 */
class TibiameHexatComParser {

    /**
     * @deprecated
     * @param type $vocation
     * @param type $slot
     * @param type $name
     * @param type $stats
     * @return type
     */
    public static function get_armour_icon($vocation, $slot, $name, $stats) {
        $name = str_replace(['(+)', '(x)', '(mtx)'], '', strtolower($name));
        if ($name == 'frost helmet' && $stats['ice'] > 20) {
            // workaround for upgraded frostscale helmet
            $name = 'frostscale helmet';
        }
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/images/icons/' . $_SESSION['icons_client_type'] . '/items/' . str_replace(' ',
                                '_', $name) . '.png')) {
            return '/images/icons/' . $_SESSION['icons_client_type'] . '/items/' . str_replace(' ',
                            '_', $name) . '.png';
        }
        $url = 'http://tibiame.hexat.com/img/lres/' . ($slot === 'legs' ? 'leg' : $slot) . '/' . ($vocation
                    ? substr($vocation, 0, 3) . '/' : '') . preg_replace('/[^A-Za-z0-9]/',
                        '', $name) . '.png';
        if (remote_file_exists($url)) {
            return copy($url,
                            $_SERVER['DOCUMENT_ROOT'] . '/images/icons/classic/items/' . str_replace(' ',
                                    '_', $name) . '.png') ? '/images/icons/classic/items/' . str_replace(' ',
                            '_', $name) . '.png' : null;
        }
        $dom = new DOMDocumentX;
        $dom->loadHTMLFile('http://tibiame.hexat.com/T/' . ($slot === 'legs' ? 'leg'
                            : $slot) . '/' . preg_replace('/[^A-Za-z0-9]/', '',
                        $name));
        $img = $dom->getElementsByTagName('table');
        if (!$img->length) {
            return null;
        }
        $img = $img->item(0)->getElementsByTagName('img');
        if (!$img->length) {
            return null;
        }
        return copy('http://tibiame.hexat.com' . str_replace('/hres/', '/lres/',
                                $img->item(0)->getAttribute('src')),
                        $_SERVER['DOCUMENT_ROOT'] . '/images/icons/classic/items/' . str_replace(' ',
                                '_', $name) . '.png') ? '/images/icons/classic/items/' . str_replace(' ',
                        '_', $name) . '.png' : null;
    }

    public static function get_icon($name, $cat, $vocation = null, $slot = null,
            $stats = null) {
        $name = GameContent::sanitize_filename($name);
        if ($name == 'frost_helmet' && $stats['ice'] > 20) {
            // workaround for upgraded frostscale helmet
            $name = 'frostscale_helmet';
        }
        if ($name == 'conflagator') {
            $name = 'conflagrator';
        }
        $local_path = '/images/icons/classic/';
        switch ($cat) {
            case 'skills_warrior':
            case 'skills_wizard':
                $local_path .= 'skills';
                break;
            default:
                $local_path .= $cat;
        }
        $local_path .= '/' . $name . '.png';
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $local_path)) {
            return str_replace('/images/icons/classic', '', $local_path);
        }
        if (!Filesystem::mkdir(pathinfo($_SERVER['DOCUMENT_ROOT'] . $local_path,
                                PATHINFO_DIRNAME))) {
            return null;
        }
        $remote_path = 'http://tibiame.hexat.com/T/';
        switch ($cat) {
            case 'skills_warrior':
            case 'skills_wizard':
                $remote_path .= 'skill';
                break;
            case 'weapons':
                $remote_path .= substr($vocation, 0, 3) . substr($vocation, 2, 1) . 'wpn';
                break;
            case 'pets':
                $remote_path .= 'pet';
                break;
            case 'monsters':
                $remote_path .= 'monster';
                break;
            case 'armours':
                $remote_path .= self::url_parse_slot($slot);
                break;
            case 'spells':
                // @todo get spells icon
                //$remote_path .= 'lres/other';
                return null;
            case 'food':
                $remote_path .= 'other';
                break;
        }
        $remote_path .= '/' . str_replace('_', '', $name);
        $dom = new DOMDocumentX;
        if (!$dom->loadHTMLFile($remote_path)) {
            log_error('could not load ' . $remote_path);
            return null;
        }
        $remote_path = $dom->getElementsByTagName('tbody')->item(0);
        if (!$remote_path) {
            return null;
        }
        $remote_path = $remote_path->getElementsByTagName('img')->item(0);
        if (!$remote_path) {
            return null;
        }
        $remote_path = $remote_path->getAttribute('src');
        $remote_path = 'http://tibiame.hexat.com/' . str_replace('/hres/',
                        '/lres/', $remote_path);
        if (copy($remote_path, $_SERVER['DOCUMENT_ROOT'] . $local_path)) {
            $local_path = Images::compress($_SERVER['DOCUMENT_ROOT'] . $local_path, true);
            $local_path = Filesystem::get_doc_root_path($local_path);
            $local_path = str_replace('/images/icons/classic', '', $local_path);
            return Images::compress($_SERVER['DOCUMENT_ROOT'] . $local_path, true);
        }
        log_error('could not copy ' . $remote_path . ' to ' . $_SERVER['DOCUMENT_ROOT'] . $local_path);
        return null;
    }

    private static function url_parse_slot($slot_name) {
        return $slot_name == 'legs' ? 'leg' : $slot_name;
    }

}

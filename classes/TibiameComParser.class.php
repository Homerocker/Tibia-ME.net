<?php

/**
 * Fetches and parses sexy stuff from tibiame.com.
 * Note that there's a separate class for highscores.
 *
 * @author Molodoy
 * @copyright (c) 2013, Tibia-ME.net
 */
class TibiameComParser
{

    private function get_client_url($client_id)
    {
        $dom = new DOMDocumentX;
        if (!$dom->loadHTMLFile('http://www.tibiame.com/?section=download&lan=en&markup=xhtmlmp&clientid=' . $client_id . '&privacypolicy=true&termsofservice=true')) {
            return null;
        }
        $dom = $dom->getElementsByTagName('a')->item(0);
        if ($dom == null) {
            return null;
        }
        return $dom->getAttribute('href');
    }

    private function get_client_type($client_name_string)
    {
        foreach (
            array(
                'Android' => 'android',
                'iPhone' => 'iphone',
                'J2ME' => 'j2me',
                'Series 60' => 's60',
                'S60v3' => 's60v3',
                'S60v5' => 's60v5',
                'Windows Phone' => 'wp',
                'Windows 8' => 'win8'
            ) as $client_keyword => $client_codename) {
            if (stripos($client_name_string, $client_keyword) !== false) {
                $client_type = $client_codename;
                break;
            }
        }
        if (!isset($client_type)) {
            log_error('could not parse client name string \'' . $client_name_string . '\'');
        }
        if ($client_type == 'j2me') {
            if (stripos($client_name_string, 'Motorola') !== false) {
                $client_type .= '_motorola';
            } elseif (stripos($client_name_string, 'Blackberry') !== false) {
                $client_type .= '_blackberry';
            }
        }
        if (stripos($client_name_string, 'Classic') !== false) {
            $client_type .= '_classic';
        } elseif (stripos($client_name_string, 'Basic') !== false) {
            $client_type .= '_basic';
        }
        return $client_type;
    }

    private function get_client_version($client_name_string)
    {
        if (!preg_match('/\d+\.\d+/', $client_name_string, $matches)) {
            log_error('could not parse version number in client name string \'' . $client_name_string . '\'');
        }
        return $matches[0];
    }

    /**
     * @deprecated since version 3.0.6
     */
    private function get_client_version_iphone($url_itunes)
    {
        $itunes_lookup = explode('/id', parse_url($url_itunes, PHP_URL_PATH));
        if (!($itunes_lookup = curl_get_contents('https://itunes.apple.com/lookup?id='
            . end($itunes_lookup)))) {
            return null;
        }
        $itunes_lookup = json_decode($itunes_lookup);
        if (empty($itunes_lookup->results[0])) {
            return null;
        }
        return $itunes_lookup->results[0]->version;
    }

    public function get_clients_versions()
    {
        $cached_versions = Cache::read('clientsversions', 600);
        if ($cached_versions !== false) {
            return $cached_versions;
        }
        $dom = new DOMDocumentX;
        if (!$dom->loadHTMLFile('https://www.tibiame.com/?section=download&lan=en&markup=xhtmlmp&subsection=clientlist', null, 5)) {
            return Cache::read('clientsversions');
        }
        $ps = $dom->getElementsByTagName('p');
        if (!$ps->length) {
            return Cache::read('clientsversions');
        }
        $versions = array();
        $cached_versions = Cache::read('clientsversions');
        foreach ($ps as $p) {
            $a = $p->getElementsByTagName('a')->item(0);
            $query = array();
            parse_str(parse_url($a->getAttribute('href'), PHP_URL_QUERY), $query);
            if (!isset($query['clientid'])) {
                // wrong link sir
                continue;
            }
            $client_type = $this->get_client_type($p->getElementsByTagName('b')->item(0)->nodeValue);
            $client_version = $this->get_client_version($a->nodeValue);
            if ($cached_versions[$client_type]['version'] == $client_version && !empty($cached_versions[$client_type]['url'])) {
                $versions[$client_type] = [
                    'version' => $client_version,
                    'url' => $cached_versions[$client_type]['url']
                ];
                continue;
            }
            $url = $this->get_client_url($query['clientid']);
            $versions[$client_type] = array(
                'version' => $client_version,
                'url' => $url
            );
        }
        if (empty($versions)) {
            return Cache::read('clientsversions');
        }
        if ($cached_versions != $versions) {
            Cache::write($versions, 'clientsversions');
        }
        return $versions;
    }

    public static function fetch_news()
    {
        $news = Cache::read('news_official', 600);
        if ($news !== false) {
            return $news;
        }
        $dom = new DOMDocumentX;
        if (!$dom->loadHTMLFile('https://www.tibiame.com/?lan=en&markup=xhtml')) {
            return Cache::read('news_official');
        }
        $ths = $dom->getElementsByTagName('th');
        if (!$ths->length) {
            return Cache::read('news_official');
        }
        $news = array();
        $i = $j = 0;
        while ($th = $ths->item($i++)) {
            switch ($th->getAttribute('class')) {
                case 'HomeNewsHeadline':
                    $news[$j]['headline'] = $th->nodeValue;
                    break;
                case 'HomeNewsDate':
                    $news[$j++]['date'] = $th->nodeValue;
                    break;
            }
        }
        $tds = $dom->getElementsByTagName('td');
        $i = $j = 0;
        while ($td = $tds->item($i++)) {
            if ($td->getAttribute('class') == 'HomeNewsBody') {
                $links = $td->getElementsByTagName('a');
                foreach ($links as $link) {
                    //parse_str(parse_url($link->getAttribute('href'), PHP_URL_QUERY), $query);
                    //if (isset($query['markup'])) {
                    //    unset($query['markup']);
                    //}
                    //$link->setAttribute('href', preg_replace('/markup=xhtml(&|\z)/', 'markup=xhtmlmp\1', ));
                    $link->setAttribute('target', '_blank');
                }
                $news[$j++]['body'] = $td->innerHTML();
            }
        }
        Cache::write($news, 'news_official');
        return $news;
    }

    /**
     *
     * @param string $code decrypted game code
     * @param $nickname
     * @param $world
     * @return boolean
     */
    public static function gamecode_activate($code, $nickname, $world)
    {
        $dom = new DOMDocumentX;
        if (!$dom->loadHTMLFile('https://payments.cipsoft.com/tibiame/index.php?page=GameCode&action=useGameCode&CustomerID='
            . $nickname . '%40' . $world . '&GameCode=' . str_replace('-', '', $code) . '&Language=en&Country=BY&markup=xhtmlmp')) {
            return false;
        }
        $dom = $dom->getElementById('Content');
        if ($dom === null) {
            return false;
        }
        $dom = $dom->nodeValue;
        if (strpos($dom, 'The Game Code has been activated.') !== false) {
            return true;
        }
        $dom->saveHTMLFile($_SERVER['DOCUMENT_ROOT'] . '/' . uniqid('PAYMENT') . '.html');
        return false;
    }

}

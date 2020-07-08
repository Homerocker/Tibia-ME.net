<?php

require __DIR__. '/../Facebook/autoload.php';

use \Facebook as fb;

class Facebook {
    
    const APP_ID = FB_APP_ID;
    const APP_SECRET = FB_APP_SECRET;

    public static function new_instance() {
        return new fb\Facebook([
            'app_id' => self::APP_ID,
            'app_secret' => self::APP_SECRET,
            'default_graph_version' => 'v7.0',
        ]);
    }
    
    public static function get_token() {
        return explode('=',curl_get_contents('https://graph.facebook.com/oauth/access_token?client_id='.Facebook::APP_ID.'&client_secret='.Facebook::APP_SECRET.'&grant_type=client_credentials'))[1];
    }
    
    public static function get_info($id, $fields = []) {
        
    }

}

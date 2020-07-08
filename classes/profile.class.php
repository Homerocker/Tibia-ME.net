<?php

/**
 * User Profile functions.
 *
 * Fetches/updates profile data,
 * sets/removes avatar,
 * links/unlinks facebook account,
 * deletes all users posts.
 *
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright 2012 (c) Tibia-ME.net
 */
class Profile
{

    /**
     * @var array $error error messages (if any)
     */
    public $error = array();

    /**
     * @var array $data profile data
     */
    public $data = array();

    /**
     * Fetches profile data to be displayed.
     * @param int|string $user_id user ID
     */
    public function __construct($user_id)
    {
        $sql = $GLOBALS['db']->query('
                    SELECT `users`.`nickname`,
                    `users`.`world`,
                    `users`.`email`,
                    `user_profile`.*,
                    `user_settings`.`hide_age`,
                    `user_settings`.`hide_email`,
                    (
                        SELECT CONCAT_WS(\'.\', album_photos.hash, album_photos.extension) as filename
                        FROM `album_photos`
                        WHERE `id` = `user_profile`.`avatarID`
                        LIMIT 1
                    ) as `avatar`,
                    (
                        SELECT COUNT(*)
                        FROM `banishments`
                        WHERE `userID` = `user_profile`.`id`
                        AND `expirationTime` > \'' . $_SERVER['REQUEST_TIME'] . '\'
                        AND `unbannedModeratorID` IS NULL
                    ) as `banned`,
                    (
                        SELECT (
                            SELECT COUNT(*)
                            FROM `forumPosts`
                            WHERE `posterID` = \'' . $user_id . '\'
                        ) + (
                            SELECT COUNT(*)
                            FROM `forumTopics`
                            WHERE `authorID` = \'' . $user_id . '\'
                        )
                    ) as `forum_posts`,
                    (
                        SELECT COUNT(*)
                        FROM `album_photos`,
                        `album_albums`
                        WHERE `album_albums`.`userID` = \'' . $user_id . '\'
                        AND `album_albums`.`id` = `album_photos`.`albumID`
                    ) as `photos`,
                    (
                        SELECT COUNT(*)
                        FROM `screenshots`
                        WHERE `authorID` = \'' . $user_id . '\'
                    ) as `screenshots`,
                    (
                        SELECT COUNT(*)
                        FROM `themes`
                        WHERE `authorID` = \'' . $user_id . '\'
                    ) as `themes`,
                    (
                        SELECT COUNT(*)
                        FROM `friendlist`
                        WHERE (
                            `userID` = \'' . $user_id . '\'
                            OR `friendID` = \'' . $user_id . '\'
                        ) AND `accepted` = \'1\'
                    ) as `friends`,
                    (
                        SELECT COUNT(*)
                        FROM `banishments`
                        WHERE `userID` = \'' . $user_id . '\'
                    ) as `banishments`' . (($user_id != $_SESSION['user_id']) ? ',
                    (
                        SELECT COUNT(*)
                        FROM `friendlist`
                        WHERE (
                            (
                                `userID` = \'' . $_SESSION['user_id'] . '\'
                                 AND `friendID` = \'' . $user_id . '\'
                             ) OR (
                                 `userID` = \'' . $user_id . '\'
                                  AND `friendID` = \'' . $_SESSION['user_id'] . '\'
                              )
                          ) AND `accepted` = \'1\'
                    ) as `is_friend`' : '') . ',
                    (
                        SELECT `scores_characters`.`id`
                        FROM `scores_highscores`,
                        `scores_characters`
                        WHERE `scores_characters`.`nickname` = `users`.`nickname`
                        AND `scores_characters`.`world` = `users`.`world`
                        AND `scores_highscores`.`characterID` = `scores_characters`.`id`
                        LIMIT 1
                    ) as `character_id`,
                    (
                        SELECT `scores_characters_guilds`.`guild`
                        FROM `scores_characters_guilds`,
                        `scores_highscores`
                        WHERE `scores_characters_guilds`.`characterID` = `character_id`
                        AND `scores_highscores`.`characterID` = `scores_characters_guilds`.`characterID`
                        AND `scores_highscores`.`date` = \'' . Scores::date() . '\'
                        LIMIT 1
                    ) as `real_guild`,
                    (
                        SELECT `scores_characters`.`vocation`
                        FROM `scores_characters`,
                        `scores_highscores`
                        WHERE `scores_characters`.`id` = `character_id`
                        AND `scores_highscores`.`characterID` = `scores_characters`.`id`
                        AND `scores_highscores`.`date` = \'' . Scores::date() . '\'
                        LIMIT 1
                    ) as `real_vocation`
                    FROM `users`,
                    `user_profile`,
                    `user_settings`
                    WHERE `users`.`id` = `user_profile`.`id`
                    AND `users`.`id` = `user_settings`.`id`
                    AND `users`.`id` = \'' . $user_id . '\''
        )->fetch_assoc();
        $this->data = $sql;
        if (!empty($this->data['birthday']) && !$this->data['hide_age']) {
            $this->data['age'] = date_diff(date_create(),
                date_create($this->data['birthday']))->format('%y');
        }
        $this->data['editable'] = (Perms::get(Perms::USERS_PROFILE_EDIT) || $user_id
            == $_SESSION['user_id']) ? 1 : 0;
        if (!is_null($sql['birthday'])) {
            list ($this->data['birthday_y'], $this->data['birthday_m'], $this->data['birthday_d'])
                = explode('-', $sql['birthday']);
        } else {
            $this->data['birthday_y'] = $this->data['birthday_m'] = $this->data['birthday_d']
                = null;
        }
        if ($this->data['facebook'] !== null) {
            try {
                $facebook = Facebook::new_instance();
                $this->data['facebook'] = $facebook->get('/' . $this->data['facebook'] . '?fields=name',
                    Facebook::APP_ID . '|' . Facebook::APP_SECRET)->getGraphUser();
            } catch (Exception $e) {
                log_error($e->getMessage());
                $this->data['facebook'] = null;
            }
        }
        if ($this->data['vk'] !== null) {
            $vk = $this->vk_instance();
            $vk = $vk->api('users.get',
                array(
                    'user_ids' => $this->data['vk'],
                    'fields' => 'photo_50'
                ));
            $this->data['vk'] = $vk['response'][0];
        }

        // location
        if (isset($this->data['country_id'])) {
            $this->data['country'] = geo::getCountries($this->data['country_id']);
        }
    }

    public static function avatar_remove($user_id)
    {
        $GLOBALS['db']->query('UPDATE `user_profile` SET `avatarID` = NULL WHERE `id` = \'' . $user_id . '\'');
        Document::reload_msg(_('Avatar removed.'),
            $_SERVER['SCRIPT_NAME'] . '?u=' . $user_id);
    }

    private static function vk_instance()
    {
        return new vkapi(VK_APP_ID, VK_APP_SECRET);
    }

    public static function delete_forum_posts($user_id)
    {
        $sql = $GLOBALS['db']->query('SELECT `id` FROM `forumTopics` WHERE `authorID` = \'' . $user_id . '\'');
        while ($row = $sql->fetch_row()) {
            Forum::topic_remove($row[0]);
        }
        $GLOBALS['db']->query('DELETE FROM `forumPosts` WHERE `posterID` = \'' . $user_id . '\'');
        Document::reload_msg(_('Changes saved.'),
            $_SERVER['SCRIPT_NAME'] . '?u=' . $user_id);
    }

    public static function facebook_link()
    {
        $facebook_instance = Facebook::new_instance();
        $helper = $facebook_instance->getRedirectLoginHelper();
        try {
            $accessToken = $helper->getAccessToken();
        } catch (Exception $e) {
            log_error($e->getMessage());
        }
        if (isset($accessToken) || $GLOBALS['db']->query('SELECT facebook FROM user_profile WHERE id = ' . $_SESSION['user_id'])->fetch_row()[0]
            !== null) {
            if (isset($accessToken)) {
                try {
                    $me = $facebook_instance->get('/me', $accessToken)->getGraphUser();
                    $GLOBALS['db']->query('UPDATE `user_profile` SET `facebook` = ' . $GLOBALS['db']->quote($me['id']) . ' WHERE `id` = ' . $_SESSION['user_id']);
                } catch (Exception $e) {
                    log_error($e->getMessage());
                }
            }
            header('Location: /user/profile.php');
        } else {
            header('Location: ' . $helper->getLoginUrl(((isset($_SERVER['HTTPS'])
                        && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME']));
        }
        exit;
    }

    public static function facebook_unlink()
    {
        $GLOBALS['db']->query('UPDATE `user_profile` SET `facebook` = NULL WHERE `id` = \'' . $_SESSION['user_id'] . '\'');
        header('Location: ' . $_SERVER['SCRIPT_NAME']);
        exit;
    }

    public static function vk_link()
    {
        $vk_data = curl_get_contents('https://oauth.vk.com/access_token?client_id=4020135&client_secret=A7R4vuccCzdHtIwrEG2x&code=' . $_GET['code'] . '&redirect_uri=' . urlencode('http://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . '?VKlink'));
        $vk_data = json_decode($vk_data, true);
        $GLOBALS['db']->query('UPDATE `user_profile` SET `vk` = ' . $vk_data['user_id']
            . ' WHERE `id` = ' . $_SESSION['user_id'] . ' LIMIT 1');
        header('Location: ' . $_SERVER['SCRIPT_NAME']);
        exit;
    }

    public static function vk_unlink()
    {
        $GLOBALS['db']->query('UPDATE `user_profile` SET `vk` = NULL'
            . ' WHERE `id` = \'' . $_SESSION['user_id'] . '\' LIMIT 1');
        header('Location: ' . $_SERVER['SCRIPT_NAME']);
        exit;
    }

    private function parse_birthday($d, $m, $y)
    {
        if (ctype_digit($d) && $d >= 1 && $d <= 31) {
            $this->birthday_d = isset($d[1]) ? $d : '0' . $d;
        } else {
            $this->error[] = _('Invalid birth day.');
            $this->birthday_d = null;
        }
        if (ctype_digit($m) && $m >= 1 && $m <= 12) {
            $this->birthday_m = isset($m[1]) ? $m : '0' . $m;
        } else {
            $this->error[] = _('Invalid birth month.');
            $this->birthday_m = null;
        }
        if (ctype_digit($y) && $y >= 1930 && $y <= date('Y')) {
            $this->birthday_y = isset($y[4]) ? intval($y) : $y;
        } else {
            $this->error[] = _('Invalid birth month.');
            $this->birthday_y = null;
        }
        if (isset($this->birthday_d) && isset($this->birthday_m) && isset($this->birthday_y)) {
            return $this->birthday_y . '-' . $this->birthday_m . '-'
                . $this->birthday_d;
        } else {
            return null;
        }
    }

    public function update()
    {
        if (Perms::get(Perms::USERS_PROFILE_EDIT) || $this->data['id'] == $_SESSION['user_id']) {
            $fields = array('name', 'gender', 'birthday', 'country_id',
                'whatsapp', 'icq', 'yahoo', 'skype', 'relationship', 'vocation',
                'signature', 'email', 'hide_email', 'hide_age', 'guild');
        }
        if (Perms::get(Perms::RANKS_ASSIGN)) {
            if (isset($fields)) {
                $fields[] = 'rank';
            } else {
                $fields = array('rank');
            }
        }
        if (!isset($fields)) {
            return;
        }
        // name
        if (in_array('name', $fields)) {
            if (empty($_POST['name'])) {
                $this->data['name'] = null;
            } else {
                $this->data['name'] = $_POST['name'];
                if (isset($this->data['name'][32])) {
                    $this->error[] = _('Name is too long.');
                }
            }
        }

        // gender
        if (in_array('gender', $fields)) {
            if (empty($_POST['gender'])) {
                $this->data['gender'] = null;
            } elseif ($_POST['gender'] == 'male' || $_POST['gender'] == 'female') {
                $this->data['gender'] = $_POST['gender'];
            } else {
                $this->error[] = _('Invalid gender.');
            }
        }

        // birthday
        if (in_array('birthday', $fields)) {
            if (empty($_POST['birthday_d']) && empty($_POST['birthday_m']) && empty($_POST['birthday_y'])) {
                $this->data['birthday'] = null;
            } else {
                $birthday_d = isset($_POST['birthday_d']) ? $_POST['birthday_d']
                    : null;
                $birthday_m = isset($_POST['birthday_m']) ? $_POST['birthday_m']
                    : null;
                $birthday_y = isset($_POST['birthday_y']) ? $_POST['birthday_y']
                    : null;
                $this->data['birthday'] = $this->parse_birthday($birthday_d,
                    $birthday_m, $birthday_y);
            }
        }

        // Country ID
        if (in_array('country_id', $fields)) {
            if (empty($_POST['country_id']) || !geo::country_exists($_POST['country_id'])) {
                $this->data['country_id'] = null;
            } else {
                $this->data['country_id'] = $_POST['country_id'];
            }
        }

        // rank
        if (in_array('rank', $fields)) {
            if (!empty($_POST['rank']) && Perms::get(Perms::RANKS_ASSIGN)) {
                if (Ranks::exists($_POST['rank']) && empty(array_diff(Ranks::get_perms($_POST['rank']),
                        Ranks::get_perms($_SESSION['user_rank'])))) {
                    $this->data['rank'] = $_POST['rank'];
                } else {
                    $this->error[] = _('Invalid rank.');
                }
            }
        }

        // WhatsApp
        if (in_array('whatsapp', $fields)) {
            if (empty($_POST['whatsapp'])) {
                $this->data['whatsapp'] = null;
            } else {
                $this->data['whatsapp'] = $_POST['whatsapp'];
                if (isset($this->data['whatsapp'][24])) {
                    $this->error[] = _('WhatsApp number is too long.');
                }
                // @todo validate phone number?
            }
        }

        // ICQ
        if (in_array('icq', $fields)) {
            if (empty($_POST['icq'])) {
                $this->data['icq'] = null;
            } else {
                $this->data['icq'] = $_POST['icq'];
                if (!preg_match('/^[0-9]{6,9}$/', $this->data['icq'])) {
                    $this->error[] = _('Invalid ICQ.');
                }
            }
        }

        // Yahoo
        if (in_array('yahoo', $fields)) {
            if (empty($_POST['yahoo'])) {
                $this->data['yahoo'] = null;
            } else {
                $this->data['yahoo'] = $_POST['yahoo'];
                if (!isset($this->data['yahoo'][3])) {
                    $this->error[] = _('Yahoo ID is too short.');
                } elseif (isset($this->yahoo[32])) {
                    $this->error[] = _('Yahoo ID is too long.');
                }
            }
        }

        // Skype
        if (in_array('skype', $fields)) {
            if (empty($_POST['skype'])) {
                $this->data['skype'] = null;
            } else {
                $this->data['skype'] = $_POST['skype'];
                if (!isset($this->data['skype'][5])) {
                    $this->error[] = _('Skype ID is too short.');
                } elseif (isset($this->data['skype'][32])) {
                    $this->error[] = _('Skype ID is too long.');
                }
            }
        }

        // Relationship
        if (in_array('relationship', $fields)) {
            if (empty($_POST['relationship'])) {
                $this->data['relationship'] = null;
            } elseif ($_POST['relationship'] == 'single' || $_POST['relationship']
                == 'relationship' || $_POST['relationship'] == 'engaged' || $_POST['relationship']
                == 'married' || $_POST['relationship'] == 'complicated' || $_POST['relationship']
                == 'searching') {
                $this->data['relationship'] = $_POST['relationship'];
            } else {
                $this->error[] = _('Invalid relationship status.');
            }
        }

        // vocation
        if (in_array('vocation', $fields)) {
            if (empty($_POST['vocation'])) {
                $this->data['vocation'] = null;
            } elseif ($_POST['vocation'] == 'warrior' || $_POST['vocation'] == 'wizard') {
                $this->data['vocation'] = $_POST['vocation'];
            } else {
                $this->error[] = _('Invalid vocation.');
            }
        }

        // signature
        if (in_array('signature', $fields)) {
            if (empty($_POST['signature'])) {
                $this->data['signature'] = null;
            } else {
                $this->data['signature'] = $_POST['signature'];
                if (isset($this->data['signature'][256])) {
                    $this->error[] = _('Signature is too long.');
                }
            }
        }

        // email
        if (in_array('email', $fields)) {
            if (empty($_POST['email'])) {
                $this->data['email'] = null;
            } else {
                $this->data['email'] = $_POST['email'];
                if (!filter_var($this->data['email'], FILTER_VALIDATE_EMAIL)) {
                    $this->error[] = _('Invalid E-mail.');
                }
            }
        }

        // hide email
        if (in_array('hide_email', $fields)) {
            if (isset($_POST['hide_email']) && $_POST['hide_email'] == 1) {
                $this->data['hide_email'] = 1;
            } else {
                $this->data['hide_email'] = 0;
            }
        }

        // hide age
        if (in_array('hide_age', $fields)) {
            if (isset($_POST['hide_age']) && $_POST['hide_age'] == 1) {
                $this->data['hide_age'] = 1;
            } else {
                $this->data['hide_age'] = 0;
            }
        }

        // guild
        if (in_array('guild', $fields)) {
            if (empty($_POST['guild'])) {
                $this->data['guild'] = null;
            } else {
                $this->data['guild'] = $_POST['guild'];
                if (isset($this->data['guild'][10])) {
                    $this->error[] = _('Guild name is too long.');
                }
            }
        }

        if (!empty($this->error)) {
            return;
        }

        foreach ($fields as $field) {
            if ($field == 'email') {
                $table = 'users';
            } elseif ($field == 'hide_email' || $field == 'hide_age') {
                $table = 'user_settings';
            } else {
                $table = 'user_profile';
            }
            $value = is_null($this->data[$field]) ? 'NULL' : '\'' . $GLOBALS['db']->real_escape_string($this->data[$field]) . '\'';
            if (!isset($sql)) {
                $sql = '`' . $table . '`.`' . $field . '` = '
                    . $value;
            } else {
                $sql .= ', `' . $table . '`.`' . $field . '` = '
                    . $value;
            }
        }

        $GLOBALS['db']->query('UPDATE `users`, `user_profile`, `user_settings` SET '
            . $sql . ' WHERE `users`.`id` = \'' . $this->data['id']
            . '\' AND `users`.`id` = `user_profile`.`id` AND `users`.`id` = `user_settings`.`id`');
        // @todo this is temp solution
        if ($this->data['country_id']) {
            $GLOBALS['db']->query('UPDATE user_profile SET location = NULL WHERE location IS NOT NULL AND id = ' . $this->data['id']);
        }
        Document::reload_msg(_('Changes saved.'),
            $_SERVER['SCRIPT_NAME'] . '?u='
            . $this->data['id']);
    }

}

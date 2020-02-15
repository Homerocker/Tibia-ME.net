<?php

class API
{

    public function __construct()
    {
        $call = $_GET['call'] ?? null;
        if (empty($call)) {
            $this->output('No call method specified.', true);
        }
        switch ($call) {
            case 'like_toggle':
                $this->like_toggle();
                break;
            case 'chat_send_message':
                if (empty($_GET['message'])) {
                    $this->output('No message specified.');
                }
                $this->output($this->chat_send_message($_GET['message']));
                break;
            case 'chat_update':
                $this->chat_update($_GET['last_id'] ?? 0);
                break;
            case 'get_platinum_bundle':
                if (empty($_GET['required_amount'])) {
                    $this->output('Amount not specified.');
                }
                if (empty($_GET['currency'])) {
                    $this->output('Currency not specified.');
                }
                $this->output($this->get_platinum_bundle($_GET['required_amount'], $_GET['currency']));
                break;
            default:
                $this->output('Unrecognized call method.');
        }
    }

    private function output($response, $error = false)
    {
        exit(json_encode([($error ? 'error' : 'result') => $response]));
    }

    private function like_toggle()
    {
        $this->output(Likes::toggle(_get('target_type'), _get('target_id'),
            _get('like')));
    }

    private function chat_update($last_id)
    {
        if (empty($last_id)) {
            $this->output($GLOBALS['db']->query('SELECT * FROM (SELECT chat.id, chat.message, chat.timestamp, COALESCE(users.nickname, chat.user_nickname) AS nickname, users.world FROM chat LEFT JOIN users ON chat.user_id = users.id ORDER BY chat.timestamp DESC LIMIT 200) t ORDER BY timestamp ASC')->fetch_all(MYSQLI_ASSOC));
        } else {
            $this->output($GLOBALS['db']->query('SELECT chat.id, chat.message, chat.timestamp, COALESCE(users.nickname, chat.user_nickname) AS nickname, users.world FROM chat LEFT JOIN users ON chat.user_id = users.id WHERE chat.id > '
                . intval($last_id) . ' ORDER BY chat.timestamp ASC')->fetch_all(MYSQLI_ASSOC));
        }
    }

    private function chat_send_message($message)
    {
        $message = htmlspecialchars(trim($message));
        $GLOBALS['db']->query('INSERT INTO chat (user_id, user_nickname, message, timestamp) VALUES (' . $GLOBALS['db']->quote($_SESSION['user_id'] ? $_SESSION['user_id'] : null) . ', ' . $GLOBALS['db']->quote($_SESSION['user_id'] ? null : $_SESSION['user_nickname']) . ', ' . $GLOBALS['db']->quote($message) . ', ' . $_SERVER['REQUEST_TIME'] . ')');
        return ($GLOBALS['db']->affected_rows == 1);
    }
    
    private function get_platinum_bundle($required_amount, $currency) {
        $bundle = new PlatinumBundle($required_amount);
        return array_merge(['amount' => $bundle->get_amount()], $bundle->get_price($currency));
    }

}

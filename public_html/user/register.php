<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../config.php';

// authorized users should not access this page
Auth::RequireLogin(false);
if (_request('agreement') != 'accepted') {
    Document::reload_msg(_('Please read and accept our User Agreement.'), './agreement.php?act=register&redirect='.  get_redirect(true, '/'));
}

// getting vars
// use urldecode() for string values as $_GET values may be encoded
$nickname = isset($_REQUEST['nickname']) ? urldecode($_REQUEST['nickname']) : null;
$world = get_world();
$email = isset($_GET['email']) ? urldecode($_GET['email']) : null;
$password = isset($_POST['password']) ? $_POST['password'] : null;
$hide_email = (!empty($_REQUEST['hide_email']) || !isset($_POST['submit'])) ? 1 : 0;

if (isset($_POST['submit'])) {
    if (!isset($_SESSION['captcha'])
            || !isset($_POST['captcha'])
            || $_SESSION['captcha'] != $_POST['captcha']) {
var_dump($_SESSION['captcha']);
var_dump($_POST['captcha']);
exit;
        Document::reload_msg(_('Incorrect confirmation code.'), $_SERVER['PHP_SELF'] . '?nickname='.urlencode($nickname).'&world='.$world.'&email='.urlencode($email).'&hide_email='.$hide_email.'&agreement=accepted');
    }
    Document::reload_msg(Auth::Register($nickname, $world, $password, $email, $hide_email), $_SERVER['PHP_SELF'].'?nickname='.urlencode($nickname).'&world='.$world.'&email='.urlencode($email).'&hide_email='.$hide_email.'&agreement=accepted');
}

$document = new Document(_('Register'));
$document->assign(array(
    'nickname' => (!empty($nickname) ? htmlspecialchars($nickname, ENT_COMPAT, 'UTF-8') : null),
    'world' => $world,
    'email' => (!empty($email) ? htmlspecialchars($email, ENT_COMPAT, 'UTF-8') : null),
    'hide_email' => $hide_email
));
$document->display('register');
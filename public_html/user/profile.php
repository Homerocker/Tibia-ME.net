<?php

/**
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright 2012 (c) Tibia-ME.net
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
Auth::RequireLogin();
$u = Auth::get_u();
if (!Auth::user_exists($u)) {
    Document::reload_msg(_('User not found.'), $_SERVER['PHP_SELF']);
}
if (isset($_GET['avatar_remove']) && ($_SESSION['user_id'] == $u || Perms::get(Perms::USERS_PROFILE_EDIT))) {
    Profile::avatar_remove($u);
} elseif (isset($_GET['FBunlink']) && $u == $_SESSION['user_id']) {
    Profile::facebook_unlink();
} elseif (isset($_GET['delete_forum_posts']) && Perms::get(Perms::FORUM_MOD)) {
    Profile::delete_forum_posts($_GET['delete_forum_posts']);
} elseif (isset($_GET['VKlink']) && $u == $_SESSION['user_id']) {
    if (isset($_GET['error'])) {
        log_error($_GET['error_description']);
    } elseif (isset($_GET['code'])) {
        Profile::vk_link();
    }
} elseif (isset($_GET['VKunlink']) && $u == $_SESSION['user_id']) {
    Profile::vk_unlink();
}
$profile = new Profile($u);
$document = new Document(User::get_display_name($profile->data['id']));


if (!empty($_POST)) {
    $profile->update();
    if (!empty($profile->error)) {
        $document->assign('error', $profile->error);
    }
}
$document->assign(array(
    'data' => $profile->data,
    'countries' => geo::getCountries()
));

$document->display('profile');

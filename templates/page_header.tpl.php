<!DOCTYPE html>
<html lang="<?= User::get_xml_lang() ?>">
<head>
    <title><?= $page_title ?></title>
    <meta charset="utf-8"/>
    <meta name="author" content="Molodoy"/>
    <meta name="description" content="TibiaME - the first massively multiplayer online role-playing game for mobile phones"/>
    <meta name="keywords" content="tibiame, tibia, game, roleplaying, rpg, online, mobile, mmorpg, massively, multiplayer, tibia-me, molodoy, molodoy3561, <?= SERVER_NAME ?>, highscores, top, screenshots, pvp, guild"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" type="text/css" href="/foundation/css/foundation.min.css"/>
    <link rel="stylesheet" type="text/css" href="/foundation/css/app.css?305"/>
    <link rel="shortcut icon" type="image/vnd.microsoft.icon" href="/favicon.ico"/>
    <link rel="icon" type="image/png" href="/favicon.png"/>
    <link rel="apple-touch-icon" href="/apple-touch-icon.png"/>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-19439373-1"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'UA-19439373-1');
    </script>
    <? if ($show_ads): ?>
        <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({
                google_ad_client: "ca-pub-3385318666093811",
                enable_page_level_ads: true
            });
        </script>
    <? endif; ?>
</head>
<body>
    <nav class="title-bar" data-responsive-toggle="responsive-menu" data-hide-for="medium">
        <button class="menu-icon" type="button" data-toggle="responsive-menu"></button>
        <div class="title-bar-title pointer" data-toggle="responsive-menu"><?= _('Menu') ?></div>
        <ul class="dropdown menu" data-dropdown-menu>
            <? if ($_SESSION['user_id']): ?>
                <li class="has_submenu">
                    <a href="#">
                        <?= ($inbox_unread + ($friend_requests ?? 0) + $notifications) ?>
                    </a>
                    <ul class="submenu menu vertical" data-submenu>
                        <li><a href="/user/letters.php?folder=inbox"><?= ($inbox_unread == 0 ? _('No new letters') : sprintf(ngettext('%d new letter', '%d new letters', $inbox_unread), $inbox_unread)) ?></a></li>
                        <li><a href="/user/friendlist.php<?= (isset($friend_requests) ? '?act=requests_in' : '') ?>"><?= (isset($friend_requests) ? sprintf(ngettext('%d friend request', '%d friend requests', $friend_requests), $friend_requests) : sprintf(ngettext('%d friend', '%d friends', $friends_count), $friends_count)) ?></a></li>
                        <li><a href="/user/notifications.php"><?= ($notifications ? sprintf(ngettext('%d notification', '%d notifications', $notifications), $notifications) : _('No new notifications')) ?></a></li>
                    </ul>
                </li>
            <? else: ?>
                <li class="has-submenu">
                    <a href="#"><?= LOCALES[$_SESSION['locale']] ?></a>
                    <ul class="submenu menu vertical" data-submenu>
                        <?php foreach (LOCALES as $key => $value): ?>
                        <? if ($_SESSION['locale'] == $key): continue; endif; ?>
                        <?php
                        $params = $_GET;
                        unset($params['lang']);
                        $params['lang'] = $key;
                        $params = http_build_query($params);
                        ?>
                        <li><a href="<?= $_SERVER['SCRIPT_NAME'] ?>?<?= $params ?>"><?= $value ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <nav class="top-bar" id="responsive-menu">
        <? if ($navi !== null || $_SERVER['SCRIPT_NAME'] != '/index.php'): ?>
            <div class="top-bar-left">
                <ul class="dropdown menu" data-dropdown-menu>
                    <? if ($_SERVER['SCRIPT_NAME'] != '/index.php'): ?>
                        <li><a href="/"><?= _('Home') ?></a></li>
                    <? endif; ?>
                    <? if ($navi != null): ?>
                        <? foreach ($navi as $index => $array):
                            if ($array === null or empty($array[1])):
                                continue;
                            endif; ?>
                            <li><a href="<?= $array[1] ?>"><?= $array[0] ?></a></li>
                        <? endforeach; ?>
                    <? endif ?>
                </ul>
            </div>
        <? endif; ?>
        <div class="top-bar-right">
            <ul class="dropdown menu" data-dropdown-menu>
                <? if ($_SESSION['user_id']): ?>
                    <li><a href="/user/memberlist.php?act=viewonline"><?= _('Online') ?>: <?= $total_online ?> (<?= $registered_online ?>/<?= $guests_online ?>)</a></li>
                    <li class="has_submenu hide-for-small-only">
                        <a href="#">
                            <?= ($inbox_unread + ($friend_requests ?? 0) + $notifications) ?>
                        </a>
                        <ul class="submenu menu vertical" data-submenu>
                            <li><a href="/user/letters.php?folder=inbox"><?= ($inbox_unread == 0 ? _('No new letters') : sprintf(ngettext('%d new letter', '%d new letters', $inbox_unread), $inbox_unread)) ?></a></li>
                            <li><a href="/user/friendlist.php<?= (isset($friend_requests) ? '?act=requests_in' : '') ?>"><?= (isset($friend_requests) ? sprintf(ngettext('%d friend request', '%d friend requests', $friend_requests), $friend_requests) : sprintf(ngettext('%d friend', '%d friends', $friends_count), $friends_count)) ?></a></li>
                            <li><a href="/user/notifications.php"><?= ($notifications ? sprintf(ngettext('%d notification', '%d notifications', $notifications), $notifications) : _('No new notifications')) ?></a></li>
                        </ul>
                    </li>
                <? else: ?>
                    <li class="menu-text"><?= _('Online') ?>: <?= $total_online ?></li>
                <? endif; ?>
                <li class="has-submenu">
                    <? if ($_SESSION['user_id']): ?>
                        <a href="#"><?= $_SESSION['user_nickname'] ?>&nbsp;w<?= $_SESSION['user_world'] ?></a>
                    <? else: ?>
                        <a href="/user/login.php?redirect=<?= get_redirect() ?>"><?= _('Log in') ?></a>
                    <? endif; ?>
                    <ul class="submenu menu vertical" data-submenu>
                        <? if ($_SESSION['user_id']): ?>
                            <li><a href="/user/profile.php"><?= _('Profile') ?></a></li>
                            <li><a href="/user/settings.php"><?= _('Settings') ?></a></li>
                            <li><a href="/user/out.php"><?= _('Log out') ?></a></li>
                        <?php else: ?>
                            <li><a href="/user/register.php?redirect=<?= get_redirect() ?>"><?= _('Register') ?></a></li>
                            <li><a href="/user/lostpassword.php"><?= _('Recover password') ?></a></li>
                        <? endif; ?>
                    </ul>
                </li>
                <? if (!$_SESSION['user_id']): ?>
                    <li class="has-submenu">
                        <a href="#"><?= LOCALES[$_SESSION['locale']] ?></a>
                        <ul class="submenu menu vertical" data-submenu>
                            <?php foreach (LOCALES as $key => $value): ?>
                            <? if ($_SESSION['locale'] == $key): continue; endif; ?>
                            <?php
                            $params = $_GET;
                            unset($params['lang']);
                            $params['lang'] = $key;
                            $params = http_build_query($params);
                            ?>
                            <li><a href="<?= $_SERVER['SCRIPT_NAME'] ?>?<?= $params ?>"><?= $value ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <? if ($show_ads): ?>
        <!-- responsive 1 -->
        <ins class="adsbygoogle text-center"
             style="display:block; margin: 2px;"
             data-ad-client="ca-pub-3385318666093811"
             data-ad-slot="2075799580"
             data-ad-format="auto"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    <? endif; ?>
    <div id="viewport" class="grid-x grid-padding-x">
        <div id="content" class="cell medium-9">

            <? if ($maintenance_msg): ?>
                <div class="callout warning text-center">
                    <?= $maintenance_msg ?>
                </div>
            <?php endif; ?>
            
            <noscript>
                <div class="callout alert text-center">
                    <?= sprintf(_('%s requires Javascript to work correctly. Please enable it in your browser settings.'), SITE_NAME) ?>
                </div>
            </noscript>
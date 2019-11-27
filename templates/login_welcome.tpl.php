<h3><?= sprintf(_('Hello, %s.'), $nickname); ?></h3>
<div class="grid-x grid-padding-x callout primary">
    <div class="cell medium-6">
    <?php
    if (isset($lastvisit)) {
        echo '<div>';
        printf(_('Last time you were here on %s.'), $lastvisit);
        echo '</div>';
    }
    if ($news !== null) {
        echo '<h3>';
        echo _('What\'s new');
        echo '</h3>';
    }
    if ($news === null) {
        echo '<div>';
        echo _('No news since your previous visit.');
        echo '</div>';
    } else {
        echo '<ul>';
        foreach ($news as $key => $count) {
            echo '<li>';
            switch ($key) {
                case 'artworks':
                    echo '<a href="/artworks">' . sprintf(ngettext('%d artwork',
                                    '%d artworks', $count), $count) . '</a>';
                    break;
                case 'letters':
                    echo '<a href="./letters.php">' . sprintf(ngettext('%d letter',
                                    '%d letters', $count), $count) . '</a>';
                    break;
                case 'news':
                    echo '<a href="/forum/viewforum.php?f=6">' . sprintf(_('%d news'),
                            $count) . '</a>';
                    break;
                case 'photos':
                    echo '<a href="/album">' . sprintf(ngettext('%d photo',
                                    '%d photos', $count), $count) . '</a>';
                    break;
                case 'themes':
                    echo '<a href="/themes">' . sprintf(ngettext('%d theme',
                                    '%d themes', $count), $count) . '</a>';
                    break;
                case 'topics':
                    echo '<a href="/forum">' . sprintf(ngettext('%d topic',
                                    '%d topics', $count), $count) . '</a>';
                    break;
            }
            echo '</li>';
        }
        echo '</ul>';
    }
    ?>
    </div>
    <div class="cell medium-6 text-center">
        <a class="button primary" href="<?= htmlspecialchars($redirect) ?>"><?= _('Continue') ?></a>
    </div>
</div>
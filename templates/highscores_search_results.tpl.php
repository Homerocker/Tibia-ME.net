<?php
if ($results == 0) {
    echo '<div class="callout secondary text-center">';
    echo _('Your search returned no results.');
    echo '</div>';
} else {
    echo '<div class="callout secondary text-center">';
    printf(ngettext('Your search returned %d result.', 'Your search returned %d results.', $results), $results);
    echo '</div>';
    echo '<div class="callout primary">';
    foreach ($data as $char):
        echo '<a href="./viewscores.php?characterID=', $char['id'], '"><img src="/images/icons/armour_'.$char['vocation'].'.png" alt="'.$char['vocation'].'"/>&nbsp;', $char['nickname'], ', w', $char['world'], '</a><br/>';
    endforeach;
    echo '</div>';
}
<h3>
	<?= _('Ranks') ?>
</h3>
<?php
foreach ($ranks as $rank) {
        echo '<div class="callout primary">';
	if ($rank['color'] !== null) {
		echo '<span class="', $rank['color'], '">';
	}
	if ($rank['prefix'] !== null) {
		echo $rank['prefix'], '-';
	}
	echo htmlspecialchars(_($rank['name']));
	if ($rank['color'] !== null) {
		echo '</span>';
	}
        echo '<div class="button-group">';
        echo '<a class="button primary" href="', $_SERVER['PHP_SELF'], '?edit=', $rank['id'], '">', _('Edit'), '</a>';
        if ($rank['id'] != 1) {
            echo '<a class="button warning" href="', $_SERVER['PHP_SELF'], '?delete=', $rank['id'], '">', _('Delete'), '</a>';
        }
        echo '</div>';
        echo '</div>';
}
?>
<a class="button primary" href="<?= $_SERVER['PHP_SELF'] ?>?add"><?= _('Add') ?></a>
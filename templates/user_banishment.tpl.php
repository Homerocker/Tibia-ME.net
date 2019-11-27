<div class="callout alert text-center">
    <a href="/user/banishments.php?view=<?= $banishment_id ?>">
            <b>
        <?= sprintf($banishment_expired ? _('You have been banished until %s. This banishment has expired. Click for more details.') : _('You have been banished until %s. Click for more details.'), $banishment_exp_datetime) ?>
            </b>
    </a>
</div>
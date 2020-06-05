<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
    <div class="callout primary">
        <?= _('Nickname') ?>:<br/>
        <b><?= htmlspecialchars($form->field('nickname')->value()) ?></b><br/>
        <?= _('World') ?>:<br/>
        <b><?= htmlspecialchars($form->field('world')->value()) ?></b><br/>
        <?= _('Amount') ?>:<br/>
        <b><?= htmlspecialchars($form->field('amount')->value()) ?></b>
        <? $form->field('nickname')->display() ?>
        <? $form->field('world')->display() ?>
        <? $form->field('amount')->display() ?>
        <div class="button-group">
            <? $form->field('submit')->display(_('Confirm')) ?>
            <a class="button warning" href="<?= $_SERVER['PHP_SELF'] ?>"><?= _('Cancel') ?></a>
        </div>
    </div>
</form>
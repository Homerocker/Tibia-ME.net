<form method="get" action="search.php">
    <div class="callout secondary">
        <div class="input-group">
            <input class="input-group-field" id="search" type="text" placeholder="<?= _('Nickname') ?>" name="search" maxlength="10" value="<?=(isset($_GET['search']) ? $_GET['search'] : '')?>"/><br/>
            <div class="input-group-button">
                <input class="button primary small" type="submit" value="<?=_('Search')?>"/>
            </div>
        </div>
    </div>
</form>

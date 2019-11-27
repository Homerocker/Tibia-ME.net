<form action="./search.php" method="get">
    <div class="callout secondary input-group">
        <input class="input-group-field" type="text" name="search" value="<?= (empty($_GET['search']) ? '' : htmlspecialchars($_GET['search'], ENT_COMPAT, 'UTF-8')) ?>"/>
        <div class="input-group-button">
            <input class="button primary" type="submit" value="<?= _('Search') ?>"/>
        </div>
    </div>
</form>
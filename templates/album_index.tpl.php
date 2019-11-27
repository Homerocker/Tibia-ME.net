<form action="<?=$_SERVER['SCRIPT_NAME']?>" method="get">
    <div class="callout secondary">
        <label for="order"><?=_('Sort')?></label>
        <select id="order" name="order">
            <option value="nickname"<?=(($order == 'nickname') ? ' selected' : '')?>><?=_('by nickname')?></option>
            <option value="date"<?=(($order == 'date') ? ' selected' : '')?>><?=_('by last update')?></option>
            <option value="comments"<?=(($order == 'comments') ? ' selected' : '')?>><?=_('by comments')?></option>
        </select>
        <label for="world"><?=_('World')?></label>
        <select id="world" name="world">
            <option value=""><?=_('all')?></option>
            <?php for ($i = 1; $i <= WORLDS; ++$i):?>
                <option value="<?=$i?>"<?=(($world == $i) ? ' selected="selected"' : '')?>><?=$i?></option>
            <?php endfor; ?>
        </select>
        <input class="button primary" type="submit" value="<?= _('Sort') ?>"/>
    </div>
</form>

<div class="grid-x grid-padding-x grid-padding-y">
<?php foreach ($data as $key => $array): ?>
    <div class="cell medium-6 large-4">
        <div class="card">
            <div class="card-section text-center">
                <a href="./albums.php?u=<?=$array['userID']?>">
                    <?php // @todo we have 'nickname' and 'world' in $array, could use it for performance boost but have to follow format standard ?>
                    <img class="thumbnail" src="<?=$array['thumbnail']?>" alt=""/><br/>
                    <?= User::get_display_name($array['userID']) ?>
                </a><br/>
                <?php printf(ngettext('%d album', '%d albums', $array['albums']), $array['albums']); ?><br/>
                <?php printf(ngettext('%d photo', '%d photos', $array['photos']), $array['photos']); ?><br/>
                <?=($array['album_allow_comments'] ? ($array['comments'] ? sprintf(ngettext('%d comment', '%d comments', $array['comments']), $array['comments']) : _('no comments')) : _('comments disabled')); ?><br/>
                <div class="button-group small align-center">
                    <a class="button primary" href="/user/profile.php?u=<?=$array['userID']?>"><?=_('View profile')?></a>
                    <a class="button primary" href="/user/letters.php?act=compose&amp;u=<?=$array['userID']?>"><?=_('Send letter')?></a>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="get">
    <div class="callout primary">
        <label for="vocation"><?= _('Vocation') ?></label>
        <select id="vocation" name="vocation">
            <option value=""><?= _('any') ?></option>
            <option value="warrior"<?= ($vocation == 'warrior' ? ' selected' : '')
?>><?= _('warrior') ?></option>
            <option value="wizard"<?=
            ($vocation == 'wizard' ? ' selected="selected"' : '')
            ?>><?= _('wizard') ?></option>
        </select>
        <label for="level_min"><?= _('Level') ?></label>
        <div class="input-group">
            <input class="input-group-field" type="number" id="level_min" name="level_min" size="3" value="<?= $level_min ?>" placeholder="<?= _('min') ?>"/>
            <span class="input-group-label">–</span>
            <input class="input-group-field" type="number" id="level_max" name="level_max" size="3" value="<?= $level_max ?>" placeholder="<?= _('max') ?>"/>
        </div>
        <fieldset class="fieldset">
            <legend><?= _('Type') ?></legend>
            <? foreach (DMG_ELEMENTS as $element): ?>
                <span class="nowrap"><input id="<?= $element ?>" type="checkbox" name="type[]" value="<?= $element ?>"<?= (($type !== null
                        && in_array($element, $type)) ? ' checked' : '') ?>/><label for="<?= $element ?>"><?= _($element) ?></label></span>
            <? endforeach; ?>
        </fieldset>
        <label for="sort"><?= _('Sort by') ?></label>
        <div class="input-group">
            <select class="input-group-field" id="sort" name="sort">
                <option value="level"><?= _('level') ?></option>
                <?
                foreach (array('energy', 'fire', 'hit',
            'soul',
            'ice') as $element):
                    ?>
                    <option value="<?= $element ?>"<?=
                    ($element == $sort ? ' selected' : '')
                    ?>><?= _($element) ?></option>
                        <? endforeach; ?>
            </select>
            <select class="input-group-field nbl" id="order" name="order">
                <option value="asc"><?= _('ascending') ?></option>
                <option value="desc"<?= ($order == 'desc' ? ' selected' : '') ?>><?= _('descending') ?></option>
            </select>
        </div>
        <input class="button primary" type="submit" value="<?= _('Update') ?>"/>
    </div>
</form>
<table class="stack-for-small">
    <thead>
        <tr>
            <td><?= _('Name') ?></td>
            <td><?= _('Level') ?></td>
            <td><?= _('Mana') ?></td>
            <td><?= _('Stats') ?></td>
            <td><?= _('Dropped by') ?></td>
        </tr>
    </thead>
    <tbody>
        <? if (empty($data)): ?>
            <tr>
                <td colspan="5" class="text-center">
                    <?= _('No items to display.') ?>
                </td>
            </tr>
        <? endif; ?>
        <? foreach ($data as $i => $item): ?>
            <tr>
                <td>
                    <img src="<?= ICONS_DIR . $item['icon'] ?>" alt=""/>
                    <? if ($item['is_upgraded']): ?>
                        (+)&nbsp;
                    <? endif; ?>
                    <?= htmlspecialchars($item['name']) ?>
                </td>
                <td>
                    <img src="/images/icons/level.gif" alt="lvl"/><?= $item['level'] ?>
                </td>
                <td><? if (isset($item['mana'])): ?>
                        <img src="/images/icons/mana.gif" alt="<?= _('Mana') ?>"/><?= $item['mana'] ?>
                    <? endif; ?></td>
                <td>
                    <? $nbsp = false; ?>
                    <?
                    foreach (['energy', 'fire', 'hit', 'soul',
                'ice'] as $element):
                        ?>
                        <? if (isset($item[$element . '_min'])): ?>
                            <?
                            if ($nbsp == false):
                                $nbsp = true;
                            else:
                                echo '&nbsp;';
                            endif;
                            ?>
                            <img src="/images/icons/<?= $element ?>.png" alt="<?= $element ?>"/><span class="nowrap"><?= $item[$element . '_min'] ?>-<?= $item[$element . '_max'] ?></span>
                        <? elseif (isset($item[$element . '_max'])): ?>
                            <?
                            if ($nbsp == false):
                                $nbsp = true;
                            else:
                                ?>
                                &nbsp;
                            <? endif; ?>
                            <img src="/images/icons/<?= $element ?>.png" alt="<?= $element ?>"/><?= $item[$element . '_max'] ?>
                        <? endif; ?>
                    <? endforeach; ?>
                </td>
                <td><? if (!empty($item['source'])): ?>
                        <?
                        foreach ($item['source'] as $i => $monster):
                            ?>
                            <? if ($i != 0): ?>
                                , 
                            <? endif; ?>
                            <a href="./monsters.php?name=<?= urlencode($monster) ?>"><?= $monster ?></a>
                        <? endforeach; ?>
                    <? endif; ?></td>
            </tr>
        <? endforeach; ?>
    </tbody>
</table>
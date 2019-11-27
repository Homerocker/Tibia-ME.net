<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="get">
    <div class="callout secondary">
        <label for="vocation"><?= _('Vocation') ?></label>
        <select id="vocation" name="vocation">
            <option value=""><?= _('any') ?></option>
            <option value="warrior"<?= ($vocation == 'warrior' ? ' selected'
                        : '') ?>><?= _('warrior') ?></option>
            <option value="wizard"<?= ($vocation == 'wizard' ? ' selected' : '') ?>><?= _('wizard') ?></option>
        </select>
        <label for="slot"><?= _('Slot') ?></label>
        <select id="slot" name="slot">
        <option value=""><?= _('any') ?></option>
        <? foreach (array('head', 'torso', 'legs', 'shield', 'amulet', 'ring') as
                    $slot_name): ?>
            <? // for translations, do not simplify
            switch ($slot_name) {
                case 'head':
                    $slot_localized = _('head');
                    break;
                case 'legs':
                    $slot_localized = _('legs');
                    break;
                case 'shield':
                    $slot_localized = _('shield');
                    break;
                case 'torso';
                    $slot_localized = _('torso');
                    break;
                case 'amulet':
                    $slot_localized = _('amulet');
                    break;
                case 'ring':
                    $slot_localized = _('ring');
                    break;
            }
            ?>
            <option value="<?= $slot_name ?>"<?= ($slot == $slot_name ? ' selected'
                        : '') ?>><?= $slot_localized ?></option>
        <? endforeach; ?>
        </select>
        <label for="level_min"><?= _('Level') ?></label>
        <div class="input-group">
            <input class="input-group-field" id="level_min" type="number" name="level_min" maxlength="2" value="<?= $level_min ?>" placeholder="<?= _('min') ?>"/>
            <span class="input-group-label">–</span>
            <input class="input-group-field" id="level_max" type="number" name="level_max" maxlength="2" value="<?= $level_max ?>" placeholder="<?= _('max') ?>"/>
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
            <select class="input-group-field" name="sort">
                <option value="level"><?= _('level') ?></option>
                <? foreach (DMG_ELEMENTS as $element): ?>
                    <?// for translations, do not simplify
                    switch ($element) {
                        case 'energy':
                            $element_localized = _('energy');
                            break;
                        case 'fire':
                            $element_localized = _('fire');
                            break;
                        case 'hit':
                            $element_localized = _('hit');
                            break;
                        case 'soul':
                            $element_localized = _('soul');
                            break;
                        case 'ice':
                            $element_localized = _('ice');
                            break;
                    } ?>
                    <option id="<?= $element ?>" value="<?= $element ?>"<?= ($element == $sort ? ' selected'
                                : '') ?>><?= $element_localized ?></option>
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
            <td><?= _('Armour') ?></td>
            <td><?= _('Level') ?></td>
            <td><?= _('Slot') ?></td>
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
<? else: ?>
    <? foreach ($data as $i => $item): ?>
            <tr>
                <td>
                    <img src="<?= ICONS_DIR . $item['icon'] ?>" alt=""/>
        <? if ($item['upgraded'] === '1'): ?>
                    (+)&nbsp;
        <? elseif ($item['upgraded'] !== '0'): ?>
                    (+&nbsp;<?= $item['upgraded'] ?>)&nbsp;
        <? endif; ?>
        <?= htmlspecialchars($item['name']) ?>
        <? if ($item['vocation']): ?>
                    &nbsp;<img src="/images/icons/armr_<?= substr($item['vocation'],
                            0, 3) ?>.gif" alt="<?= $item['vocation'] ?>"/>
        <? endif; ?>
                </td>
                <td>
                    <img src="/images/icons/level.gif" alt=""/><?= $item['level'] ?>
                </td>
                <td><?= _($item['slot']) ?></td>
                <td>
        <?= $nbsp = false; ?>
        <? foreach (DMG_ELEMENTS as $element): ?>
            <? if ($item[$element] != 0): ?>
                <? if ($nbsp == false):
                    $nbsp = true;
                else: ?>
                    &nbsp;
                <? endif; ?>
                <img src="/images/icons/<?= $element ?>.png" alt="<?= $element ?>"/><?= $item[$element] ?>%
            <? endif; ?>
        <? endforeach; ?>
                </td>
                <td>
        <? if (!empty($item['source'])): ?>
            <? foreach ($item['source'] as $i => $monster): ?>
                <? if ($i != 0): ?>
                    , 
                <? endif; ?>
                <a href="./monsters.php?name=<?= urlencode($monster) ?>"><?= $monster ?></a>
            <? endforeach; ?>
        <? endif; ?>
                </td>
            </tr>
    <? endforeach; ?>
<? endif; ?>
    </tbody>
</table>
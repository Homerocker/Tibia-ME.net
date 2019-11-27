<form action="<?= $_SERVER['SCRIPT_NAME'] ?>" method="get">
    <div class="callout secondary">
        <label for="vocation"><?= _('Vocation') ?></label>
        <select id="vocation" name="vocation">
            <option value=""><?= _('any') ?></option>
            <option value="warrior"<?= (($vocation == 'warrior') ? ' selected="selected"' : '') ?>><?= _('warrior') ?></option>
            <option value="wizard"<?= (($vocation == 'wizard') ? ' selected="selected"' : '') ?>><?= _('wizard') ?></option>
        </select>
        <label for="type"><?= _('Type') ?></label>
        <select id="type" name="type">
            <option value=""><?= _('any') ?></option>
            <option value="heal"<?= (($type == 'heal') ? ' selected="selected"' : '') ?>><?= _('heal') ?></option>
            <option value="buff"<?= (($type == 'buff') ? ' selected="selected"' : '') ?>><?= _('buff') ?></option>
            <option value="debuff"<?= (($type == 'debuff') ? ' selected="selected"' : '') ?>><?= _('debuff') ?></option>
            <option value="hit"<?= (($type == 'hit') ? ' selected="selected"' : '') ?>><?= _('hit') ?></option>
            <option value="fire"<?= (($type == 'fire') ? ' selected="selected"' : '') ?>><?= _('fire') ?></option>
            <option value="ice"<?= (($type == 'ice') ? ' selected="selected"' : '') ?>><?= _('ice') ?></option>
            <option value="energy"<?= (($type == 'energy') ? ' selected="selected"' : '') ?>><?= _('energy') ?></option>
            <option value="soul"<?= (($type == 'soul') ? ' selected="selected"' : '') ?>><?= _('soul') ?></option>
            <option value="weapon"<?= (($type == 'weapon') ? ' selected="selected"' : '') ?>><?= _('weapon') ?></option>
        </select>
        <label for="target"><?= _('Target') ?></label>
        <select for="target" name="target">
        <option value=""><?= _('any') ?></option>
        <option value="self"<?= (($target == 'self') ? ' selected="selected"' : '') ?>><?= _('self') ?></option>
        <option value="guild"<?= (($target == 'guild') ? ' selected="selected"' : '') ?>><?= _('guild') ?></option>
        <option value="AoE"<?= (($target == 'AoE') ? ' selected="selected"' : '') ?>><?= _('AoE') ?></option>
        <option value="single"<?= (($target == 'single') ? ' selected="selected"' : '') ?>><?= _('single') ?></option>
        </select>
        <label for="order"><?= _('Order') ?></label>
        <select id="order" name="order">
        <option value="asc"><?= _('ascending') ?></option>
        <option value="desc"<?= ($order == 'desc' ? ' selected="selected"' : '') ?>><?= _('descending') ?></option>
        </select>
        <input class="button primary" type="submit" value="<?= _('Update') ?>"/>
    </div>
</form>
<? if (empty($data)): ?>
    <div class="callout secondary text-center">
    <?= _('No items to display.') ?>
    </div>
<? else: ?>
    <table class="stack">
        <thead>
            <tr>
                <td><?= _('Spell') ?></td>
                <td><?= _('Level') ?></td>
                <td><?= _('Mana') ?></td>
                <td><?= _('Amount') ?></td>
                <td><?= _('Target') ?></td>
                <td><?= _('Cooldown') ?></td>
                <td><?= _('Duration') ?></td>
                <td><?= _('Description') ?></td>
            </tr>
        </thead>
        <tbody>
    <? foreach ($data as $i => $spell): ?>
            <tr>
                <td>
                    <? if ($spell['icon'] !== null): ?>
                        <img src="<?= $spell['icon'] ?>" alt=""/> 
                    <? endif; ?>
                    <?= $spell['name'] ?>
                    <? if ($spell['vocation'] !== null): ?>
                         <img src="/images/icons/wpn_<?= substr($spell['vocation'], 0, 3) ?>.gif" alt=""/>
                    <? endif; ?>
                </td>
                <td>
                    <img src="/images/icons/level.gif" alt=""/><?= $spell['level'] ?>
                </td>
                <td>
                    <img src="/images/icons/mana.gif" alt=""/><?= $spell['mana'] ?>
                </td>
                <td>
                    <? if ($spell['dmg'] !== null): ?>
                        <? foreach (DMG_ELEMENTS as $element): ?>
                            <? if (in_array($element, $spell['type'])): ?>
                                <img src="/images/icons/<?= $element ?>.gif" alt=""/>
                            <? endif; ?>
                        <? endforeach; ?>
                        <? if (in_array('weapon', $spell['type'])): ?>
                            <img src="/images/icons/wpn_war.gif" alt=""/>
                        <? endif; ?>
                        <?= $spell['dmg'] ?><br/>
                    <? endif; ?>
                    <? if ($spell['heal'] !== null): ?>
                        <img src="/images/icons/hp.gif" alt=""/>
                        <?= $spell['heal'] ?>
                        <span class="nowrap">(<?= sprintf(_('%.2f HP/s'), $spell['hps']) ?>)</span>
                        <? // echo '<br/>', sprintf(_('%.2f HP per 1 MP'), $spell['hpmp']); ?>
                    <? endif; ?>
                    <? if ($spell['amount'] !== null): ?>
                        <? if ('vulnerability' == $spell['modifies'] || (in_array('buff', $spell['type']) && ($spell['modifies'] == 'damage' || $spell['modifies'] == 'attack'))): ?>
                            <? switch ($spell['target']) {
                                case 'guild':
                                case 'AoE':
                                    for ($i = 0; $i < 4; ++$i) {
                                        echo '<img src="/images/icons/wpn_war.gif" alt=""/>';
                                    }
                                    break;
                                default:
                                    echo '<img src="/images/icons/wpn_war.gif" alt=""/>';
                            } ?>
                        <? elseif (in_array('debuff', $spell['type']) && ($spell['modifies'] == 'attack' || $spell['modifies'] == 'damage')): ?>
                            <? switch ($spell['target']) {
                                case 'guild':
                                case 'AoE':
                                    for ($i = 0; $i < 4; ++$i) {
                                        echo '<img src="/images/icons/defense.gif" alt=""/>';
                                    }
                                    break;
                                default:
                                    echo '<img src="/images/icons/defense.gif" alt=""/>';
                            } ?>
                        <? endif; ?>
                        <?= $spell['amount'] ?>
                    <? endif; ?>
                </td>
                <td>
                    <span class="show-for-medium"><?= _('Target') ?>: </span>
                    <? // translations
                    switch ($spell['target']) {
                        case 'self':
                            echo _('self');
                            break;
                        case 'AoE':
                            echo _('AoE');
                            break;
                        case 'guild':
                            echo _('guild');
                            break;
                        case 'single':
                            echo _('single enemy');
                            break;
                    } ?>
                </td>
                <td>
                    <span class="show-for-medium"><?= _('Cooldown') ?>: </span>
                    <?= $spell['cooldown'] ?>s
                </td>
                <td>
                    <span class="show-for-medium"><?= _('Duration') ?>: </span>
                    <?= $spell['duration'] ? $spell['duration'] . 's' : '-' ?>
                </td>
                <td>
                    <?= _($spell['description']) ?>
                </td>
            </tr>
    <? endforeach; ?>
        </tbody>
    </table>
<? endif; ?>
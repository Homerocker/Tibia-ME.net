<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="get">
    <div class="callout secondary">
        <label for="world"><?php echo _('World'); ?></label>
        <select id="world" name="world">
        <option value=""><?php echo _('all'); ?></option>
        <?php
        for ($i = 1; $i <= WORLDS; ++$i) {
            ?>
            <option value="<?php echo $i; ?>"<?php if ($world == $i) echo ' selected="selected"'; ?>><?php echo $i; ?></option>
            <?php
        }
        ?>
        </select>
        <label for="vocation"><?php echo _('Vocation'); ?></label>
        <select id="vocation" name="vocation">
            <option value=""><?php echo _('all'); ?></option>
            <option value="warrior"<?php if ($vocation == 'warrior') echo ' selected="selected"'; ?>><?php echo _('warriors'); ?></option>
            <option value="wizard"<?php if ($vocation == 'wizard') echo ' selected="selected"'; ?>><?php echo _('wizards'); ?></option>
        </select>
        <input class="button primary" type="submit" value="<?= _('Confirm') ?>"/>
    </div>
</form>
<table>
    <thead>
        <tr>
            <td><?= _('Rank') ?></td>
            <td><?=_('Character')?></td>
            <td><?=_('Points')?></td>
        </tr>
    </thead>
    <tbody>
<?php
if (empty($data)) {
    echo '<tr><td class="text-center" colspan="3">';
    echo _('No data.');
    echo '</td></tr>';
} else {
    foreach ($data as $key => $array) {
        echo '<tr>';
        echo '<td>' . $array['rank'] . '</td>';
        echo '<td>';
        echo '<a href="./viewscores.php?characterID=' , $array['id'] , '">';
        echo '<img src="/images/icons/armour_'.$array['vocation'].'.png" alt="',$array['vocation'],'"/> ' , $array['nickname'];
        if (!isset($world)) {
            echo ', w' . $array['world'];
        }
        echo '</a></td>';
        echo '<td>' . $array['points'] . '</td>';
        echo '</tr>';
    }
}
?>
    </tbody>
</table>
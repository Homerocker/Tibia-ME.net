<table>
    <caption><?= _('Emoticons') ?></caption>
    <tbody>
<?php foreach($data as $key => $array) { ?>
    <tr>
        <td>
            <img src="<?=SMILIES_DIR?>/<?=$array['image']?>" alt="<?=$array['image']?>"/>
        </td>
        <td>
            <?=$array['code']?>
        </td>
    </tr>
</div>
<?php
} ?>
    </tbody>
</table>
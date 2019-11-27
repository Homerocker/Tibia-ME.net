<?= _('Upload artwork') ?>
<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
    <div class="callout primary text-center">
        <?php
        if (!empty($result)):
            echo $result, '<br/>';
        endif;
        if ($thumbnail):
            ?>
            <img class="thumbnail" src="<?= $thumbnail ?>" alt=""/>
        <?php endif; ?>

        <input type="hidden" name="id" value="<?= $artwork_id ?>"/>
        <div class="input-group">
            <input class="input-group-field" type="file" name="artwork"/>
            <div class="input-group-button">
                <input class="button primary" type="submit" name="upload" value="<?= (($artwork_id == 'auto_increment')
                    ? _('Upload') : _('Save')) ?>"/>
            </div>
        </div>
    </div>
</form>
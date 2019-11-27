<h3><?php echo _('Add photos'); ?></h3>
<form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data">
    <div class="callout secondary">
        <input class="nbt" type="file" name="photo[]"/>
        <input class="nbt" type="file" name="photo[]"/>
        <input class="nbt" type="file" name="photo[]"/>
        <input class="nbt" type="file" name="photo[]"/>
        <input class="nbt" type="file" name="photo[]"/>
        <input type="hidden" name="album_id" value="<?= $_REQUEST['album_id'] ?>"/>
        <input class="button primary" type="submit" name="upload" value="<?php echo _('Upload'); ?>"/>
    </div>
</form>
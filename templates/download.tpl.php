<? if ($version === false): ?>
    <p><?= _('Downloads are temporarily unavailable.') ?></p>
<? else: ?>
    <div class="grid-x grid-padding-x grid-padding-y">
        <div class="cell medium-6 large-4">
            <h3>IPhone</h3>
            <ul>
                <li><a href="<?= $version['iphone']['url'] ?>">TibiaME <?= $version['iphone']['version'] ?></a></li>
            </ul>
        </div>
        <div class="cell medium-6 large-4">
            <h3>Android</h3>
            <ul>
                <li><a href="<?= $version['android']['url'] ?>">TibiaME <?= $version['android']['version'] ?></a></li>
            </ul>
        </div>
        <div class="cell medium-6 large-4">
            <h3>Windows Phone</h3>
            <ul>
                <li><a href="<?= $version['wp']['url'] ?>">TibiaME <?= $version['wp']['version'] ?></a></li>
            </ul>
        </div>
        <div class="cell medium-6 large-4">
            <h3>Windows 8</h3>
            <ul>
                <li><a href="<?= $version['win8']['url'] ?>">TibiaME <?= $version['win8']['version'] ?></a></li>
            </ul>
        </div>
        <div class="cell medium-6 large-4">
            <h3>Series 60</h3>
            <ul>
                <li><a href="<?= $version['s60_classic']['url'] ?>">TibiaME <?= $version['s60_classic']['version'] ?> Classic</a></li>
            </ul>
        </div>
        <div class="cell medium-6 large-4">
            <h3>S60v3</h3>
            <ul>
                <li><a href="<?= $version['s60v3']['url'] ?>">TibiaME <?= $version['s60v3']['version'] ?></a></li>
                <li><a href="<?= $version['s60v3_classic']['url'] ?>">TibiaME <?= $version['s60v3_classic']['version'] ?> Classic</a></li>
            </ul>
        </div>
        <div class="cell medium-6 large-4">
            <h3>S60v5</h3>
            <ul>
                <li><a href="<?= $version['s60v5']['url'] ?>">TibiaME <?= $version['s60v5']['version'] ?></a></li>
            </ul>
        </div>
        <div class="cell medium-6 large-4">
            <h3>Java ME</h3>
            <ul>
                <li><a href="<?= $version['j2me_classic']['url'] ?>">TibiaME <?= $version['j2me_classic']['version'] ?></a></li>
                <li><a href="<?= $version['j2me_motorola_classic']['url'] ?>">TibiaME <?= $version['j2me_motorola_classic']['version'] ?> Motorola</a></li>
                <li><a href="<?= $version['j2me_basic']['url'] ?>">TibiaME <?= $version['j2me_basic']['version'] ?> Basic</a></li>
                <li><a href="<?= $version['j2me_motorola_basic']['url'] ?>">TibiaME <?= $version['j2me_motorola_basic']['version'] ?> Motorola Basic</a></li>
                <li><a href="<?= $version['j2me_blackberry_classic']['url'] ?>">TibiaME <?= $version['j2me_blackberry_classic']['version'] ?> Blackberry</a></li>
            </ul>
        </div>
    </div>
<? endif; ?>
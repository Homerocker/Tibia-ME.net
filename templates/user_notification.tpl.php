<?php if (!empty($_SESSION['notification'])) { ?>
    <div class="callout primary text-center" data-closable="slide-out-right">
        <button class="close-button" aria-label="Close alert" type="button" data-close>
            <span aria-hidden="true">&times;</span>
        </button>
        <p><?= implode('</p><p>', $_SESSION['notification']) ?></p>
    </div>
    <?php
    unset($_SESSION['notification']);
}
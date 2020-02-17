<form action="<?= $_SERVER['PHP_SELF'] ?>" method="get">
    <div class="callout primary">
        <? $form->field('nickname')->display(_('Nickname')) ?>
        <? $form->field('world')->display(_('World')) ?>
        <? $form->field('currency')->display(_('Payment method')) ?>
        <? $form->field('desired_amount')->display(_('Platinum amount')) ?>
        <? $form->field('amount')->display() ?>
        <div class="text-center b">You will receive <span id="amount_display">0</span> platinum for <span class="nowrap"
                                                                                                          id="price">USD 0</span>.
        </div>
        <? $form->field('submit')->display(_('Proceed')) ?>
    </div>
</form>

<input type="hidden" id="amount" name="amount" value=""/>

<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function () {
        get_platinum_bundle($("#desired_amount").val(), $("#currency").val());
    });
</script>
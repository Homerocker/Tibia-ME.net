<? if ($field->type !== 'submit' && $label !== null): ?>
    <label<?= (empty($field->errors) ? '' : ' class="is-invalid-label"') ?>
            for="<?= $field->name ?>"><?= $label ?>
        <? if (isset($field->minlength) && isset($field->maxlength)): ?>
            <span class="text-small">
                <? if ($field->minlength == $field->maxlength): ?>
                    (<?= $field->minlength ?>)
                <? else: ?>
                    (<?= $field->minlength ?>-<?= $field->maxlength ?>)
                <? endif; ?>
            </span>
        <? endif; ?>
        <? if ($field->required): ?>
            <span class="red b">*</span>
        <? endif; ?>
    </label>
<? endif; ?>
<? if (isset($field->description)): ?>
    <p class="help-text">(<?= $field->description ?>)</p>
<? endif; ?>
<?php
$class = [];
if (!empty($field->errors)) {
    $class[] = 'is-invalid-input';
}
if ($field->type == 'submit') {
    $class[] = 'button';
}
?>
<? if ($field->element == 'textarea'): ?>
    <textarea<?= (empty($class) ? '' : ' class="' . implode(' ', $class) . '"') ?> id="<?= $field->id ?>" name="<?= $field->name ?>" rows="<?= $field->rows ?>"><?= htmlspecialchars($field->value) ?></textarea>
<? elseif ($field->element == 'select'): ?>
    <select<?= (empty($class) ? '' : ' class="' . implode(' ', $class) . '"') ?> id="<?= $field->id ?>" name="<?= $field->name ?>">
        <? if (!$field->required): ?>
            <option value=""></option>
        <? endif; ?>
        <? foreach ($field->options as $key => $value): ?>
            <option value="<?= $key ?>"<?= ($key == $field->value ? ' selected' : '') ?>><?= $value ?></option>
        <? endforeach; ?>
    </select>
<? else: ?>
    <input<?= ($field->disabled ? ' disabled="disabled"' : '') ?><?= (empty($class) ? '' : ' class="' . implode(' ', $class) . '"') ?> id="<?= $field->id ?>"
                                                                                type="<?= $field->type ?>"
                                                                                name="<?= $field->name ?>"
                                                                                maxlength="<?= $field->maxlength ?>" value="<?= ($field->type == 'submit' ? $label : htmlspecialchars($field->value)) ?>"<?= $field->get_events_string() ?>/>
<? endif; ?>
<? if (!empty($field->errors)): ?>
    <p class="form-error is-visible">
        <? foreach ($field->errors as $error): ?>
            <?= $error ?><br/>
        <? endforeach; ?>
    </p>
<? endif; ?>

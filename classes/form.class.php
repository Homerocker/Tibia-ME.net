<?php

/**
 * HTML form data and error handling.
 *
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2018, Tibia-ME.net
 */
class Form
{
    public $fields = [];

    public function addinput($id, $type, $name, $minlength = null, $maxlength = null, $required = true, $pattern = null)
    {
        $this->fields[$id] = new FormInput($id, $type, $name, $minlength, $maxlength, $required, $pattern);
    }

    public function addselect($id, $name, $options, $required = true)
    {
        $this->fields[$id] = new FormSelect($id, $name, $options, $required);
    }

    public function submit()
    {
        if (!isset($_REQUEST['submit'])) {
            return false;
        }

        $valid = true;

        foreach ($this->fields as &$field) {
            if ($field->type == 'submit') {
                continue;
            }

            $field->value = $_REQUEST[$field->name] ?? null;
            if ($field->required && $field->value === null) {
                // required field not passed
                $field->errors[] = _('Field is required.');
                continue;
            } else {
                $field->value = trim($field->value);
            }

            $bytes = strlen($field->value);
            $chars = mb_strlen($field->value);
            if ($bytes === 0) {
                if ($field->required) {
                    // required field is empty
                    $field->errors[] = _('Field is required.');
                } else {
                    // optional field is empty
                    continue;
                }
            } elseif ($field->type == 'input' || $field->type == 'textarea') {
                if ($chars < $field->minlength) {
                    $field->errors[] = _('Input is too short.');
                } elseif ($bytes > 65535 || $chars > $field->maxlength) {
                    $field->errors[] = _('Input is too long.');
                }
            } elseif ($field->type == 'select' && !array_key_exists($field->value, $field->options)) {
                $field->errors[] = _('Invalid input.');
            }

            if (isset($field->pattern) && !preg_match($field->value, '^/' . $field->pattern . '/$')) {
                $field->errors[] = _('Invalid input.');
            }
            if (!empty($field->errors) && $valid == true) {
                $valid = false;
            }
        }

        return $valid;
    }

}

class FormField
{
    public $id, $type, $name, $required, $value = '', $errors = [];

    public function __construct($id, $type, $name, $required)
    {
        $this->id = $id;
        $this->type = $type;
        $this->name = $name;
        $this->required = $required;
    }

    public function display($label = null)
    {
        $templates = new Templates;
        $templates->assign([
            'label' => $label,
            'field' => $this
        ]);
        $templates->display('formfield');
    }
}

class FormInput extends FormField
{
    public $minlength, $maxlength, $pattern;

    public function __construct($id, $type, $name, $minlength, $maxlength, $required, $pattern)
    {
        parent::__construct($id, $type, $name, $required);
        $this->minlength = $minlength;
        $this->maxlength = $maxlength;
        $this->pattern = $pattern;
    }
}

class FormSelect extends FormField
{
    public $options;

    public function __construct($id, $name, $options, $required)
    {
        parent::__construct($id, 'select', $name, $required);
        $this->options = $options;
    }
}

class FormTextarea extends FormField
{
    public $rows;

    public function __construct($id, $name, $rows = 5, $required)
    {
        parent::__construct($id, 'textarea', $name, $required);
        $this->rows = $rows;
    }
}
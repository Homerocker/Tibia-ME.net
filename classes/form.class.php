<?php

/**
 * HTML form data and error handling.
 *
 * @author Molodoy <molodoy3561@gmail.com>
 * @copyright (c) 2018, Tibia-ME.net
 */
class Form
{
    private $fields = [], $name;

    /**
     * Form constructor.
     * @param $form_name
     */
    public function __construct($form_name)
    {
        $this->name = $form_name;
        $this->fields['submit'] = new FormInput($this->name, 'submit', $this->name);
    }

    public function addinput($id, $type, $name, $minlength = null, $maxlength = null, $required = true, $pattern = null)
    {
        $this->fields[$id] = new FormInput($id, $type, $name, $minlength, $maxlength, $required, $pattern);
    }

    public function addselect($id, $name, $options, $required = true)
    {
        $this->fields[$id] = new FormSelect($id, $name, $options, $required);
    }

    public function addtextarea($id, $name, $rows = 5, $maxlength = null, $required = true)
    {
        $this->fields[$id] = new FormTextarea($id, $name, $rows, $maxlength, $required);
    }

    public function submit()
    {
        if (!isset($_REQUEST[$this->name])) {
            return false;
        }

        $valid = true;

        foreach ($this->fields as &$field) {
            if ($field->type == 'submit') {
                continue;
            }

            $field->value = isset($_REQUEST[$field->name]) ? trim($_REQUEST[$field->name]) : null;
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
            } else {
                if ($field->element == 'input' || $field->element == 'textarea') {
                    if (isset($field->minlength) && $chars < $field->minlength) {
                        $field->errors[] = _('Input is too short.');
                    } elseif ($bytes > 65535 || (isset($field->maxlength) && $chars > $field->maxlength)) {
                        $field->errors[] = _('Input is too long.');
                    }
                } elseif ($field->element == 'select' && !array_key_exists($field->value, $field->options)) {
                    $field->errors[] = _('Invalid input.');
                }

                if (isset($field->pattern) && !preg_match($field->value, '^/' . $field->pattern . '/$')) {
                    $field->errors[] = _('Invalid input.');
                }
                foreach ($field->validate as $validate) {
                    if (!$validate['handler']($field->value)) {
                        $field->errors[] = $validate['error'] ?? _('Invalid input.');
                    }
                }
                $field->errors = array_unique($field->errors);
            }
            if (!empty($field->errors) && $valid == true) {
                $valid = false;
            }
        }

        return $valid;
    }

    public function field($id)
    {
        return $this->fields[$id];
    }

}

class FormField
{
    public $id, $type, $name, $required, $value = '', $description = null, $events = [], $validate = [], $errors = [];

    public function __construct($id, $name, $required)
    {
        $this->id = $id;
        $this->name = $name;
        $this->required = $required;
    }

    public function event($event, $handler): bool
    {
        if (!in_array($event, ['onupdate', 'oninput', 'onchange', 'onclick'])) {
            return false;
        }
        $this->events[$event] = $handler;
        return true;
    }

    public function value($value = null)
    {
        if ($value) {
            $this->value = $value;
        }
        return $this->value;
    }

    public function get_events_string(): string
    {
        $events = '';
        foreach ($this->events as $event => $handler) {
            $events .= ' ' . $event . '="' . htmlspecialchars($handler) . '"';
        }
        return $events;
    }

    public function set_description($description): bool
    {
        $this->description = $description;
        return true;
    }

    public function validate($handler, $error_message = null): bool
    {
        $this->validate[] = [
            'handler' => $handler,
            'error' => $error_message
        ];
        return true;
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
    public $element = 'input';
    public $type, $minlength, $maxlength, $pattern, $disabled = false;

    public function __construct($id, $type, $name, $minlength = null, $maxlength = null, $required = true, $pattern = null)
    {
        $this->type = $type;
        $this->minlength = $minlength;
        $this->maxlength = $maxlength;
        $this->pattern = $pattern;
        parent::__construct($id, $name, $required);
    }

    public function set_disabled(bool $disabled): bool
    {
        return $this->disabled = $disabled;
    }
}

class FormSelect extends FormField
{
    public $element = 'select';
    public $options;

    public function __construct($id, $name, $options, $required)
    {
        if ($options instanceof Closure) {
            $this->options = $options();
        } else {
            $this->options = $options;
        }
        parent::__construct($id, $name, $required);
    }
}

class FormTextarea extends FormField
{
    public $element = 'textarea';
    public $rows, $maxlength;

    public function __construct($id, $name, $rows, $maxlength, $required)
    {
        $this->rows = $rows;
        $this->maxlength = $maxlength;
        parent::__construct($id, $name, $required);
    }
}
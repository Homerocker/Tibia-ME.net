<?php

/**
 * @author Molodoy
 * @copyright Copyright (c) 2011 Tibia-ME.net
 * @version 2.4
 */
class Templates {

    /**
     * @var array list of variables names and values which will be available in template 
     */
    private $vars = array();

    /**
     * sends variables from $vars to template
     * @param array|string $var1 either variable name or an array with variables names and values
     * @param string $var2 optional variable value, required if $var1 is not an array, otherwise ignored
     * @return boolean true
     */
    public function assign($var1, $var2 = null) {
        if (is_array($var1)) {
            foreach ($var1 as $name => $value) {
                $this->vars[$name] = $value;
            }
        } else {
            $this->vars[$var1] = $var2;
        }
    }

    /**
     * displays template
     * @param string $template_name template file name without extension
     */
    public function display($template_name) {
        foreach ($this->vars as $tpl_var_name => $tpl_var_value) {
            ${$tpl_var_name} = $tpl_var_value;
        }
        unset($tpl_var_name, $tpl_var_value);
        require __DIR__ . '/../templates/' . $template_name . '.tpl.php';
    }

    public static function buttons($buttons, $form_submit = false) {
        require __DIR__ . '/../templates/buttons.tpl.php';
        Templates::buttons([
            [_('Accept'), '/ok.php', true],
            [_('Cancel'), '/no.php']
                ]);
    }

}

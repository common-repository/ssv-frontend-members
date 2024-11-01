<?php
if (!defined('ABSPATH')) {
    exit;
}
/**
 * This function can be called from anywhere and will redirect the page to the given location.
 *
 * @param string $location is the url where the page should be redirected to.
 */
function ssv_redirect($location)
{
    $redirect_script = '<script type="text/javascript">';
    $redirect_script .= 'window.location = "' . $location . '"';
    $redirect_script .= '</script>';
    echo $redirect_script;
}

/**
 * This function is for development purposes only and lets the developer print a variable in the PHP formatting to inspect what the variable is set to.
 *
 * @param mixed $variable any variable that you want to be printed.
 * @param bool  $die      set true if you want to call die() after the print. $die is ignored if $return is true.
 * @param bool  $return   set true if you want to return the print as string.
 * @param bool  $newline  set false if you don't want to print a newline at the end of the print.
 *
 * @return mixed|null|string returns the print in string if $return is true, returns null if $return is false, and doesn't return if $die is true.
 */
function ssv_print($variable, $die = false, $return = false, $newline = true)
{
    $print = highlight_string("<?php " . var_export($variable, true), true);
    $print = trim($print);
    $print = preg_replace("|^\\<code\\>\\<span style\\=\"color\\: #[a-fA-F0-9]{0,6}\"\\>|", "", $print, 1);  // remove prefix
    $print = preg_replace("|\\</code\\>\$|", "", $print, 1);
    $print = trim($print);
    $print = preg_replace("|\\</span\\>\$|", "", $print, 1);
    $print = trim($print);
    $print = preg_replace("|^(\\<span style\\=\"color\\: #[a-fA-F0-9]{0,6}\"\\>)(&lt;\\?php&nbsp;)(.*?)(\\</span\\>)|", "\$1\$3\$4", $print);
    if ($return) {
        return $print;
    } else {
        echo $print;
        if ($newline) {
            echo '<br/>';
        }
    }

    if ($die) {
        die();
    }
    return null;
}

function ssv_get_tr($id, $content, $visible = true)
{
    ob_start();
    if ($visible) {
        ?>
        <tr id="<?php echo $id; ?>"
            style="vertical-align: top; border-bottom: 1px solid gray; border-top: 1px solid gray;">
            <?php
            echo $content;
            ?>
        </tr>
        <?php
    } else {
        ?>
        <tr id="<?php echo $id; ?>" style="display: none;">
            <?php
            echo $content;
            ?>
        </tr>
        <?php
    }

    return trim(preg_replace('/\s+/', ' ', ob_get_clean()));
}

function ssv_get_td($content, $colspan = 1)
{
    ob_start();
    ?>
    <td style="vertical-align: middle; cursor: move;" colspan="<?php echo $colspan; ?>"><?php echo $content; ?></td>
    <?php
    return trim(preg_replace('/\s+/', ' ', ob_get_clean()));
}

function ssv_get_draggable_icon()
{
    ob_start();
    ?><img src="<?php echo plugins_url('images/icon-menu.svg', __FILE__); ?>" style="padding-right: 15px; margin: 10px 0;"/><?php
    return trim(preg_replace('/\s+/', ' ', ob_get_clean()));
}

function ssv_get_text_input($title, $id, $value, $type = "text", $args = array(), $esc_html = true)
{
    $title = $esc_html ? esc_html($title) : $title;
    $id    = $esc_html ? esc_html($id) : $id;
    $value = $esc_html ? esc_html($value) : $value;
    $type  = $esc_html ? esc_html($type) : $type;
    ob_start();
    if ($title != "") {
        $object_name = $id . "_" . strtolower(str_replace(" ", "_", $title));
        ?>
        <label for="<?php echo $object_name; ?>"><?php echo $title; ?></label>
        <br/>
        <?php
    } else {
        $object_name = $id;
    }
    ?>
    <input type="<?php echo $type; ?>" id="<?php echo $object_name; ?>" name="<?php echo $object_name; ?>" style="width: 100%;"
           value="<?php echo $value; ?>" <?php foreach ($args as $arg) {
        echo $arg . ' ';
    } ?>/>
    <?php
    return trim(preg_replace('/\s+/', ' ', ob_get_clean()));
}

function ssv_get_text_area($title, $id, $value, $type = "text", $args = array(), $esc_html = true)
{
    $title = $esc_html ? esc_html($title) : $title;
    $id    = $esc_html ? esc_html($id) : $id;
    $value = $esc_html ? esc_html($value) : $value;
    $type  = $esc_html ? esc_html($type) : $type;
    ob_start();
    if ($title != "") {
        $object_name = $id . "_" . strtolower(str_replace(" ", "_", $title));
        ?>
        <label for="<?php echo $object_name; ?>"><?php echo $title; ?></label>
        <br/>
        <?php
    } else {
        $object_name = $id;
    }
    ?>
    <textarea type="<?php echo $type; ?>" id="<?php echo $object_name; ?>" name="<?php echo $object_name; ?>" style="width: 100%;"
           <?php foreach ($args as $arg) {
        echo $arg . ' ';
    } ?>><?php echo $value; ?></textarea>
    <?php
    return trim(preg_replace('/\s+/', ' ', ob_get_clean()));
}

function ssv_get_select($title, $id, $selected, $options, $args = array(), $allow_custom = false, $input_type_custom = null, $title_on_newline = true, $esc_html = true)
{
    $title = $esc_html ? esc_html($title) : $title;
    $id    = $esc_html ? esc_html($id) : $id;
    ob_start();
    if ($allow_custom) {
        $options[] = "Custom";
    }
    $object_name        = $id . "_" . strtolower(str_replace(" ", "_", $title));
    $object_custom_name = $id . "_" . strtolower(str_replace(" ", "_", $title)) . "_custom";
    ?>
    <label for="<?php echo $object_name; ?>"><?php echo $title; ?></label>
    <?php
    if ($title_on_newline) {
        echo '<br/>';
    }
    ?>
    <select id="<?php echo $object_name; ?>" name="<?php echo $object_name; ?>" style="width: 100%;" <?php foreach ($args as $arg) {
        echo $arg . ' ';
    } ?>>
        <?php foreach ($options as $option) { ?>
            <option value="<?php echo strtolower(str_replace(" ", "_", $option)); ?>" <?php if ($selected == strtolower(str_replace(" ", "_", $option))) {
                echo "selected";
            } ?>><?php echo $esc_html ? esc_html($option) : $option; ?></option>
        <?php } ?>
    </select>
    <?php if ($allow_custom && $selected == "custom") { ?>
    <div>
        <!--suppress HtmlFormInputWithoutLabel -->
        <input type="text" id="<?php echo $object_custom_name; ?>" name="<?php echo $object_custom_name; ?>" style="width: 100%;"
               value="<?php echo $input_type_custom; ?>" required/>
    </div>
<?php }

    return trim(preg_replace('/\s+/', ' ', ob_get_clean()));
}

function ssv_get_checkbox($title, $id, $value, $args = array(), $on_new_line = false, $esc_html = true)
{
    $title = $esc_html ? esc_html($title) : $title;
    $id    = $esc_html ? esc_html($id) : $id;
    $value = $esc_html ? esc_html($value) : $value;
    ob_start();
    $object_name = $id . "_" . strtolower(str_replace(" ", "_", $title));
    if ($on_new_line) {
        ?><label for="<?php echo $object_name; ?>"><?php echo $title; ?></label><?php
    }
    ?>
    <br/><input type="checkbox" id="<?php echo $object_name; ?>" name="<?php echo $object_name; ?>"
                value="yes" <?php if ($value == "yes") : echo "checked"; endif; ?><?php foreach ($args as $arg):
    echo $esc_html ? esc_html($arg) : $arg; endforeach; ?>/><?php
    if (!$on_new_line) {
        ?><label for="<?php echo $object_name; ?>"><?php echo $title; ?></label><?php
    }
    return trim(preg_replace('/\s+/', ' ', ob_get_clean()));
}

function ssv_get_options($parent_id, $options, $type, $args = array(), $esc_html = true)
{
    $parent_id = $esc_html ? esc_html($parent_id) : $parent_id;
    $type      = $esc_html ? esc_html($type) : $type;
    ob_start();
    ?>
    <ul id="<?php echo $parent_id; ?>_options" style="margin: 0;">
        Options<br/>
        <?php foreach ($options as $option) :
            echo ssv_get_option($parent_id, $option, $args, $esc_html);
        endforeach; ?>
        <li>
            <button type="button" id="<?php echo $parent_id; ?>_add_option"
                    onclick="add_<?php echo $type; ?>_option(<?php echo $parent_id; ?>)">Add Option
            </button>
        </li>
    </ul>
    <?php
    return trim(preg_replace('/\s+/', ' ', ob_get_clean()));
}

function ssv_get_option($parent_id, $option, $args = array(), $esc_html = true)
{
    $parent_id = $esc_html ? esc_html($parent_id) : $parent_id;
    ob_start();
    $object_name = $parent_id . "_option" . $option["id"];
    $object_name = $esc_html ? esc_html($object_name) : $object_name;
    if ($option["type"] == "role") {
        echo "<li>" . ssv_get_role_select($object_name, "option", $option["value"], false, array(), $esc_html) . "</li>";
    } else {
        ?>
        <li>
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" id="<?php echo $object_name; ?>_option" name="<?php echo $object_name; ?>_option" style="width: 100%;"
                   value="<?php echo $esc_html ? esc_html($option["value"]) : $option["value"]; ?>" <?php foreach ($args as $arg) : echo $esc_html ? esc_html($arg) : $arg; endforeach; ?>/>
        </li>
        <?php
    }

    return trim(preg_replace('/\s+/', ' ', ob_get_clean()));
}

function ssv_get_hidden($id, $name, $value, $esc_html = true)
{
    $name        = $esc_html ? esc_html($name) : $name;
    $value       = $esc_html ? esc_html($value) : $value;
    $object_name = $id == null ? $name : $id . "_" . strtolower(str_replace(" ", "_", $name));
    ob_start();
    ?><input type="hidden" id="<?php echo $id; ?>" name="<?php echo $object_name; ?>" value="<?php echo $value; ?>"><?php
    return trim(preg_replace('/\s+/', ' ', ob_get_clean()));
}

function ssv_get_role_select($id, $title, $value, $with_title = true, $args = array(), $esc_html = true)
{
    $id          = $esc_html ? esc_html($id) : $id;
    $title       = $esc_html ? esc_html($title) : $title;
    $value       = $esc_html ? esc_html($value) : $value;
    $object_name = $id . "_" . strtolower(str_replace(" ", "_", $title));
    $object_name = $esc_html ? esc_html($object_name) : $object_name;
    ob_start();
    wp_dropdown_roles($value);
    $roles_options = trim(preg_replace('/\s+/', ' ', ob_get_clean()));
    $roles_options = trim(preg_replace('/\s\s+/', ' ', $roles_options));
    $roles_options = str_replace("'", '"', $roles_options);
    ob_start();
    if ($with_title) {
        ?>
        <label for="<?php echo $object_name; ?>"><?php echo $title; ?></label><br/>
        <?php
    }
    ?>
    <select id="<?php echo $object_name; ?>" name="<?php echo $object_name; ?>" style="width: 100%;" <?php foreach ($args as $arg) :
        echo $esc_html ? esc_html($arg) : $arg; endforeach; ?>>
        <option value=""></option><?php echo $roles_options; ?>
    </select>
    <?php
    return trim(preg_replace('/\s+/', ' ', ob_get_clean()));
}

function ssv_starts_with($haystack, $needle)
{
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

function ssv_ends_with($haystack, $needle)
{
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
}

function ssv_is_valid_iban($iban)
{
    $iban      = strtolower(str_replace(' ', '', $iban));
    $Countries = array('al' => 28, 'ad' => 24, 'at' => 20, 'az' => 28, 'bh' => 22, 'be' => 16, 'ba' => 20, 'br' => 29, 'bg' => 22, 'cr' => 21, 'hr' => 21, 'cy' => 28, 'cz' => 24, 'dk' => 18, 'do' => 28, 'ee' => 20, 'fo' => 18, 'fi' => 18, 'fr' => 27, 'ge' => 22, 'de' => 22, 'gi' => 23, 'gr' => 27, 'gl' => 18, 'gt' => 28, 'hu' => 28, 'is' => 26, 'ie' => 22, 'il' => 23, 'it' => 27, 'jo' => 30, 'kz' => 20, 'kw' => 30, 'lv' => 21, 'lb' => 28, 'li' => 21, 'lt' => 20, 'lu' => 20, 'mk' => 19, 'mt' => 31, 'mr' => 27, 'mu' => 30, 'mc' => 27, 'md' => 24, 'me' => 22, 'nl' => 18, 'no' => 15, 'pk' => 24, 'ps' => 29, 'pl' => 28, 'pt' => 25, 'qa' => 29, 'ro' => 24, 'sm' => 27, 'sa' => 24, 'rs' => 22, 'sk' => 24, 'si' => 19, 'es' => 24, 'se' => 24, 'ch' => 21, 'tn' => 24, 'tr' => 26, 'ae' => 23, 'gb' => 22, 'vg' => 24);
    $Chars     = array('a' => 10, 'b' => 11, 'c' => 12, 'd' => 13, 'e' => 14, 'f' => 15, 'g' => 16, 'h' => 17, 'i' => 18, 'j' => 19, 'k' => 20, 'l' => 21, 'm' => 22, 'n' => 23, 'o' => 24, 'p' => 25, 'q' => 26, 'r' => 27, 's' => 28, 't' => 29, 'u' => 30, 'v' => 31, 'w' => 32, 'x' => 33, 'y' => 34, 'z' => 35);

    try {
        if (strlen($iban) == $Countries[substr($iban, 0, 2)]) {

            $MovedChar      = substr($iban, 4) . substr($iban, 0, 4);
            $MovedCharArray = str_split($MovedChar);
            $NewString      = "";

            foreach ($MovedCharArray AS $key => $value) {
                if (!is_numeric($MovedCharArray[$key])) {
                    $MovedCharArray[$key] = $Chars[$MovedCharArray[$key]];
                }
                $NewString .= $MovedCharArray[$key];
            }

            if (bcmod($NewString, '97') == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } catch (Exception $ex) {
        return false;
    }
}

if (!function_exists('bcmod')) {
    function bcmod($x, $y)
    {
        $take = 5;
        $mod  = '';

        do {
            $a   = (int)$mod . substr($x, 0, $take);
            $x   = substr($x, $take);
            $mod = $a % $y;
        } while (strlen($x));

        return (int)$mod;
    }
}

?>
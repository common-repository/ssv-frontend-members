<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Created by: Jeroen Berkvens
 * Date: 23-4-2016
 * Time: 14:48
 */
class FrontendMember extends \WP_User
{
    /**
     * FrontendMember constructor.
     *
     * @param \WP_User $user the WP_User component used as base for the FrontendMember
     */
    function __construct($user)
    {
        parent::__construct($user);
    }

    /**
     * This function searches for a FrontendMember by its ID.
     *
     * @param int $id is the ID used to find the FrontendMember
     *
     * @return FrontendMember|null returns the FrontendMember it found or null if it can't find one.
     */
    public static function get_by_id($id)
    {
        if ($id == null) {
            return null;
        }
        return new FrontendMember(get_user_by('id', $id));
    }

    public static function get_current_user()
    {
        if (!is_user_logged_in()) {
            return null;
        }
        return new FrontendMember(wp_get_current_user());
    }

    public static function registerFromPOST()
    {
        $parent_id = wp_create_user(
            sanitize_text_field($_POST['username']),
            sanitize_text_field($_POST['password']),
            sanitize_text_field($_POST['email'])
        );
        unset($_POST['username']);
        unset($_POST['password']);
        unset($_POST['email']);

        return new FrontendMember(get_user_by('ID', $parent_id));
    }

    /**
     * @return bool returns true if this is the current user.
     */
    public function isCurrentUser()
    {
        if ($this->ID == wp_get_current_user()->ID) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return bool true if this user has the board role (and can edit other member profiles).
     */
    public function isBoard()
    {
        return in_array(get_option('ssv_frontend_members_board_role'), $this->roles);
    }

    /**
     * @param string $password The plaintext new user password
     *
     * @return bool false, if the $password does not match the member's password
     */
    public function checkPassword($password)
    {
        return wp_check_password($password, $this->data->user_pass, $this->ID);
    }

    /**
     * This function sets the metadata defined by the key (or an alias of that key).
     * The aliases are:
     *  - email, email_address, member_email => user_email
     *  - name => display_name
     *  - login, username, user_name => user_login
     * If the key contains "_role" or "_role_select" this function will also add, remove or change the role.
     *
     * @param string $meta_key the key that defines which metadata to set.
     * @param string $value    the value to set.
     *
     * @return bool|Message true if success, else it provides an object consisting of a message and a type (notification or error).
     */
    function updateMeta($meta_key, $value)
    {
        $currentUserIsBoardMember = FrontendMember::get_current_user() == null ?: FrontendMember::get_current_user()->isBoard();
        $value                    = sanitize_text_field($value);
        if ($meta_key == "email" || $meta_key == "email_address" || $meta_key == "user_email" || $meta_key == "member_email") {
            wp_update_user(array('ID' => $this->ID, 'user_email' => sanitize_text_field($value)));
            update_user_meta($this->ID, 'user_email', $value);
            $this->user_email = $value;
            return true;
        } elseif ($meta_key == "name" || $meta_key == "display_name") {
            wp_update_user(array('ID' => $this->ID, 'display_name' => sanitize_text_field($value)));
            update_user_meta($this->ID, 'display_name', sanitize_text_field($value));
            $this->display_name = $value;
            return true;
        } elseif ($meta_key == "first_name" || $meta_key == "last_name") {
            update_user_meta($this->ID, $meta_key, sanitize_text_field($value));
            $display_name = $this->getMeta('first_name') . ' ' . $this->getMeta('last_name');
            wp_update_user(array('ID' => $this->ID, 'display_name' => sanitize_text_field($display_name)));
            update_user_meta($this->ID, 'display_name', sanitize_text_field($display_name));
            $this->display_name = $display_name;
            return true;
        } elseif ($meta_key == "login" || $meta_key == "username" || $meta_key == "user_name" || $meta_key == "user_login") {
            return new Message('Cannot change the user-login. Please consider setting the field display to \'disabled\'', Message::NOTIFICATION_MESSAGE); //cannot change user_login
        } elseif ($meta_key == "iban" || $meta_key == "IBAN") {
            if (!ssv_is_valid_iban($value)) {
                return new Message('The IBAN is invalid!', Message::ERROR_MESSAGE);
            } else {
                update_user_meta($this->ID, $meta_key, $value);
                return true;
            }
        } elseif (strpos($meta_key, "_role_select") !== false) {
            $old_role = $this->getMeta($meta_key, true);
            if ($old_role == $value) {
                return true;
            }
            if ($currentUserIsBoardMember) {
                parent::remove_role($old_role);
                parent::add_role($value);
            }
            update_user_meta($this->ID, $meta_key, $value);

            if (!isset($_POST['register']) && !$currentUserIsBoardMember) {
                $to      = get_option('ssv_frontend_members_member_admin');
                $subject = "Member Role Changed";
                $url     = get_site_url() . '/profile/?user_id=' . $this->ID;
                $message = 'Hello,<br/><br/>' . $this->display_name . ' wants to changed his role from ' . $old_role . ' to ' . $value . '.<br/><a href="' . esc_url($url) . '" target="_blank">View User</a><br/><br/>Greetings, Jeroen Berkvens.';
                $headers = "From: " . get_option('ssv_frontend_members_member_admin') . "\r\n";
                add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
                wp_mail($to, $subject, $message, $headers);
            }

            return true;
        } elseif (strpos($meta_key, "_role") !== false) {
            $role      = str_replace("_role", "", $meta_key);
            $old_value = $this->getMeta($role, true);
            $to        = get_option('ssv_frontend_members_member_admin');
            if ($old_value == $value) {
                return true;
            }
            if ($value == "yes") {
                if ($currentUserIsBoardMember) {
                    parent::add_role($role);
                }
                $subject = "Member Joined " . $role;
                $url     = get_site_url() . '/profile/?user_id=' . $this->ID;
                $message = 'Hello,<br/><br/>' . $this->display_name . ' wants to join ' . $role . '.<br/><a href="' . esc_url($url) . '" target="_blank">View User</a><br/><br/>Greetings, Jeroen Berkvens.';
            } else {
                parent::remove_role($role);
                $subject = "Member Left " . $role;
                $url     = get_site_url() . '/profile/?user_id=' . $this->ID;
                $message = 'Hello,<br/><br/>' . $this->display_name . ' has left ' . $role . '.<br/><a href="' . esc_url($url) . '" target="_blank">View User</a><br/><br/>Greetings, Jeroen Berkvens.';
            }
            update_user_meta($this->ID, $role, $value);
            $headers = "From: " . get_option('ssv_frontend_members_member_admin') . "\r\n";
            add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
            if (!isset($_POST['register']) && !$currentUserIsBoardMember) {
                wp_mail($to, $subject, $message, $headers);
            }

            return true;
        } else {
            update_user_meta($this->ID, $meta_key, $value);

            return true;
        }
    }

    /**
     * This function returns the metadata associated with the given key (or an alias of that key).
     * The aliases are:
     *  - email, email_address, member_email => user_email
     *  - name => display_name
     *  - login, username, user_name => user_login
     * If the key contains "_role" this function will return if the FrontendMember is part of that role.
     *
     * @param string $meta_key defines which metadata should be returned.
     * @param bool   $single   defines if it should return a single value or an array of values. Default it will return
     *                         a single value.
     *
     * @return string the value associated with the key.
     */
    function getMeta($meta_key, $single = true)
    {
        if ($meta_key == "email" || $meta_key == "email_address" || $meta_key == "user_email" || $meta_key == "member_email") {
            return $this->user_email;
        } elseif ($meta_key == "name" || $meta_key == "display_name") {
            return $this->display_name;
        } elseif ($meta_key == "login" || $meta_key == "username" || $meta_key == "user_name" || $meta_key == "user_login") {
            return $this->user_login;
        } elseif (strpos($meta_key, "_role_select") !== false) {
            return get_user_meta($this->ID, $meta_key, $single);
        } elseif (strpos($meta_key, "_role") !== false) {
            return in_array(str_replace("_role", "", $meta_key), $this->roles);
        } else {
            return stripslashes(get_user_meta($this->ID, $meta_key, $single));
        }
    }

    function getProfileLink($target = '')
    {
        return '<a href="' . esc_url($this->getProfileURL()) . '" target="' . $target . '">' . $this->display_name . '</a>';
    }

    function getProfileURL()
    {
        return get_site_url() . '/profile/?user_id=' . $this->ID;
    }
}
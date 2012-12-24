<?php

class email {

    private $template_type;
    private $assigns;

    // optoutable email types
    public static $types = array('HEADER-1'      => 'General site emails',
                                 //'newfollower'   => 'Email me when someone follows me',
                                 'newphoto'      => 'Email me when someone I follow uploads a photo',
                                 // comment email types
                                 'HEADER-2'      => 'Comment related emails ',
                                 'newcomment'    => 'Email me when someone comments on one of my photos',
                                 //'commentreply'  => 'Email me when someone also comments on a photo I commented on'
                                 );

    // non-optoutable email types
    public static $non_optout_types = array('signup', 'confirm', 'forgotpass', 'firstreminder', 'reviewreminder', 'inactivereminder');

    // a list of email aliases. optouts apply to email types as well as their aliases
    public static $aliases = array('alsofollowing' => 'newfollower');

    public function __construct($template_type) {
        $this->template_type = $template_type;
        $this->assigns['BASE_URL'] = BASE_URL;
        $this->assigns['FEEDBACK_PAGE_URL'] = BASE_URL . '/feedback/';
    }

    public function assign($key, $val) {
        $this->assigns[$key] = $val;
    }

    // $to can either be an actual email address or a user object
    public function send($to) {
        $to_email = is_object($to) && ($to instanceof user) ? $to->get_email() : $to;
        $template = file_get_contents(BASE_DIR . 'etmpl' . DIR_SEP . $this->template_type . '.txt');
        list($subject, $body) = explode("\n", $template, 2);
        $subject = trim($subject);
        $body = trim($body);

        // append the footer
        $body .= $this->get_footer();

        // if this is an email that can be opted out of (i.e. it's not in the non-optoutable list)
        if (!in_array($this->template_type, self::$non_optout_types)) {

            $email_optouts = $to->get_email_optouts();

            // don't send email if the user has opted-out of this email type
            if (in_array($this->template_type, $email_optouts)) {
                return false;
            }

            // check if the template type has an alias that has been opted out of
            if (isset(self::$aliases[$this->template_type]) && in_array(self::$aliases[$this->template_type], $email_optouts)) {
                return false;
            }

            // append email opt-out text
            $body .= $this->get_optout_text($to_email);
        }

        // add the optout url link to the list of assigns
        $this->assigns['OPT_OUT_URL'] = BASE_URL . '/emailprefs/';
        if (is_object($to)) {
            $this->assigns['OPT_OUT_URL'] .= str_encrypt(EMAIL_OPTOUT_SECRET, $to->get_id() . '-' . time());
        }
        // this is an alias
        $this->assigns['EMAIL_PREFS_LINK'] = $this->assigns['OPT_OUT_URL'];

        // make assignment substitutions here
        foreach ($this->assigns as $key => $val) {
            list($subject, $body) = str_replace('{{' . strtoupper($key) . '}}', $val, array($subject, $body));
        }

        // sanity check: get rid of all unreplaced substituions
        list($subject, $body) = preg_replace('/{{.+?}}/', '', array($subject, $body));

        $headers = 'From: Fotavia <' . SITE_EMAIL . ">\r\n" . 'Reply-To: ' . SITE_EMAIL;
        if (user::valid_email($to_email)) {
            #die('<pre>' . $subject . "\n\n" . $body);
            if (!IS_DEV) {
                return mail($to_email, $subject, $body, $headers);
            } else {
                return true;
            }
        }
    }

    private function get_footer() {
        return "\r\n
Cheers,
The Fotavia Team
{{BASE_URL}}";
    }

    private function get_optout_text($to_email) {
        return "\r\n\r\n--
This message was intended for " . $to_email . ". Turn off these emails at\r\n{{OPT_OUT_URL}}";
    }

}

?>

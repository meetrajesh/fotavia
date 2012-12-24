<?php

class page {

    private $title = array();
    private $nav_links = array();
    private $scripts = array();
    private $css = array();
    private $feed;

    // for feedback msgs
    public $err;
    public $msg;

    public function __construct() {
        if (!empty($_SESSION['feedback_msg'])) {
            $this->msg = $_SESSION['feedback_msg'];
        }
        if (!empty($_SESSION['feedback_err'])) {
            $this->err = $_SESSION['feedback_err'];
        }
        unset($_SESSION['feedback_msg'], $_SESSION['feedback_err']);
    }

    public function title($title) {
        $this->title = func_get_args();
    }

    public function add_nav_link($link, $href = null) {
        $this->nav_links[$link] = $href;
    }

    public function add_script($file) {
        $this->scripts[] = $file;
    }

    public function set_rss_feed($feed_url, $feed_title='') {
        $this->feed = array('url' => $feed_url, 'title' => $feed_title);
    }

    public function header() {
        #v($_COOKIE);
        #v(cookie::get('login'));
        $header = file_get_contents(BASE_DIR . 'tmpl/header.php');

        if (is_array($this->feed)) {
            $feed_title = empty($this->feed['title']) ? _('RSS Feed') : hsc($this->feed['title']);
            $feed_tag = spf('<link rel="alternate" type="application/rss+xml" title="%s" href="%s">', $feed_title, hsc($this->feed['url']));
            $header = str_replace('{{RSS_FEED}}', $feed_tag, $header);
        } else {
            $header = str_replace('{{RSS_FEED}}', '', $header);
        }

        // page title
        $this->title = array_map('hsc', $this->title);
        $header = str_replace('{{TITLE}}', 'Fotavia &raquo; ' . implode(' &raquo; ', $this->title), $header);

        // add some menu links if nothing exists
        if (empty($this->nav_links)) {
            if (user::has_active() && !is_sitedown_page()) {
                $this->add_nav_link('dashboard', '/dash');
                user::active()->can_upload_photo() ? $this->add_nav_link('add new', '/photo/add') : $this->add_nav_link('add new');
                $this->add_nav_link('profile', user::active()->get_profile_url());
                $this->add_nav_link('search', '/search');
                $this->add_nav_link('settings', '/settings');
                $this->add_nav_link('logout', '/logout');
            } else {
                $this->add_nav_link('login', '/login');
                $this->add_nav_link('signup', '/signup');
            }
        }

        // add some css
        $this->css[] = 'page.css';
        if (ends_with($_SERVER['SCRIPT_FILENAME'], '/www/view.php') && user::has_active() && user::active()->has_big_screen()) {
            $this->css[] = 'bigscreen.css';
        }

        $header = $this->make_nav_links($header);
        $header = $this->make_script_tags($header);
        $header = $this->make_css_tags($header);

        echo $header;
    }

    public function footer() {
        require BASE_DIR . 'tmpl/footer.php';
    }

    public function smart_redirect() {
        // perform redirect
        $r = isset($_SESSION['r']) && rtrim($_SESSION['r'], '/') != BASE_URL && !in_str($_SESSION['r'], '/api/') ? $_SESSION['r'] : '/dash';
        unset($_SESSION['r']);
        redirect($r);
    }

    public function is_post() {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    // if any one of the values of the array is empty
    public function any_empty_var($array=array()) {
        if (empty($array)) {
            $array = $_POST;
        }
        foreach ($array as $val) {
            $val = trim($val);
            if (empty($val)) {
                return true;
            }
        }
        return false;
    }

    public function get_form_action() {
        return hsc($_SERVER['REQUEST_URI']);
    }

    public function feedback() {
        if (!empty($this->err)) {
            echo '<p class="feedback error">' . $this->err . '</p>';
        } elseif (!empty($this->msg)) {
            echo '<p class="feedback success">' . $this->msg . '</p>';
        }
    }

    public function quit($err = null) {
        $this->err = $err;
        $this->header();
        $this->feedback();
        echo '<p><a href="/dash">' . _('Return to your dashboard') . '</a></p>';
        $this->footer();
        exit;
    }

    public function ensure_login() {
        user::attempt_auto_login();
        if (!user::has_active()) {
            $_SESSION['r'] = $_SERVER['REQUEST_URI'];
            redirect('/login');
        }
    }

    private function make_nav_links($header) {

        // make a string for navigation links
        $nav_content = array();
        foreach ($this->nav_links as $link => $href) {
            if (empty($href) || is_page($href)) {
                $nav_content[] = $link;
            } else {
                $nav_content[] = spf('<a href="%s">%s</a>', hsc($href), hsc($link));
            }
        }

        return str_replace('{{NAV}}', implode(' | ', $nav_content), $header);

    }

    private function make_script_tags($header) {

        // create a string for script tags
        $script_html = '';
        foreach ($this->scripts as $file) {
            $file = preg_match('~^http://~', $file) ? $file : '/js/' . $file;
            $script_html .= '<script type="text/javascript" src="' . $file . '"></script>' . PHP_EOL;
        }
        return str_replace('{{SCRIPT}}', $script_html, $header);

    }

    private function make_css_tags($header) {

        // create a string for css tags
        $css_html = '';
        foreach ($this->css as $file) {
            $css_html .= '<link rel="stylesheet" type="text/css" href="/css/' . $file . '" />' . PHP_EOL;
        }
        return str_replace('{{CSS}}', $css_html, $header);

    }

}

?>

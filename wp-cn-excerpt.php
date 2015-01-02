<?php
/*
Plugin Name:WP CN Excerpt
Plugin URI: http://wordpress.org/plugins/cn-excerpt/
Description: WordPress高级摘要插件。支持在后台设置摘要长度，摘要最后的显示字符，以及允许哪些html标记在摘要中显示
Version:4.4.0
Author: overtrue
Author URI: http://weibo.com/joychaocc
*/
class AdvancedCNExcerpt
{
    // Plugin configuration
    public $name;
    public $textDomain;
    protected $options = array(
        'length'          => 100,
        'only_excerpt'    => 1,
        'no_shortcode'    => 1,
        'finish_sentence' => 0,
        'ellipsis'        => '...',
        'read_more_tpl'   => '<a href=":url" class="read-more">阅读全文</a>',
        'add_link'        => 1,
        'allowed_tags'    => array('_all'),
    );

    // Basic HTML tags (determines which tags are in the checklist by default)
    public static $optionsBasicTags = array(
        'a', 'abbr', 'acronym', 'b', 'big', 'blockquote', 'br', 'center', 'cite', 'code', 'dd', 'del', 'div', 'dl',
        'dt', 'em', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'ins', 'li', 'ol', 'p', 'pre',
        'q', 's', 'small', 'span', 'strike', 'strong', 'sub', 'sup', 'table', 'td', 'th', 'tr', 'u', 'ul'
    );
    // Almost all HTML tags (extra options)
    public static $optionsAllTags = array(
        'a', 'abbr', 'acronym', 'address', 'applet', 'area', 'b', 'bdo', 'big', 'blockquote', 'br', 'button',
        'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em',
        'fieldset', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'iframe',
        'img', 'input', 'ins', 'isindex', 'kbd', 'label', 'legend', 'li', 'map', 'menu', 'noframes', 'noscript',
        'object', 'ol', 'optgroup', 'option', 'p', 'param', 'pre', 'q', 's', 'samp', 'script', 'select', 'small',
        'span', 'strike', 'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th',
        'thead', 'tr', 'tt', 'u', 'ul', 'var'
    );

    // Singleton
    private static $instance = NULL;


    public static function instance($new = FALSE)
    {
        if (self::$instance == NULL || $new) {
            self::$instance = new AdvancedCNExcerpt();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->name       = 'wp-cn-excerpt';
        $this->textDomain = $this->name;
        $this->loadOptions();

        load_plugin_textdomain($this->textDomain, false, dirname(plugin_basename(__FILE__ )) . '/lang');

        register_activation_hook(__FILE__, array($this, 'install'));

        //register_deactivation_hook($file, array($this, 'uninstall'));
        add_action('admin_menu', array($this, 'adminPage'));

        // Replace the default filter (see /wp-includes/default-filters.php)
        remove_filter('get_the_content', 'wp_trim_excerpt');

        // Replace everything
        remove_all_filters('get_the_content');
        remove_all_filters('get_the_excerpt');
        remove_all_filters('excerpt_length');

        add_filter('the_excerpt', array($this, 'filter'), 99999999);
        add_filter('the_content', array($this, 'filter'), 99999999);
    }

    /**
     * plugin entrance
     *
     * @param string $text post content
     *
     * @return string
     */
    public function filter($text)
    {
        if (is_single() || is_page() || is_singular()) {
            return $text;
        }

        if (!$post = get_post()) {
      	    return false;
      	}

	      $text = $post->post_content;
        $allowedTags = $this->options['allowed_tags'];
        $text = force_balance_tags($text);

        if (1 == $this->options['no_shortcode']) {
            $text = strip_shortcodes($text);
        }

        // From the default wp_trim_excerpt():
        // Some kind of precaution against malformed CDATA in RSS feeds I suppose
        $text = str_replace(']]>', ']]&gt;', $text);

        // Determine allowed tags
        if (!isset($allowedTags)) {
            $allowedTags = self::$optionsAllTags;
        }

        if (isset($excludeTags)) {
            $allowedTags = array_diff($allowedTags, $excludeTags);
        }

        // Strip HTML if allow-all is not set
        if (!in_array('_all', $allowedTags)) {
            if (count($allowedTags) > 0) {
                $tagString = '<' . implode('><', $allowedTags) . '>';
            } else {
                $tagString = '';
            }

            $text = strip_tags($text, $tagString);
        }

        // Create the excerpt
        $text = $this->excerpt($text,
            $this->options['length'],
            $this->options['finish_sentence'],
            $this->options['ellipsis']);

        // Add the ellipsis or link
        $this->options['add_link'] && $text = $this->readmore($text, $this->options['read_more_tpl']);

        return $text;
    }

    /**
     * activate the plugin.
     *
     * @return void
     */
    public function install()
    {
        foreach ($this->options as $option => $value) {
            add_option($this->name . '_' . $option, $value);
        }
    }

    /**
     * delete all options.
     *
     * @return void
     */
    public function uninstall()
    {
        foreach (array_keys($this->options) as $option) {
            delete_option($this->name . '_' . $option);
        }
    }

    /**
     * the admin page.
     *
     * @return string
     */
    public function optionPage()
    {
        include dirname(__FILE__) . '/wp-cn-excerpt-options.php';
    }

    /**
     * add script for admin page
     *
     * @return void
     */
    public function pageScript()
    {
        wp_enqueue_script($this->name . '_script', plugins_url('/cn-excerpt/wp-cn-excerpt.js'), array('jquery'));
    }

    /**
     * add the admin page.
     */
    public function adminPage()
    {

        $optionsPage = add_utility_page(__("中文摘要设置", $this->textDomain),
        __("中文摘要设置", $this->textDomain), 'manage_options', 'options-' . $this->name, array($this,'optionPage'));

        // Scripts
        add_action('admin_print_scripts-' . $optionsPage, array($this,'pageScript'));

        //setting menu
        add_filter('plugin_action_links', array($this,'pluginLinks'), 10, 2);
    }

    /**
     * add setting link to plugin panel.
     *
     * @param array  $links
     * @param string $file
     *
     * @return array
     */
    public function pluginLinks($links, $file)
    {
        if ($file == plugin_basename(__FILE__)) {
            array_unshift($links, '<a href="options-general.php?page=options-' . $this->name.'">'.__('设置').'</a>');
        }

        return $links;
    }

    /**
     * get the excerpt from post content
     *
     * @param string  $text           post content
     * @param integer $maxLength      max length.
     * @param boolean $finishSentence return first sentence
     * @param string  $ellipsis       ellipsis string.
     *
     * @return string.
     */
    protected function excerpt($text, $maxLength, $finishSentence, $ellipsis)
    {
        $tokens      = array();
        $out         = '';
        $outLength   = 0;
        $lastTagName = '';

        // clean
        $search = array(
                   '/<br\s*\/?>/' => "\n",
                   '/\\n\\n/'     => "\n",
                   '/&nbsp;/i'    => '',
                  );
        $text = preg_replace(array_keys($search), $search, $text);

        //parse
        $tokens = preg_split("/(\n|<.*?>)/", $text, 0, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($tokens as $token) { // Parse each token
            $token = trim($token);

            if (empty($token)) {
                continue;
            }

            //如果是以第一段结束
            if ($this->options['finish_sentence']
                    && $token[0] != '<'
                    && mb_strlen($token, 'UTF-8') > 15) {
                $out = $token;
                break;
            }

            // 如果不是标签，并且最后一个标签不是[a|pre|code]
            if ($token[0] != '<' && $lastTagName != 'a' && $lastTagName != 'pre' && $lastTagName != 'code') {
                $lineLength = mb_strlen(trim($token), 'utf-8'); //整句长度

                preg_match_all('/&[\w#]{2,};/', $token, $matches);//匹配html实体

                //如果$token中有html实体
                if (!empty($matches[0])) {
                    $entityLength = strlen(join($matches[0])) - count($matches); //实体长度: 实体字符总长度 - 实体个数
                    $lineLength -= $entityLength; //减去实体长度
                }

                $subLength  = $lineLength > 30 ? $maxLength - $outLength : $lineLength;
                $overLength = $outLength + $lineLength > $maxLength;

                if ($subLength > 0) {
                    //结束如果带^\.。\p{Han}？”"；;以外的字符会比较难看
                    $token = preg_replace('/[^\.。\p{Han}？”"；;]$/u', '', $token);
                    $out .= $this->msubstr($token, 0, $subLength, 'utf-8', $overLength ? $ellipsis : '');
                }

                if ($overLength) {
                    break;
                }

                $outLength += $lineLength;
            } else {
                $out .= $token;
            }

            preg_match('/<([a-z]+)/', trim($token), $matches);

            empty($matches[1]) || $lastTagName = $matches[1];
        }

        return force_balance_tags($out);
    }

    /**
     * mb substr
     *
     * @param string  $str       string.
     * @param integer $start     offset.
     * @param integer $maxLength length.
     * @param string  $charset   charset of string.
     * @param string  $ellipsis  after string.
     *
     * @return string
     */
    protected function msubstr($str, $start = 0, $maxLength, $charset = "utf-8", $ellipsis = '...')
    {
        $ellipsis = htmlentities(html_entity_decode($ellipsis));
        $re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";

        preg_match_all($re[$charset], $str, $match);

        $slice = join("", array_slice($match[0], $start, $maxLength));

        return empty($slice) ? '' : $slice . $ellipsis;
    }

    /**
     * add 'read more' link
     *
     * @param string $text         post content.
     * @param string $readMoreText text for link.
     *
     * @return string.
     */
    protected function readmore($text, $tpl = '')
    {
        !empty($tpl) || $tpl = '<a href=":url" class="read-more">:label</a>';

        $tpl = $tpl;

        $replace = array(
            ':url'   => get_permalink(),
        );

        // After the content
        $text .= str_replace(array_keys($replace), $replace, $tpl);

        return $text;
    }

    /**
     * load the options of plugin.
     *
     * @return void
     */
    protected function loadOptions()
    {
        foreach ($this->options as $k => $v) {
            $this->options[$k] = get_option($this->name . '_' . $k, $v);
        }
    }

    /**
     * update the options
     *
     * @return void
     */
    protected function updateOptions()
    {
        $maxLength      = (int)$_POST[$this->name . '_length'];
        $onlyExcerpt    = ('on' == $_POST[$this->name . '_only_excerpt']) ? 0 : 1;
        $noShortcode    = ('on' == $_POST[$this->name . '_no_shortcode']) ? 1 : 0;
        $finishSentence = ('on' == $_POST[$this->name . '_finish_sentence']) ? 1 : 0;
        $addLink        = ('on' == $_POST[$this->name . '_add_link']) ? 1 : 0;
        // TODO: Drop magic quotes (deprecated in php 5.3)
        $ellipsis    = (get_magic_quotes_gpc() == 1) ? stripslashes($_POST[$this->name . '_ellipsis']) : $_POST[$this->name . '_ellipsis'];
        $readMoreTpl = $_POST[$this->name . '_read_more_tpl'];
        $allowedTags = array_unique((array)$_POST[$this->name . '_allowed_tags']);
        if (in_array('_all', $allowedTags)) {
            $allowedTags = array('_all');
        }

        update_option($this->name . '_length', $maxLength);
        update_option($this->name . '_only_excerpt', $onlyExcerpt);
        update_option($this->name . '_no_shortcode', $noShortcode);
        update_option($this->name . '_finish_sentence', $finishSentence);
        update_option($this->name . '_ellipsis', $ellipsis);
        update_option($this->name . '_read_more_tpl', force_balance_tags(stripslashes($readMoreTpl)));
        update_option($this->name . '_add_link', $addLink);
        update_option($this->name . '_allowed_tags', $allowedTags);

        $this->loadOptions();

        echo '<div class="updated fade"><p>设置已保存</p></div>';
    }
}// end of class

AdvancedCNExcerpt::instance();

// Do not use outside the Loop!
function the_advanced_excerpt($args = '', $get = FALSE)
{
    if (!empty($args) && !is_array($args))
    {
        $args = wp_parse_args($args);

        // Parse query style parameters
        if (isset($args['ellipsis'])) {
            $args['ellipsis'] = urldecode($args['ellipsis']);
        }

        if (isset($args['allowed_tags'])) {
            $args['allowed_tags'] = preg_split('/[\s,]+/', $args['allowed_tags']);
        }

        if (isset($args['exclude_tags']))
        {
            $args['exclude_tags'] = preg_split('/[\s,]+/', $args['exclude_tags']);
        }
    }

    // Set temporary options
    AdvancedExcerpt::instance()->options = $args;

    if ($get) {
      return get_the_content();
    } else {
      the_excerpt();
    }
}

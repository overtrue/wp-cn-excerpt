<?php
/*
Plugin Name:WP CN Excerpt
Plugin URI: http://www.joychao.cc/692.html
Description: WordPress高级摘要插件。支持在后台设置摘要长度，摘要最后的显示字符，以及允许哪些html标记在摘要中显示
Version: 4.2.6
Author: Joychao
Author URI: http://www.joychao.cc
Copyright 2012 Joychao
========================本插件修改自：Advanced excerpt by Bas van Doren(http://basvd.com/)==========================
*/
if (!class_exists('AdvancedExcerpt')):
    class AdvancedExcerpt
    {
        // Plugin configuration
        public $name;
        public $text_domain;
        public $options;
        public $default_options = array(
            'length'    => 100,
            'only_excerpt' => 1,
            'no_shortcode' => 1,
            'finish_sentence' => 0,
            'ellipsis'  => '&hellip;',
            'read_more' => '阅读全文',
            'add_link' => 1,
            'allowed_tags' => array('_all'),
        );

        // Basic HTML tags (determines which tags are in the checklist by default)
        public static $options_basic_tags = array(
            'a', 'abbr', 'acronym', 'b', 'big', 'blockquote', 'br', 'center', 'cite', 'code', 'dd', 'del', 'div', 'dl',
            'dt', 'em', 'form', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'ins', 'li', 'ol', 'p', 'pre',
            'q', 's', 'small', 'span', 'strike', 'strong', 'sub', 'sup', 'table', 'td', 'th', 'tr', 'u', 'ul'
        );
        // Almost all HTML tags (extra options)
        public static $options_all_tags = array(
            'a', 'abbr', 'acronym', 'address', 'applet', 'area', 'b', 'bdo', 'big', 'blockquote', 'br', 'button',
            'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em',
            'fieldset', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'iframe',
            'img', 'input', 'ins', 'isindex', 'kbd', 'label', 'legend', 'li', 'map', 'menu', 'noframes', 'noscript',
            'object', 'ol', 'optgroup', 'option', 'p', 'param', 'pre', 'q', 's', 'samp', 'script', 'select', 'small',
            'span', 'strike', 'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea', 'tfoot', 'th',
            'thead', 'tr', 'tt', 'u', 'ul', 'var'
        );

        // Singleton
        private static $inst = NULL;

        public static function Instance($new = FALSE) {
            if (self::$inst == NULL || $new) {
                self::$inst = new AdvancedExcerpt();
            }
            return self::$inst;
        }

        private function __construct() {
            $this->name        = strtolower(get_class());
            $this->text_domain = $this->name;
            $this->load_options();
            load_plugin_textdomain($this->text_domain, FALSE, dirname(plugin_basename(__FILE__)));
            register_activation_hook(__FILE__, array(&$this, 'install'));
            //register_deactivation_hook($file, array(&$this, 'uninstall'));
            add_action('admin_menu', array(&$this, 'add_pages'));
            // Replace the default filter (see /wp-includes/default-filters.php)
            //remove_filter('get_the_content', 'wp_trim_excerpt');
            // Replace everything
            remove_all_filters('get_the_content', 100);
            remove_all_filters('excerpt_length', 100);
            $contentType = $this->default_options['only_excerpt'] ? 'the_excerpt' : 'the_content'; //显示时机
            add_filter($contentType, array(&$this, 'filter'), 100);
        }

        public function filter($text) {
            if (!is_home() and !is_archive()) {
                return $text;
            }
            // Extract options (skip collisions)
            if (is_array($this->options)) {
                extract($this->options, EXTR_SKIP);
                $this->options = NULL; // Reset
            }
            extract($this->default_options, EXTR_SKIP);
            // Get the full content and filter it
            $text = get_the_content('');
            if (1 == $no_shortcode)
                $text = strip_shortcodes($text);
            $text = apply_filters('get_the_content', $text);
            // From the default wp_trim_excerpt():
            // Some kind of precaution against malformed CDATA in RSS feeds I suppose
            $text = str_replace(']]>', ']]&gt;', $text);
            // Determine allowed tags
            if (!isset($allowed_tags))
                $allowed_tags = self::$options_all_tags;

            if (isset($exclude_tags))
                $allowed_tags = array_diff($allowed_tags, $exclude_tags);

            // Strip HTML if allow-all is not set
            if (!in_array('_all', $allowed_tags)) {
                if (count($allowed_tags) > 0)
                    $tag_string = '<' . implode('><', $allowed_tags) . '>';
                else
                    $tag_string = '';
                $text = strip_tags($text, $tag_string);
            }
            // Create the excerpt
            $text = $this->text_excerpt($text, $length, $finish_sentence, $ellipsis);
            // Add the ellipsis or link
            $text = $this->text_add_more($text, ($add_link) ? $read_more : FALSE);
            return $text;
        }

        public function text_excerpt($text, $length, $finish_sentence, $ellipsis) {
            $tokens      = array();
            $out         = '';
            $c           = 0;
            $lastTagName = '';
            $tokens      = preg_split('/(<.*?>)/', $text, 0, PREG_SPLIT_DELIM_CAPTURE);
            foreach ($tokens as $t) { // Parse each token

                //如果是以第一段结束
                if ($finish_sentence and !preg_match('/<.*?>/', $t) and $lastTagName != 'a' and preg_match('/[\?\.\!！。]\s*$/s', $t)) {//以句子结束
                    $out .= $t;
                    break;
                } else {
                    if ($t[0] != '<' and $lastTagName != 'a' and $lastTagName != 'pre' and $lastTagName != 'code') {
                        $l = mb_strlen(trim($t), 'utf-8'); //整句长度
                        preg_match_all('/&[\w#]{2,};/', $t, $match);
                        if (!empty($match[0])) {
                            $entityLength = strlen(join($match[0])) - count($match[0]); //实体长度
                            $l -= $entityLength; //减去实体长度
                        }
                        if ($l > $length) {
                            $out .= $this->msubstr($t, 0, $length, 'utf-8', TRUE, $ellipsis);
                            break;
                        }
                        elseif ($c + $l >= $length) {
                            $out .= $this->msubstr($text, 0, $length - $c, 'utf-8', TRUE, $ellipsis);
                            break;
                        }
                        else {
                            $c += $l;
                        }
                    }
                }
                $out .= $t;
                preg_match('/<([a-z]+)/', trim($t), $match);
                $lastTagName = $match[1];
            }
            return ltrim(force_balance_tags($out));
        }

        public function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = TRUE, $ellipsis = '...') {
            $re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("", array_slice($match[0], $start, $length));
            if ($suffix)
                return $slice . $ellipsis;
            return $slice;
        }

        public function text_add_more($text, $read_more) {
            // After the content
            $text .= sprintf(' <a href="%s" class="read_more">%s</a>', get_permalink(), $read_more);
            return $text;
        }

        public function install() {
            foreach ($this->default_options as $k => $v) {
                add_option($this->name . '_' . $k, $v);
            }
        }

        public function uninstall() {
            // Nothing to do (note: deactivation hook is also disabled)
        }

        private function load_options() {
            foreach ($this->default_options as $k => $v) {
                $this->default_options[$k] = get_option($this->name . '_' . $k, $v);
            }
        }

        private function update_options() {
            $length          = (int)$_POST[$this->name . '_length'];
            $only_excerpt    = ('on' == $_POST[$this->name . '_only_excerpt']) ? 0 : 1;
            $no_shortcode    = ('on' == $_POST[$this->name . '_no_shortcode']) ? 1 : 0;
            $finish_sentence = ('on' == $_POST[$this->name . '_finish_sentence']) ? 1 : 0;
            $add_link        = ('on' == $_POST[$this->name . '_add_link']) ? 1 : 0;
            // TODO: Drop magic quotes (deprecated in php 5.3)
            $ellipsis     = (get_magic_quotes_gpc() == 1) ? stripslashes($_POST[$this->name . '_ellipsis']) : $_POST[$this->name . '_ellipsis'];
            $read_more    = (get_magic_quotes_gpc() == 1) ? stripslashes($_POST[$this->name . '_read_more']) : $_POST[$this->name . '_read_more'];
            $allowed_tags = array_unique((array)$_POST[$this->name . '_allowed_tags']);
            update_option($this->name . '_length', $length);
            update_option($this->name . '_only_excerpt', $only_excerpt);
            update_option($this->name . '_no_shortcode', $no_shortcode);
            update_option($this->name . '_finish_sentence', $finish_sentence);
            update_option($this->name . '_ellipsis', $ellipsis);
            update_option($this->name . '_read_more', $read_more);
            update_option($this->name . '_add_link', $add_link);
            update_option($this->name . '_allowed_tags', $allowed_tags);
            $this->load_options();
            ?>
        <div id="message" class="updated fade"><p>设置已保存</p></div>
        <?php
        }

        public function page_options() {
            if ('POST' == $_SERVER['REQUEST_METHOD']) {
                check_admin_referer($this->name . '_update_options');
                $this->update_options();
            }
            extract($this->default_options, EXTR_SKIP);
            $ellipsis  = htmlentities($ellipsis);
            $read_more = $read_more;
            $tag_list  = array_unique(self::$options_basic_tags + $allowed_tags);
            sort($tag_list);
            $tag_cols = 5;
?>
<div class="wrap" style=" font-family:Microsoft YaHei; ">
    <div id="icon-options-general" class="icon32"><br /></div>
    <h2><?php
      _e("中文摘要设置", $this->text_domain);
?></h2>
    <div style="height:100px; line-height:50px; border-top:1px dashed #ccc;border-bottom:1px dashed #ccc;font-size:22px;"><div>作者：<a style="text-decoration:none;" href="http://www.joychao.cc" target="_blank" title="访问他博客">@Joychao</a> 微博：<a  style="text-decoration:none;" href="http://weibo.com/joychaocc" target="_blank"><img src="http://www.sinaimg.cn/blog/developer/wiki/LOGO_32x32.png" style="vertical-align:-8px;"  />@安正超</a>  捐赠链接：<a href="https://me.alipay.com/joychao" target="_blank"><img src="<?php echo WP_PLUGIN_URL;?>/cn-excerpt/alipay.png" style="vertical-align:-10px;" /></a></div>
<!-- Baidu Button BEGIN -->
    <div style="font-size:22px;height:50px; float:left; line-height:50px;">推荐给你的朋友们吧！</div><div id="bdshare" class="bdshare_t bds_tools_32 get-codes-bdshare" data="{'url':'http://www.joychao.cc/692.html'}">
       <a class="bds_qzone"></a>
        <a class="bds_tsina"></a>
        <a class="bds_tqq"></a>
        <a class="bds_renren"></a>
        <a class="bds_diandian"></a>
        <a class="bds_meilishuo"></a>
        <a class="bds_tieba"></a>
        <a class="bds_douban"></a>
        <a class="bds_tqf"></a>
        <a class="bds_kaixin001"></a>
        <a class="bds_ff"></a>
        <a class="bds_huaban"></a>
        <a class="bds_mail"></a>
        <span class="bds_more">更多</span>
    <a class="shareCount"></a>
    </div>
    </div>
<script type="text/javascript" id="bdshare_js" data="type=tools&amp;uid=533119" ></script>
<script type="text/javascript" id="bdshell_js"></script>
<script type="text/javascript">
  /**
   * 在这里定义bds_config
   */
  var bds_config = {
    'bdDes':'推荐一款强大的wordpress中文摘要插件：http://www.joychao.cc/692.html',    //'请参考自定义分享摘要'
    'bdText':'给大家推荐一款强大的wordpress中文摘要插件！可选主题内容函数摘要显示，支持各种中文编码。可以说是一个非常理想的wordpress文章摘要插件。详情猛击这里->http://www.joychao.cc/692.html',   //'请参考自定义分享内容'
    'bdComment':'非常理想的wordpress文章摘要插件',  //'请参考自定义分享评论'
    'bdPic':'http://www.joychao.cc/wp-content/uploads/2012/07/QQ%E6%88%AA%E5%9B%BE20120930063343.png', //'请参考自定义分享出去的图片'
    'searchPic':false,
    'wbUid':'2193182644',   //'请参考自定义微博 id'
    'snsKey':{'tsina':'4000238328'}   //'请参考自定义分享到平台的appkey'
  }
  document.getElementById("bdshell_js").src = "http://bdimg.share.baidu.com/static/js/shell_v2.js?cdnversion=" + new Date().getHours();
</script>
<!-- Baidu Button END -->
    <form method="post" action="">
    <?php
      if (function_exists('wp_nonce_field'))
        wp_nonce_field($this->name . '_update_options');
?>
        <table id="formTable" >
          <tr valign="top">
                <th scope="row"><label for="<?php echo $this->name; ?>_only_excerpt">
                <?php _e("显示情况：", $this->text_domain); ?></label></th>
                <td>
                    <input name="<?php echo $this->name; ?>_only_excerpt" type="checkbox"
                           id="<?php echo $this->name; ?>_only_excerpt" value="on" <?php
                           echo (0 == $only_excerpt) ? 'checked="checked" ' : ''; ?>/>
                           <?php _e("当模板里使用 the_excerpt 和 the_content 时都显示摘要(摘要无效时可以尝试勾选,不清楚这是什么建议不选)", $this->text_domain); ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo $this->name; ?>_only_excerpt">
                <?php _e("首段摘要：", $this->text_domain); ?></label></th>
                <td>
                    <input name="<?php echo $this->name; ?>_finish_sentence" type="checkbox"
                           id="<?php echo $this->name; ?>_finish_sentence" value="on" <?php
                           echo (1 == $finish_sentence) ? 'checked="checked" ' : ''; ?>/>
                           <?php _e("设置第一段为摘要，不按字数切割（条件为?。！!;”’,\"'等符号结束）", $this->text_domain); ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo $this->name; ?>_length">
                <?php _e("摘要长度：", $this->text_domain); ?></label></th>
                <td>
                    <input name="<?php echo $this->name; ?>_length" type="text"
                           id="<?php echo $this->name; ?>_length"
                           value="<?php echo $length; ?>" size="10"/>个字符
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo $this->name; ?>_ellipsis">
                <?php _e("省略符号：", $this->text_domain); ?></label></th>
                <td>
                    <input name="<?php echo $this->name; ?>_ellipsis" type="text" id="<?php echo $this->name; ?>_ellipsis" value="<?php echo $ellipsis; ?>" size="15"/>
                    <?php _e('(使用 <a href="http://www.joychao.cc/769.html" target="_blank">HTML 实体</a>)', $this->text_domain); ?>
                    <?php _e("将会替代文章的摘要显示.默认为省略号“...”", $this->text_domain); ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo $this->name; ?>_read_more">
                <?php  _e("链接文本：", $this->text_domain); ?></label></th>
                <td>
                    <input name="<?php echo $this->name; ?>_read_more" type="text"  id="<?php echo $this->name; ?>_read_more" value="<?php echo $read_more; ?>" />
                    <input name="<?php echo $this->name; ?>_add_link" type="checkbox"  id="<?php echo $this->name; ?>_add_link" value="on" <?php echo (1 == $add_link) ? 'checked="checked" ' : ''; ?>/>
                           <?php _e("添加此链接到摘要结尾", $this->text_domain); ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo $this->name; ?>_no_shortcode">
                <?php _e("过滤标签：", $this->text_domain); ?></label></th>
                <td>
                    <input name="<?php echo $this->name; ?>_no_shortcode" type="checkbox" id="<?php echo $this->name; ?>_no_shortcode" value="on" <?php echo (1 == $no_shortcode) ? 'checked="checked" ' : ''; ?>/>
                           <?php _e("从摘要中移除短标签。(推荐)", $this->text_domain); ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e("保留标签：", $this->text_domain); ?></th>
                <td>
                    <table id="<?php echo $this->name; ?>_tags_table">
                        <tr>
                            <td colspan="<?php echo $tag_cols; ?>">
                                <input name="<?php echo $this->name; ?>_allowed_tags[]" type="checkbox"  value="_all" <?php echo (in_array('_all', $allowed_tags)) ? 'checked="checked" ' : ''; ?>/>
                                <?php _e("不移除任何标签", $this->text_domain); ?>
                            </td>
                        </tr>
<?php
      $i = 0;
      $j = 0;

      foreach ($tag_list as $tag):
        if ($tag == '_all')
          continue;
        if (0 == $i % $tag_cols):
?>
                        <tr>
<?php
        endif;
        $i++;
?>
                            <td>
    <input name="<?php echo $this->name; ?>_allowed_tags[]" type="checkbox"
           value="<?php echo $tag; ?>" <?php
           echo (in_array($tag, $allowed_tags)) ? 'checked="checked" ' : ''; ?> id="tag<?php echo $j;?>"/>
    <code><label for="tag<?php echo $j++;?>"><?php echo $tag; ?></label></code>
                            </td>
<?php
        if (0 == $i % $tag_cols):
          $i = 0;
          echo '</tr>';
        endif;
      endforeach;
      if (0 != $i % $tag_cols):
?>
                          <td colspan="<?php echo ($tag_cols - $i); ?>">&nbsp;</td>
                        </tr>
<?php
      endif;
?>
                    </table>
                    <a href="" id="<?php echo $this->name; ?>_select_all">全选</a>
                    / <a href="" id="<?php echo $this->name; ?>_select_none">全不选</a><br />
                    更多标签:
                    <select name="<?php echo $this->name; ?>_more_tags" id="<?php echo $this->name; ?>_more_tags">
<?php
      foreach (self::$options_all_tags as $tag):
?>
                        <option value="<?php echo $tag; ?>"><?php echo $tag; ?></option>
<?php
      endforeach;
?>
                    </select>
                    <input type="button" name="<?php echo $this->name; ?>_add_tag" id="<?php echo $this->name; ?>_add_tag" class="button" value="添加标签" />
                </td>
            </tr>
        </table>
        <div style="padding:10px;border:1px dashed #bebebe;margin:10px 0;"><strong>注意：</strong> 使用过程中有任何问题，欢迎到<a href="http://www.joychao.cc/692.html" target="_blank">我的博客</a> 留言 或者给我邮件：<strong>joy@joychao.cc</strong>，我会在最短的时间内尽可能的解决您的问题,感谢您的支持！</div>
        <p class="submit"><input type="submit" name="Submit" class="button-primary"
                                 value="<?php _e("保存设置", $this->text_domain); ?>" /></p>
    </form>
    <style type="text/css">
    #formTable{ table-layout:fixed;empty-cells:show; border-collapse: collapse; clear: both;}
    #formTable th{width: 80px; text-align:left;padding: 10px 0;border-bottom: 1px solid #bebebe;}
    #formTable td{padding: 10px 0; line-height: 2em; border-bottom: 1px solid #bebebe;}
    #formTable table td{line-height: auto;padding: 0;border-bottom:none;}
    </style>
</div>
<?php
    }
    public function page_script()
    {
      wp_enqueue_script($this->name . '_script', WP_PLUGIN_URL . '/cn-excerpt/wp-cn-excerpt.js', array(
        'jquery'
      ));
    }
    public function add_pages()
    {
      $options_page = add_options_page(__("中文摘要设置", $this->text_domain),
       __("中文摘要设置", $this->text_domain), 'manage_options', 'options-' . $this->name, array(&$this,'page_options'
      ));
      // Scripts
      add_action('admin_print_scripts-' . $options_page, array(&$this,'page_script'));
    }
  }

  AdvancedExcerpt::Instance();
  // Do not use outside the Loop!
  function the_advanced_excerpt($args = '', $get = FALSE)
  {
    if (!empty($args) && !is_array($args))
    {
      $args = wp_parse_args($args);
      // Parse query style parameters
      if (isset($args['ellipsis']))
        $args['ellipsis'] = urldecode($args['ellipsis']);
      if (isset($args['allowed_tags']))
        $args['allowed_tags'] = preg_split('/[\s,]+/', $args['allowed_tags']);
      if (isset($args['exclude_tags']))
      {
        $args['exclude_tags'] = preg_split('/[\s,]+/', $args['exclude_tags']);
      }
    }
    // Set temporary options
    AdvancedExcerpt::Instance()->options = $args;

    if ($get)
      return get_the_content();
    else
      the_excerpt();
  }
endif;

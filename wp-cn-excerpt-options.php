<?php
if ('POST' == $_SERVER['REQUEST_METHOD']) {
    check_admin_referer($this->name . '_update_options');
    $this->updateOptions();
}
extract($this->options, EXTR_SKIP);

$ellipsis = htmlentities($ellipsis);
$tagList  = array_unique(self::$optionsBasicTags + $allowed_tags);

sort($tagList);

$tagCols = 5;
$plugin = get_plugin_data(__DIR__ . '/wp-cn-excerpt.php');
?>
<div class="wrap" style=" font-family:Microsoft YaHei; ">
    <h2><span class=" dashicons-before dashicons-admin-page"></span><?php echo __("中文摘要设置 v", $this->textDomain) . $plugin['Version']; ?></h2>

    <form method="post" action="">
    <?php
      if (function_exists('wp_nonce_field'))
        wp_nonce_field($this->name . '_update_options');
    ?>
        <table id="formTable" >
            <tr valign="top">
                <th scope="row"><label for="<?php echo $this->name; ?>_only_excerpt">
                <?php _e("首段摘要", $this->textDomain); ?></label></th>
                <td>
                    <input name="<?php echo $this->name; ?>_finish_sentence" type="checkbox"
                           id="<?php echo $this->name; ?>_finish_sentence" value="on" <?php
                           echo (1 == $finish_sentence) ? 'checked="checked" ' : ''; ?>/>
                           <?php _e("设置第一段为摘要，不按字数切割（条件为?。！!;”’,\"'等符号结束）", $this->textDomain); ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo $this->name; ?>_length">
                <?php _e("摘要长度", $this->textDomain); ?></label></th>
                <td>
                    <input name="<?php echo $this->name; ?>_length" type="text" id="<?php echo $this->name; ?>_length" value="<?php echo $length; ?>" size="3"/><?php _e('个字符', $this->textDomain) ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo $this->name; ?>_ellipsis">
                <?php _e("省略符号", $this->textDomain); ?></label></th>
                <td>
                    <input name="<?php echo $this->name; ?>_ellipsis" type="text" id="<?php echo $this->name; ?>_ellipsis" value="<?php echo $ellipsis; ?>" size="15"/>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo $this->name; ?>_read_more">
                <?php  _e("全文链接", $this->textDomain); ?></label></th>
                <td>
                    <div><input name="<?php echo $this->name; ?>_add_link" type="checkbox"  id="<?php echo $this->name; ?>_add_link" value="on" <?php echo (1 == $add_link) ? 'checked="checked" ' : ''; ?>/><?php _e("添加全文链接到摘要结尾", $this->textDomain); ?></div>
                    <textarea name="<?php echo $this->name; ?>_read_more_tpl" type="text"  id="<?php echo $this->name; ?>_read_more_tpl" style="min-width:500px;"><?php echo htmlentities(stripslashes($read_more_tpl)); ?></textarea>
                    <div><span><code>:url</code><?php _e('表示文章链接', $this->textDomain) ?></span> <br/>  <a target="_blank" href="http://dev.w3.org/html5/html-author/charref">HTML <?php _e('实体符号列表', $this->textDomain);?></a></div>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="<?php echo $this->name; ?>_no_shortcode">
                <?php _e("过滤标签", $this->textDomain); ?></label></th>
                <td>
                    <input name="<?php echo $this->name; ?>_no_shortcode" type="checkbox" id="<?php echo $this->name; ?>_no_shortcode" value="on" <?php echo (1 == $no_shortcode) ? 'checked="checked" ' : ''; ?>/>
                           <?php _e("从摘要中移除短标签。(推荐)", $this->textDomain); ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e("保留标签", $this->textDomain); ?></th>
                <td>
                    <table id="<?php echo $this->name; ?>_tags_table">
                        <tr>
                            <td colspan="<?php echo $tagCols; ?>">
                                <input name="<?php echo $this->name; ?>_allowed_tags[]" type="checkbox"  value="_all" <?php echo (in_array('_all', $allowed_tags)) ? 'checked="checked" ' : ''; ?>/>
                                <?php _e("不移除任何标签", $this->textDomain); ?>
                            </td>
                        </tr>
                        <?php
                              $i = 0;
                              $j = 0;

                            foreach ($tagList as $tag):
                                if ($tag == '_all') {
                                  continue;
                                }
                                if (0 == $i % $tagCols) {
                                  echo '<tr>';
                                }

                                $i++;
                        ?>
                          <td>
                              <input name="<?php echo $this->name; ?>_allowed_tags[]" type="checkbox" value="<?php echo $tag; ?>" <?php echo (in_array($tag, $allowed_tags)) ? 'checked="checked" ' : ''; ?> id="tag<?php echo $j;?>"/>
                              <code><label for="tag<?php echo $j++;?>"><?php echo $tag; ?></label></code>
                          </td>
                          <?php
                            if (0 == $i % $tagCols) {
                              $i = 0;
                              echo '</tr>';
                            }
                          endforeach;
                                if (0 != $i % $tagCols):
                          ?>
                          <td colspan="<?php echo ($tagCols - $i); ?>">&nbsp;</td>
                        </tr>
                            <?php
                                  endif;
                            ?>
                    </table>
                    <a href="" id="<?php echo $this->name; ?>_select_all">全选</a> / <a href="" id="<?php echo $this->name; ?>_select_none">全不选</a><br />
                    更多标签:
                    <select name="<?php echo $this->name; ?>_more_tags" id="<?php echo $this->name; ?>_more_tags">
                      <?php
                            foreach (array_diff(self::$optionsAllTags, $tagList) as $tag):
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
        <div>
            <div class="message fade">
                <p><a href="https://github.com/overtrue" class="button" target="_blank"> @overtrue </a> <a href="https://github.com/overtrue/wp-cn-excerpt/issues" target="_blank" class="button"><?php _e('反馈问题', $this->textDomain) ?></a></p>
                <p>作者：<a style="text-decoration:none;" href="http://weibo.com/joychaocc" target="_blank">@安正超 </a>
                     GitHub源码：<a href="https://github.com/overtrue/wp-cn-excerpt" target="_blank">overtrue/wp-cn-excerpt</a>
                </p>
                <p style="color:red">另外：本插件不可能兼容所有的主题，本插件仅在主题输出内容时使用的wordpress提供<code>the_content()</code>与<code>the_excerpt()</code>等函数时才正常摘要，如果您发现无法正常摘要时，95%的机率就是与主题不兼容！</p>

                <!-- Baidu Button BEGIN -->
                <div class="bdsharebuttonbox">
                    <a href="#" class="bds_tsina" data-cmd="tsina" title="分享到新浪微博"></a>
                    <a href="#" class="bds_qzone" data-cmd="qzone" title="分享到QQ空间"></a>
                    <a href="#" class="bds_tqq" data-cmd="tqq" title="分享到腾讯微博"></a>
                    <a href="#" class="bds_renren" data-cmd="renren" title="分享到人人网"></a>
                    <a href="#" class="bds_weixin" data-cmd="weixin" title="分享到微信"></a>
                    <a href="#" class="bds_more" data-cmd="more"></a>
                </div>
            </div>
            <script>window._bd_share_config={
                "common":{
                    "bdMini":"2",
                    "bdMiniList":false,
                    "bdPic":"",
                    "bdStyle":"1",
                    "bdSize":"24",
                    'bdDes':'推荐一款强大的wordpress中文摘要插件【wp-cn-excerpt】：http://wordpress.org/plugins/cn-excerpt/',    //'请参考自定义分享摘要'
                    'bdText':'给大家推荐一款强大的wordpress中文摘要插件！可选主题内容函数摘要显示，支持各种中文编码。可以说是一个非常理想的wordpress文章摘要插件。详情猛击这里->http://wordpress.org/plugins/cn-excerpt/',   //'请参考自定义分享内容'
                    'bdComment':'非常理想的wordpress文章摘要插件',  //'请参考自定义分享评论'
                    'searchPic':false,
                    'wbUid':'2193182644',   //'请参考自定义微博 id'
                    'bdSnsKey':{'tsina':'4000238328'}   //'请参考自定义分享到平台的appkey'
                },
                "share":{},
                "image":{
                    "viewList":["qzone", "tsina", "tqq", "renren","weixin"],
                    "viewText":"分享到：",
                    "viewSize":"16"
                },
                "selectShare":{
                    "bdContainerClass":null,
                    "bdSelectMiniList":["qzone", "tsina", "tqq", "renren","weixin"]
                    }
                };
                  with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];
                </script>
                </div>

            <!-- Baidu Button END -->
        <p class="submit">
        <input type="submit" name="Submit" class="button-primary" value="<?php _e("保存设置", $this->textDomain); ?>" /></p>
    </form>
    <style type="text/css">
    input, textarea, code, pre{font-family: Monaco, Consolas !important;}
    #wpcontent {background: #fff;}
    .message {margin:0 0 15px; border-color: #7ad03a;background: #fff; border-left: 4px solid #7ad03a; -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);margin: 5px 0px 2px;padding: 10px 12px;}
    #formTable{ table-layout:fixed;empty-cells:show; border-collapse: collapse; clear: both;}
    #formTable th{width: 80px; text-align:left;padding: 10px 0;border-bottom: 1px solid #eee;}
    #formTable td{padding: 10px 0; line-height: 2em; border-bottom: 1px solid #eee;}
    #formTable table td{line-height: auto;padding: 0;border-bottom:none;}
    </style>

</div>

=== WP CN Excerpt ===
Contributors: Carlos
Tags: 中文摘要,摘要,chinese,cn,excerpt, advanced, post, posts, template, formatting
Donate link: https://github.com/overtrue
Requires at least: 3.2
Tested up to: 4.1
Stable tag:4.3.7


== Description ==

 WordPress高级摘要插件。支持在后台设置摘要长度，摘要最后的显示字符，以及允许哪些html标记在摘要中显示。

<ul>
 <li>在摘要中支持HTML标签的显示；</li>
 <li>自动裁剪的摘要功能；</li>
 <li>优化算法，让截取结果更易读；</li>
 <li>可以自己定制摘要的长度和省略号的显示；</li>
 <li>"阅读全文" 标签会被自动的添加（可选）；</li>
 <li>摘要长度是真实的内容的长度（不包含HTML标签）；</li>
 <li>主题开发者可以使用the_advanced_expert()方法进行更多的控制。</li>
 <li>这个插件可以完美的支持自动中文摘要，而且不局限于生成中文摘要，所有的UNICODE字符都支持。</li>
 </ul>

 <h4>设置:</h4>
 <p>"控制面板" > "中文摘要设置"</p>

== Installation ==
1，下载插件上传到/wp-content/plugins/目录后台启用即可
2，后台“插件”->“安装插件”->搜索框输入："wp cn experct"->安装启用即可

== Changelog ==

= 4.4.0=

- “阅读全文”支持自定义模板
- 重构部分代码
- bugfix

= 4.3.0=

- 重构截取算法，更友好的结果
- 重构大部分代码
- bugfix

= 4.1.7=

添加可选the_excerpt显示摘要选项

= 4.1.6=

修正了默认主题下无法摘要的bug


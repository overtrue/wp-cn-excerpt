wp-cn-excerpt
=============

wordpress中文摘要插件： [WP CN Excerpt](https://wordpress.org/plugins/cn-excerpt/)

## 后台设置界面
![wp-cn-excerpt](http://mystorage.qiniudn.com/wp-cn-excerpt.jpg)


# 安装

有3种安装方式：

1. WordPress后台搜索`WP CN Excerpt` 安装;
2. [下载](https://github.com/overtrue/wp-cn-excerpt/releases)zip包解压到`wp-content/plugins`目录,后台激活即可;
3. 使用git克隆：
```shell
cd 你的博客目录/wp-content/plugins/
git clone https://github.com/overtrue/wp-cn-excerpt
```
然后后台激活即可。

# 说明
- 填写的数字只是大致范围，截取的字数不会严格等于填写的数字，因为算法会根据填写的数字选取最优的结束位置来结束；

# 安装统计
<div id='plugin-download-stats' class='chart' style='margin-bottom:25px;'>
<script src="https://www.google.com/jsapi" type="text/javascript"></script>
<script type="text/javascript">
google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(drawPluginDownloadsChart);

function drawPluginDownloadsChart() {
    jQuery(document).ready(function($){
        jQuery.getJSON('https://api.wordpress.org/stats/plugin/1.0/downloads.php?slug=cn-excerpt&limit=267&callback=?', function (data) {
            draw_plugin_downloads_graph(data, 'plugin-download-stats');
        });

        function draw_plugin_downloads_graph(downloads, id) {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Date');
            data.addColumn('number', 'Downloads');
            var count = 0;
            jQuery.each(downloads, function (key, value) {
                data.addRow();
                data.setValue(count, 0, key);
                data.setValue(count, 1, Number(value));
                count++;
            });
            var sml = data.getNumberOfRows() < 225 ? true : false;
            new google.visualization.ColumnChart(document.getElementById(id)).
                draw(data,
                    {
                        colors: ['#253578'],
                        legend: { position: 'none' },
                        titlePosition: 'in',
                        axisTitlesPosition: 'in',
                        chartArea: {
                            height: 280,
                            left: (sml ? 50 : 0),
                            width: (sml ? 482 : '100%'),
                        },
                        hAxis: {
                            textStyle: { color: 'black', fontSize: 9 }
                        },
                        vAxis: {
                            format: '###,###',
                            textPosition: (sml ? 'out' : 'in'),
                            viewWindowMode: 'explicit',
                            viewWindow: { min: 0 },
                        },
                        bar: { groupWidth: (data.getNumberOfRows() > 100 ? "100%" : null) },
                        height: 350,
                        width: 532,
                    }
                );
        }
    });
}
</script>
</div>

# License

MIT

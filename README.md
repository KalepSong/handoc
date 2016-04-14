# HanDoc
PHP类及其方法的注释生成文档

好吧，其实就是依据注释生成了md文件而已，md->html请参考 [markdown-styles](https://github.com/mixu/markdown-styles)

假设您已经安装过了markdown-styles

`以下假设handoc被下载至/data/handoc/目标`

## HanDoc 目录说明
├── config.php		配置文件

├── HanDoc.php		主程序

├── html			html目标文件夹

├── md				md目标文件夹

├── README.md		

└── test.php		测试例子

### config.php配置文件
```php 
<?php
return array(
    'mdDir'=>'./md',//md目标文件夹配置
    'params' => array(
        'long_desc' => '描述'//参数名自定义
    ),
    'source' => array(
    	//源文件夹配置, 可多个。`多个文件夹将默认生成index.html入口文档页`
        array(
            'name' => '控制器',
            'dir' => '/data/test/php/',
            'x' => ''
        ),
        array(
            'name' => '用户中心',
            'dir' => '/data/test/user/',
            'x' => ''
        ),
    )
);
```
### USEAGE, 见./test.php
```php 
#!/usr/bin/php
<?php
require './Handoc.php';
$handoc =new HanDoc();
$handoc->run();
```

###  md --> html
```bash
cd /data/handoc/;
generate-md   --layout mixu-bootstrap-2col --input ./md/ --output ./html/
```

`众所周知的伟大墙，生成的某些css使用了不可逾越墙外地址，造成访问不可用,建议使用handoc导出模板生成html。 命令如下`
```bash
generate-md   --layout /data/markdown-styles-tpl/handoc --input ./md/ --output ./html/
```

### ./html对外发布即可
效果图

![HanDoc-Tpl](https://github.com/mixu/markdown-styles/raw/master/screenshots/mixu-bootstrap-2col.jpg)
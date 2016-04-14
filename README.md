# HanDoc
PHP类及其方法的注释生成文档

好吧，其实就是依据注释生成了md文件而已，md->html请参考 [markdown-styles](https://github.com/mixu/markdown-styles)

假设您已经安装过了markdown-styles

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
    	//源文件夹配置, 可多个。
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

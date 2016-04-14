#!/usr/bin/php
<?php

require './Handoc.php';
$handoc =new HanDoc();
$handoc->run();


exit;
require_once './HandocFile.php';
$config = require './config.php';

if (empty($config['source'])) {
    die('请在配置文件config.php设置PHP文件源文件夹');
}

$data = array();
foreach ($config['source'] as $k => $dir) {
    $files = getFiles($dir['dir']);
    
    if (! empty($files)) {
        $handocFile = new HanDocFile();
        foreach ($files as $f) {
            $dir['result'][] = $handocFile->setFilePath($f)->parser();
        }
        $data[] = $dir;
    }
}

printr($data);

$md = array();
if (! empty($data)) {
    foreach ($data as $d) {
        $md[] = '# ' . $d['name'] . '(' . $d['dir'] . ')';
        if (isset($d['result']) && ! empty($d['result'])) {
            foreach ($d['result'] as $res) {
                $md[] = '## ' . $res['className'];
                if (isset($res['classDoc']) && ! empty($res['classDoc'])) {
                    $md[] = '```';
                    foreach ($res['classDoc'] as $classDocKey => $classDocVal) {
                        $md[] = $classDocKey . ': ' . $classDocVal . PHP_EOL;
                    }
                    $md[] = '```';
                }
                // method
                if (! empty($res['function'])) {
                    foreach ($res['function'] as $fun) {
                        $md[] = '### ' . $fun['name'];
                        
                        if (isset($fun['metadata']) && ! empty($fun['metadata'])) {
                            $md[] = '```';
                            foreach ($fun['metadata'] as $funKey => $funVal) {
                                $md[] = $funKey . ': ' . $funVal . PHP_EOL;
                            }
                            $md[] = '```';
                        }
                    }
                }
            }
        }
    }
}

$mdstr = implode(PHP_EOL, $md);
file_put_contents('./ttt.md', $mdstr);

function getFiles($dir)
{
    $dir = preg_replace('/\/$/', '', $dir);
    $files = array();
    
    if (! is_dir($dir)) {
        return $files;
    }
    
    $handle = opendir($dir);
    if ($handle) {
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..') {
                $filename = $dir . "/" . $file;
                if (is_file($filename)) {
                    $files[] = $filename;
                } else {
                    $files = array_merge($files, get_files($filename));
                }
            }
        } // end while
        closedir($handle);
    }
    return $files;
} // end function

function printr($data)
{
    echo "\n=======\n";
    print_r($data);
    exit();
}


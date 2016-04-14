<?php
/**
 * 读取配置文件指定文件夹下所有PHP类注释生成MD文件至目标文件夹
 * @author Kalep
 */
class HanDoc
{
    private $config;

    public function __construct()
    {
        $this->config = require './config.php';
        if (empty($this->config['source'])) {
            die('请在配置文件config.php设置PHP文件源文件夹');
        }
    }

    public function run()
    {
        if (empty($this->config['source'])) {
            return false;
        }
        
        $data = array();
        foreach ($this->config['source'] as $k => $dir) {
            $files = $this->_getFiles($dir['dir']);
            
            if (! empty($files)) {
                $handocFile = new HanDocFile();
                foreach ($files as $f) {
                    $dir['result'][] = $handocFile->setFilePath($f)->parser();
                }
                
                $data[] = $dir;
            }
        }
        
        // 生成MD文件
        $this->_createMd($data);
    }

    private function _createMd($data)
    {
        if (empty($data)) {
            return false;
        }
        
        $mdFiles = array();
        if (! empty($data)) {
            foreach ($data as $dir) {
                $md = array();
                $md[] = '# ' . $dir['name'] . '(' . $dir['dir'] . ')';
                if (isset($dir['result']) && ! empty($dir['result'])) {
                    foreach ($dir['result'] as $res) {
                        $md[] = '## ' . $res['className'];
                        if (isset($res['classDoc']) && ! empty($res['classDoc'])) {
                            $md[] = '```';
                            foreach ($res['classDoc'] as $classDocKey => $classDocVal) {
                                $md[] = $this->_getTextByConfigParams($classDocKey) . ': ' . $classDocVal ;
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
                                        $md[] = $this->_getTextByConfigParams($funKey) . ': ' . $funVal . PHP_EOL;
                                    }
                                    $md[] = '```';
                                }
                            }
                        }
                    }
                }
                
                if (! empty($md)) {
                    $mdstr = implode(PHP_EOL, $md);
                    
                    $mdFileName = $this->_getMdNameByDir($dir['dir']);
                    file_put_contents($this->_getMdDir() . DIRECTORY_SEPARATOR . $mdFileName, $mdstr);
                    
                    $mdFiles[] = array(
                        'title' => $dir['name'],
                        'file' => $mdFileName
                    );
                }
            }
        }
        
        if (count($mdFiles) > 1) {
            // 生成默认入口MD文件
            // TODO
        }
    }

    private function _getTextByConfigParams($key)
    {
        return isset($this->config['params'][$key]) ? $this->config['params'][$key] : $key;
    }

    /**
     * 获取md文件目标文件夹路径
     */
    private function _getMdDir()
    {
        $dir = isset($this->config['mdDir']) ? $this->config['mdDir'] : './md';
        $dir = preg_replace('/\/$/', '', $dir);
        return $dir;
    }

    /**
     * 获取MD自动创建名
     *
     * @param string $dir            
     * @return mixed
     */
    private function _getMdNameByDir($dir)
    {
        $dir = preg_replace('/^\/|\/$/', '', $dir);
        return str_replace(DIRECTORY_SEPARATOR, '-', $dir) . '.md';
    }

    private function _getFiles($dir)
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
}

class HanDocFile
{

    private $filePath = '';

    public function __construct($filePath = '')
    {
        $this->filePath = $filePath;
    }

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function parser()
    {
        $result = array();
        
        // echo $this->filePath,"\n";
        clearstatcache();
        if (! file_exists($this->filePath)) {
            return false;
        }
        $handle = fopen($this->filePath, "r");
        if ($handle) {
            $docLines = false;
            while (! feof($handle)) {
                $buffer = fgets($handle, 4096);
                $b = trim($buffer);
                
                $m = array();
                if (strpos($buffer, 'class') !== false) {
                    preg_match_all('/class (.*?) /', $buffer, $m);
                    $result['className'] = isset($m[1][0]) ? trim($m[1][0]) : '';
                    $result['classDoc'] = $this->parserDocStr($docLines);
                    $docLines = false;
                    continue;
                } elseif (strpos($buffer, 'function') !== false) {
                    preg_match_all('/function (.*?)\t?\(/', $b, $m);
                    
                    $fun = array();
                    if (isset($m[1][0])) {
                        $fun['name'] = $m[1][0];
                        $fun['metadata'] = $this->parserDocStr($docLines);
                    }
                    empty($fun) || $result['function'][] = $fun;
                    $docLines = false;
                    continue;
                }
                
                if ($b == '/**') {
                    $startDoc = true;
                    $docLines = array();
                } elseif ($b != '*/' && $b) {
                    $docLines !== false && $docLines[] = $b;
                }
            }
            fclose($handle);
        }
        
        return $result;
    }

    private function parserDocStr($docs)
    {
        if (! is_array($docs) || empty($docs)) {
            return false;
        }
        
        $res = array(
            'long_desc' => array()
        );
        foreach ($docs as $doc) {
            $doc = trim($doc);
            if (! $doc) {
                continue;
            }
            
            preg_match_all('/@(\w+)([\s\S]{1,})/', $doc, $m);
            
            if (isset($m[1][0])) {
                $res[$m[1][0]] = isset($m[2][0]) ? trim($m[2][0]) : '';
            } else {
                $res['long_desc'][] = str_replace('* ', '', $doc);
            }
        }
        
        $res['long_desc'] = implode(PHP_EOL, $res['long_desc']);
        return $res;
    }
}
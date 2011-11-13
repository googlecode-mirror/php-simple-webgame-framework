<?php

class URI {

    /**
     * 当前的路径模式 
     * 可通过config中的变量覆盖设置
     */
    private $path_model = 'none';

    /**
     * 需要跳转的url列表 以跳转url的md5值做索引
     */
    private $path_locations = array();

    /**
     * 需要按照规则跳转的url
     * 包括正则表达式和需要跳转的路径
     * 按照优先级程序逐条匹配
     */
    private $path_rewrite = array();

    public function __construct() {
        $this->path_model = App::conf('path_model');
    }


    /**
     * 根据当前配置的URL路径规则 返回URL中包含的所有变量
     * 
     * @return array 系统参数数组
     */
    public function getVars() {
        $vars = array();
        switch ($this->path_model) {
            case 'rewrite':
                {
                    $path_string = isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] :
                        current (explode('?', $_SERVER['REQUEST_URI']));
                    $path_info = explode('/', $path_string);
                    if (!empty($path_info[1]))
                        $vars['c'] = $path_info[1];
                    if (!empty($path_info[2]))
                        $vars['a'] = $path_info[2];
                    break;
                }
            case 'pathinfo':
                {
                    $path_info = explode('/', $_GET['s']);
                    if (!empty($path_info[0]))
                        $vars['c'] = $path_info[0];
                    if (!empty($path_info[1]))
                        $vars['a'] = $path_info[1];

                    break;
                }
            default:
                {
                    //do nothing
                }

        }

        return $vars;
    }

    /**
     * 获得需要跳转的URL
     * 
     * @param string $url
     * @return
     */
    public function getLocation($url = '') {
        if (empty($url))
            $url = $_SERVER['REQUEST_URI'];
        $key = md5($url);
        if (isset($this->path_locations[$key])) {
            return $this->path_locations[$key];
        }
    }

}

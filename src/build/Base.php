<?php
/**
 * Created by PhpStorm.
 * User: 南丞
 * Date: 2019/3/5
 * Time: 16:03
 *
 *
 *                      _ooOoo_
 *                     o8888888o
 *                     88" . "88
 *                     (| ^_^ |)
 *                     O\  =  /O
 *                  ____/`---'\____
 *                .'  \\|     |//  `.
 *               /  \\|||  :  |||//  \
 *              /  _||||| -:- |||||-  \
 *              |   | \\\  -  /// |   |
 *              | \_|  ''\---/''  |   |
 *              \  .-\__  `-`  ___/-. /
 *            ___`. .'  /--.--\  `. . ___
 *          ."" '<  `.___\_<|>_/___.'  >'"".
 *        | | :  `- \`.;`\ _ /`;.`/ - ` : | |
 *        \  \ `-.   \_ __\ /__ _/   .-` /  /
 *  ========`-.____`-.___\_____/___.-`____.-'========
 *                       `=---='
 *  ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
 *           佛祖保佑       永无BUG     永不修改
 *
 */

namespace pf\route\build;

use pf\config\Config;
use pf\request\Request;
use pf\route\Route;

class Base
{
    use Setting, Compile;
    protected $route = [];
    protected $requestUrl;

    public function bootstrap()
    {
        $this->setDefaultController();
        $this->requestUrl = $this->getRequestUrl();
        //设置路由缓存 TODO
        //if(Config::get('route.cache') && ($route =))
        $this->parseRoute();

        foreach ($this->route as $key => $value) {
            //var_dump($value);exit;
            $method = '_' . $value['method'];
            if ($this->$method($key) === true) {
                return $this;
            }
        }
        return $this;
    }

    protected function getRequestUrl()
    {
        $REQUEST_URI = str_replace($_SERVER['SCRIPT_NAME'], '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        return trim($REQUEST_URI, '/');
    }

    protected function setDefaultController()
    {
        //设置默认控制器
        $http = Request::get(Config::get('http.url_var'));
        if (!empty($http)) {
            $info = explode('/', $http);
            $method = array_pop($info);
            $controller = ucfirst(array_pop($info));

        } else {
            $class = Config::get('http.default_controller');
            $method = Config::get('http.default_action');
            $action = $class . '@' . $method;
        }
        Route::any('.*', $action);
    }

    protected function parseRoute()
    {
        foreach ($this->route as $key => $value) {
            $regexp = $value['route'];
            //var_dump($regexp);exit;
            preg_match_all("#\{(.*?)(\?)?\}#", $regexp, $args, PREG_SET_ORDER);
            //var_dump($args);
        }
    }

}
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

use pf\cache\Cache;
use pf\config\Config;
use pf\request\Request;
use pf\route\Route;

class Base
{
    use Setting, Compile, Controller;
    protected $route = [];
    protected $requestUrl;
    protected $patterns = [
        ':num' => '[0-9]+',
        ':all' => '.*'
    ];

    public function bootstrap()
    {
        $this->setDefaultController();
        $this->requestUrl = $this->getRequestUrl();
        if (Config::get('route.cache') && ($route = Cache::get('_ROUTES_'))) {
            $this->route = $route;
        } else {
            $this->parseRoute();
        }
        //var_dump($this->route);exit;
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
        //var_dump($http);exit;
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
            if (strpos($regexp, ":") !== false) {
                $regexp = str_replace(
                    array_keys($this->patterns),
                    array_values($this->patterns),
                    $regexp
                );
            }
            preg_match_all("#\{(.*?)(\?)?\}#", $regexp, $args, PREG_SET_ORDER);
            foreach ($args as $i => $ato) {
                $has = isset($ato[2]) ? $ato[2] : '';
            }

            $this->route[$key]['regexp'] = '#^' . $regexp . '$#';
            $this->route[$key]['args'] = $args;
        }
        if (Config::get('route.cahce')) {
            Cache::set('__ROUTES__', $this->route);
        }
        return $this->route;
    }

}
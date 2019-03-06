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
            $this->route = $this->parseRoute();
        }
        foreach ($this->route as $key => $value) {
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
            $module = array_pop($info);
            $info[count($info) - 1] = ucfirst($info[count($info) - 1]);
            $action = Config::get('app.path') . '\\' . $module . '\\controller\\' . $controller . '@' . $method;
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
                if ($has) {
                    $regexp = str_replace($ato[0], '?([a-z0-9]+?)' . $has, $regexp);
                } else {
                    $regexp = str_replace($ato[0], '([a-z0-9]+?)' . $has, $regexp);
                }
            }
            $this->route[$key]['regexp'] = '#^' . $regexp . '$#';
            $this->route[$key]['args'] = $args;
        }
        if (Config::get('route.cahce')) {
            Cache::set('__ROUTES__', $this->route);
        }
        return $this->route;
    }

    public function where($rule, $regexp = '')
    {
        $rule = is_array($rule) ? $rule : [$rule => $regexp];
        $routeKey = count($this->route) - 1;
        foreach ($rule as $k => $v) {
            $this->route[$routeKey]['where'][$k] = '#^' . $v . '$#';
        }
        return $this;
    }

}
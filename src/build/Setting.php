<?php
/**
 * Created by PhpStorm.
 * User: 南丞
 * Date: 2019/3/5
 * Time: 16:44
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

trait Setting
{
    protected $prefix;

    public function __call($name, $arguments)
    {
        $this->route[] = [
            'method' => $name
            , 'route' => $this->prefix . @trim(array_shift($arguments), '/')
            , 'callback' => array_shift($arguments)
            , 'regexp' => '/./'
            , 'args' => []
            , 'get' => []
        ];
        return $this;
    }

    public function group(array $prefix, $callback)
    {
        $this->prefix = $prefix['prefix'] . '/';
        $callback();
        $this->prefix = '';
    }

    public function controller($route, $param)
    {
        $route = trim($route, '/');
        $this->route[] = [
            'method' => 'controller',
            'route' => $this->prefix . $route . '/{method}(\.\w+)?',
            'callback' => $param,
            'regexp' => '',
            'args' => [],
        ];
        return $this;
    }

    public function resource($route, $controller)
    {
        $route = trim($route, '/');
        $this->get("$route", $controller . '@index');
        $this->get("$route/create", $controller . '@create');
        $this->post("$route", $controller . '@store');
        $this->get("$route/{id}", $controller . '@show');
        $this->get("$route/{id}/edit", $controller . '@edit');
        $this->put("$route/{id}", $controller . '@update');
        $this->delete("$route/{id}", $controller . '@destroy');
        return $this;
    }
}
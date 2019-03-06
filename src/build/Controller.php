<?php
/**
 * Created by PhpStorm.
 * User: nancheng
 * Date: 2019/3/5
 * Time: 11:38 PM
 * Email: Lampxiezi@163.com
 * Blog:  http://friday-go.cc/
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
 *         佛祖保佑       永无BUG     永不修改
 *
 */

namespace pf\route\build;

use pf\container\Container;
use pf\route\Route;
use ReflectionMethod;

trait Controller
{
    protected $controller;
    protected $action;

    public function executeControllerAction($action, $args = [])
    {
        $info = explode('@', $action);
        //var_dump($info);exit;
        $this->setController($controller = $info[0]);
        $this->setAction($action = $info[1]);
        $path = str_replace(['controller', '\\'], ['view', '/'], Route::getController());
        //var_dump(class_exists($controller));exit;
        if (!class_exists($controller) || !method_exists($controller, $action)) {
            throw  new \Exception('访问的方法不存在');
        }
        $controller = Container::make($controller, true);
        try {
            $reflectionMethod = new \ReflectionMethod($controller, $action);
            foreach ($reflectionMethod->getParameters() as $k => $p) {
                if (isset($this->args[$p->name])) {
                    $args[$p->name] = $this->args[$p->name];
                } else {
                    if ($dependency = $p->getClass()) {
                        $args[$p->name] = Container::build($dependency->name);
                    } else {
                        $args[$p->name] = Container::resolveNonClass($p);
                    }
                }
            }
            return $reflectionMethod->invokeArgs($controller, $args);
        } catch (\ReflectionException $e) {
            $method = new ReflectionMethod($controller, '__call');
            return $method->invokeArgs($controller, [$action, '']);
        }
    }

    protected function setController($controller)
    {
        $this->controller = $controller;
    }

    protected function setAction($action)
    {
        $this->action = $action;
    }

    public function getController()
    {
        return $this->controller;
    }
}
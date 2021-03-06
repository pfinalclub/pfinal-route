<?php
/**
 * Created by PhpStorm.
 * User: 南丞
 * Date: 2019/3/5
 * Time: 17:08
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

use Closure;
use pf\container\Container;
use pf\request\Request;
use ReflectionFunction;

trait Compile
{
    protected $matchRouteKey;

    public $args = [];

    protected function _any($key)
    {
        return $this->isMatch($key);
    }

    protected function _get($key)
    {
        return Request::isMethod('get') && $this->isMatch($key);
    }

    protected function _post($key)
    {
        return Request::isMethod('post') && $this->isMatch($key);
    }

    protected function _put($key)
    {
        return Request::isMethod('put') && $this->isMatch($key);
    }

    protected function _delete($key)
    {
        return Request::isMethod('delete') && $this->isMatch($key);
    }

    protected function isMatch($key)
    {
        if (preg_match($this->route[$key]['regexp'], $this->requestUrl)) {
            //获取参数
            $this->route[$key]['get'] = $this->getArgs($key);
            if (!$this->checkArgs($key)) {
                return false;
            }
            $this->args = $this->route[$key]['get'];
            foreach ((array)$this->args as $k => $v) {
                Request::set('get.' . $k, $v);
            }
            $this->matchRouteKey = $key;
            $this->action = $this->matchRouteKey;
            return true;
        }

    }

    protected function getArgs($key)
    {
        $args = [];
        if (preg_match_all($this->route[$key]['regexp'], $this->requestUrl, $matched, PREG_SET_ORDER)) {
            foreach ($this->route[$key]['args'] as $n => $val) {
                if (isset($matched[0][$n + 1])) {
                    //数值类型转换
                    $v = $matched[0][$n + 1];
                    $args[$val[1]] = is_numeric($v) ? intval($v) : $v;
                }
            }
        }
        return $args;
    }

    protected function checkArgs($key)
    {
        $route = $this->route[$key];
        if (!empty($route['where'])) {
            foreach ($route['where'] as $name => $regexp) {
                if (isset($route['get'][$name])
                    && !preg_match($regexp, $route['get'][$name])
                ) {
                    return false;
                }
            }
        }
        return true;
    }

    public function getMatchRoute()
    {
        return $this->route[$this->matchRouteKey];
    }

    public function exec()
    {
        $key = $this->matchRouteKey;
        if ($this->route[$key]['callback'] instanceof Closure) {
            $reflectionFunction = new ReflectionFunction($this->route[$key]['callback']);
            $args = [];
            foreach ($reflectionFunction->getParameters() as $k => $v) {
                if (isset($this->args[$v->name])) {
                    $args[$v->name] = $this->args[$v->name];
                } else {
                    if ($dependency = $v->getClass()) {
                        $args[$v->name] = Container::build($dependency->name);
                    } else {
                        $args[$v->name] = Container::resolveNonClass($v);
                    }
                }
            }
            return $reflectionFunction->invokeArgs($args);
        } else {
            return $this->executeControllerAction($this->route[$key]['callback']);
        }
    }

    protected function _controller($key)
    {
        if ($this->route[$key]['method'] == 'controller' && $this->isMatch($key)) {
            $method = strtolower(Request::getRequestType()) . ucfirst($this->route[$key]['get']['method']);
            $this->route[$key]['callback'] .= '@' . $method;
            return true;
        }
    }

    public function getArg()
    {
        return $this->args;
    }

    public function input($name = null)
    {
        if (is_null($name)) {
            return $this->args;
        } else {
            return isset($this->args[$name]) ? $this->args[$name] : null;
        }
    }
}
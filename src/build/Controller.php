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

use pf\route\Route;

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
        var_dump(strtolower($path));
        exit;

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
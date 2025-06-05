<?php
/**
 * Typecho REST API 插件
 * 移植自 <a href="https://github.com/ShuShuicu/TTDF">TTDF<a> 框架的 RESTAPI 功能
 * 
 * @package TyJson
 * @author 鼠子
 * @version 1.0.0
 * @link http://blog.miomoe.cn/
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
class TyJson_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 插件激活方法
     */
    public static function activate()
    {
        Helper::addRoute('tyjson_api', '/ty-json/[action]', 'TyJson_Action', 'dispatch');
        Helper::addRoute('tyjson_api_slash', '/ty-json/[action]/', 'TyJson_Action', 'dispatch');
    
        Helper::addRoute('tyjson_api_params', '/ty-json/[action]/[params]', 'TyJson_Action', 'dispatch');
    }
    

    /**
     * 插件禁用方法
     */
    public static function deactivate()
    {
        Helper::removeRoute('tyjson_main');
        Helper::removeRoute('tyjson_api');
        Helper::removeRoute('tyjson_api_params');
    }

    /**
     * 插件配置面板
     */
    public static function config(Typecho_Widget_Helper_Form $form) {}

    /**
     * 个人用户配置面板
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form) {}
}

<?php

/*
 * This file is part of the "Prestashop Clean URLs" module.
 *
 * (c) Faktiva (http://faktiva.com)
 *
 * NOTICE OF LICENSE
 * This source file is subject to the CC BY-SA 4.0 license that is
 * available at the URL https://creativecommons.org/licenses/by-sa/4.0/
 *
 * DISCLAIMER
 * This code is provided as is without any warranty.
 * No promise of being safe secure
 *
 * @autor   Emiliano 'AlberT' Gabrielli <albert@faktiva.com>
 * @license  https://creativecommons.org/licenses/by-sa/4.0/  CC-BY-SA-4.0
 * @source   https://github.com/faktiva/prestashop-clean-urls
 */

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Core\Routing\Router;

class Link extends LinkCore
{
    /**
     * Create a link to a category.
     *
     * @param mixed  $category         Category object (can be an ID category, but deprecated)
     * @param string $alias
     * @param int    $id_lang
     * @param string $selected_filters Url parameter to autocheck filters of the module blocklayered
     *
     * @return string
     */
    public function getCategoryLink($category, $alias = null, $id_lang = null, $selected_filters = null, $id_shop = null, $relative_protocol = false)
    {
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }

        $url = $this->getBaseLink($id_shop, null, $relative_protocol).$this->getLangLink($id_lang, null, $id_shop);

        if (!is_object($category)) {
            $category = new Category($category, $id_lang);
        }

        // Set available keywords
        $params = array();
        $params['id'] = $category->id;
        $params['rewrite'] = (!$alias) ? $category->link_rewrite : $alias;
        $params['meta_keywords'] = Tools::str2url($category->getFieldByLang('meta_keywords'));
        $params['meta_title'] = Tools::str2url($category->getFieldByLang('meta_title'));

        // Selected filters is used by the module blocklayered
        $selected_filters = is_null($selected_filters) ? '' : $selected_filters;

        if (empty($selected_filters)) {
            $rule = 'category_rule';
        } else {
            $rule = 'layered_rule';
            $params['selected_filters'] = $selected_filters;
        }

        /** @var Router $router */
        $router = SymfonyContainer::getInstance()->get('router');
        $dispatcher = Dispatcher::getInstance();

        return $router->generate($rule, $params, Router::ABSOLUTE_URL);
    }

    /**
     * Get pagination link.
     *
     * @param string $type       Controller name
     * @param int    $id_object
     * @param bool   $nb         Show nb element per page attribute
     * @param bool   $sort       Show sort attribute
     * @param bool   $pagination Show page number attribute
     * @param bool   $array      If false return an url, if true return an array
     *
     * @return array|string
     */
    public function getPaginationLink($type, $id_object, $nb = false, $sort = false, $pagination = false, $array = false)
    {
        // If no parameter $type, try to get it by using the controller name
        if (!$type && !$id_object) {
            $method_name = 'get'.Dispatcher::getInstance()->getController().'Link';
            if (method_exists($this, $method_name) && isset($_GET['id_'.Dispatcher::getInstance()->getController()])) {
                $type = Dispatcher::getInstance()->getController();
                $id_object = $_GET['id_'.$type];
            }
        }

        if ($type && $id_object) {
            $url = $this->{'get'.$type.'Link'}($id_object, null);
        } else {
            if (isset(Context::getContext()->controller->php_self)) {
                $name = Context::getContext()->controller->php_self;
            } else {
                $name = Dispatcher::getInstance()->getController();
            }
            $url = $this->getPageLink($name);
        }

        $vars = array();
        $vars_nb = array('n', 'search_query');
        $vars_sort = array('orderby', 'orderway');
        $vars_pagination = array('p');

        foreach ($_GET as $k => $value) {
            if ($k != 'id_'.$type && $k != 'controller' && $k != $type.'_rewrite' /* skip *_rewrite */) {
                if (Configuration::get('PS_REWRITING_SETTINGS') && ($k == 'isolang' || $k == 'id_lang')) {
                    continue;
                }
                $if_nb = (!$nb || ($nb && !in_array($k, $vars_nb)));
                $if_sort = (!$sort || ($sort && !in_array($k, $vars_sort)));
                $if_pagination = (!$pagination || ($pagination && !in_array($k, $vars_pagination)));
                if ($if_nb && $if_sort && $if_pagination) {
                    if (!is_array($value)) {
                        $vars[urlencode($k)] = $value;
                    } else {
                        foreach (explode('&', http_build_query(array($k => $value), '', '&')) as $key => $val) {
                            $data = explode('=', $val);
                            $vars[urldecode($data[0])] = $data[1];
                        }
                    }
                }
            }
        }

        if ($array) {
            return array('url' => $url, 'params' => $vars);
        }

        return $url.(count($vars) ? '?'.http_build_query($vars) : '');
    }
}
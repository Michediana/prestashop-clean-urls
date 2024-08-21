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
 * No promise of being safe or secure
 *
 * @author   Emiliano 'AlberT' Gabrielli <albert@faktiva.com>
 * @license  https://creativecommons.org/licenses/by-sa/4.0/  CC-BY-SA-4.0
 * @source   https://github.com/faktiva/prestashop-clean-urls
 */

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Module\ModuleRepository;

class CategoryController extends CategoryControllerCore
{
    public function init()
    {
        $context = Context::getContext();
        $category_rewrite = Tools::getValue('category_rewrite');

        if ($category_rewrite) {
            $sql = new DbQuery();
            $sql->select('id_category')
                ->from('category_lang')
                ->where('link_rewrite = \''.pSQL(str_replace('.html', '', $category_rewrite)).'\'')
                ->where('id_lang = '.(int) $context->language->id);

            if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP) {
                $sql->where('id_shop = '.(int) Shop::getContextShopID());
            }

            $id_category = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
            if ($id_category > 0) {
                $_GET['id_category'] = $id_category;
            }
        }

        parent::init();
    }
}
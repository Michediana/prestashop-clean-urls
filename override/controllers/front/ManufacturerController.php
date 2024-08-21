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
 * @autor   Emiliano 'AlberT' Gabrielli <albert@faktiva.com>
 * @license  https://creativecommons.org/licenses/by-sa/4.0/  CC-BY-SA-4.0
 * @source   https://github.com/faktiva/prestashop-clean-urls
 */

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Core\Routing\Router;

class ManufacturerController extends ManufacturerControllerCore
{
    public function init()
    {
        $context = Context::getContext();
        $manufacturer_rewrite = Tools::getValue('manufacturer_rewrite');

        if ($manufacturer_rewrite) {
            $sql = new DbQuery();
            $sql->select('m.`id_manufacturer`')
                ->from('manufacturer', 'm')
                ->leftJoin('manufacturer_shop', 's', 'm.`id_manufacturer` = s.`id_manufacturer`')
                ->where('m.`name` LIKE \''.pSQL(str_replace('-', '_', $manufacturer_rewrite)).'\'')
                ->where('s.`id_shop` = '.(int) $context->shop->id);

            $id_manufacturer = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
            if ($id_manufacturer > 0) {
                $_GET['id_manufacturer'] = $id_manufacturer;
            }
        }

        parent::init();
    }
}
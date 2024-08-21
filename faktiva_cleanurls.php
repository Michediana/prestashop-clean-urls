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
 * @source https://github.com/juferlover/prestashop-clean-urls/tree/Prestashop-1.7-Update
 */

if (!defined('_PS_VERSION_')) {
    return;
}

// Set true to enable debugging
define('FKV_DEBUG', false);

if (version_compare(phpversion(), '7.3.0', '>=')) { // Namespaces support is required
    include_once __DIR__.'/tools/debug.php';
}

class FaktivaCleanUrls extends Module
{
    public function __construct()
    {
        $this->name = 'faktiva_cleanurls';
        $this->tab = 'seo';
        $this->version = '1.2.4';
        $this->author = 'Faktiva';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Faktiva Clean URLs');
        $this->description = $this->l('This override-Module allows you to remove URL IDs.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall "Faktiva Clean URLs" module?');
    }

    public function getContent()
    {
        $output = '';

        $sql = 'SELECT `id_product`, `link_rewrite`, `id_lang`, `name`
                FROM `'._DB_PREFIX_.'product_lang`
                WHERE `link_rewrite`
                IN (SELECT `link_rewrite` FROM `'._DB_PREFIX_.'product_lang`
                GROUP BY `link_rewrite`, `id_lang`
                HAVING count(`link_rewrite`) > 1)';
        if (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP) {
            $sql .= ' AND `id_shop` = '.(int) Shop::getContextShopID();
        }

        if ($res = Db::getInstance()->executeS($sql)) {
            $err = $this->l('You need to fix duplicate URL entries:').'<br>';
            foreach ($res as $row) {
                $lang = Language::getLanguage($row['id_lang']);
                $err .= $row['name'].' ('.$row['id_product'].') - '.$row['link_rewrite'].'<br>';

                $shop = Shop::getShop($row['id_shop']);
                $err .= $this->l('Language: ').$lang['name'].'<br>'.$this->l('Shop: ').$shop['name'].'<br><br>';
            }
            $output .= $this->displayWarning($err);
        } else {
            $output .= $this->displayConfirmation($this->l('Nice. You have no duplicate URL entry.'));
        }

        return '<div class="panel">'.$output.'</div>';
    }

    public function install()
    {
        // add link_rewrite as index to improve search
        foreach (array('category_lang', 'cms_category_lang', 'cms_lang', 'product_lang') as $tab) {
            if (!Db::getInstance()->executeS('SHOW INDEX FROM `'._DB_PREFIX_.$tab.'` WHERE Key_name = \'link_rewrite\'')) {
                Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.$tab.'` ADD INDEX ( `link_rewrite` )');
            }
        }

        if (!parent::install()) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        return true;
    }
}

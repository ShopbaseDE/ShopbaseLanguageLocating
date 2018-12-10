<?php
/**
 * Language Locating
 * Copyright (c) shopbase
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ShopbaseLanguageLocating;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;

class ShopbaseLanguageLocating extends Plugin
{
    /**
     * Executed on install plugin
     *
     * @param InstallContext $context
     */
    public function install(InstallContext $context)
    {
        parent::install($context);
    }

    /**
     * Executed on uninstall plugin
     *
     * @param UninstallContext $context
     */
    public function uninstall(UninstallContext $context)
    {
        $context->scheduleClearCache(UninstallContext::CACHE_LIST_ALL);

        if($context->keepUserData()) {
            return;
        }

        parent::uninstall($context);
    }

    /**
     * Executed on activate plugin
     *
     * @param ActivateContext $context
     */
    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(ActivateContext::CACHE_LIST_ALL);

        parent::activate($context);
    }

    /**
     * Executed on deactivate plugin
     *
     * @param DeactivateContext $context
     */
    public function deactivate(DeactivateContext $context)
    {
        $context->scheduleClearCache(DeactivateContext::CACHE_LIST_ALL);

        parent::deactivate($context);
    }

    /**
     * Add event to Shopware loading process
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_Frontend_Index_Index' => 'onPostDispatch'
        ];
    }

    /**
     * Controller event
     *
     * @param \Enlight_Controller_ActionEventArgs $args
     * @return array
     */
    public function onPostDispatch(\Enlight_Event_EventArgs $args)
    {

        if(!isset(Shopware()->Session()->languageLocatingExecuted)) {
            $config = $this->getConfig();
            $shops = $this->getShops(boolval($config['useSubshops']));
            $language = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'])[0];
            $languageIsActive = $this->checkShopLanguages($shops, Shopware()->Shop()->getId(), $language);

            Shopware()->Session()->languageLocatingExecuted = true;

            // Executed if current shop has same language as browser language
            if($languageIsActive) {
                return;
            }

            // Executed if shop with current browser language exists
            $existingShop = $this->getShopByLanguage($shops, $language);
            if($existingShop !== NULL) {

                $url = $existingShop['host'];

                if($existingShop['base_path'] !== NULL) {
                    $url .= '/' . $existingShop['base_path'];
                }

                if($existingShop['base_url'] !== NULL) {
                    $url .= '/' . $existingShop['base_url'];
                }

                if(strpos($url, 'http') === FALSE) {
                    $url = 'http://' . $url;
                }

                Shopware()->Front()->Response()->setRedirect($url);
            }

            // Executed if no shop with current browser language exists
            if(boolval($config['showSelection'])) {
                $controller = $args->getSubject();
                $controller->View()->addTemplateDir($this->getPath() . '/Resources/views');
                $controller->View()->assign('isLanguageLocatingShopSelection', true);
                $controller->View()->assign('languageLocatingShops', $shops);
                return;
            }

            // Executed if fallback is configured
            if($config['fallback']) {
                Shopware()->Front()->Response()->setRedirect($config['fallback']);
            }
        }
    }

    /**
     * Get the shops
     *
     * @param bool $useSubShops
     * @return array
     */
    protected function getShops($useSubShops = false)
    {
        $connection = $this->container->get('dbal_connection');
        $sql_query = 'SELECT s.id, s.name, s.host, s.base_url, s.base_path, s.locale_id, l.locale, l.language, l.territory, s.default FROM s_core_shops s JOIN s_core_locales l ON s.locale_id = l.id WHERE s.active = 1';
        if($useSubShops === false) {
            $sql_query .= ' AND s.base_path IS NULL';
        }
        $result = $connection->query($sql_query)->fetchAll();
        return $result;
    }

    /**
     * Get the plugin config
     *
     * @return array
     */
    protected function getConfig()
    {
        $config = $this->container->get('shopware.plugin.cached_config_reader')->getByPluginName($this->getName(), Shopware()->Shop());
        return $config;
    }

    /**
     * Check if shop with given language exists
     *
     * @param $shops
     * @param $currentShopId
     * @param $language
     * @return bool
     */
    protected function checkShopLanguages($shops, $currentShopId, $language)
    {
        $languageIsActive = false;

        foreach($shops as $key => $shop) {
            if(intval($shop['id']) === intval($currentShopId) && strtolower($shop['locale']) == str_replace('-', '_', strtolower($language))) {
                $languageIsActive = true;
            }
        }
        return $languageIsActive;
    }

    /**
     * Get an shop by language
     *
     * @param $shops
     * @param $language
     * @return null
     */
    protected function getShopByLanguage($shops, $language)
    {
        $existingShop = NULL;

        foreach($shops as $key => $shop) {
            if(strtolower($shop['locale']) == str_replace('-', '_', strtolower($language))) {
                $existingShop = $shop;
            }
        }

        return $existingShop;
    }
}

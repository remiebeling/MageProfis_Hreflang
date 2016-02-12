<?php

class MageProfis_HrefLang_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_DISABLED_STOREVIEWS = 'mp_hreflang/general/disabled_storeviews';

    /**
     * Get a list with store ids that will never have a href lang tag generated
     *
     * @return array
     */
    public function getStoreIdsBlacklist($withAdmin = true)
    {
        $config = Mage::getStoreConfig(self::XML_PATH_DISABLED_STOREVIEWS);
        $ids = array_filter(array_map('trim', explode(',', $config)));
        return ($withAdmin ? array_merge($ids, array(0)) : $ids);
    }

    /**
     * Get Product URL in a specific store
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_Core_Model_Store      $store
     *
     * @return string
     */
    public function getProductUrlInStore($product, $store)
    {
        if (!$store instanceof Mage_Core_Model_Store) {
            $store = Mage::app()->getStore($store);
        }

        $idPath = 'product/' . $product->getId();
        foreach ($this->getStoreUrls($idPath) as $storeId => $url) {
            if ($storeId == $store->getId()) {
                return $url;
            }
        }

        return $product->getUrlInStore();
    }

    /**
     * Get Category URL in a specific store
     *
     * @param Mage_Catalog_Model_Category $category
     * @param Mage_Core_Model_Store       $store
     *
     * @return string
     */
    public function getCategoryUrlInStore($category, $store)
    {
        if (!$store instanceof Mage_Core_Model_Store) {
            $store = Mage::app()->getStore($store);
        }

        $idPath = 'category/' . $category->getId();
        foreach ($this->getStoreUrls($idPath) as $storeId => $url) {
            if ($storeId == $store->getId()) {
                return $url;
            }
        }

        return $category->getUrl();
    }

    /**
     * Get a list with urls from all stores
     *
     * @param string $idPath
     *
     * @return array List with store urls
     */
    public function getStoreUrls($idPath)
    {
        $rewrite = Mage::getResourceModel('core/url_rewrite');
        $read = $rewrite->getReadConnection();

        $select = $read->select()
            ->from($rewrite->getMainTable(), array('store_id', 'request_path'))
            ->where('id_path = ?', $idPath);
        $data = $read->fetchPairs($select);

        $urls = array();
        foreach (Mage::app()->getStores() as $store) {
            if (isset($data[$store->getId()])) {
                $baseUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, null);
                $urls[$store->getId()] = $baseUrl . $data[$store->getId()];
            }
        }

        return $urls;
    }
}
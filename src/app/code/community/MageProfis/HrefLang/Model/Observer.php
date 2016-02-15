<?php

class MageProfis_HrefLang_Model_Observer
{
    /**
     * Add HrefLang tag to product view page
     */
    public function addTagToProductPage()
    {
        $product = Mage::registry('current_product');
        $skipStores = Mage::helper('mp_hreflang')->getStoreIdsBlacklist();

        foreach ($product->getStoreIds() as $storeId) {
            if (in_array($storeId, $skipStores)) {
                // Skip disabled stores
                continue;
            }

            // Is product enabled for this store?
            $model = Mage::getModel('mp_hreflang/catalog_product_collection')->getCollection()
                ->setStore($storeId)
                ->addAttributeToSelect('status')
                ->addIdFilter($product->getId())
                ->getFirstItem();

            if ($model->getStatus() === '1') {
                $store  = Mage::app()->getStore($storeId);
                $locale = Mage::getStoreConfig('general/locale/code', $storeId);
                $url    = Mage::helper('mp_hreflang')->getProductUrlInStore($product, $store);
                $this->_addLinkRelAlternate($url, $locale);
            }
        }
    }

    /**
     * Add HrefLang tag to category view page
     */
    public function addTagToCategoryPage()
    {
        $category = Mage::registry('current_category');
        $skipStores = Mage::helper('mp_hreflang')->getStoreIdsBlacklist();

        foreach ($category->getStoreIds() as $storeId) {
            if (in_array($storeId, $skipStores)) {
                // Skip disabled stores
                continue;
            }

            // Is category enabled for this store?
            $model = Mage::getModel('mp_hreflang/catalog_category_collection')->getCollection()
                ->setStoreId($storeId)
                ->addIsActiveFilter()
                ->addIdFilter($category->getId())
                ->getFirstItem();

            if ($model->getIsActive() === '1') {
                $store  = Mage::app()->getStore($storeId);
                $locale = Mage::getStoreConfig('general/locale/code', $storeId);
                $url    = Mage::helper('mp_hreflang')->getCategoryUrlInStore($category, $store);
                $this->_addLinkRelAlternate($url, $locale);
            }
        }
    }

    /**
     * Add <link rel="alternate" hreflang="xx_XX" href="..."/> tag to html head
     *
     * @param string $href   The alternate URL
     * @param string $locale The alternate locale
     */
    protected function _addLinkRelAlternate($href, $locale)
    {
        $headBlock = $this->_getHeadBlock();
        if ($headBlock) {
            $headBlock->addLinkRel('alternate"' . ' hreflang="' . $locale, $href);
        }
    }

    /**
     * Get head block
     *
     * @return Mage_Page_Block_Html_Head
     */
    protected function _getHeadBlock()
    {
        if (!$this->headBlock) {
            $this->headBlock = Mage::app()->getLayout()->getBlock('head');
        }

        return $this->headBlock;
    }
}

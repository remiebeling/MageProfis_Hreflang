<?php

class MageProfis_HrefLang_Model_Observer
{
    /**
     * Holder for head block
     *
     * @var Mage_Page_Block_Html_Head
     */
    protected $headBlock;

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
            $model = Mage::getModel('mp_hreflang/resource_catalog_product_collection')
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
            $model = Mage::getModel('mp_hreflang/resource_catalog_category_collection')
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
     * @mageEvent controller_action_layout_render_before_cms_page_view
     * @param Varien_Event_Observer $event
     */
    public function onCmsPageView(Varien_Event_Observer $event)
    {
        $pageId = (int) Mage::app()->getRequest()->getParam('page_id');
        if (!$pageId) {
            return;
        }

        $page = Mage::getModel('cms/page')->load($pageId);
        /* @var $page Mage_Cms_Model_Page */

        $skipStores = Mage::helper('mp_hreflang')->getStoreIdsBlacklist();

        // Get pages with the same groupname
        $pages = Mage::getModel('cms/page')->getCollection()
            ->addFieldToFilter('groupname', $page->getGroupname())
        ;

        foreach ($pages as $p) {
            $storeIds = Mage::getResourceModel('cms/page')->lookupStoreIds($p->getId());

            foreach ($storeIds as $storeId) {
                if (in_array($storeId, $skipStores)) {
                    continue;
                }

                $href  = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
                // Check for "." (for .htm/.html cms page identifiers)
                $href .= strstr($p->getIdentifier(), '.') ? $p->getIdentifier() : trim($p->getIdentifier(), '/') . '/';

                $locale = Mage::getStoreConfig('general/locale/code', $storeId);
                $this->_addLinkRelAlternate($href, $locale);
            }
        }
    }

    /**
     * AdminHtml: Add field "groupname" to CMS page form
     *
     * @mageEvent adminhtml_cms_page_edit_tab_main_prepare_form
     * @param type $event
     */
    public function addFieldToMainTab (Varien_Event_Observer $event)
    {
        /*
         * Checking if user have permissions to save information
         */
        $isElementDisabled = true;
        if (Mage::getSingleton('admin/session')->isAllowed('cms/page/save')) {
            $isElementDisabled = false;
        }
        $form = $event->getForm();
        /* @var $form Varien_Data_Form */
        $fieldset = $form->getElement('base_fieldset');
        /* @var $fieldset Varien_Data_Form_Element_Fieldset */
        $fieldset->addField('groupname', 'text', array(
            'name'      => 'groupname',
            'label'     => Mage::helper('mp_hreflang')->__('Group Name'),
            'title'     => Mage::helper('mp_hreflang')->__('Group Name'),
            'required'  => true,
            'class'     => 'validate-identifier',
            'note'      => Mage::helper('mp_hreflang')->__('For multilanguage pages'),
            'disabled'  => $isElementDisabled
        ), 'identifier');
    }

    /**
     * AdminHtml: Add field "groupname" to CMS page grid
     *
     * @mageEvent controller_action_layout_generate_blocks_after
     * @param type $event
     */
    public function addFieldToGrid (Varien_Event_Observer $event)
    {
        $action = $event->getEvent()->getAction();
        /* @var $action Mage_Adminhtml_Cms_PageController */
        if ($action instanceof Mage_Adminhtml_Cms_PageController && in_array($action->getFullActionName(), array('adminhtml_cms_page_index'))) {
            $block = $action->getLayout()->getBlock('cms_page.grid');
            /* @var $block Mage_Adminhtml_Block_Cms_Page_Grid */
            if ($block) {
                $block->addColumnAfter('groupname', array(
                        'header'    => Mage::helper('mp_hreflang')->__('Group Name'),
                        'width'     => '50px',
                        'align'     => 'left',
                        'type'      => 'text',
                        'index'     => 'groupname'
                    ), 'identifier');
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
            $linkRel = $this->_getLayout()
                ->createBlock('core/text', 'link_rel_' . md5($locale . $href))
                ->setText('<link rel="alternate" hreflang="' . $locale . '" href="' . $href . '"/>')
            ;
            $headBlock->append($linkRel);
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
            $this->headBlock = $this->_getLayout()->getBlock('head');
        }

        return $this->headBlock;
    }

    /**
     * Get layout
     *
     * @return Mage_Core_Model_Layout
     */
    protected function _getLayout()
    {
        return Mage::app()->getLayout();
    }
}

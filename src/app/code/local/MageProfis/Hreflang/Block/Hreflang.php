<?php 
class MageProfis_Hreflang_Block_Hreflang extends Mage_Core_Block_Template
{
    /**
     * get current product from registry
     * @return Object
     */
    public function getCurrentProduct()
    {
        return Mage::registry('current_product');
    }
    
    /**
     * get Current Category from registry
     * @return Object   
     */
    public function getCurrentCategory()
    {
        return Mage::registry('current_category');
    }
    
    /**
     * get current cms Page from registry
     * @return Object
     */
    public function getCurrentCmsPage()
    {
        return Mage::getSingleton('cms/page');
    }
    
    /**
     * get language code by store id
     * @param string $id
     * @return string
     */
    public function getLangCodeByStoreId($id)
    {
        $ids = array(
            1 => "de-DE",
            2 => "en-US"
        );
        return $ids[$id];
    }
    
    public function getRequestPath($store_id)
    {
        if($this->getCurrentCmsPage()->getIdentifier())
        {
            $identifier = $this->getCurrentCmsPage()->getIdentifier();
            return $identifier; 
        }
        $query = $this->_connection()->select()
            ->from($this->getTableName('core_url_rewrite'), 'request_path');
        
        if($this->getCurrentProduct())
        {
           $query->where('product_id = ?', $this->getCurrentProduct()->getId());
           $query->where('id_path = ?', 'product/' . $this->getCurrentProduct()->getId()); 
        }
        elseif($this->getCurrentCategory())
        {
           $query->where('category_id = ?', $this->getCurrentCategory()->getId());
           $query->where('id_path = ?', 'category/' . $this->getCurrentCategory()->getId()); 
        }
        
        $query->where('store_id = ?', $store_id)
                 ->limit(1); 
        return $this->_connection()->fetchOne($query);
    }
    
    public function getStoreUrl($url, $store_id)
    {
        if(Mage::getBlockSingleton('page/html_header')->getIsHomePage())
        {
            return Mage::app()->getStore($store_id)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
        }
        return Mage::app()->getStore($store_id)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK) . $url;
    }
    
    public function getStores()
    {
        return Mage::app()->getStores();
    }
    
    /**
     * 
     * @return Mage_Core_Model_Resource
     */
    protected function _resource()
    {
        return Mage::getSingleton('core/resource');
    }
    
    /**
     * 
     * @return Varien_Db_Adapter_Interface
     */
    protected function _connection()
    {
        return $this->_resource()->getConnection('core_read');
    }
    
    /**
     * 
     * @param type $name
     * @return string
     */
    protected function getTableName($name)
    {
        return $this->_resource()->getTableName($name);
    } 
}
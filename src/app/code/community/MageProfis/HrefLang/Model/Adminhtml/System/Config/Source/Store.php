<?php

class MageProfis_HrefLang_Model_Adminhtml_System_Config_Source_Store
{
    protected $_options;

    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = Mage::getSingleton('adminhtml/system_store')
                     ->getStoreValuesForForm(false, true);
        }
        return $this->_options;
    }
}
<?php

/**
 * this class is not a rewrite!
 */
class MageProfis_HrefLang_Model_Resource_Catalog_Product_Collection
extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * is always False!
     *
     * @return boolean
     */
    public function isEnabledFlat()
    {
        return false;
    }
}

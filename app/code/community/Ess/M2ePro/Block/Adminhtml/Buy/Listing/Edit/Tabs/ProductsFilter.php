<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Buy_Listing_Edit_Tabs_ProductsFilter extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyListingEditTabsProductsFilter');
        //------------------------------

        $this->setTemplate('M2ePro/buy/listing/tabs/products_filter.phtml');
    }

    protected function _beforeToHtml()
    {
        //-------------------------------
        $accounts = Mage::helper('M2ePro/Component_Buy')->getCollection('Account')
            ->setOrder('title', 'ASC')
            ->toArray();
        $this->setData('accounts', $accounts['items']);
        //-------------------------------

        //-------------------------------
        $formData = Mage::helper('M2ePro')->getGlobalValue('temp_data');
        $this->setData('attributes',
            Mage::helper('M2ePro/Magento')->getAttributesByAttributeSets($formData['attribute_sets']));
        //-------------------------------

        //------------------------------
        $treeSettings = array(
            'show_products_amount' => false,
            'hide_products_this_listing' => false
        );

        $treeBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_listing_category_tree','',array(
            'component'=>Ess_M2ePro_Helper_Component_Buy::NICK,
            'tree_settings' => $treeSettings
        ));
        $this->setChild('categoriesTree', $treeBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }
}

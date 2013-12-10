<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Mobile
 * @version    1.6.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Mobile_Block_Checkout_Onepage_Success extends Mage_Checkout_Block_Onepage_Success
{
    protected $_order = null;

    public function getOrder()
    {
        if (!$this->_order){
            if ($this->getOrderId()){
                $this->_order = Mage::getModel('sales/order')->loadByIncrementId($this->getOrderId());
            }
        }
        return $this->_order;
    }

    public function getCanViewOrder()
    {
        return ($this->getOrder() && $this->getOrder()->getCustomerId());
    }
}
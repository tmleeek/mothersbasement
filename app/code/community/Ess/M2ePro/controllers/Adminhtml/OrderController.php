<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_OrderController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/sales')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Sales'))
             ->_title(Mage::helper('M2ePro')->__('Orders'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/OrderHandler.js')
             ->addJs('M2ePro/Order/Edit/ItemHandler.js');

        $this->_initPopUp();

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/sales/order');
    }

    //#############################################

    public function preDispatch()
    {
        parent::preDispatch();

        Mage::getSingleton('M2ePro/Order_Log_Manager')
            ->setInitiator(Ess_M2ePro_Model_Order_Log::INITIATOR_USER);
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_order'));

        $this->renderLayout();
    }

    //#############################################

    public function logGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::helper('M2ePro/Component_Ebay')->getObject('Order', (int)$id);

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $order);

        $grid = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_order_view_log_grid');
        $this->getResponse()->setBody($grid->toHtml());
    }

    //#############################################

    public function getCountryRegionsAction()
    {
        $country = $this->getRequest()->getParam('country');
        $regions = array();

        if (!empty($country)) {
            $regionsCollection = Mage::getResourceModel('directory/region_collection')
                ->addCountryFilter($country)
                ->load();

            foreach ($regionsCollection as $region) {
                $regions[] = array(
                    'value' => $region->getData('code'),
                    'label' => $region->getData('default_name')
                );
            }

            if (count($regions) > 0) {
                array_unshift($regions, array(
                    'value' => '',
                    'label' => Mage::helper('directory')->__('-- Please select --')
                ));
            }
        }

        exit(json_encode($regions));
    }

    //#############################################

    public function reservationPlaceAction()
    {
        $id = $this->getRequest()->getParam('id');
        $ids = $this->getRequest()->getParam('ids');

        if (is_null($id) && is_null($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select order(s).'));
            return $this->_redirect('*/adminhtml_order/index');
        }

        $ordersIds = array();
        !is_null($id) && $ordersIds[] = $id;
        !is_null($ids) && $ordersIds = array_merge($ordersIds,(array)$ids);

        /** @var $orders Ess_M2ePro_Model_Order[] */
        $orders = Mage::getModel('M2ePro/Order')
            ->getCollection()
                ->addFieldToFilter('id', array('in' => $ordersIds))
                ->addFieldToFilter('reservation_state', array('neq' => Ess_M2ePro_Model_Order_Reserve::STATE_PLACED))
                ->addFieldToFilter('magento_order_id', array('null' => true));

        try {
            $actionSuccessful = false;

            foreach ($orders as $order) {
                if (!$order->isReservable()) {
                    continue;
                }

                if ($order->getReserve()->place()) {
                    $actionSuccessful = true;
                }
            }

            if ($actionSuccessful) {
                $this->_getSession()->addSuccess(
                    Mage::helper('M2ePro')->__('QTY for selected Order(s) was successfully reserved.')
                );
            } else {
                $this->_getSession()->addError(
                    Mage::helper('M2ePro')->__('QTY for selected Order(s) was not reserved.')
                );
            }

        } catch (Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__('QTY for selected Order(s) was not reserved. Reason: %s', $e->getMessage())
            );
        }

        $this->_redirectUrl($this->_getRefererUrl());
    }

    public function reservationCancelAction()
    {
        $id = $this->getRequest()->getParam('id');
        $ids = $this->getRequest()->getParam('ids');

        if (is_null($id) && is_null($ids)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select order(s).'));
            return $this->_redirect('*/adminhtml_order/index');
        }

        $ordersIds = array();
        !is_null($id) && $ordersIds[] = $id;
        !is_null($ids) && $ordersIds = array_merge($ordersIds,(array)$ids);

        /** @var $orders Ess_M2ePro_Model_Order[] */
        $orders = Mage::getModel('M2ePro/Order')
            ->getCollection()
                ->addFieldToFilter('id', array('in' => $ordersIds))
                ->addFieldToFilter('reservation_state', Ess_M2ePro_Model_Order_Reserve::STATE_PLACED);

        try {
            $actionSuccessful = false;

            foreach ($orders as $order) {
                if ($order->getReserve()->cancel()) {
                    $actionSuccessful = true;
                }
            }

            if ($actionSuccessful) {
                $this->_getSession()->addSuccess(
                    Mage::helper('M2ePro')->__('QTY reserve for selected Order(s) was successfully canceled.')
                );
            } else {
                $this->_getSession()->addError(
                    Mage::helper('M2ePro')->__('QTY reserve for selected Order(s) was not canceled.')
                );
            }

        } catch (Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'QTY reserve for selected Order(s) was not canceled. Reason: %s', $e->getMessage()
                )
            );
        }

        $this->_redirectUrl($this->_getRefererUrl());
    }

    //#############################################

    public function editItemAction()
    {
        $itemId = $this->getRequest()->getParam('item_id');
        /** @var $item Ess_M2ePro_Model_Order_Item */
        $item = Mage::getModel('M2ePro/Order_Item')->load($itemId);

        $this->getResponse()->setHeader('Content-type', 'application/json');

        if (is_null($item->getId())) {
            $this->getResponse()->setBody(json_encode(array(
                'error' => Mage::helper('M2ePro')->escapeJs(Mage::helper('M2ePro')->__('Order item does not exist.'))
            )));

            return;
        }

        Mage::helper('M2ePro')->setGlobalValue('order_item', $item);

        if (is_null($item->getProductId()) || !$item->getMagentoProduct()->exists()) {
            $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_order_item_product_mapping');

            $this->getResponse()->setBody(json_encode(array(
                'title' => Mage::helper('M2ePro')->__('Mapping Product "%s"', $item->getChildObject()->getTitle()),
                'html' => $block->toHtml(),
                'pop_up_config' => array(
                    'height' => 500,
                    'width'  => 750
                ),
            )));

            return;
        }

        if ($item->getMagentoProduct()->hasRequiredOptions()) {
            $block = $this->loadLayout()->getLayout()->createBlock(
                'M2ePro/adminhtml_order_item_product_options_mapping', '', array(
                    'order_id' => $item->getOrderId(),
                    'product_id' => $item->getProductId()
                )
            );

            $this->getResponse()->setBody(json_encode(array(
                'title' => Mage::helper('M2ePro')->__('Setting Product Options'),
                'html' => $block->toHtml()
            )));

            return;
        }

        $this->getResponse()->setBody(json_encode(array(
            'error' => Mage::helper('M2ePro')->__('Product does not have required options.')
        )));
    }

    //#############################################

    public function assignProductAction()
    {
        $sku = $this->getRequest()->getPost('sku');
        $productId = $this->getRequest()->getPost('product_id');
        $orderItemId = $this->getRequest()->getPost('order_item_id');

        /** @var $orderItem Ess_M2ePro_Model_Order_Item */
        $orderItem = Mage::getModel('M2ePro/Order_Item')->load($orderItemId);

        $this->getResponse()->setHeader('Content-type', 'application/json');

        if ((!$productId && !$sku) || !$orderItem->getId()) {
            $this->getResponse()->setBody(json_encode(array(
                'error' => Mage::helper('M2ePro')->__('Please specify required options.')
            )));
            return;
        }

        $collection = Mage::getModel('catalog/product')->getCollection()
            ->joinField(
                'qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );

        $productId && $collection->addFieldToFilter('entity_id', $productId);
        $sku && $collection->addFieldToFilter('sku', $sku);

        $tempData = $collection->getSelect()->query()->fetch();

        if (!$tempData) {
            $this->getResponse()->setBody(json_encode(array(
                'error' => Mage::helper('M2ePro')->__('Product does not exist.')
            )));
            return;
        }

        $orderItem->assignProduct($tempData['entity_id']);

        if (!$orderItem->getMagentoProduct()->hasRequiredOptions()) {
            $orderItem->setActionRequired(false)->save();
        }

        $orderItem->getOrder()->addSuccessLog('Order item "%title%" was successfully mapped.', array(
            'title' => $orderItem->getChildObject()->getTitle()
        ));

        $this->getResponse()->setBody(json_encode(array(
            'success'  => Mage::helper('M2ePro')->__('Order item was successfully mapped.'),
            'continue' => $orderItem->getMagentoProduct()->hasRequiredOptions()
        )));
    }

    public function productMappingGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_order_item_product_mapping_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //#############################################

    public function assignProductDetailsAction()
    {
        $orderItemId = $this->getRequest()->getPost('order_item_id');
        $options = $this->getRequest()->getPost('option_id');
        $saveRepair = $this->getRequest()->getPost('save_repair');

        /** @var $orderItem Ess_M2ePro_Model_Order_Item */
        $orderItem = Mage::getModel('M2ePro/Order_Item')->load($orderItemId);

        $this->getResponse()->setHeader('Content-type', 'application/json');

        if (count($options) == 0 || !$orderItem->getId()) {
            $this->getResponse()->setBody(json_encode(array(
                'error' => Mage::helper('M2ePro')->__('Please specify required options.')
            )));
            return;
        }

        $associatedOptions  = array();
        $associatedProducts = array();

        foreach ($options as $optionId => $optionData) {
            $optionData = json_decode($optionData, true);

            if (!isset($optionData['value_id']) || !isset($optionData['product_ids'])) {
                $this->getResponse()->setBody(json_encode(array(
                    'error' => Mage::helper('M2ePro')->__('Please specify required options.')
                )));
                return;
            }

            $optionId = (int)$optionId;
            $valueId  = (int)$optionData['value_id'];

            $associatedOptions[$optionId] = $valueId;
            $associatedProducts["{$optionId}::{$valueId}"] = $optionData['product_ids'];
        }

        try {
            $orderItem->assignProductDetails($associatedOptions, $associatedProducts);
        } catch (Exception $e) {
            $this->getResponse()->setBody(json_encode(array(
                'error' => $e->getMessage()
            )));
            return;
        }

        if ($saveRepair) {
            $outputData = array(
                'associated_options'  => $orderItem->getAssociatedOptions(),
                'associated_products' => $orderItem->getAssociatedProducts()
            );

            /** @var $orderRepair Ess_M2ePro_Model_Order_Repair */
            $orderRepair = Mage::getModel('M2ePro/Order_Repair');
            $orderRepair->create(
                $orderItem->getProductId(),
                $orderItem->getChildObject()->getRepairInput(),
                $outputData,
                $orderItem->getComponentMode(),
                Ess_M2ePro_Model_Order_Repair::TYPE_VARIATION
            );
        }

        $orderItem->getOrder()->addSuccessLog('Order item "%title%" options were successfully configured.', array(
            'title' => $orderItem->getChildObject()->getTitle()
        ));

        $this->getResponse()->setBody(json_encode(array(
            'success' => Mage::helper('M2ePro')->__('Order item options were successfully configured.')
        )));
    }

    //#############################################

    public function unassignProductAction()
    {
        $orderItemId = $this->getRequest()->getPost('order_item_id');

        /** @var $orderItem Ess_M2ePro_Model_Order_Item */
        $orderItem = Mage::getModel('M2ePro/Order_Item')->load($orderItemId);

        $this->getResponse()->setHeader('Content-type', 'application/json');

        if (!$orderItem->getId()) {
            $this->getResponse()->setBody(json_encode(array(
                'error' => Mage::helper('M2ePro')->__('Please specify required options.')
            )));
            return;
        }

        if ($orderItem->hasRepairInput()) {
            $hash = Ess_M2ePro_Model_Order_Repair::generateHash($orderItem->getChildObject()->getRepairInput());

            /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
            $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
            $connWrite->delete(
                Mage::getResourceModel('M2ePro/Order_Repair')->getMainTable(),
                array(
                    'product_id = ?' => $orderItem->getProductId(),
                    'hash = ?' => $hash
                )
            );
        }

        $orderItem->unassignProduct();

        $orderItem->getOrder()->addSuccessLog('Item "%title%" was successfully unmapped.', array(
            'title' => $orderItem->getChildObject()->getTitle()
        ));

        $this->getResponse()->setBody(json_encode(array(
            'success' => Mage::helper('M2ePro')->__('Item was successfully unmapped.')
        )));
    }

    //#############################################

    public function getDebugInformationAction()
    {
        $id = $this->getRequest()->getParam('id');

        if (is_null($id)) {
            return $this->getResponse()->setBody('');
        }

        try {
            $order = Mage::helper('M2ePro/Component')->getUnknownObject('Order', (int)$id);
        } catch (Exception $e) {
            return $this->getResponse()->setBody('');
        }

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $order);

        $debugBlock = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_order_debug');
        $this->getResponse()->setBody($debugBlock->toHtml());
    }

    //#############################################

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam('id');

        if (is_null($id)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Order ID is not defined.'));
            return $this->_redirect('*/*/index');
        }

        $order = Mage::getModel('M2ePro/Order')->load($id);

        if (is_null($order->getId())) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Order with such ID does not exist.'));
            return $this->_redirect('*/*/index');
        }

        $order->deleteInstance();

        $this->_getSession()->addSuccess(Mage::helper('M2ePro')->__('Order was successfully deleted.'));
        $this->_redirect('*/*/index');
    }

    //#############################################
}
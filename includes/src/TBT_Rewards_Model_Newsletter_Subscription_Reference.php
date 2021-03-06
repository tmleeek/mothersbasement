<?php
/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 * http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Transfer Reference
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Newsletter_Subscription_Reference extends TBT_Rewards_Model_Transfer_Reference_Abstract {
	const REFERENCE_TYPE_ID = 10;
	//protected $_transferCellRenderer = 'rewardsref/customer_transfers_referral_cell';
	/* 
     * in the format [reference_type] => renderer
    public function getTRefCellRenderers() {
        return array(self::REFERENCE_TYPE_ID => $this->_transferCellRenderer);
    }
    
    
    
    */
	
	public function clearReferences(&$transfer) {
		if ($transfer->hasData ( 'newsletter_id' )) {
			$transfer->unsetData ( 'newsletter_id' );
		}
		return $this;
	}
	
	public function getReferenceOptions() {
		$reference_options = array (self::REFERENCE_TYPE_ID => Mage::helper ( 'rewards' )->__ ( 'Newsletter' ) );
		return $reference_options;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see TBT_Rewards_Model_Transfer_Reference_Abstract::loadReferenceInformation()
	 */
	public function loadReferenceInformation(&$transfer) {
		$this->loadTransferId ( $transfer );
		return $this;
	}
	
	/**
	 * 
	 * @param TBT_Rewards_Model_Transfer $transfer
	 * @param int $id
	 */
	public function loadTransferId($transfer) {
		$id = $transfer->getReferenceId ();
		$transfer->setReferenceType ( TBT_Rewards_Model_Newsletter_Subscription_Reference::REFERENCE_TYPE_ID );
		$transfer->setReferenceId ( $id );
		$transfer->setData ( 'newsletter_id', $id );
		
		return $this;
	}

}
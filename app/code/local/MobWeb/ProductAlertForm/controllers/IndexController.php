<?php
class MobWeb_ProductAlertForm_IndexController extends Mage_Core_Controller_Front_Action
{
	public function sendAction() {
		// Get the parameters from the request sent
		$parameters = $this->getRequest()->getParams();

		// Get the settings from the admin panel
		$transactional_email_id = Mage::getStoreConfig('productalertform/email_confirmation/transactional_email_id');
		$copy_recipient = Mage::getStoreConfig('productalertform/email_confirmation/email_copy_recipient');

		// If no valid copy recipient has been specified, no email can be sent
		if(!filter_var($copy_recipient, FILTER_VALIDATE_EMAIL)) {
			Mage::log('MobWeb_ProductAlertForm was unable to send the product alert request because the following email adress is invalid or has not been set:' . $copy_recipient, null, 'mobweb_productalertform.log', true);
			Mage::getSingleton('core/session')->addError($this->__('There has been a problem submitting your product alert request. Please contact us directly with your request.'));
		} else {
			// Send the transactional email to the customer
			Mage::getModel('core/email_template')->sendTransactional(
				$transactional_email_id,
				array(
					'name' => Mage::getStoreConfig('trans_email/ident_support/name'),
				    'email' =>  Mage::getStoreConfig('trans_email/ident_support/email')
				),
				$parameters['email'],
				$parameters['name'],
				array(
					'manufacturer' => $parameters['manufacturer'],
					'model' => $parameters['model'],
					'customer_name' => $parameters['name'],
					'customer_email' => $parameters['email'],
					'customer_comment' => $parameters['comment'],
				),
				Mage::app()->getStore()->getId()
			);

			// And a copy to the store admin, so he can keep track of the requests
			Mage::getModel('core/email_template')->sendTransactional(
				$transactional_email_id,
				array(
					'name' => Mage::getStoreConfig('trans_email/ident_support/name'),
				    'email' => Mage::getStoreConfig('trans_email/ident_support/email')
				),
				$copy_recipient,
				$copy_recipient,
				array(
					'manufacturer' => $parameters['manufacturer'],
					'model' => $parameters['model'],
					'customer_name' => $parameters['name'],
					'customer_email' => $parameters['email'],
					'customer_comment' => $parameters['comment'],
				),
				Mage::app()->getStore()->getId()
			);

			// Set the success message
			Mage::getSingleton('core/session')->addSuccess($this->__('Your product alert request has been received.'));
		}

		// Redirect the user to the front page
		$redirect_url = isset($parameters['form_url']) ? $parameters['form_url'] : '/';
		$this->_redirect($redirect_url);
	}
}
?>
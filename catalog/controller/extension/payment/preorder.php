<?php
class ControllerExtensionPaymentPreorder extends Controller {
	public function index() {
		return $this->load->view('extension/payment/preorder');
	}

	public function confirm() {
		$json = array();
		
		if (($this->session->data['payment_method']['code'] == 'pp_standard') || ($this->session->data['payment_method']['code'] == 'cardlink') ) {
			$this->load->model('checkout/order');

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], PREORDER_ID);
		
			$json['redirect'] = $this->url->link('checkout/success');
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}
}

<?php
class ControllerExtensionModuleCustomerGroupPrice extends Controller {
	
	public function getProductPrice(&$route, &$data, &$args) {
        if($this->config->get('module_customer_group_price_status')) {

            $this->load->model('extension/module/customer_group_price');
            if($price = $this->model_extension_module_customer_group_price->getProductPrice($args)) {
                $args['price'] += ($args['price'] * $price) / 100;
            }
        }
    }
}
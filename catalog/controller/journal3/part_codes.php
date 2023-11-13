<?php

use Journal3\Opencart\Controller;

class ControllerJournal3PartCodes extends Controller {

	public function index($args) {
		if(isset($this->request->get['product_id'])) {
            $product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);

            if($product_info && $product_info['ean']) {
                return $product_info['ean'];
            }
        }
	}

}

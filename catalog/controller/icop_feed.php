<?php

class ControllerIcopFeed extends Controller {
    private $payment_codes = array('cardlink', 'winbnk', 'winbank', 'pp_standard', 'vivapayment', 'vivawallet', 'simplify_commerce', 'simplifycommerce');

    public function index() {
        $json = array();

        $json['version'] = VERSION;

        if (version_compare(VERSION, '2.3.0', '<') || version_compare(VERSION, '3', '>')) {
            $this->load->model('setting/extension');
            $payments = $this->model_setting_extension->getExtensions('payment');
            $shippings = $this->model_setting_extension->getExtensions('shipping');
        } elseif (version_compare(VERSION, '3', '<')) {
            $this->load->model('extension/extension');
            $shippings = $this->model_extension_extension->getExtensions('shipping');
            $payments = $this->model_extension_extension->getExtensions('payment');
        }
        

        // shippings
        $json['shippings'] = array();
        foreach ($shippings as $shipping) {
            if ($this->config->get($shipping['code'] . '_status') || $this->config->get('shipping_' . $shipping['code'] . '_status')) {
                if (version_compare(VERSION, '2.3.0', '<')) {
                    $this->load->language('shipping/' . $shipping['code']);
                } else {
                    $this->load->language('extension/shipping/' . $shipping['code']);
                }

                $json['shippings'][] = array(
                    'name'  => $this->language->get('text_description') != 'text_description' ? $this->language->get('text_description') : 'Name not found',
                    'code'  => $shipping['code']
                );
            }
        }

        // payments
        $json['payments'] = array();
        foreach ($payments as $payment) {
            if (($this->config->get($payment['code'] . '_status') || $this->config->get('payment_' . $payment['code'] . '_status')) && in_array($payment['code'], $this->payment_codes)) {
                $bank = '';
                $code = $payment['code'];
                $state = '';

                if ($payment['code'] == 'cardlink') {
                    if (version_compare(VERSION, '2.3.0', '<')) {
                        $state = $this->config->get('cardlink_test');
                        $url = ($state == 'test') ? $this->config->get('cardlink_turl') : $this->config->get('cardlink_url');
                        $bank = strpos($url, 'euro') ? 'Eurobank' : 'Alpha Bank';
                    } elseif (version_compare(VERSION, '3', '<')) {
                        $state = $this->config->get('cardlink_enviroment') ? 'live' : 'test';
                        $url = ($state == 'test') ? $this->config->get('cardlink_test_url') : $this->config->get('cardlink_url');
                        $bank = strpos($url, 'euro') ? 'Eurobank' : 'Alpha Bank';
                    } else {
                        $state = $this->config->get('payment_cardlink_mode') ? 'live' : 'test';
                        $bank = $this->config->get('payment_cardlink_bank') == 'euro' ? 'Eurobank' : 'Alpha Bank';
                    }
                } elseif (($payment['code'] == 'winbnk') || ($payment['code'] == 'winbank')) {
                    $bank = 'Winbank';
                    if (version_compare(VERSION, '2.3.0', '>')) {
                        $state = $this->config->get('winbank_enviroment') || $this->config->get('payment_winbank_enviroment') ? 'live' : 'test';
                    }
                    $code = "winbank";
                } elseif ($payment['code'] == 'pp_standard') {
                    $bank = 'Paypal';
                    $state = $this->config->get('payment_pp_standard_test') || $this->config->get('pp_standard_test') ? 'test' : 'live';
                } elseif (($payment['code'] == 'vivapayment') || ($payment['code'] == 'vivawallet')) {
                    $bank = 'Viva';
                    $state = $this->config->get('payment_vivapayment_test') || $this->config->get('vivapayment_test') ? 'test' : 'live';
                    $code = "vivapayment";
                } elseif (($payment['code'] == 'simplify_commerce') || ($payment['code'] == 'simplifycommerce')) {
                    $bank = 'NBG';
                    $state = $this->config->get('payment_simplifycommerce_test') || $this->config->get('simplify_commerce_test') ? 'test' : 'live';
                    $code = 'simplifycommerce';
                }

                $json['payments'][] = array('name' => $bank, 'code' => $code, 'state' => $state);
            }
        }

        // Modules
        $json['modules'] = array();
        $modules = $this->db->query("SELECT code FROM " . DB_PREFIX . "extension WHERE `type` = 'module' ORDER BY code")->rows;
        $admin_path = realpath(DIR_APPLICATION . '/..') . ( is_dir(realpath(DIR_APPLICATION . '/..'). '/admin') ?  '/admin/' : '/cmsadmin/');
        
        foreach ($modules as $module) {
            if(strpos($module['code'], 'journal2') === 0) continue;
            
            if(is_file($admin_path .'language/en-gb/extension/module/' . $module['code'] . '.php')) {
                include_once($admin_path .'language/en-gb/extension/module/' . $module['code'] . '.php');
            } elseif (is_file($admin_path .'language/en-gb/module/' . $module['code'] . '.php')) {
                include_once($admin_path .'language/en-gb/module/' . $module['code'] . '.php');
            } elseif (is_file($admin_path .'language/english/module/' . $module['code'] . '.php')) {
                include_once($admin_path .'language/english/module/' . $module['code'] . '.php');
            } elseif (is_file($admin_path .'language/english/extension/module/' . $module['code'] . '.php')) {
                include_once($admin_path .'language/english/extension/module/' . $module['code'] . '.php');
            } elseif(is_file($admin_path .'language/el-gr/extension/module/' . $module['code'] . '.php')) {
                include_once($admin_path .'language/el-gr/extension/module/' . $module['code'] . '.php');
            } elseif (is_file($admin_path .'language/el-gr/module/' . $module['code'] . '.php')) {
                include_once($admin_path .'language/el-gr/module/' . $module['code'] . '.php');
            } elseif (is_file($admin_path .'language/greek/module/' . $module['code'] . '.php')) {
                include_once($admin_path .'language/greek/module/' . $module['code'] . '.php');
            } elseif (is_file($admin_path .'language/greek/extension/module/' . $module['code'] . '.php')) {
                include_once($admin_path .'language/greek/extension/module/' . $module['code'] . '.php');
            } else {
                $_['heading_title'] = ucwords($module['code']);
            }

           $json['modules'][] = array(
                'name'  => strip_tags($_['heading_title']),
                'code'  => $module['code']

           );
        }


        // Feeds
        $json['feeds'] = array();
        $files = glob($admin_path . 'controller/{extension/feed,feed}/*.php', GLOB_BRACE);
		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');
                if($this->config->get($extension . '_status') || $this->config->get('feed_' . $extension . '_status')) {
                    if (version_compare(VERSION, '2.3.0', '<')) {
                        include_once($admin_path . 'language/english/feed/' . $extension . '.php');
                    } else {
                        include_once($admin_path . 'language/en-gb/extension/feed/' . $extension . '.php');
                    }
                    $json['feeds'][] = array('name' => $_['heading_title'], 'code' => $extension);
                }
			}
		}

        $this->response->addHeader('application/json');
        $this->response->setOutput(json_encode($json));
    }
}
<?php

/**
 * Plugin Name:       Exchange Rate Block
 * Description:       A block that displays the rates of selected currencies.
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           1.0
 * Text Domain:       exchangeRateData
 *
 * @package           create-block
 */

defined('ABSPATH') || exit;

if( !class_exists( 'Exchange_Rate' ) ) {
	class Exchange_Rate
	{
		private $api_nbp_url;

		public function __construct()
		{
			add_action('init', array($this, 'create_block_starter_block_block_init'));
			add_action('wp_ajax_get_ajax_rate_data', array($this, 'get_ajax_rate_data'));
			add_action('wp_ajax_nopriv_get_ajax_rate_data', array($this, 'get_ajax_rate_data'));

			$this->api_nbp_url = "http://api.nbp.pl/api/exchangerates/rates/A/";
		}
		
		public function create_block_starter_block_block_init()
		{
			register_block_type(
				__DIR__,
				array(
					'attributes'      => array(
						"currency" => array(
							"type" => "string",
							"enum" => array("CHF", "EUR", "INR", "USD"),
							"default" => "CHF"
						),
						"showDatePicker" => array(
							"type" => "boolean",
							"default," => false
						),
						'className'    => array(
							'type'      => 'string'
						),
					),
					'render_callback' => array($this, "render_my_callback"),
					"script" => $this->enqueue_frontend_script()
				),
			);
		}

		public function enqueue_frontend_script()
		{
			if (!is_admin()) {
				wp_register_script('exchange-rate-frontend', plugin_dir_url(__FILE__) . 'build/frontend.js', array('wp-element'), null, true);
				wp_localize_script('exchange-rate-frontend', 'exchangeRateData', array(
					'ajaxurl' => admin_url('admin-ajax.php'),
					'ajaxnonce' => wp_create_nonce( 'ajax-nonce' )
				));
				wp_enqueue_script('exchange-rate-frontend');
			}
		}

		public function render_my_callback($args)
		{
			$currency = $args["currency"];
			$today = $this->get_today_day();
			$last_working_day = $this->get_last_working_day();

			$exchange_rate_response = $this->get_rate_data($currency, $last_working_day);
			$exchange_rate_rate = $exchange_rate_response["rate"];

			$id = uniqid('exchange-rate-info-');
			$class_name = isset($args["className"]) ? $args["className"] : '';

			if( $exchange_rate_response["error_message"] !== "" ) {
				$class_name .= 'exchangerate__is-error';
			}

			$content = '<div id="' . $id . '" class="exchangerate ' . $class_name . '">
							<div class="exchangerate__row exchangerate__values">
								<div class="exchangerate__date">
									<span class="exchangerate__label">' . __("Data kursu", "exchangerate") . ':</span>
									<span class="exchangerate__value exchangerate__date_value">' . $last_working_day . '</span>
								</div>
								<div class="exchangerate__rate">
									<span class="exchangerate__label">' . __("Kurs", "exchangerate") . ':</span>
									<span class="exchangerate__value exchangerate__rate_value">' . $exchange_rate_rate . '</span>
								</div>
								<div class="exchangerate__currency">
									<span class="exchangerate__label">' . __("Waluta", "exchangerate") . ':</span>
									<spaan class="exchangerate__value exchangerate__rate_rate">' . $currency . '</span>
								</div>
							</div>';

			if ($args["showDatePicker"]) {
				$content .= '<div class="exchangerate__row">
								<span class="exchangerate__label">' . __("Wybierz datę", "exchangerate") . ':</span>
								<input data-currency="' . $currency . '" data-id="' . $id . '" class="exchangerate__date-picker" type="date" value="' . $last_working_day . '" max="' . $today . '">
							</div>';
			}

			$content .= '<div class="exchangerate__row exchangerate__error">' . $exchange_rate_response["error_message"] . '</div>
					</div>';

			return $content;
		}

		public function get_today_day() {
			return date("Y-m-d");
		}

		public function get_last_working_day()
		{
			$currentDay = date("w");

			switch ($currentDay) {
				case "1": {
				  $lastWorkingDay = date("Y-m-d", strtotime("-3 day"));
				  break;
				}
				case "0": {
				  $lastWorkingDay = date("Y-m-d", strtotime("-2 day"));
				  break;
				}
				default: {
				  $lastWorkingDay = date("Y-m-d", strtotime("-1 day"));
				  break;
				}
			  }

			  return $lastWorkingDay;
		}

		public function get_ajax_rate_data()
		{
			check_ajax_referer( 'ajax-nonce', 'ajaxNonce' );

			$date = $_REQUEST['date'];
			$currency = $_REQUEST['currency'];
			$data = $this->get_rate_data($currency, $date);

			if($data["status"] === 200) {
				wp_send_json_success($data);
			} else {
				wp_send_json_error($data);
			}

			wp_die();
		}

		public function get_rate_data($currency, $date = null)
		{
			$response = wp_remote_get($this->api_nbp_url . $currency . '/' . $date . '?format=json');
			$http_code = wp_remote_retrieve_response_code($response);
			$body = wp_remote_retrieve_body($response);
			$data = $this->process_response($http_code, $body);

			return $data;
		}

		public function process_response($http_code, $body)
		{
			$exchange_rate_data = [];
			$exchange_rate_data["status"] = $http_code;

			if ($http_code === 200) {
				$response_decode = json_decode($body, true);
				$exchange_rate_data["error_message"] = "";
				$exchange_rate_data["rate"] = $response_decode["rates"][0]["mid"];
			} else {
				$exchange_rate_data["error_message"] = $body;
				$exchange_rate_data["rate"] = __("n/a", "exchangerate");
			}

			return $exchange_rate_data;
		}
	}
}

if( class_exists( 'Exchange_Rate' ) ) {
	$exchange_rate_info = new Exchange_Rate;
}

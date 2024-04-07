<?php defined('ABSPATH') || die('No direct access allowed!');

/*
 * Plugin name: P6rguP6der Euro Converter
 * Description: A simple lightweight plugin for converting Euros into other currencies (and vice versa).
 * Author: P6rguP6der
 * Version: 1.0
 */

class P6rguP6derEuroConverter {

	public $currency_rates = array();
	public $last_update = '1970-01-01';


	public function __construct() {
		$currency_data = get_option('p6rgup6der_currency_data');

		if (false == $currency_data) {
			$this->_download_data();
		} else {
			if (isset($currency_data['rates'])) {
				$this->currency_rates = $currency_data['rates'];
			}

			if (isset($currency_data['last_updated'])) {
				$this->last_update = $currency_data['last_updated'];
			}

			$lu_timestamp = strtotime($this->last_update);

			// Trigger download if last update was more than 24 hours ago
			if (time() > ($lu_timestamp + (24 * 60 * 60))) {
				$this->_download_data();
			}
		}

		// In case there's a need to add additional currencies into the existing list,
		// this is the place we can do it.
		do_action('p6rgup6der_euro_converter_after_construct', $this);
	}


	/*
	 * Do the conversion magic.
	 * Note: we can convert other currencies as well, not just Euros.
	 * For example: GBP into SEK, USD into GPB, and so on...
	 * (Default conversion: 1.00 EUR into USD)
	 */
	public function convert($amount = 1, $from = 'EUR', $into = 'USD', $decimals = 2) {

		$exit_prematurely = false;

		if ($from == $into) {
			$exit_prematurely = true;
		}

		if ($from != 'EUR' && !isset($this->currency_rates[$from])) {
			$exit_prematurely = true;
		}

		if ($into != 'EUR' && !isset($this->currency_rates[$into])) {
			$exit_prematurely = true;
		}

		if ($exit_prematurely) {
			return number_format($amount, $decimals);
		}

		if ($from == 'EUR') {
			$calculation = $amount * $this->currency_rates[$into];
		} elseif ($into == 'EUR') {
			$calculation = (1 / $this->currency_rates[$from]) * $amount;
		} else {
			$calculation = ($amount / $this->currency_rates[$from]) * $this->currency_rates[$into];
		}

		return number_format($calculation, $decimals);
	}


	public function convert_from_EUR($amount, $currency, $decimals = 2) {
		return $this->convert($amount, 'EUR', $currency, $decimals);
	}


	public function convert_into_EUR($amount, $currency, $decimals = 2) {
		return $this->convert($amount, $currency, 'EUR', $decimals);
	}


	/*
	 * Get exchange rates from remote XML file and store that data locally
	 */
	protected function _download_data() {
		$xml_source = 'http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml';
		$xml_content = @file_get_contents($xml_source);

		if ($xml_content) {
			$regex_pattern = "/<Cube\s*currency='(\w*)'\s*rate='([\d\.]*)'\/>/is";
			preg_match_all($regex_pattern, $xml_content, $rates_from_xml);
			array_shift($rates_from_xml);

			for ($i = 0; $i < count($rates_from_xml[0]); $i++) {
				$this->currency_rates[$rates_from_xml[0][$i]] = $rates_from_xml[1][$i];
			}

			$this->last_update = date("Y-m-d");

			$tmp_data = array(
				'last_updated' => $this->last_update,
				'rates' => $this->currency_rates,
			);

			update_option('p6rgup6der_currency_data', $tmp_data);
		}
	}
}


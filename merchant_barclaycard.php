<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
/*
 * CI-Merchant Library
 *
 * Copyright (c) 2011-2012 Crescendo Multimedia Ltd
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Merchant Barclaycard ePDQ Class
 *
 * Payment processing using Barclaycard ePDQ (external)
 * Documentataion: http://www.barclaycard.co.uk/business/documents/pdfs/cpi_integration_guidev11.0.pdf
 * Author: Martyn James, www.kineticpulse.co.uk
 */

class Merchant_barclaycard extends Merchant_driver {
	const PROCESS_URL = 'https://secure2.epdq.co.uk/cgi-bin/CcxBarclaysEpdq.e';
	const PROCESS_URL_TEST = 'https://secure2.mde.epdq.co.uk/cgi-bin/CcxBarclaysEpdq.e';
	const ENCRYPTION_URL = "https://secure2.epdq.co.uk/cgi-bin/CcxBarclaysEpdqEncTool.e";
	const ENCRYPTION_URL_TEST = "https://secure2.mde.epdq.co.uk/cgi-bin/CcxBarclaysEpdqEncTool.e";

	public function default_settings() {
		return array('test_mode' => TRUE, 'clientid' => '', 'passphrase' => '', 'clientdisplayname' => '', 'chargetype' => 'Auth');
	}

	public function purchase() {

		$epdqdata = $this -> _encrypt();

		$request = array();
		$request['merchantdisplayname'] = $this -> setting('clientdisplayname');
		$request['returnurl'] = $this -> param('return_url');
		//$request['name'] = $this -> param('name'); not supported by Barclaycard due to data privacy
		$request['baddr1'] = $this -> param('address1');
		$request['baddr2'] = $this -> param('address2');
		$request['bcity'] = $this -> param('city');
		$request['bpostalcode'] = $this -> param('postcode');
		$request['bcountry'] = $this -> param('country');
		$request['btelephonenumber'] = $this -> param('phone');
		$request['email'] = $this -> param('email');

		$request['epdqdata'] = $epdqdata;
		$this -> post_redirect($this -> _process_url(), $request);
		//$this->redirect($this->_process_url().'?'.http_build_query($request));


	}

	public function purchase_return() {

		$oid = $this -> CI -> input -> get("oid");

		$this->EE =& get_instance();
		//$this->EE->load->library('firephp');
		//$this->EE->firephp->log($oid, 'CI Post id');
	
		
		$results = $this->EE->db->query("SELECT * FROM exp_store_epdq where transaction_id = '$oid' ");

		
		$transaction_status = $results->row('transaction_status');
		$amount = $results->row('transaction_amount');

		if ($transaction_status == 'DECLINED') {

			return new Merchant_response(Merchant_response::FAILED, 'Sorry - payment was declined. Please contact your card issuer or try another card', $transaction_id, $amount);

		} elseif ($transaction_status == 'Success') {

			return new Merchant_response(Merchant_response::COMPLETE, $transaction_status, $transaction_id, $amount);

		} else {
			return new Merchant_response(Merchant_response::FAILED, $transaction_status, $transaction_id, $amount);

		}

	}

	private function _encryption_url() {
		return $this -> setting('test_mode') ? self::ENCRYPTION_URL_TEST : self::ENCRYPTION_URL;
	}

	private function _process_url() {
		return $this -> setting('test_mode') ? self::PROCESS_URL_TEST : self::PROCESS_URL;
	}

	private function _encrypt() {
		$request = array();
		$request['clientid'] = $this -> setting('clientid');
		$request['password'] = $this -> setting('passphrase');
		$request['chargetype'] = $this -> setting('chargetype');
		$request['oid'] = $this -> param('order_id');
		$request['total'] = $this -> amount_dollars();
		$request['currencycode'] = "826";
		$url = $this -> _encryption_url();
		$response = $this -> post_request($url, $request);
		// Find encryptyed value only from returned <input> field
		$pos = strpos($response, "value=") + 7;
		$len = strlen($response);
		$strEPDQ = substr($response, $pos, $len - $pos - 2);

		return $strEPDQ;
	}



}

/* End of file ./libraries/merchant/drivers/Merchant_barclaycard.php */

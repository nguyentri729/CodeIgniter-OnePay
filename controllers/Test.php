<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller {

	public function index()
	{

		$this->load->library('onepay');
		$this->load->helper('url');
		$this->load->helper('string');
		//ID Thanh toán.


		$order_id = random_string('numeric', 10);
		//Số tiền thanh toán.

		$thanh_tien = 100000;

		$array = array(
			'order_id' => $order_id,  
			'order_info' => 'THANH TOAN DON HANG',
			'total_amount' => $thanh_tien,
			'return_url' => base_url('Test/dr')
		);

		$link_direct =  $this->onepay->build_link($array);

		//Link thanh toán đơn hàng.
		redirect($link_direct);
	}
	public function dr(){
		$this->load->library('onepay');
		$data = $this->onepay->validate($this->input->get());
		
	}
	

}

/* End of file Test.php */
/* Location: ./application/controllers/Test.php */
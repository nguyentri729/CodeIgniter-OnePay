# CodeIgniter-OnePay
OnePay library for CodeIgniter
-----------------------
<h3>THƯ VIỆN TÍCH HỢP CỔNG THANH TOÁN ONEPAY CHO CODEIGNITER.</h3><hr>
* Tài liệu tích hợp thanh toán : https://mtf.onepay.vn/developer/
<h3>Sử dụng.</h3>
- Tạo đường dẫn thanh toán:
<code>

		$this->load->library('onepay');
    
		$this->load->helper('url');
    
		$this->load->helper('string');
	


		$order_id = random_string('numeric', 10);
		

		$thanh_tien = 100000;

		$array = array(
			'order_id' => $order_id,  
			'order_info' => 'THANH TOAN DON HANG',
			'total_amount' => $thanh_tien,
			'return_url' => base_url('Test/dr')
		);

		$link_direct =  $this->onepay->build_link($array);

		
		redirect($link_direct);
</code>


- Kết quả trả về :<br>
+ Một mảng dạng :


<code>


                $kqua = array(
                        'status' => true,
                        'message' => 'Giao dịch thành công',
                        'other_data' => array(
                                'amount' => 'bla..bla',
                                'locale' => 'bla..bla',
                                'command' =>'bla..bla',
                                'version' => 'bla..bla',
                                'orderInfo' =>'bla..bla',
                                'merchantID' =>'bla..bla',
                                'merchTxnRef' => 'bla..bla',
                                'transactionNo' =>'bla..bla'
                        )
                );
</code>


- Kiểm tra kết quả :<br>
<code>
	
	
	
if($kqua['status'] == true){
  //Giao dịch thành công !
}else{
  //Giao dịch thất bại.
}



</code>

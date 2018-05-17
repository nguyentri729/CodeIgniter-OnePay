<?php defined('BASEPATH') OR exit('No direct script access allowed');

class OnePay {

        protected $CI;
        /**
        * Virtual Payment Client URL.
        *
        */
        protected $client_url  = 'https://mtf.onepay.vn/onecomm-pay/vpc.op';

        public function __construct()
        {
                $this->CI =& get_instance();
                $this->CI->load->config('onepay');
                $this->secure = $this->CI->config->item('onepay_secure');
                $this->merchant = $this->CI->config->item('onepay_merchant');
                $this->access = $this->CI->config->item('onepay_access');
        }
        /**
        * Build URL order
        *
        */
        public function build_link($info){

                $vpcURL = $this->client_url.'?';
                $mang = array(
                  'Title' =>  'VPC 3-Party' ,
                  'vpc_Merchant' =>  $this->merchant,
                  'vpc_AccessCode' => $this->access,
                  'vpc_MerchTxnRef' => $info['order_id'],
                  'vpc_OrderInfo' => $info['order_info'],
                  'vpc_Amount' => $info['total_amount']*100,
                  'vpc_ReturnURL' => $info['return_url'],
                  'vpc_Version' => '2' ,
                  'vpc_Command' => 'pay',
                  'vpc_Locale' => 'vn',
                  'vpc_Currency' => 'VND' ,
                  'vpc_TicketNo' => $this->CI->input->ip_address()
                );

                $stringHashData = '';

                ksort ($mang);

                $appendAmp = 0;

                foreach($mang as $key => $value) {

                    
                    if (strlen($value) > 0) {
                       
                        if ($appendAmp == 0) {
                            $vpcURL .= urlencode($key) . '=' . urlencode($value);
                            $appendAmp = 1;
                        } else {
                            $vpcURL .= '&' . urlencode($key) . "=" . urlencode($value);
                        }
                        
                        if ((strlen($value) > 0) && ((substr($key, 0,4)=="vpc_") || (substr($key,0,5) =="user_"))) {
                                    $stringHashData .= $key . "=" . $value . "&";
                        }
                    }
                }
                $stringHashData = rtrim($stringHashData, "&");
                if (strlen($this->secure) > 0) {
                    $vpcURL .= "&vpc_SecureHash=" . strtoupper(hash_hmac('SHA256', $stringHashData, pack('H*',$this->secure)));
                }
                return $vpcURL;
        }
        public function validate($mang)
        {

                $vpc_Txn_Secure_Hash = $mang["vpc_SecureHash"];
                unset ( $mang ["vpc_SecureHash"] );

               
                $errorExists = false;

                ksort ($mang);

                if (strlen ( $this->secure ) > 0 && $mang ["vpc_TxnResponseCode"] != "7" && $mang ["vpc_TxnResponseCode"] != "No Value Returned") {
                        
                    //$stringHashData = $SECURE_SECRET;
                    //*****************************khởi tạo chuỗi mã hóa rỗng*****************************
                    $stringHashData = "";
                        
                        // sort all the incoming vpc response fields and leave out any with no value
                        foreach ( $mang as $key => $value ) {
                //        if ($key != "vpc_SecureHash" or strlen($value) > 0) {
                //            $stringHashData .= $value;
                //        }
                //      *****************************chỉ lấy các tham số bắt đầu bằng "vpc_" hoặc "user_" và khác trống và không phải chuỗi hash code trả về*****************************
                        if ($key != "vpc_SecureHash" && (strlen($value) > 0) && ((substr($key, 0,4)=="vpc_") || (substr($key,0,5) =="user_"))) {
                                    $stringHashData .= $key . "=" . $value . "&";
                                }
                        }
                //  *****************************Xóa dấu & thừa cuối chuỗi dữ liệu*****************************
                    $stringHashData = rtrim($stringHashData, "&");      
                        
                        
                //    if (strtoupper ( $vpc_Txn_Secure_Hash ) == strtoupper ( md5 ( $stringHashData ) )) {
                //    *****************************Thay hàm tạo chuỗi mã hóa*****************************
                        if (strtoupper ( $vpc_Txn_Secure_Hash ) == strtoupper(hash_hmac('SHA256', $stringHashData, pack('H*',$this->secure)))) {
                                // Secure Hash validation succeeded, add a data field to be displayed
                                // later.
                                $hashValidated = "CORRECT";
                        } else {
                                // Secure Hash validation failed, add a data field to be displayed
                                // later.
                                $hashValidated = "INVALID HASH";
                        }
                } else {
                        // Secure Hash was not validated, add a data field to be displayed later.
                        $hashValidated = "INVALID HASH";
                }

                // Define Variables
                // ----------------
                // Extract the available receipt fields from the VPC Response
                // If not present then let the value be equal to 'No Value Returned'
                // Standard Receipt Data
                $amount = $this->null2unknown ( $mang ["vpc_Amount"] );

                $locale = $this->null2unknown ( $mang ["vpc_Locale"] );
                
                $command = $this->null2unknown ( $mang ["vpc_Command"] );
                
                $version = $this->null2unknown ( $mang ["vpc_Version"] );
               
                $orderInfo = $this->null2unknown ( $mang ["vpc_OrderInfo"] );
                
                $merchantID = $this->null2unknown ( $mang ["vpc_Merchant"] );
                
                $merchTxnRef = $this->null2unknown ( $mang ["vpc_MerchTxnRef"] );
                $transactionNo = $this->null2unknown ( $mang ["vpc_TransactionNo"] );
                
                $txnResponseCode = $this->null2unknown ( $mang ["vpc_TxnResponseCode"] );
                if($txnResponseCode == 0 AND $hashValidated =='CORRECT'){
                        $status = true;
                }else{
                        $status = false;
                }
                $kqua = array(
                        'status' => $status,
                        'message' => $this->getResponseDescription($txnResponseCode),
                        'other_data' => array(
                                'amount' => $amount,
                                'locale' => $amount,
                                'command' => $command,
                                'version' => $version,
                                'orderInfo' => $orderInfo,
                                'merchantID' => $merchantID,
                                'merchTxnRef' => $merchTxnRef,
                                'transactionNo' => $transactionNo
                        )
                );
                return $kqua;


        }
        // This method uses the QSI Response code retrieved from the Digital
        // Receipt and returns an appropriate description for the QSI Response Code
        //
        // @param $responseCode String containing the QSI Response Code
        //
        // @return String containing the appropriate description
        //
        public function getResponseDescription($responseCode) {
                
                switch ($responseCode) {
                        case "0" :
                                $result = "Giao dịch thành công - Approved";
                                break;
                        case "1" :
                                $result = "Ngân hàng từ chối giao dịch - Bank Declined";
                                break;
                        case "3" :
                                $result = "Mã đơn vị không tồn tại - Merchant not exist";
                                break;
                        case "4" :
                                $result = "Không đúng access code - Invalid access code";
                                break;
                        case "5" :
                                $result = "Số tiền không hợp lệ - Invalid amount";
                                break;
                        case "6" :
                                $result = "Mã tiền tệ không tồn tại - Invalid currency code";
                                break;
                        case "7" :
                                $result = "Lỗi không xác định - Unspecified Failure ";
                                break;
                        case "8" :
                                $result = "Số thẻ không đúng - Invalid card Number";
                                break;
                        case "9" :
                                $result = "Tên chủ thẻ không đúng - Invalid card name";
                                break;
                        case "10" :
                                $result = "Thẻ hết hạn/Thẻ bị khóa - Expired Card";
                                break;
                        case "11" :
                                $result = "Thẻ chưa đăng ký sử dụng dịch vụ - Card Not Registed Service(internet banking)";
                                break;
                        case "12" :
                                $result = "Ngày phát hành/Hết hạn không đúng - Invalid card date";
                                break;
                        case "13" :
                                $result = "Vượt quá hạn mức thanh toán - Exist Amount";
                                break;
                        case "21" :
                                $result = "Số tiền không đủ để thanh toán - Insufficient fund";
                                break;
                        case "99" :
                                $result = "Người sủ dụng hủy giao dịch - User cancel";
                                break;
                        default :
                                $result = "Giao dịch thất bại - Failured";
                }
                return $result;
        }
        
        //  -----------------------------------------------------------------------------
        // If input is null, returns string "No Value Returned", else returns input
        public function null2unknown($data) {
                if ($data == "") {
                        return "No Value Returned";
                } else {
                        return $data;
                }
        }


}
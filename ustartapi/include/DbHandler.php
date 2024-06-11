<?php
/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author manikanta sarma
 * @link URL Tutorial link
 */
ini_set("allow_url_fopen", 1);

class DbHandler {
private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        require_once dirname(__FILE__) . '/SmsService.php';
        require_once dirname(__FILE__) . '/PasswordHash.php';
        // opening db connection
        date_default_timezone_set('UTC');
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /************function for check is valid api key*******************************/
    function isValidApiKey($token)
    {
        //echo 'SELECT userId FROM registerCustomers WHERE apiToken="'.$token.'"';exit;
        $query ='SELECT userId FROM registerCustomers WHERE apiToken="'.$token.'"';// AND password = $userPass";
        $result = mysqli_query($this->conn, $query);
        $num=mysqli_num_rows($result);
        return $num;
    }

    /************function for check is valid api key*******************************/
    function isValidSessionToken($token,$user_id)
    {
        //echo 'SELECT userId FROM registerCustomers WHERE apiToken="'.$token.'"';exit;
        $query ='SELECT * FROM ohrm_user_token WHERE userid = "'.$user_id.'" and session_token ="'.$token.'"';// AND password = $userPass";
        $result = mysqli_query($this->conn, $query);
		$num=mysqli_num_rows($result);
		return $num;
	}
		/**
     * Generating random Unique MD5 String for user Api key
     */
    function generateApiKey() {
        return md5(uniqid(rand(), true));
    }
	/** Password Encryption Algorithim*/
	function encrypt($str)
	{
		$key='grubvanapp1#20!8';
		$block = mcrypt_get_block_size('rijndael_128', 'ecb');
		$pad = $block - (strlen($str) % $block);
		$str .= str_repeat(chr($pad), $pad);
		$rst = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $str,MCRYPT_MODE_ECB,str_repeat("\0", 16)));
		return str_ireplace('+', '-', $rst);
	}

   /************function for check is valid api key*******************************/
  function getUserId($token)
    {
		$user_id='';
		// $query = "SELECT userId FROM  registerCustomers WHERE apiToken='$token'"; //table
		// $result=mysqli_query($this->conn, $query);
		// if(mysqli_num_rows($result)>0)
		// {
		//    $row = mysqli_fetch_array($result);
		//    $user_id=$row['userId'];
	 //    }
	   return 6;
	}

	function generateSessionToken($user_id)
	{
		$data=array();
		$token=$this->generateApiKey();
		$query = "SELECT * FROM ohrm_user_token WHERE userid = $user_id";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
			$token_userid = $row['userid'];
				if($token_userid == $user_id){
					$updatesql ="UPDATE ohrm_user_token SET session_token='$token' WHERE userid=$user_id";
					if($result2 = mysqli_query($this->conn, $updatesql)){
						$data['session_token'] = $token;
				        $data['status']=1;
					}else{
					    $data['status']=0;
					}
				}else{
					$data['status']=0;
				}
		}
		return $data;
    }
//usersignupfrommobile
  // User SignUp
    function userSignUpfrommobile($userid,$mobile,$username,$email)
    {
        $data=array();
        $data['id'] = $userid;
    		$data['username'] = $username;
			$data['mobile'] = $mobile;
			$data['email'] = $email;
            $otp = rand(100000, 999999);
			$data['otp'] = $otp;
			$u= 'USTART2020';
            $string = $u.$userid;

			// Prepare an insert statement
		$sql = "update users set username = '$username',email = '$email',otp = '$otp',refid = '$string' where id ='$userid'";
		$res = $this->conn->query($sql);
		$un="rx100";
$pw="Raju@sms";
$sid="RXGAME";

$url1 = 'http://smslogin.mobi/spanelv2/api.php?username='.$un.'&password='.$pw.'&to='.$mobile.'&from='.$sid.'&message='.$otp;

$ret = file($url1);

		if($res){
		$data['userDetails'] = $data;
		$data['status']=1;
		}else{
			$data['status']=0;
		}
	return $data;
    }

function getotpfrommobile($mobile)
    {
        $data=array();


			$sql1 = "SELECT * FROM users where mobile='$mobile' ";
		$res1 = $this->conn->query($sql1);
		$check_row1 = $res1->fetch_array();
		if($check_row1){
			  $data['mobile'] = $mobile;
			  $data['id'] = $check_row1['id'];
			$otp = rand(100000, 999999);
			$data['otp'] = $otp;
			$sql1 = "UPDATE users SET otp='$otp' WHERE mobile = '$mobile' ";
			 $res1 = $this->conn->query($sql1);   //Message Here
			$un="rx100";
			$pw="Raju@sms";
			$sid="RXGAME";

			$url1 = 'http://smslogin.mobi/spanelv2/api.php?username='.$un.'&password='.$pw.'&to='.$mobile.'&from='.$sid.'&message='.$otp;

			$ret = file($url1);
			$data['userDetails'] = $data;
				$data['status']=1;


			}else{
				$sql2 = "INSERT INTO users (mobile) VALUES('$mobile')";
				$res2 = $this->conn->query($sql2);
				$sql3 = "SELECT * FROM users where mobile='$mobile'";
				$res3 = $this->conn->query($sql3);

				$check_row3 = $res3->fetch_array();
				if($check_row3){
 				$data['id'] = $check_row3['id'];
 				$data['mobile'] = $check_row3['mobile'];
                 $data['otp'] = 0;
 				$data['userDetails'] = $data;
				$data['status']=1;
 				}else{
						$data['status']=0;
					}
				}

	return $data;

}

    //updateProfile

     function updateProfile($userid,$username,$adhar,$pan,$gender,$dob){

    	$data = array();

			$data['id'] = $userid;
			$data['username'] = $username;
			$data['adhar'] = $adhar;
			$data['pan'] = $pan;
			$data['gender'] = $gender;
			$data['dob'] = $dob;

			$sql1 = "UPDATE users SET username='$username',adhar='$adhar',pan='$pan',gender='$gender',dob='$dob' WHERE id = '$userid'";
			 $res1 = $this->conn->query($sql1);   //Message Here

			if($res1){
		$data['userDetails'] = $data;
		$data['status']=1;
		}else{
			$data['status']=0;
		}
	return $data;
    }


    // Login Services
    function userLogin($username,$password){
    	$pass = md5($password);
    	$data = array();
    	$sql = "SELECT * FROM users where username = '$username' AND password = '$pass'" ;
		$res = $this->conn->query($sql);
		$check_row = $res->fetch_array();

		if($check_row){
			$data['username'] = $check_row['username'];
			$data['password'] = $check_row['password'];
			$data['mobile'] = $check_row['mobile'];


			$mobile = $check_row['mobile'];
			$data['userId'] = $check_row['id'];
			$otp = rand(100000, 999999);
			$data['otp'] = $otp;
			$sql1 = "UPDATE users SET otp='$otp' WHERE username = '$username' AND password = '$pass' ";
			 $res1 = $this->conn->query($sql1);   //Message Here
$un="rx100";
$pw="Raju@sms";
$sid="RXGAME";

$url1 = 'http://smslogin.mobi/spanelv2/api.php?username='.$un.'&password='.$pw.'&to='.$mobile.'&from='.$sid.'&message='.$otp;

$ret = file($url1);
			$data['userDetails'] = $data;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;
    }

// Login through gmail
    function loginGmail($email){

    	$data = array();
    	$sql = "SELECT * FROM users where email = '$email'" ;
		$res = $this->conn->query($sql);
		$check_row = $res->fetch_array();

		if($check_row){
			$data['email'] = $check_row['email'];
			$data['mobile'] = $check_row['mobile'];


			$mobile = $check_row['mobile'];
			$data['userId'] = $check_row['id'];
			$otp = rand(100000, 999999);
			$data['otp'] = $otp;
			$sql1 = "UPDATE users SET otp='$otp' WHERE email = '$email'";
			 $res1 = $this->conn->query($sql1);   //Message Here
$un="rx100";
$pw="Raju@sms";
$sid="RXGAME";

$url1 = 'http://smslogin.mobi/spanelv2/api.php?username='.$un.'&password='.$pw.'&to='.$mobile.'&from='.$sid.'&message='.$otp;

$res = file($url1);
			$data['userDetails'] = $data;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;
    }

    //disbtobank

    function disbToBank($cust_id,$checksumhash,$status,$sgid,$mid,$ben_account,$ben_ifsc,$ben_name,$orderid,$amount,$statuscode,$paymentmode,$statusmessage)
    {
        $data=array();
        $data['cust_id'] = $cust_id;
			$data['checksumhash'] = $checksumhash;
			$data['status'] = $status;
			$data['sgid'] = $sgid;
			$data['mid'] = $mid;
			$data['ben_account'] = $ben_account;
			$data['ben_ifsc'] = $ben_ifsc;
			$data['ben_name'] = $ben_name;
			$data['orderid'] = $orderid;

			$data['amount'] = $amount;
			$data['statuscode'] = $statuscode;
			$data['paymentmode'] = $paymentmode;
			$data['statusmessage'] = $statusmessage;



			// Prepare an insert statement
	$sql = "INSERT INTO disbtobank_initiate(cust_id,checksumhash,status,sgid,mid,ben_account,ben_ifsc,ben_name,orderid,amount,statuscode,paymentmode,statusmessage) VALUES ('$cust_id','$checksumhash','$status','$sgid','$mid','$ben_account','$ben_ifsc','$ben_name','$orderid','$amount','$statuscode','$paymentmode','$statusmessage') ";
		$res = $this->conn->query($sql);

		if($res){
		$data['userDetails'] = $data;
		$data['status']=1;
		}else{
			$data['status']=0;
			$data['userDetails'] = $data;
		}
	return $data;
    }

//getprofielother

     function getprofileother($userid){

    	$data = array();



			$sql1 = "SELECT username,adhar,pan,gender,dob from users WHERE id = '$userid'";
		        $res= $this->conn->query($sql1);   //Message Here
                $check_row = $res->fetch_array();

			if($check_row){
				$data['username'] = $check_row['username'];
			$data['adhar'] = $check_row['adhar'];
			$data['pan'] = $check_row['pan'];
			$data['dob'] = $check_row['dob'];
			$data['gender'] = $check_row['gender'];
		$data['userDetails'] = $data;
		$data['status']=1;
		}else{
			$data['status']=0;
		}
	return $data;
    }


    //addMoneyptm
			  function addMoneyptm($cust_id,$status, $checksum,$bankname,$orderid,$txnamount,$txndate,$mid,$txnid,$response_code,$payment_mode,$bank_transaction_id,$currency,$gateway_name,$resp_msg)
    {
        $data=array();

      $data['CUST_ID'] = $cust_id;
      $data['STATUS'] = $status;
      $data['CHECKSUMHASH'] = $checksum;
       $data['BANKNAME'] = $bankname;
       $data['ORDERID'] = $orderid;
        $data['TXNAMOUNT'] = $txnamount;
         $data['TXNDATE'] = $txndate;
          $data['MID'] = $mid;
           $data['TXNID'] = $txnid;
            $data['RESPCODE'] = $response_code;
             $data['PAYMENTMODE'] = $payment_mode;
              $data['BANKTXNID'] = $bank_transaction_id;
               $data['CURRENCY'] = $currency;
                $data['GATEWAYNAME'] = $gateway_name;
                 $data['RESPMSG'] = $resp_msg;

			// Prepare an insert statement
	$sql = "INSERT INTO addmoney_ptm (cust_id, status, checksumhash, bankname, orderid, txnamount, txndate, mid, txnid, respcode, paymentmode, banktxnid, currency, gatewayname, respmsg) VALUES ('$cust_id','$status', '$checksum','$bankname','$orderid','$txnamount','$txndate','$mid','$txnid','$response_code','$payment_mode','$bank_transaction_id','$currency','$gateway_name','$resp_msg') ";
		$res = $this->conn->query($sql);

		if($res){
		$data['userDetails'] = $data;
		$data['status']=1;
		}else{
			$data['status']=0;
		}
	return $data;
    }

    //getmessage
    function getmessage($userid){

        $data = array();



			$sql1 = "SELECT message from  message ORDER BY id DESC LIMIT 1";
		        $res= $this->conn->query($sql1);   //Message Here
                $check_row = $res->fetch_array();

			if($check_row){
				$data['message'] = $check_row['message'];

		$data['userDetails'] = $data;
		$data['status']=1;
		}else{
			$data['status']=0;
		}
	return $data;
    }


// Login through facebook
    function loginFacebook($facebook){

    	$data = array();
    	$sql = "SELECT * FROM users where facebook = '$facebook'" ;
		$res = $this->conn->query($sql);
		$check_row = $res->fetch_array();

		if($check_row){
			$data['facebook'] = $check_row['facebook'];
			$data['mobile'] = $check_row['mobile'];


			$mobile = $check_row['mobile'];
			$data['userId'] = $check_row['id'];
			$otp = rand(100000, 999999);
			$data['otp'] = $otp;
			$sql1 = "UPDATE users SET otp='$otp' WHERE facebook = '$facebook'";
			 $res1 = $this->conn->query($sql1);   //Message Here
$un="rx100";
$pw="Raju@sms";
$sid="RXGAME";


$url1 = 'http://smslogin.mobi/spanelv2/api.php?username='.$un.'&password='.$pw.'&to='.$mobile.'&from='.$sid.'&message='.$otp;

$ret = file($url1);
			$data['userDetails'] = $data;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;
    }



    //GettingProfile

    function getprofiledb($userid){

    	$data = array();
    	$sql = "SELECT * FROM users where id = '$userid' ";
		$res = $this->conn->query($sql);
		$check_row = $res->fetch_array();

		if($check_row){
			$data['username'] = $check_row['username'];
			$data['mobile'] = $check_row['mobile'];
           $data['email'] = $check_row['email'];
            $data['refid'] = $check_row['refid'];

			$data['userDetails'] = $data;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;
    }

    //getBalance
       function getbalancedb($userid){

    	$data = array();
    	$sql = "SELECT * FROM game_wallet_balance where cust_id='$userid' ";
		$res = $this->conn->query($sql);
		$check_row = $res->fetch_array();

		if($check_row){
			$data['g_balance'] = $check_row['g_balance'];
			$data['w_balance'] = $check_row['w_balance'];
			$data['rf_balance'] = $check_row['rf_balance'];
            $data['rating'] = $check_row['rating'];



			$data['userDetails'] = $data;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;
    }

    //getresultdb
       function getresultdb($userid){

    	$data = array();
    	$sql = "SELECT * FROM game_result";
		$res = $this->conn->query($sql);
		$check_row = $res->fetch_array();
		$sql1 = "SELECT * FROM game_wallet_balance where cust_id='$userid'";
		$res1 = $this->conn->query($sql1);
		$check_row1 = $res1->fetch_array();

if($check_row1){
			$data['g_balance'] = $check_row1['g_balance'];
			$data['w_balance'] = $check_row1['w_balance'];

}
		if($check_row){
			$data['gameid'] = $check_row['gameid'];
			$data['resultcard'] = $check_row['resultcard'];


			$data['userDetails'] = $data;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;
    }
//Resend otp
     function resendOtp($userid,$mobile){

    	$data = array();



			$otp = rand(100000, 999999);
			$data['otp'] = $otp;
			$data['id'] = $userid;
			$data['mobile'] = $mobile;
			$sql1 = "UPDATE users SET otp='$otp' WHERE id = '$userid' AND mobile = '$mobile' ";
			 $res1 = $this->conn->query($sql1);   //Message Here
$un="rx100";
$pw="Raju@sms";
$sid="RXGAME";

$url1 = 'http://smslogin.mobi/spanelv2/api.php?username='.$un.'&password='.$pw.'&to='.$mobile.'&from='.$sid.'&message='.$otp;

$ret = file($url1);
			$data['userDetails'] = $data;
			$data['status']=1;

		return $data;
    }

    //forgot password

     function forgotPassword($userid){

    	$data = array();

			$data['id'] = $userid;

			$sql =  "SELECT mobile FROM users where id='$userid' ";
			 $res = $this->conn->query($sql);   //Message Here
           $check_row = $res->fetch_array();


			 if($check_row){
			$data['mobile'] = $check_row['mobile'];



			$data['userDetails'] = $data;
			$data['status']=1;
		}else{
			$data['status']=0;
		}

		return $data;
    }

    //Reset Password
     function resetPwd($userid,$password){

    	$data = array();

			$data['id'] = $userid;
			$data['password'] = $password;
			$sql1 = "UPDATE users SET password='$password' WHERE id = '$userid' ";
			 $res1 = $this->conn->query($sql1);   //Message Here

			if($res){
		$data['userDetails'] = $data;
		$data['status']=1;
		}else{
			$data['status']=0;
		}
	return $data;
    }


    // User SignUp
    function userSignUp($username,$password,$mobile,$email)
    {
        $data=array();
        $data['username'] = $username;
			$data['password'] = $password;
			$data['mobile'] = $mobile;
			$data['email'] = $email;
            $otp = rand(100000, 999999);
			$data['otp'] = $otp;

			// Prepare an insert statement
		$sql = "INSERT INTO users (username,password,mobile,email,otp) VALUES ('$username','$password','$mobile','$email','$otp')";
		$res = $this->conn->query($sql);
		$un="rx100";
$pw="Raju@sms";
$sid="RXGAME";

$url1 = 'http://smslogin.mobi/spanelv2/api.php?username='.$un.'&password='.$pw.'&to='.$mobile.'&from='.$sid.'&message='.$otp;

$ret = file($url1);

		if($res){
		$data['userDetails'] = $data;
		$data['status']=1;
		}else{
			$data['status']=0;
		}
	return $data;
    }


    //getstartTime
     function timeStarts($gameid){

    	$data = array();

    	$sql = "SELECT * FROM game_result";
		$res = $this->conn->query($sql);

	$check_row = $res->fetch_array();
		if($check_row){

			$data['game_status'] = $check_row['game_status'];
			$data['game_start_time'] = $check_row['game_start_time'];
			$data['gameid'] = $check_row['gameid'];
			$data['userDetails'] = $data;
				$data['status']=1;
		}else{
			$data['status']=0;
		}
	return $data;
    }
    //bidding insert
    function bidInstert($gameid,$custid,$card,$amount)
    {
        $data=array();
        $data['gameid'] = $gameid;
			$data['cust_id'] = $custid;
			$data['card'] = $card;
			$data['amount'] = $amount;
			$sql1 = "SELECT * FROM game_wallet_balance where cust_id='$custid'";
		$res1 = $this->conn->query($sql1);
		$check_row1 = $res1->fetch_array();
if($check_row1){
			$data['g_balance'] = $check_row1['g_balance'];

			if($check_row1['w_balance'] >= $amount)

			{
					$newwallet =  $check_row1['w_balance'] - $amount;
					$sql2 = "UPDATE game_wallet_balance SET w_balance='$newwallet' WHERE cust_id='$custid'  ";
					$res2 = $this->conn->query($sql2);
					$sql = "INSERT INTO bidding_log (gameid,cust_id,card,amount) VALUES ('$gameid','$custid','$card','$amount')";
			$res = $this->conn->query($sql);
			$data['w_balance'] = $newwallet;

			if($res){
			$data['userDetails'] = $data;
				$data['status']=1;
			}

			}
			else{
			$data['status']=0;
		}
	return $data;
    }


			}


    //ADDMONEY

    function addMoney($cust_id,$status, $checksum,$bankname,$orderid,$txnamount,$txndate,$mid,$txnid,$response_code,$payment_mode,$bank_transaction_id,$currency,$gateway_name,$resp_msg)
    {
        $data=array();
      $data['cust_id'] = $cust_id;
      $data['status'] = $status;
      $data['checksum'] = $checksum;
       $data['bankname'] = $bankname;
       $data['orderid'] = $orderid;
        $data['txnamount'] = $txnamount;
         $data['txndate'] = $txndate;
          $data['mid'] = $mid;
           $data['txnid'] = $txnid;
            $data['response_code'] = $response_code;
             $data['payment_mode'] = $payment_mode;
              $data['bank_transaction_id'] = $bank_transaction_id;
               $data['currency'] = $currency;
                $data['gateway_name'] = $gateway_name;
                 $data['resp_msg'] = $resp_msg;

			// Prepare an insert statement
	$sql = "INSERT INTO addmoney_txn (cust_id,status,checksum,bankname,orderid,txnamount,txndate,mid,txnid,response_code,payment_mode,bank_transaction_id,currency,gateway_name,resp_msg) VALUES ($cust_id,'$status', '$checksum','$bankname','$orderid','$txnamount','$txndate','$mid','$txnid','$response_code','$payment_mode','$bank_transaction_id','$currency','$gateway_name','$resp_msg') ";
		$res = $this->conn->query($sql);

		if($res){
		$data['userDetails'] = $data;
		$data['status']=1;
		}else{
			$data['status']=0;
		}
	return $data;
    }
    //wallet to game money
     function gmtow($custid,$amount)
    {
        $data=array();

			$data['cust_id'] = $custid;

			$data['amount'] = $amount;
$sql1 = "SELECT * FROM game_wallet_balance where cust_id='$custid'";

		$res1 = $this->conn->query($sql1);
		$check_row1 = $res1->fetch_array();
if($check_row1){
			$data['w_balance'] = $check_row1['w_balance'];

			if($check_row1['g_balance'] >= $amount)

			{
					$newwallet =  $check_row1['g_balance'] - $amount;
					$newwallet2 = $check_row1['w_balance'] + $amount;
					$sql2 = "UPDATE game_wallet_balance SET g_balance='$newwallet', w_balance='$newwallet2' WHERE cust_id='$custid'  ";
					$res2 = $this->conn->query($sql2);

			$data['g_balance'] = $newwallet;

$data['w_balance'] = $newwallet2;


			$data['userDetails'] = $data;
				$data['status']=1;


			}
			else{
			$data['status']=0;
		}
	return $data;
    }


			}

}
?>

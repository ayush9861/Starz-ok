<?php
header('content-type:text/html;charset=utf-8');
require_once '../include/DbHandler.php';
require_once '../include/EmailService.php';
require_once '../include/SmsService.php';
require '.././libs/Slim/Slim.php';


// \Stripe\Stripe::setApiKey($stripe['secret_key']);

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// User id from db - Global Variable
$user_id = NULL;
$session_token= NULL;
/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
    // Verifying Authorization Header
    if (isset($headers['Authorization']))
    {
        $db = new DbHandler();
        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key

    if (!$db->isValidApiKey($api_key))
    {
            $response["status"] ="error";
            $response["message"] = "Access Denied";
            //$response["message"] = "Access Denied. Invalid Api key";
            echoRespnse(401, $response);
            $app->stop();
        }
        else
        {
            global $user_id;
            //get user primary key id
           $user_id = $db->getUserId($api_key);

        }
    }
    else
    {
        // api key is missing in header
        $response["status"] ="error";
        //$response["message"] = "Api key is misssing";
        $response["message"] = "Access Denied";
        echoRespnse(401, $response);
        $app->stop();
    }
}


function accessToken($user_id) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
    // Verifying Authorization Header
    if (isset($headers['sessiontoken']))
    {
        $db = new DbHandler();
        // get the api key
        $api_key = $headers['sessiontoken'];
        // validating api key
        if (!$db->isValidSessionToken($api_key,$user_id))
        {
            $response["status"] ="error";
            $response["message"] = "Token Expired";
            //$response["message"] = "Access Denied. Invalid Api key";
            echoRespnse(401, $response);
            $app->stop();
        }
    }
    else
    {
        // api key is missing in header
        $response["status"] ="error";
        //$response["message"] = "Api key is misssing";
        $response["message"] = "sessiontoken key is missing";
        echoRespnse(401, $response);
        $app->stop();
    }
}

/*** Indian Date Time Generation ***/
  function getCurrentDateTime(){
    $datetime = date('Y-m-d H:i:s');
    $given = new DateTime($datetime, new DateTimeZone("UTC"));
    $given->setTimezone(new DateTimeZone("asia/kolkata"));
    $output = $given->format("Y-m-d H:i:s");
    return $output;
  }

function authenticatedefault(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
    $APPKEY = "b8416f2680eb194d61b33f9909f94b9d";
    // Verifying Authorization Header
   //print_r($headers);exit;
    if (isset($headers['Authorization']) || isset($headers['authorization']) || isset($headers['Naacodenaistam']))
    {
    if(isset($headers['authorization']))
    {
      $headers['Authorization']=$headers['authorization'];
    }
    if(isset($headers['Naacodenaistam']))
    {
      $headers['Authorization']=$headers['Naacodenaistam'];
    }


        $db = new DbHandler();

        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key

        if($api_key != $APPKEY)
        {
      $response["status"] ="error";
            $response["message"] = "Access Denied";
            echoRespnse(401, $response);
            $app->stop();
    }
       else
        {
            global $user_id;
            // get user primary key id
          //$user_id = $db->getUserId($api_key);

        }
    }
    else
    {
        // api key is missing in header
        $response["status"] ="error";
        //$response["message"] = "Api key is misssing";
        $response["message"] = "Access Denied";
        echoRespnse(401, $response);
        $app->stop();
    }
}

///////////////////////////////////////
/**
 * User Login
 * url - /login
 * method - POST
 * params - username, password,deviceId,pushId,latitude,longitude,platform , 'authenticatedefault'
 */
$app->post('/bidinsert', 'authenticatedefault', function() use ($app)
{

            // reading post params

    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $gameid  = $data['gameid'];
    $custid = $data['custid'];
      $card = $data['card'];
        $amount = $data['amount'];
    $response = array();
    $db = new DbHandler();
    $result=$db->bidInstert($gameid,$custid,$card,$amount);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "bid inserted successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'please add money to wallet';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });
 //update profile
$app->post('/updateProfile', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $userid  = $data['id'];
    $username = $data['username'];
     $adhar = $data['adhar'];
      $pan = $data['pan'];
       $gender = $data['gender'];
         $dob = $data['dob'];



    $response = array();
    $db = new DbHandler();
    $result=$db->updateProfile($userid,$username,$adhar,$pan,$gender,$dob);

     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "data inserted successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'qyery not succussfull';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });


 //getprofileother
$app->post('/getprofileother', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);


    $userid  = $data['id'];



    $response = array();
    $db = new DbHandler();
    $result=$db->getprofileother($userid);

     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "data fetched successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'qyery not succussfull';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });

 //getmessage

 $app->post('/getmessage', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);


    $userid  = $data['this'];



    $response = array();
    $db = new DbHandler();
    $result=$db->getmessage($userid);

     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['result'] = "data fetched successfully";
           $response["message"]=$result['message'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'qyery not succussfull';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });
 //disbtobank
$app->post('/disbToBank', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    // $result = implode(',',$data);

    // $platform   = $data['platform'];
    $cust_id  = $data['cust_id'];
    $checksumhash = $data['checksumhash'];
    $status = $data['status'];
    $sgid  = $data['sgid'];
    $mid  = $data['mid'];
    $ben_account  = $data['ben_account'];
   $ben_name  = $data['ben_name'];

    $ben_ifsc = $data['ben_ifsc'];

    $orderid  = $data['orderid'];

    $amount  = $data['amount'];
    $statuscode  = $data['statuscode'];
    $paymentmode  = $data['paymentmode'];
    $statusmessage  = $data['statusmessage'];


    $response = array();
    $db = new DbHandler();
    $result=$db->disbToBank($cust_id,$checksumhash,$status,$sgid,$mid,$ben_account,$ben_ifsc,$ben_name,$orderid,$amount,$statuscode,$paymentmode,$statusmessage);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "Inserted successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'not inserted sucuussfully transaction failed';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });
//addmoney_ptm
$app->post('/addmoneyptm', 'authenticatedefault', function() use ($app)
{

            // reading post params

    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];

    $cust_id  = $data['CUST_ID'];
    $status  = $data['STATUS'];
    $checksumhash  = $data['CHECKSUMHASH'];
    $bankname  = $data['BANKNAME'];
    $orderid  = $data['ORDERID'];
    $txnamount  = $data['TXNAMOUNT'];
    $txndate  = $data['TXNDATE'];

    $mid  = $data['MID'];
    $txnid  = $data['TXNID'];
    $response_code  = $data['RESPCODE'];
    $payment_mode  = $data['PAYMENTMODE'];
    $bank_transaction_id  = $data['BANKTXNID'];

    $currency  = $data['CURRENCY'];
    $gateway_name  = $data['GATEWAYNAME'];
    $resp_msg  = $data['RESPMSG'];


    $response = array();
    $db = new DbHandler();
    $result=$db->addMoneyptm($cust_id,$status, $checksumhash,$bankname,$orderid,$txnamount,$txndate,$mid,$txnid,$response_code,$payment_mode,$bank_transaction_id,$currency,$gateway_name,$resp_msg);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "bid inserted successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'not submitted';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });
 //usersignupfrommobile
 $app->post('/usersignupfrommobile', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    // $result = implode(',',$data);

    // $platform   = $data['platform'];
    $username  = $data['username'];
    $userid = $data['id'];


    $email  = $data['email'];
    $mobile = $data['mobile'];

    $response = array();
    $db = new DbHandler();
    $result=$db->userSignUpfrommobile($userid,$mobile,$username,$email);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "Inserted successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'Invalid User';
           $response['message'] = 'Number already existed';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });

//getotpfrommobile
$app->post('/getotpfrommobile', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];

    $mobile = $data['mobile'];

    $response = array();
    $db = new DbHandler();
    $result=$db->getotpfrommobile($mobile);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "Logged in successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'Incorrect Passcode';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });



//addmoney_txn
$app->post('/addmoney', 'authenticatedefault', function() use ($app)
{

            // reading post params

    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $cust_id  = $data['cust_id'];
    $status  = $data['status'];
    $checksum  = $data['checksum'];
    $bankname  = $data['bankname'];
    $orderid  = $data['orderid'];
    $txnamount  = $data['txnamount'];
    $txndate  = $data['txndate'];

    $mid  = $data['mid'];
    $txnid  = $data['txnid'];
    $response_code  = $data['response_code'];
    $payment_mode  = $data['payment_mode'];
    $bank_transaction_id  = $data['bank_transaction_id'];

    $currency  = $data['currency'];
    $gateway_name  = $data['gateway_name'];
    $resp_msg  = $data['resp_msg'];


    $response = array();
    $db = new DbHandler();
    $result=$db->addMoney($cust_id,$status, $checksum,$bankname,$orderid,$txnamount,$txndate,$mid,$txnid,$response_code,$payment_mode,$bank_transaction_id,$currency,$gateway_name,$resp_msg);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "bid inserted successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'not submitted';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });
//instanceurl
$app->post('/instanceurl', 'authenticatedefault', function() use ($app)
{

            // reading post params

            $platform= $app->request()->post('platform');//1-Android ,2-IOS
            $url = $app->request()->post('url');

            // check for required params
            verifyRequiredParams(array('url','platform'));
            $req = $app->request;
            $base_url = $req->getUrl()."".$req->getRootUri()."/";
            $response = array();

           if ($base_url == $url)
           {

                 $response["status"] =1;
                 $response['message'] = "Success";
            }
            else
            {
                $response['status'] =0;
                $response['message'] = 'Entered instance url is invalid';

            }

            echoRespnse(200, $response);
 });


$app->post('/generate/sessiontoken', 'authenticatedefault', function() use ($app)
{
             $json = $app->request->getBody();
            $data = json_decode($json, true);
            // reading post params
            $user_id = $data['user_id'];

            // check for required params
            // verifyRequiredParams(array('user_id','platform'));

            $response = array();
            $db = new DbHandler();
            $result=$db->generateSessionToken($user_id);
           if ($result['status']==1)
           {
                 $response["status"] =1;
                 $response['message'] = "Session Token generated in successfully";
                 $response["session_token"]=$result['session_token'];
            }
            else
            {
                $response['status'] =0;
                $response['message'] = 'Session Token generation failed';
                $response["session_token"]=array();
            }

            echoRespnse(200, $response);
 });


$app->post('/login', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $username = $data['username'];
    $password = $data['password'];


    $response = array();
    $db = new DbHandler();
    $result=$db->userLogin($username,$password);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "Logged in successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'Incorrect Passcode';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });

//login through gmail

$app->post('/loginGmail', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];


     $email = $data['email'];
     $response = array();
    $db = new DbHandler();
    $result=$db->loginGmail($email);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "Logged in successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'Incorrect mail';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });

//login through facebook

$app->post('/loginFacebook', 'authenticatedefault', function() use ($app)
{




     $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $facebook = $data['facebook'];

    $response = array();
    $db = new DbHandler();
    $result=$db->loginFacebook($facebook);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "Logged in successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'Incorrect address';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });




//resend otp post method
$app->post('/resendOTP', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $userid  = $data['id'];
    $mobile = $data['mobile'];

    $response = array();
    $db = new DbHandler();
    $result=$db->resendOtp($userid,$mobile);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "Logged in successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'Incorrect Passcode';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });


//Getting profile

$app->post('/getprofile', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $userid  = $data['id'];

    $response = array();
    $db = new DbHandler();
    $result=$db->getprofiledb($userid);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "Logged in successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'Incorrect Passcode';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });


//getBalance

$app->post('/getBalance', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $userid  = $data['id'];

    $response = array();
    $db = new DbHandler();
    $result=$db->getbalancedb($userid);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "Logged in successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'Incorrect Passcode';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });
//gameresult
$app->post('/gameResult', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $userid  = $data['id'];

    $response = array();
    $db = new DbHandler();
    $result=$db->getresultdb($userid);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "results extracted sucuussfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'Incorrect userid';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });
//forgot password


$app->post('/forgotPassword', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $userid  = $data['id'];

    $response = array();
    $db = new DbHandler();
    $result=$db->forgotPassword($userid);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "results extracted sucessfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'Invalid userid';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });

//reset password

$app->post('/resetPassword', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
   $userid  = $data['id'];

    $response = array();
    $db = new DbHandler();
    $result=$db->resetPwd($userid,$password);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "results extracted sucessfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'Incorrect userid';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });


//user sign up
$app->post('/userSignUp', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    // $result = implode(',',$data);

    // $platform   = $data['platform'];
    $username  = $data['username'];
    $password = md5($data['password']);

    $mobile  = $data['mobile'];
    $email  = $data['username'];

    $response = array();
    $db = new DbHandler();
    $result=$db->userSignUp($username,$password,$mobile,$email);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "Inserted successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'Invalid User';
           $response['message'] = 'Number already existed';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });
//wallet to game Money
$app->post('/gmtow', 'authenticatedefault', function() use ($app)
{

            // reading post params

    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
      $custid = $data['custid'];
      $amount = $data['amount'];
    $response = array();
    $db = new DbHandler();
    $result=$db->gmtow($custid,$amount);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "transferred amount successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'transaction failed';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });
///////////////////////////////////////////////////
/**
 * Verifying required params posted or not
 */


//getstartTime

$app->post('/startTime', 'authenticatedefault', function() use ($app)
{

            // reading post params

    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
   $gameid = $data['gameid'];
   $response = array();
    $db = new DbHandler();
    $result=$db->timeStarts($gameid);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = 'Timestarts';
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'time has stopped';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });

function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT req


    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
//print_r($error);
//exit;
    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        //$response["error"] = true;
        $response["status"] =0;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(200, $response);
        $app->stop();
    }
}

/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoRespnse(200, $response);
        $app->stop();
    }
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}
$app->run();
?>

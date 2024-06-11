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
       // if("b8416f2680eb194d61b33f9909f94b9d" != $APPKEY)
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

////////////////////////////////////////////////////// Kavitha Start //////////////////////////////////////////////////////////////////////////
//studentlogin
$app->post('/studentlogin', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $email = $data['email'];
    $password = $data['password'];


    $response = array();
    $db = new DbHandler();
    $result=$db->studentLogin($email,$password);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = " student Logged in successfully";
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

 //adminlogin
$app->post('/adminlogin', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $email = $data['email'];
    $password = $data['password'];


    $response = array();
    $db = new DbHandler();
    $result=$db->adminLogin($email,$password);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = " admin Logged in successfully";
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

  //informationprovider login
$app->post('/iplogin', 'authenticatedefault', function() use ($app)
{

   
    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $email = $data['email'];
    $password = $data['password'];


    $response = array();
    $db = new DbHandler();
    $result=$db->ipLogin($email,$password);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = " information provider  Logged in successfully";
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



 //Getting transportation

 $app->get('/getAllTravelInfo', 'authenticatedefault', function() use ($app) {
    // Initialize response array
    $response = array();
    $db = new DbHandler();

    // Call the getAllTravelInfo function to fetch all travel information
    $allTravelInfo = $db->getAllTravelInfo();

    // Check if any travel information was found
    if ($allTravelInfo) {
        $response["status"] = 1;
        $response["message"] = "All travel information found";
        $response["travel_info"] = $allTravelInfo;
    } else {
        $response["status"] = 0;
        $response["message"] = "No travel information found";
        $response["travel_info"] = array();
    }

    // Send the response
    echoRespnse(200, $response);
});
 //update travel 
 $app->post('/updateTravel', 'authenticatedefault', function() use ($app) {
    // Get the JSON data from the request body
    $json = $app->request->getBody();
    $data = json_decode($json, true);
    
    // Extract data from the JSON
    $travel_id = $data['travel_id'];
    $country = $data['country'];
    $transportation_options = $data['transportation_options'];
    $travel_advisories = $data['travel_advisories'];
    $travel_tips = $data['travel_tips'];

    // Initialize response array
    $response = array();
    $db = new DbHandler();

    // Call the updateTravel function
    $result = $db->updateTravel($travel_id, $country, $transportation_options, $travel_advisories, $travel_tips);

    // Check if update was successful
    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Data updated successfully";
        $response["userDetails"] = $result['userDetails'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Query not successful';
        $response["userDetails"] = array();
    }

    // Send the response
    echoRespnse(200, $response);
});
//getAllHealthcareServices
$app->get('/getAllHealthcareServices', function() use ($app) {
    $response = array();
    $db = new DbHandler();
    $result = $db->getAllHealthcareServices();

    if ($result) {
        $response["status"] = 1;
        $response["data"] = $result;
    } else {
        $response["status"] = 0;
        $response["message"] = "No data found";
    }

    echo json_encode($response);
});
//update HealthcareService
$app->post('/updateHealthcareService', function() use ($app) {
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    $service_id = $data['service_id'];
    $country = $data['country'];
    $medical_facility = $data['medical_facility'];
    $health_insurance_requirements = $data['health_insurance_requirements'];
    $emergency_contact = $data['emergency_contact'];
    $emergency_number = $data['emergency_number'];
    $additional_notes = $data['additional_notes'];

    $response = array();
    $db = new DbHandler();
    $result = $db->updateHealthcareService($service_id, $country, $medical_facility, $health_insurance_requirements, $emergency_contact, $emergency_number, $additional_notes);

    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Data updated successfully";
        $response["serviceDetails"] = $result['serviceDetails'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Update not successful';
        $response["serviceDetails"] = array();
    }

    echo json_encode($response);
});
//universities  filtering 
$app->post('/universities/filter/location', function() use ($app) {
    $response = array();
    $request = json_decode($app->request()->getBody());

    $userID = $request->userID;
    $country = $request->country;
    $city = $request->city;

    $db = new DbHandler();
    $result = $db->filterUniversitiesByLocation($country, $city);

    if ($result) {
        $response["status"] = 1;
        $response["data"] = $result;
    } else {
        $response["status"] = 0;
        $response["message"] = "No universities found matching the criteria";
    }

    echo json_encode($response);
});
//Filterpartime jobs 
$app->post('/filter/parttimejobs', function() use ($app) {
    $response = array();
    $request = json_decode($app->request()->getBody());

    $title = $request->title;
    $location = $request->location;
    $salary = $request->salary;

    $db = new DbHandler();
    $result = $db->filterparttimejobs($title, $location, $salary);

    if ($result) {
        $response["status"] = 1;
        $response["data"] = $result;
    } else {
        $response["status"] = 0;
        $response["message"] = "No parttime jobs found matching the criteria";
    }

    echo json_encode($response);
});
 //Filter accommodations
$app->post('/filter/accommodations', function() use ($app) {
    $response = array();
    $request = json_decode($app->request()->getBody());

    $city = $request->city;
    $country = $request->country;
   
    $room_cost=$request->room_cost;
    $category=$request->category;

    $db = new DbHandler();
    $result = $db->filteraccommodations( $city,$country,$room_cost, $category);

    if ($result) {
        $response["status"] = 1;
        $response["data"] = $result;
    } else {
        $response["status"] = 0;
        $response["message"] = "No parttime jobs found matching the criteria";
    }

    echo json_encode($response);
});

////////////////////////////////////////////////////// Kavitha End //////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////// Vinith Start //////////////////////////////////////////////////////////////////////////
// --------START----------------vinith -----------START --------------//
//Students
$app->post('/updateStudentsProfile', 'authenticatedefault', function() use ($app) {
    // Reading post params
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    // Extracting data
    $student_id = $data['student_id'];
    $first_name = $data['first_name'];
    $last_name = $data['last_name'];
    $address = $data['address'];
    $email = $data['email'];
    $qualification = $data['qualification'];

    // Initialize response
    $response = array();
    $db = new DbHandler();
    $result = $db->students($student_id, $first_name, $last_name, $address, $email, $qualification);

    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Successful";
        $response["students"] = $result['userDetails'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Not Successful';
        $response["students"] = array();
    }

    echoRespnse(200, $response);
});

//Admin
$app->post('/updateAdminProfile', 'authenticatedefault', function() use ($app) {
    // Reading post params
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    // Extracting data
    $admin_id = $data['admin_id'];
    $username = $data['username'];
    $first_name = $data['first_name'];
    $last_name = $data['last_name'];
    $phone_number = $data['phone_number'];
    $address = $data['address'];
    $email = $data['email'];

    // Initialize response
    $response = array();
    $db = new DbHandler();
    $result = $db->admin($admin_id,$username , $first_name, $last_name, $phone_number, $address, $email);

    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Successful";
        $response["admin"] = $result['userDetails'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Not Successful';
        $response["admin"] = array();
    }

    echoRespnse(200, $response);
});

//Information Providers
$app->post('/updateInformationProvidersProfile', 'authenticatedefault', function() use ($app) {
    // Reading post params
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    // Extracting data
    $provider_id = $data['provider_id'];
    $username = $data['username'];
    $mobile = $data['mobile'];
    $email = $data['email'];

    // Initialize response
    $response = array();
    $db = new DbHandler();
    $result = $db->information_providers($provider_id, $username, $mobile, $email);

    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Successful";
        $response["information_providers"] = $result['userDetails'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Not Successful';
        $response["information_providers"] = array();
    }

    echoRespnse(200, $response);
});

//scholarships
$app->post('/updatescholarships', 'authenticatedefault', function() use ($app) {
    // Reading post params
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    // Extracting data
    $scholarship_id = $data['scholarship_id'];
    $name = $data['name'];
    $description = $data['description'];
    $eligibility_criteria = $data['eligibility_criteria'];
    $application_deadline = $data['application_deadline'];
    $award_amount = $data['award_amount'];
    $contact_email = $data['contact_email'];
    $contact_phone = $data['contact_phone'];

    // Initialize response
    $response = array();
    $db = new DbHandler();
    $result = $db->updatescholarships($scholarship_id, $name, $description, $eligibility_criteria, $application_deadline, $award_amount, $contact_email, $contact_phone);

    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Successful";
        $response["scholarships"] = $result['userDetails'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Not Successful';
        $response["scholarships"] = array();
    }

    echoRespnse(200, $response);
});

//getscholarships
$app->get('/getscholarships', function() use ($app) {
    $response = array();
    $db = new DbHandler();
    $result = $db->getscholarships();

    if ($result) {
        $response["status"] = 1;
        $response["data"] = $result;
    } else {
        $response["status"] = 0;
        $response["message"] = "No data found";
    }

    echo json_encode($response);
});

//legalSafetyGuidelines
$app->post('/updatelegalSafetyGuidelines', 'authenticatedefault', function() use ($app) {
    // Reading post params
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    // Extracting data
    $guideline_id = $data['guideline_id'];
    $country = $data['country'];
    $legal_requirements = $data['legal_requirements'];
    $safety_guidelines = $data['safety_guidelines'];
    $emergency_protocols = $data['emergency_protocols'];

    // Initialize response
    $response = array();
    $db = new DbHandler();
    $result = $db->updatelegalSafetyGuidelines($guideline_id, $country, $legal_requirements, $safety_guidelines, $emergency_protocols);

    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Successful";
        $response["legalSafetyGuidelines"] = $result['userDetails'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Not Successful';
        $response["legalSafetyGuidelines"] = array();
    }

    echoRespnse(200, $response);
});

//getlegalSafetyGuidelines
$app->get('/getlegalSafetyGuidelines', function() use ($app) {
    $response = array();
    $db = new DbHandler();
    $result = $db->getlegalSafetyGuidelines();

    if ($result) {
        $response["status"] = 1;
        $response["data"] = $result;
    } else {
        $response["status"] = 0;
        $response["message"] = "No data found";
    }

    echo json_encode($response);
});

////////////////////////////////////////////////////// Vinith End //////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////// Suresh Start //////////////////////////////////////////////////////////////////////////

 //get accommodations
$app->post('/accommodations', 'authenticatedefault', function() use ($app) {
    // Reading post params
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['status'] = 0;
        $response['message'] = 'Invalid JSON format';
        $response["accommodations"] = array();
        echoResponse(400, $response);
        return;
    }

    // Check for required fields
    $required_fields = ['accommodation_id', 'name', 'address', 'city', 'country', 'description', 'contact_email', 'contact_phone', 'room_cost', 'banner', 'category'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            $response['status'] = 0;
            $response['message'] = "Missing required field: $field";
            $response["accommodations"] = array();
            echoResponse(400, $response);
            return;
        }
    }

    // Extracting data
    $accommodation_id = $data['accommodation_id'];
    $name = $data['name'];
    $address = $data['address'];
    $city = $data['city'];
    $country = $data['country'];
    $description = $data['description'];
    $contact_email = $data['contact_email'];
    $contact_phone = $data['contact_phone'];
    $room_cost = $data['room_cost'];
    $banner = $data['banner'];
    $category = $data['category'];

    // Initialize response
    $response = array();
    $db = new DbHandler();
    $result = $db->accommodations($accommodation_id, $name, $address, $city, $country, $description, $contact_email,$contact_phone, $room_cost, $banner, $category);

    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Successful";
        $response["accommodations"] = $result['accommodations'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Not Successful';
        $response["accommodations"] = array();
    }

    echoResponse(200, $response);
});

if (!function_exists('echoResponse')) {
    function echoResponse($status_code, $response) {
        $app = \Slim\Slim::getInstance();
        $app->status($status_code);
        $app->contentType('application/json');
        echo json_encode($response);
    }
}



 //get University Information
$app->post('/universities', 'authenticatedefault', function() use ($app) {
    // Reading post params
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['status'] = 0;
        $response['message'] = 'Invalid JSON format';
        $response["universities"] = array();
        echoResponse(400, $response);
        return;
    }

    // Check for required fields
    $required_fields = ['university_id', 'name', 'country', 'website', 'contact_email', 'contact_phone', 'logo', 'banner', 'timestamp', 'location', 'address', 'Admission_link', 'Contact_name', 'information_providedby'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            $response['status'] = 0;
            $response['message'] = "Missing required field: $field";
            $response["universities"] = array();
            echoResponse(400, $response);
            return;
        }
    }

    // Extracting data
    $university_id = $data['university_id'];
    $name = $data['name'];
    $country = $data['country'];
    $website = $data['website'];
    $contact_email = $data['contact_email'];
    $contact_phone = $data['contact_phone'];
    $logo = $data['logo'];
    $banner = $data['banner'];
    $timestamp = $data['timestamp'];
    $location = $data['location'];
    $address = $data['address'];
    $Description = isset($data['Description']) ? $data['Description'] : '';  // Default to empty string if not set
    $Admission_link = $data['Admission_link'];
    $Contact_name = $data['Contact_name'];
    $information_providedby = $data['information_providedby'];

    // Initialize response
    $response = array();
    $db = new DbHandler();
    $result = $db->universities($university_id, $name, $country, $website, $contact_email, $contact_phone, $logo, $banner, $timestamp, $location, $address, $Description, $Admission_link, $Contact_name, $information_providedby);

    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Successful";
        $response["universities"] = $result['universities'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Not Successful';
        $response["universities"] = array();
    }

    echoResponse(200, $response);
});

// function echoResponse($status_code, $response) {
//     $app = \Slim\Slim::getInstance();
//     $app->status($status_code);
//     $app->contentType('application/json');
//     echo json_encode($response);
// }


//get System Logs
$app->post('/logs', 'authenticatedefault', function() use ($app) {
    // Reading post params
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['status'] = 0;
        $response['message'] = 'Invalid JSON format';
        $response["logs"] = array();
        echoResponse(400, $response);
        return;
    }

    // Check for required fields
    $required_fields = ['log_id', 'log_level', 'message', 'details'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            $response['status'] = 0;
            $response['message'] = "Missing required field: $field";
            $response["logs"] = array();
            echoResponse(400, $response);
            return;
        }
    }

    // Extracting data
    $log_id = $data['log_id'];
    $log_level = $data['log_level'];
    $message = $data['message'];
    $details = $data['details'];
    
    // Initialize response
    $response = array();
    $db = new DbHandler();
    $result = $db->logs($log_id, $log_level, $message, $details);

    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Successful";
        $response["logs"] = $result['logs'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Not Successful';
        $response["logs"] = array();
    }

    echoResponse(200, $response);
});

// Part-Time Job Listings
$app->post('/jobs', 'authenticatedefault', function() use ($app) {
    // Reading post params
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['status'] = 0;
        $response['message'] = 'Invalid JSON format';
        $response["jobs"] = array();
        echoResponse(400, $response);
        return;
    }

    // Check for required fields
    $required_fields = ['job_id', 'title', 'description', 'location', 'salary', 'contact_email', 'contact_phone'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            $response['status'] = 0;
            $response['message'] = "Missing required field: $field";
            $response["jobs"] = array();
            echoResponse(400, $response);
            return;
        }
    }

    // Extracting data
    $job_id = $data['job_id'];
    $title = $data['title'];
    $description = $data['description'];
    $location = $data['location'];
    $salary = $data['salary'];
    $contact_email = $data['contact_email'];
    $contact_phone = $data['contact_phone'];

    // Initialize response
    $response = array();
    $db = new DbHandler();
    $result = $db->jobs($job_id, $title, $description, $location, $salary, $contact_email, $contact_phone);

    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Successful";
        $response["jobs"] = $result['jobs'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Not Successful';
        $response["jobs"] = array();
    }

    echoResponse(200, $response);
});

if (!function_exists('echoResponse')) {
    function echoResponse($status_code, $response) {
        $app = \Slim\Slim::getInstance();
        $app->status($status_code);
        $app->contentType('application/json');
        echo json_encode($response);
    }
}

 //get Predict
$app->post('/predict', 'authenticatedefault', function() use ($app) {
    // Reading post params
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['status'] = 0;
        $response['message'] = 'Invalid JSON format';
        $response["predict"] = array();
        echoResponse(400, $response);
        return;
    }

    // Check for required fields
    $required_fields = ['student_id', 'previous_grades', 'attendance_rate', 'participation_rate', 'study_hours_per_week', 'extra_curricular_activities'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            $response['status'] = 0;
            $response['message'] = "Missing required field: $field";
            $response["predict"] = array();
            echoResponse(400, $response);
            return;
        }
    }

    // Extracting data
    $student_id = $data['student_id'];
    $previous_grades = $data['previous_grades'];
    $attendance_rate = $data['attendance_rate'];
    $participation_rate = $data['participation_rate'];
    $study_hours_per_week = $data['study_hours_per_week'];
    $extra_curricular_activities = $data['extra_curricular_activities'];
   
    // Initialize response
    $response = array();
    $db = new DbHandler();
    $result = $db->predict($student_id, $previous_grades, $attendance_rate, $participation_rate, $study_hours_per_week, $extra_curricular_activities);

    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Successful";
        $response["predict"] = $result['predict'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Not Successful';
        $response["predict"] = array();
    }

    echoResponse(200, $response);
});
 
 //get analytics
 $app->post('/analytics', 'authenticatedefault', function() use ($app) {
    // Reading post params
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['status'] = 0;
        $response['message'] = 'Invalid JSON format';
        $response["analytics"] = array();
        echoResponse(400, $response);
        return;
    }

    // Check for required fields
    $required_fields = ['id', 'total_interactions', 'unique_users', 'popular_interactions', 'period'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            $response['status'] = 0;
            $response['message'] = "Missing required field: $field";
            $response["analytics"] = array();
            echoResponse(400, $response);
            return;
        }
    }

    // Extracting data
    $id = $data['id'];
    $total_interactions = $data['total_interactions'];
    $unique_users = $data['unique_users'];
    $popular_interactions = $data['popular_interactions'];
    $period = $data['period'];
   
    // Initialize response
    $response = array();
    $db = new DbHandler();
    $result = $db->analytics($id, $total_interactions, $unique_users, $popular_interactions, $period);

    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Successful";
        $response["analytics"] = $result['analytics'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Not Successful';
        $response["analytics"] = array();
    }

    echoResponse(200, $response);
});   

////////////////////////////////////////////////////// SureshEnd //////////////////////////////////////////////////////////////////////////

 
////////////////////////////////////////////////////// Durga rao Start //////////////////////////////////////////////////////////////////////////

//get courses

$app->post('/courses', 'authenticatedefault', function() use ($app) {
    // Reading post params
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['status'] = 0;
        $response['message'] = 'Invalid JSON format';
        $response["courses"] = array();
        echoResponse(400, $response);
        return;
    }

    // Check for required fields
    $required_fields = ['course_id', 'university_id', 'name', 'level', 'duration', 'fee', 'Category', 'Description', 'created_at'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            $response['status'] = 0;
            $response['message'] = "Missing required field: $field";
            $response["courses"] = array();
            echoResponse(400, $response);
            return;
        }
    }

    // Extracting data
    $course_id = $data['course_id'];
    $university_id = $data['university_id'];
    $name = $data['name'];
    $level = $data['level'];
    $duration = $data['duration'];
    $fee = $data['fee'];
    $Category = $data['duration'];
    $Description = $data['Description'];
    $created_at = $data['created_at'];
    

    // Initialize response
    $response = array();
    $db = new DbHandler();
    $result = $db->courses($name, $level, $duration, $fee, $Category, $Description, $created_at, $course_id, $university_id);

    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Successful";
        $response["courses"] = $result['courses'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Not Successful';
        $response["courses"] = array();
    }

    echoResponse(200, $response);
});


//modeltraining_data

$app->post('/modeltraining_data', 'authenticatedefault', function() use ($app) {
    // Reading post params
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['status'] = 0;
        $response['message'] = 'Invalid JSON format';
        $response["modeltraining_data"] = array();
        echoResponse(400, $response);
        return;
    }

    // Check for required fields
    $required_fields = ['data_id', 'student_id', 'course_id', 'data_type', 'grade', 'completion_time', 'message'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            $response['status'] = 0;
            $response['message'] = "Missing required field: $field";
            $response["modeltraining_data"] = array();
            echoResponse(400, $response);
            return;
        }
    }

    // Extracting data
    $data_id = $data['data_id'];
    $student_id = $data['student_id'];
    $course_id = $data['course_id'];
    $data_type = $data['data_type'];
    $grade = $data['grade'];
    $completion_time = $data['completion_time'];
    $message = $data['message'];
     

    // Initialize response
    $response = array();
    $db = new DbHandler();
    $result = $db->modeltraining_data($data_id, $student_id, $course_id, $data_type, $grade, $completion_time, $message);

    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Successful";
        $response["modeltraining_data"] = $result['modeltraining_data'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Not Successful';
        $response["modeltraining_data"] = array();
    }

    echoResponse(200, $response);
});

 // to get clickstreams

$app->post('/clickstreams', 'authenticatedefault', function() use ($app) {
    // Reading post params
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['status'] = 0;
        $response['message'] = 'Invalid JSON format';
        $response["clickstreams"] = array();
        echoResponse(400, $response);
        return;
    }

    // Check for required fields
    $required_fields = ['click_id', 'student_id', 'click_time', 'page_visited', 'action_taken'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            $response['status'] = 0;
            $response['message'] = "Missing required field: $field";
            $response["clickstreams"] = array();
            echoResponse(400, $response);
            return;
        }
    }

    // Extracting data
    $click_id = $data['click_id'];
    $student_id = $data['student_id'];
    $click_time = $data['click_time'];
    $page_visited = $data['page_visited'];
    $action_taken = $data['action_taken'];
    

    // Initialize response
    $response = array();
    $db = new DbHandler();
    $result = $db->clickstreams($click_id, $student_id, $click_time, $page_visited, $action_taken);

    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Successful";
        $response["clickstreams"] = $result['clickstreams'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Not Successful';
        $response["clickstreams"] = array();
    }

    echoResponse(200, $response);
});

//consultations to get

$app->post('/consultations', 'authenticatedefault', function() use ($app) {
    // Reading post params
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['status'] = 0;
        $response['message'] = 'Invalid JSON format';
        $response["consultations"] = array();
        echoResponse(400, $response);
        return;
    }

    // Check for required fields
    $required_fields = ['consultation_id', 'student_id', 'consultant_id', 'notes', 'consultation_type', 'consultation_date'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            $response['status'] = 0;
            $response['message'] = "Missing required field: $field";
            $response["consultations"] = array();
            echoResponse(400, $response);
            return;
        }
    }

    // Extracting data
    $consultation_id = $data['consultation_id'];
    $student_id = $data['student_id'];
    $consultant_id = $data['consultant_id'];
    $notes = $data['notes'];
    $consultation_type = $data['consultation_type'];
    $consultation_date = $data['consultation_date'];

    // Initialize response
    $response = array();
    $db = new DbHandler();
    $result = $db->consultations($consultation_id, $student_id, $consultant_id, $notes, $consultation_type, $consultation_date);

    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Successful";
        $response["consultations"] = $result['consultations'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Not Successful';
        $response["consultations"] = array();
    }

    echoResponse(200, $response);
});

function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    $app->status($status_code);
    $app->contentType('application/json');

    echo json_encode($response);
}


//get alumni_network

$app->post('/alumni_network', 'authenticatedefault', function() use ($app) {
    // Reading post params
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['status'] = 0;
        $response['message'] = 'Invalid JSON format';
        $response["alumni_network"] = array();
        echoResponse(400, $response);
        return;
    }

    // Check for required fields
    $required_fields = ['university_id', 'student_id', 'alumni_id', 'graduation_year', 'contact_email', 'contact_phone'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            $response['status'] = 0;
            $response['message'] = "Missing required field: $field";
            $response["alumni_network"] = array();
            echoResponse(400, $response);
            return;
        }
    }

    // Extracting data
    $university_id = $data['university_id'];
    $student_id = $data['student_id'];
    $alumni_id = $data['alumni_id'];
    $graduation_year = $data['graduation_year'];
    $contact_email = $data['contact_email'];
    $contact_phone = $data['contact_phone'];

    // Initialize response
    $response = array();
    $db = new DbHandler();
    $result = $db->alumni_network($university_id, $student_id, $alumni_id, $graduation_year, $contact_email, $contact_phone);

    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Successful";
        $response["alumni_network"] = $result['alumni_network'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Not Successful';
        $response["alumni_network"] = array();
    }

    echoResponse(200, $response);
});


// feedback
$app->post('/feedback', 'authenticatedefault', function() use ($app) {
    // Reading post params
    $json = $app->request->getBody();
    $data = json_decode($json, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['status'] = 0;
        $response['message'] = 'Invalid JSON format';
        $response["feedback"] = array();
        echoResponse(400, $response);
        return;
    }

    // Check for required fields
    $required_fields = ['feedback_id', 'student_id', 'feedback_text', 'comments', 'status'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            $response['status'] = 0;
            $response['message'] = "Missing required field: $field";
            $response["feedback"] = array();
            echoResponse(400, $response);
            return;
        }
    }

    // Extracting data
    $feedback_id = $data['feedback_id'];
    $student_id = $data['student_id'];
    $feedback_text = $data['feedback_text'];
    $comments = $data['comments'];
    $status = $data['status'];
    

    // Initialize response
    $response = array();
    $db = new DbHandler();
    $result = $db->feedback($feedback_id, $student_id, $feedback_text, $comments, $status);

    if ($result['status'] == 1) {
        $response["status"] = 1;
        $response['message'] = "Successful";
        $response["feedback"] = $result['feedback'];
    } else {
        $response['status'] = 0;
        $response['message'] = 'Not Successful';
        $response["feedback"] = array();
    }

    echoResponse(200, $response);
});

////////////////////////////////////////////////////// Durga rao End //////////////////////////////////////////////////////////////////////////

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

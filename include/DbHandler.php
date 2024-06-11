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

////////////////////////////////////////////////////// Kavitha Start //////////////////////////////////////////////////////////////////////////
// studentLogin Services
function studentLogin($email, $password) {
		
	$response = array();
	$hashedPassword = md5($password);
	$sql = "SELECT * FROM students WHERE email = ? AND password = ?";

	if ($stmt = $this->conn->prepare($sql)) {
		$stmt->bind_param("ss", $email, $hashedPassword);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($check_row = $result->fetch_array(MYSQLI_ASSOC)) {
			
			$response['status'] = 1;
			$response['message'] = "  student Logged in successfully";
			$response['userDetails'] = array(
				'email' => $check_row['email'],
				'password' => $check_row['password'],
				
				// 'studentID' => $check_row['student_id']
			);
		} else {
			// Incorrect email or password
			$response['status'] = 0;
			$response['message'] = "Incorrect email or password";
			$response['userDetails'] = array();
		}
		
		// Close the statement
		$stmt->close();
	} else {
		// Error preparing the SQL statement
		$response['status'] = 0;
		$response['message'] = "Database error";
		$response['userDetails'] = array();
	}
	
	// Return the response data
	return $response;
}


    // adminLogin Services
	function adminLogin($email, $password) {
		
		$response = array();
		$hashedPassword = md5($password);
		$sql = "SELECT * FROM admins WHERE email = ? AND password = ?";
	
		if ($stmt = $this->conn->prepare($sql)) {
			$stmt->bind_param("ss", $email, $hashedPassword);
			$stmt->execute();
			$result = $stmt->get_result();
			if ($check_row = $result->fetch_array(MYSQLI_ASSOC)) {
				
				$response['status'] = 1;
				$response['message'] = "Logged in successfully";
				$response['userDetails'] = array(
					'email' => $check_row['email'],
					'password' => $check_row['password'],
					
					// 'studentID' => $check_row['student_id']
				);
			} else {
				// Incorrect email or password
				$response['status'] = 0;
				$response['message'] = "Incorrect email or password";
				$response['userDetails'] = array();
			}
			
			// Close the statement
			$stmt->close();
		} else {
			// Error preparing the SQL statement
			$response['status'] = 0;
			$response['message'] = "Database error";
			$response['userDetails'] = array();
		}
		
		// Return the response data
		return $response;
	}
	
// Information Login Services
function ipLogin($email, $password) {
	$response = array();
	$hashedPassword = md5($password);
	$sql = "SELECT * FROM information_providers WHERE email = ? AND password = ?";

	if ($stmt = $this->conn->prepare($sql)) {
		$stmt->bind_param("ss", $email, $hashedPassword);
		$stmt->execute();
		$result = $stmt->get_result();
		if ($check_row = $result->fetch_array(MYSQLI_ASSOC)) {
			
			$response['status'] = 1;
			$response['message'] = " information provider Logged in successfully";
			$response['userDetails'] = array(
				'email' => $check_row['email'],
				'password' => $check_row['password'],
				
				// 'studentID' => $check_row['student_id']
			);
		} else {
			// Incorrect email or password
			$response['status'] = 0;
			$response['message'] = "Incorrect email or password";
			$response['userDetails'] = array();
		}
		
		// Close the statement
		$stmt->close();
	} else {
		// Error preparing the SQL statement
		$response['status'] = 0;
		$response['message'] = "Database error";
		$response['userDetails'] = array();
	}
	
	// Return the response data
	return $response;
}


//getting travel information
function getAllTravelInfo() {
    // Initialize an empty array to store all travel information
    $allTravelInfo = array();

    // Query the database to fetch all travel information
    $sql = "SELECT * FROM travel_information";
    $result = $this->conn->query($sql);

    // Check if any rows were returned
    if ($result->num_rows > 0) {
        // Fetch all travel information and populate the allTravelInfo array
        while ($row = $result->fetch_assoc()) {
            $allTravelInfo[] = $row;
        }
    }

    // Return the array containing all travel information
    return $allTravelInfo;
}

//updatetravelinformation

function updateTravel($travel_id, $country, $transportation_options, $travel_advisories, $travel_tips) {
    // Initialize data array
    $data = array();

    // Update the travel information in the database
    $sql1 = "UPDATE travel_information SET country='$country', transportation_options='$transportation_options', travel_advisories='$travel_advisories', travel_tips='$travel_tips' WHERE travel_id = '$travel_id'";
    $res1 = $this->conn->query($sql1);

    // Check if update was successful
    if ($res1) {
        // Populate data array with updated information
        $data['travel_id'] = $travel_id;
        $data['country'] = $country;
        $data['transportation_options'] = $transportation_options;
        $data['travel_advisories'] = $travel_advisories;
        $data['travel_tips'] = $travel_tips;
        $data['userDetails'] = $data;
        $data['status'] = 1;
    } else {
        $data['status'] = 0;
    }

    // Return the data array
    return $data;
}
//getAllHealthcareServices
 function getAllHealthcareServices() {
	$stmt = $this->conn->prepare("SELECT * FROM healthcare_services");
	$stmt->execute();
	$result = $stmt->get_result();
	$services = array();

	while ($service = $result->fetch_assoc()) {
		$services[] = $service;
	}

	$stmt->close();
	return $services;
}

//updateHealthcareService//

 function updateHealthcareService($service_id, $country, $medical_facility, $health_insurance_requirements, $emergency_contact, $emergency_number, $additional_notes) {
    $data = array();
    $data['service_id'] = $service_id;
    $data['country'] = $country;
    $data['medical_facility'] = $medical_facility;
    $data['health_insurance_requirements'] = $health_insurance_requirements;
    $data['emergency_contact'] = $emergency_contact;
    $data['emergency_number'] = $emergency_number;
    $data['additional_notes'] = $additional_notes;

    $sql = "UPDATE healthcare_services SET country=?, medical_facility=?, health_insurance_requirements=?, emergency_contact=?, emergency_number=?, additional_notes=? WHERE service_id=?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ssssssi", $country, $medical_facility, $health_insurance_requirements, $emergency_contact, $emergency_number, $additional_notes, $service_id);
    $res = $stmt->execute();

    if ($res) {
        $data['serviceDetails'] = $data;
        $data['status'] = 1;
    } else {
        $data['status'] = 0;
    }

    $stmt->close();
    return $data;
}
// filter universities  
function filterUniversitiesByLocation($country, $city) {
    $stmt = $this->conn->prepare("SELECT * FROM universities WHERE country = ? AND city = ?");
    
    if (!$stmt) {
        // Handle query preparation error
        return false;
    }

    $stmt->bind_param("ss", $country, $city);
    $stmt->execute();
    $result = $stmt->get_result();
    $universities = array();

    while ($university = $result->fetch_assoc()) {
        $universities[] = $university;
    }

    $stmt->close();
    return $universities;
}

// filter accommodations 
function filteraccommodations($city,$country,$room_cost, $category) { 
    $stmt = $this->conn->prepare("SELECT * FROM accommodations WHERE city = ? AND country = ? AND room_cost =?  AND category = ?;");
    
    if (!$stmt) {
        // Handle query preparation error
        return false;
    }

    $stmt->bind_param("ssss", $city,$country,$room_cost, $category);
    $stmt->execute();
    $result = $stmt->get_result();
    $parttimejobs = array();

    while ($row = $result->fetch_assoc()) {
        $accommodations[] = $row;
    }

    $stmt->close();
    return $accommodations;
}

////////////////////////////////////////////////////// Kavitha End //////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////// Vinith Start //////////////////////////////////////////////////////////////////////////

//students
function students($student_id, $first_name, $last_name, $address, $email, $qualification) {
	$data = array();

	$sql1 = "UPDATE students SET first_name='$first_name', last_name='$last_name', address='$address', email='$email', qualification='$qualification' WHERE student_id = '$student_id'";
	$res1 = $this->conn->query($sql1);

	if ($res1) {
		$data['userDetails'] = array(
			'student_id' => $student_id,
			'first_name' => $first_name,
			'last_name' => $last_name,
			'address' => $address,
			'email' => $email,
			'qualification' => $qualification
		);
		$data['status'] = 1;
	} else {
		$data['status'] = 0;
	}

	return $data;
}

//admin
function admin($admin_id, $username, $first_name, $last_name, $phone_number, $address, $email,) {
	$data = array();

	$sql1 = "UPDATE admins SET first_name='$first_name',username='$username', last_name='$last_name', phone_number='$phone_number',address='$address', email='$email' WHERE admin_id = '$admin_id'";
	$res1 = $this->conn->query($sql1);

	if ($res1) {
		$data['userDetails'] = array(
			'admin_id' => $admin_id,
			'username' => $username,
			'first_name' => $first_name,
			'last_name' => $last_name,
			'phone_number' => $phone_number,
			'address' => $address,
			'email' => $email
		);
		$data['status'] = 1;
	} else {
		$data['status'] = 0;
	}

	return $data;
}

	//information_providers
	function information_providers($provider_id, $username, $mobile, $email) {
		$data = array();

		$sql1 = "UPDATE information_providers SET username='$username', mobile='$mobile', email='$email' WHERE provider_id = '$provider_id'";
		$res1 = $this->conn->query($sql1);

		if ($res1) {
			$data['userDetails'] = array(
				'provider_id' => $provider_id,
				'username' => $username,
				'mobile' => $mobile,
				'email' => $email
			);
			$data['status'] = 1;
		} else {
			$data['status'] = 0;
		}

		return $data;
	}

	//scholarships
	function updatescholarships($scholarship_id, $name, $description, $eligibility_criteria, $application_deadline, $award_amount, $contact_email, $contact_phone) {
		$data = array();
	
		$sql1 = "UPDATE scholarships SET name='$name', description='$description', eligibility_criteria='$eligibility_criteria', application_deadline='$application_deadline', award_amount='$award_amount', contact_email='$contact_email', contact_phone='$contact_phone' WHERE scholarship_id = '$scholarship_id'";
		$res1 = $this->conn->query($sql1);
	
		if ($res1) {
			$data['userDetails'] = array(
				'scholarship_id' => $scholarship_id,
				'name' => $name,
				'description' => $description,
				'eligibility_criteria' => $eligibility_criteria,
				'application_deadline' => $application_deadline,
				'award_amount' => $award_amount,
				'contact_email' => $contact_email,
				'contact_phone' => $contact_phone
			);
			$data['status'] = 1;
		} else {
			$data['status'] = 0;
		}
	
		return $data;
	}

	// getscholarships
	function getscholarships() {
		$stmt = $this->conn->prepare("SELECT * FROM scholarships");
		$stmt->execute();
		$result = $stmt->get_result();
		$services = array();
	
		while ($service = $result->fetch_assoc()) {
			$services[] = $service;
		}
	
		$stmt->close();
		return $services;
	}

	///legal-safety-guidelines
	function updatelegalSafetyGuidelines($guideline_id, $country, $legal_requirements, $safety_guidelines, $emergency_protocols) {
		$data = array();
	
		$sql1 = "UPDATE legal_safety_guidelines SET country='$country', legal_requirements='$legal_requirements', safety_guidelines='$safety_guidelines', emergency_protocols='$emergency_protocols' WHERE guideline_id = '$guideline_id'";
		$res1 = $this->conn->query($sql1);
	
		if ($res1) {
			$data['userDetails'] = array(
				'guideline_id' => $guideline_id,
				'country' => $country,
				'legal_requirements' => $legal_requirements,
				'safety_guidelines' => $safety_guidelines,
				'emergency_protocols' => $emergency_protocols
			);
			$data['status'] = 1;
		} else {
			$data['status'] = 0;
		}
	
		return $data;
	}

	// getlegalSafetyGuidelines
	function getlegalSafetyGuidelines() {
		$stmt = $this->conn->prepare("SELECT * FROM legal_safety_guidelines");
		$stmt->execute();
		$result = $stmt->get_result();
		$services = array();
	
		while ($service = $result->fetch_assoc()) {
			$services[] = $service;
		}
	
		$stmt->close();
		return $services;
	}
////////////////////////////////////////////////////// Vinith End //////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////// Suresh Start //////////////////////////////////////////////////////////////////////////

//getaccommodations

function accommodations($accommodation_id, $name, $address, $city, $country, $description, $contact_email, $contact_phone, $room_cost, $banner, $category) {
    $data = array();

    // Prepare the SQL statement with placeholders
    $sql1 = "UPDATE accommodations 
             SET name = ?, address = ?, city = ?, country = ?, contact_email = ?, description = ?, 
			 contact_phone = ?, room_cost = ?, banner = ?, category = ? WHERE accommodation_id = ?";

    // Prepare the statement
    if ($stmt = $this->conn->prepare($sql1)) {
        // Bind the parameters
        $stmt->bind_param('ssssssssssi', $name, $address, $city, $country, $description, $contact_email, $contact_phone, $room_cost, $banner, $category, $accommodation_id);
        
        // Execute the statement
        $res1 = $stmt->execute();

        if ($res1) {
            $data['accommodations'] = array(
                'accommodation_id' => $accommodation_id,
                'name' => $name,
                'address' => $address,
                'city' => $city,
                'country' => $country,
                'description' => $description,
                'contact_email' => $contact_email,
				'contact_phone' => $contact_phone,
                'room_cost' => $room_cost,
                'banner' => $banner,
                'category' => $category
            );
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }
        
        // Close the statement
        $stmt->close();
    } else {
        $data['status'] = 0;
    }

    return $data;
}



//universities

function universities($university_id, $name, $country, $website, $contact_email, $contact_phone, $logo, $banner, $timestamp, $location, $address, $Admission_link, $Contact_name, $information_providedby) {
    $data = array();

    // Prepare the SQL statement with placeholders
    $sql1 = "UPDATE universities 
             SET name = ?, country = ?, website = ?, contact_email = ?, contact_phone = ?, logo = ?, banner = ?,
             timestamp = ?, location = ?, address = ?, Admission_link = ?, Contact_name = ?, 
             information_providedby = ? WHERE university_id = ?";

    // Prepare the statement
    if ($stmt = $this->conn->prepare($sql1)) {
        // Bind the parameters
        $stmt->bind_param('sssssssssssssi', $name, $country, $website, $contact_email, $contact_phone, $logo, $banner, $timestamp, $location, $address, $Admission_link, $Contact_name, $information_providedby, $university_id);
        
        // Execute the statement
        $res1 = $stmt->execute();

        if ($res1) {
            $data['universities'] = array(
                'university_id' => $university_id,
                'name' => $name,
                'country' => $country,
                'website' => $website,
                'contact_email' => $contact_email,
                'contact_phone' => $contact_phone,
                'logo' => $logo,
                'banner' => $banner,
                'timestamp' => $timestamp,
                'location' => $location,
                'address' => $address,
                'Admission_link' => $Admission_link,
                'Contact_name' => $Contact_name,
                'information_providedby' => $information_providedby
            );
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }
        
        // Close the statement
        $stmt->close();
    } else {
        $data['status'] = 0;
    }

    return $data;
}


//get System logs by  log_id

function logs($log_id, $log_level, $message, $details) {
    $data = array();

    // Prepare the SQL statement with placeholders
    $sql1 = "UPDATE logs 
             SET log_level = ?, message = ?, details = ? WHERE log_id = ?";

    // Prepare the statement
    if ($stmt = $this->conn->prepare($sql1)) {
        // Bind the parameters
        $stmt->bind_param('sssi', $log_level, $message, $details, $log_id);
        
        // Execute the statement
        $res1 = $stmt->execute();

        if ($res1) {
            $data['logs'] = array(
                'log_id' => $log_id,
                'log_level' => $log_level,
                'message' => $message,
                'details' => $details,
            );
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }
        
        // Close the statement
        $stmt->close();
    } else {
        $data['status'] = 0;
    }

    return $data;
}

//Part-job list

function jobs($job_id, $title, $description, $location, $salary, $contact_email, $contact_phone) {
    $data = array();

    // Prepare the SQL statement with placeholders
    $sql1 = "UPDATE jobs 
             SET title = ?, description = ?, location = ?, salary = ?, contact_email = ?, contact_phone = ? WHERE job_id = ?";

    // Prepare the statement
    if ($stmt = $this->conn->prepare($sql1)) {
        // Bind the parameters
        $stmt->bind_param('ssssssi', $title, $description, $location, $salary, $contact_email, $contact_phone, $job_id);
        
        // Execute the statement
        $res1 = $stmt->execute();

        if ($res1) {
            $data['jobs'] = array(
                'job_id' => $job_id,
                'title' => $title,
                'description' => $description,
                'location' => $location,
                'salary' => $salary,
                'contact_email' => $contact_email,
                'contact_phone' => $contact_phone
            );
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }
        
        // Close the statement
        $stmt->close();
    } else {
        $data['status'] = 0;
    }

    return $data;
}

//get Predict

function predict($student_id, $previous_grades, $attendance_rate, $participation_rate, $study_hours_per_week, $extra_curricular_activities) {
    $data = array();

    // Prepare the SQL statement with placeholders
    $sql1 = "UPDATE predict 
             SET previous_grades = ?, attendance_rate = ?, participation_rate = ?, study_hours_per_week = ?, extra_curricular_activities = ?
              WHERE student_id = ?";

    // Prepare the statement
    if ($stmt = $this->conn->prepare($sql1)) {
        // Bind the parameters
        $stmt->bind_param('sssssi', $previous_grades, $attendance_rate, $participation_rate, $study_hours_per_week, $extra_curricular_activities, $student_id);
        
        // Execute the statement
        $res1 = $stmt->execute();

        if ($res1) {
            $data['predict'] = array(
                'student_id' => $student_id,
                'previous_grades' => $previous_grades,
                'attendance_rate' => $attendance_rate,
                'participation_rate' => $participation_rate,
                'study_hours_per_week' => $study_hours_per_week,
                'extra_curricular_activities' => $extra_curricular_activities
            );
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }
        
        // Close the statement
        $stmt->close();
    } else {
        $data['status'] = 0;
    }

    return $data;
}


//get analytics

function analytics($id, $total_interactions, $unique_users, $popular_interactions, $period) {
    $data = array();

    // Convert arrays to JSON strings
    $popular_interactions_json = json_encode($popular_interactions);
    $period_json = json_encode($period);

    // Prepare the SQL statement with placeholders
    $sql1 = "UPDATE analytics 
             SET total_interactions = ?, unique_users = ?, popular_interactions = ?, period = ? WHERE id = ?";

    // Prepare the statement
    if ($stmt = $this->conn->prepare($sql1)) {
        // Bind the parameters
        $stmt->bind_param('ssssi', $total_interactions, $unique_users, $popular_interactions_json, $period_json, $id);
        
        // Execute the statement
        $res1 = $stmt->execute();

        if ($res1) {
            $data['analytics'] = array(
                'id' => $id,
                'total_interactions' => $total_interactions,
                'unique_users' => $unique_users,
                'popular_interactions' => $popular_interactions,
                'period' => $period
            );
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }
        
        // Close the statement
        $stmt->close();
    } else {
        $data['status'] = 0;
    }

    return $data;
}

////////////////////////////////////////////////////// Suresh End //////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////// Durga rao Start //////////////////////////////////////////////////////////////////////////

//modeltraining_data data_id

function modeltraining_data($data_id, $student_id, $course_id, $data_type, $grade, $completion_time, $message) {
    $data = array();

    // Prepare the SQL statement with placeholders
    $sql1 = "UPDATE modeltraining_data 
             SET data_type = ?, grade = ?, completion_time = ?, message = ?
             WHERE student_id = ? AND data_id = ? AND course_id = ?";
    
    // Prepare the statement
    if ($stmt = $this->conn->prepare($sql1)) {
        // Bind the parameters
        $stmt->bind_param('ssssiii', $data_type, $grade, $completion_time, $message, $student_id, $data_id, $course_id);
        
        // Execute the statement
        $res1 = $stmt->execute();

        if ($res1) {
            $data['modeltraining_data'] = array(
                'student_id' => $student_id,
                'data_id' => $data_id,
                'course_id' => $course_id,
                'data_type' => $data_type,
                'grade' => $grade,
                'completion_time' => $completion_time,
                'message' => $message
            );
            $data['status'] = 1;
        } else {
            // Capture and log the error for debugging
            $data['status'] = 0;
            $data['error'] = $stmt->error;
        }
        
        // Close the statement
        $stmt->close();
    } else {
        // Capture and log the preparation error
        $data['status'] = 0;
        $data['error'] = $this->conn->error;
    }

    return $data;
}


//get click_id

function clickstreams($click_id, $student_id, $click_time, $page_visited, $action_taken) {
    $data = array();

    // Prepare the SQL statement with placeholders
    $sql1 = "UPDATE clickstreams 
             SET click_time = ?, page_visited = ?, action_taken = ? 
             WHERE student_id = ? AND click_id = ?";
    
    // Prepare the statement
    if ($stmt = $this->conn->prepare($sql1)) {
        // Bind the parameters
        $stmt->bind_param('sssii',$click_time, $page_visited, $action_taken, $student_id, $click_id);
        
        // Execute the statement
        $res1 = $stmt->execute();

        if ($res1) {
            $data['clickstreams'] = array(
                'student_id' => $student_id,
                'click_id' => $click_id,
                'click_time' => $click_time,
                'page_visited' => $page_visited,
                'action_taken' => $action_taken
                
            );
            $data['status'] = 1;
        } else {
            // Capture and log the error for debugging
            $data['status'] = 0;
            $data['error'] = $stmt->error;
        }
        
        // Close the statement
        $stmt->close();
    } else {
        // Capture and log the preparation error
        $data['status'] = 0;
        $data['error'] = $this->conn->error;
    }

    return $data;
}
//consultations

function consultations($consultation_id, $student_id, $consultant_id, $notes, $consultation_type, $consultation_date) {
    $data = array();

    // Prepare the SQL statement with placeholders
    $sql1 = "UPDATE consultations 
             SET notes = ?, consultation_type = ?, consultation_date = ? 
             WHERE student_id = ? AND consultation_id = ?";
    
    // Prepare the statement
    if ($stmt = $this->conn->prepare($sql1)) {
        // Bind the parameters
        $stmt->bind_param('sssii', $notes, $consultation_type, $consultation_date, $student_id, $consultation_id);
        
        // Execute the statement
        $res1 = $stmt->execute();

        if ($res1) {
            $data['consultations'] = array(
                'student_id' => $student_id,
                'consultation_id' => $consultation_id,
                'consultant_id' => $consultant_id,
                'notes' => $notes,
                'consultation_type' => $consultation_type,
                'consultation_date' => $consultation_date
            );
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }
        
        // Close the statement
        $stmt->close();
    } else {
        $data['status'] = 0;
    }

    return $data;
}






// get course_id

function courses($name, $level, $duration, $fee, $Category, $Description, $created_at, $course_id, $university_id) {
    $data = array();

    // Prepare the SQL statement with placeholders
    $sql1 = "UPDATE courses 
             SET name = ?, level = ?, duration = ?, fee = ?, Category = ?, Description = ?, created_at = ?
             WHERE course_id = ? AND university_id = ?";
    
    // Prepare the statement
    if ($stmt = $this->conn->prepare($sql1)) {
        // Bind the parameters
        $stmt->bind_param('sssssssii', $name, $level, $duration, $fee, $Category, $Description, $created_at, $course_id, $university_id);
        
        // Execute the statement
        $res1 = $stmt->execute();

        if ($res1) {
            $data['courses'] = array(
                'course_id' => $course_id,
                'university_id' => $university_id,
                'name' => $name,
                'level' => $level,
                'duration' => $duration,
                'fee' => $fee,
                'Category' => $Category,
				'Description' => $Description,
				'created_at' => $created_at
				
            );
            $data['status'] = 1;
        } else {
            // Capture and log the error for debugging
            $data['status'] = 0;
            $data['error'] = $stmt->error;
        }
        
        // Close the statement
        $stmt->close();
    } else {
        // Capture and log the preparation error
        $data['status'] = 0;
        $data['error'] = $this->conn->error;
    }

    return $data;
}


//alumni_network

function alumni_network($university_id, $student_id, $alumni_id, $graduation_year, $contact_email, $contact_phone) {
    $data = array();

    // Prepare the SQL statement with placeholders
    $sql1 = "UPDATE alumni_network 
             SET graduation_year = ?, contact_email = ?, contact_phone = ? 
             WHERE student_id = ? AND alumni_id = ? AND university_id = ?";
    
    // Prepare the statement
    if ($stmt = $this->conn->prepare($sql1)) {
        // Bind the parameters
        $stmt->bind_param('sssiii', $graduation_year, $contact_email, $contact_phone, $student_id, $alumni_id, $university_id);
        
        // Execute the statement
        $res1 = $stmt->execute();

        if ($res1) {
            $data['alumni_network'] = array(
                'student_id' => $student_id,
                'university_id' => $university_id,
                'alumni_id' => $alumni_id,
                'graduation_year' => $graduation_year,
                'contact_email' => $contact_email,
                'contact_phone' => $contact_phone
            );
            $data['status'] = 1;
        } else {
            // Capture and log the error for debugging
            $data['status'] = 0;
            $data['error'] = $stmt->error;
        }
        
        // Close the statement
        $stmt->close();
    } else {
        // Capture and log the preparation error
        $data['status'] = 0;
        $data['error'] = $this->conn->error;
    }

    return $data;
}




//get feedback id

function feedback($feedback_id, $student_id, $feedback_text, $comments, $status) {
    $data = array();

    // Prepare the SQL statement with placeholders
    $sql1 = "UPDATE feedback 
             SET feedback_text = ?, comments = ?, status = ? 
             WHERE student_id = ? AND feedback_id = ?";
    
    // Prepare the statement
    if ($stmt = $this->conn->prepare($sql1)) {
        // Bind the parameters
        $stmt->bind_param('sssii', $feedback_text, $comments, $status, $student_id, $feedback_id);
        
        // Execute the statement
        $res1 = $stmt->execute();

        if ($res1) {
            $data['feedback'] = array(
                'student_id' => $student_id,
                'feedback_id' => $feedback_id,
                'feedback_text' => $feedback_text,
                'comments' => $comments,
                'status' => $status
            );
            $data['status'] = 1;
        } else {
            // Capture and log the error for debugging
            $data['status'] = 0;
            $data['error'] = $stmt->error;
        }
        
        // Close the statement
        $stmt->close();
    } else {
        // Capture and log the preparation error
        $data['status'] = 0;
        $data['error'] = $this->conn->error;
    }

    return $data;
}

////////////////////////////////////////////////////// Durga rao End //////////////////////////////////////////////////////////////////////////


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

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

	function getUserRoleByUserId($id)
		{
			$query = "SELECT u.user_role_id AS id,ur.name AS name, u.emp_number AS empNumber FROM ohrm_user u LEFT JOIN ohrm_user_role ur ON u.user_role_id = ur.id WHERE u.id = $id"; //table
			$result=mysqli_query($this->conn, $query);
			if(mysqli_num_rows($result)>0)
			{
			   $row = mysqli_fetch_array($result);
			   $id=$row['id'];
			   $name=$row['name'];
			  
			   $empNumber=$row['empNumber'];
			   
		    }
		    $details = array();
		    $details['id'] = $id;
		    $details['name'] = $name;
		    $details['empNumber'] = $empNumber;
		   return $details;
		}

		

		function getUserRoleByEmpNumber($id)
		{
			//echo $id;
			$query = "SELECT u.user_role_id AS id,ur.name AS name, u.emp_number AS empNumber FROM ohrm_user u LEFT JOIN ohrm_user_role ur ON u.user_role_id = ur.id WHERE u.emp_number = $id"; //table

			$result=mysqli_query($this->conn, $query);
			if(mysqli_num_rows($result)>0)
			{
			   $row = mysqli_fetch_array($result);
			   $id=$row['id'];
			   $name=$row['name'];
			  
			   $empNumber=$row['empNumber'];
			   
		    }
		    $details = array();
		    $details['id'] = $id;
		    $details['name'] = $name;
		    $details['empNumber'] = $empNumber;
		   return $details;
		}

		function getDepEngineer($ticket_id){
			$query = "SELECT e.emp_number FROM hs_hr_employee e LEFT JOIN ohrm_user u ON u.emp_number = e.emp_number LEFT JOIN ohrm_ticket t ON t.user_department_id = e.work_station WHERE u.user_role_id = 11 AND t.id=$ticket_id
			UNION
			SELECT toi.engineer_id FROM ohrm_ticket t
			LEFT JOIN ohrm_type_of_issue toi ON t.type_of_issue_id = toi.id
			WHERE t.id = $ticket_id"; //table

			$result=mysqli_query($this->conn, $query);
			if(mysqli_num_rows($result)>0)
			{
			   $row = mysqli_fetch_array($result);
			   $empNumber=$row['emp_number'];
		    }

		    return $empNumber;
		}




		function getAcceptedEngId($ticket_id)
		{
			$query = "SELECT ta.accepted_by as accepted_by FROM `ohrm_ticket_acknowledgement_action_log` ta LEFT JOIN hs_hr_employee e ON e.emp_number = ta.accepted_by LEFT JOIN ohrm_user u ON u.emp_number = e.emp_number WHERE u.user_role_id = 11 AND ta.ticket_id = $ticket_id ORDER BY ta.id DESC"; //table

			
			$result=mysqli_query($this->conn, $query);
			if(mysqli_num_rows($result)>0)
			{
			   $row = mysqli_fetch_array($result);
			   $empNumber=$row['accepted_by'];
		    }

		    if(empty($empNumber)){
		    	$empNumber = $this->getDepEngineer($ticket_id);
		    }
		   return $empNumber;
		}

		function getEmpnameByEmpNumber($emp_number)
		{
			$query = "SELECT concat(emp.emp_firstname,' ',emp.emp_middle_name,' ',emp.emp_lastname) as empname FROM hs_hr_employee emp WHERE emp.emp_number = $emp_number"; //table
			$result=mysqli_query($this->conn, $query);
			if(mysqli_num_rows($result)>0)
			{
			   $row = mysqli_fetch_array($result);
			   $empName=$row['empname'];
		    }

		   
		   return $empName;
		}

	function getPriorityByTypeOfIssueId($typeOfIssueId)
		{
			$query = "SELECT * FROM `ohrm_type_of_issue` WHERE id = $typeOfIssueId"; //table
			$result=mysqli_query($this->conn, $query);
			if(mysqli_num_rows($result)>0)
			{
			   $row = mysqli_fetch_array($result);
			   $priority_id=$row['priority_id'];
		    }
		   return $priority_id;
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


    function getEmailByUsrname($user_name)
		{

			$data=array();

			$query = "SELECT emp.emp_work_email as email FROM hs_hr_employee emp 
						LEFT JOIN ohrm_user u ON emp.emp_number = u.emp_number WHERE u.user_name = '$user_name'";
			$result=mysqli_query($this->conn, $query);
			
			if(mysqli_num_rows($result)>0)
			{
				/*$row=mysqli_fetch_assoc($count);
				echo "if";
				exit();*/
			   $row = mysqli_fetch_array($result);
			   $email=$row['email'];
			   /*echo $email;
			   exit();*/
		    }

		    else
		    {

				$email = "";

		    }
		   return $email;
		}


		function getUserIdByUsrname($user_name)
		{

			$data=array();

			$query = "SELECT id as userId FROM ohrm_user WHERE user_name = '$user_name'";
			$result=mysqli_query($this->conn, $query);
			
			if(mysqli_num_rows($result)>0)
			{
				/*$row=mysqli_fetch_assoc($count);
				echo "if";
				exit();*/
			   $row = mysqli_fetch_array($result);
			   $userId=$row['userId'];
			   /*echo $userId;
			   exit();*/
		    }
		    else
		    {
		    		$userId = "";

		    }
		   return $userId;
		}




		function getLocByEmpNumber($emp_number)
		{

			$data=array();

			
			$query = "SELECT loc.location_id as locationId FROM hs_hr_emp_locations loc LEFT JOIN ohrm_user usr ON loc.emp_number = usr.emp_number WHERE usr.emp_number = $emp_number";
			$result=mysqli_query($this->conn, $query);
			
			if(mysqli_num_rows($result)>0)
			{
				/*$row=mysqli_fetch_assoc($count);
				echo "if";
				exit();*/
			   $row = mysqli_fetch_array($result);
			   $locId=$row['locationId'];
			   /*echo $userId;
			   exit();*/
		    }
		    /*else
		    {
		    		$userId = "";

		    }*/
		   return $locId;
		}


function getPlayStoreUpdate()
		{

			$data=array();
			$token=$this->generateApiKey();
			$query = "SELECT MAX(sno) AS serialNo,version_code as versionCode, version_name	as versionName FROM playstore_update";

			
			$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{			
			$row=mysqli_fetch_assoc($count);

						    	$data['serialNo'] = $row['serialNo'];
						    	$data['versionCode'] = $row['versionCode'];
						    	$data['versionName'] = $row['versionName'];
						        $data['playStoreDetails'] = $data;
						        $data['status']=1;
		}
			else{
				$data['status']=0;
			}
		   return $data;
		}


		function getEmpDepartmentByEmpNumber($emp_number)
		{
			$query = "SELECT work_station as workStation FROM hs_hr_employee WHERE emp_number = $emp_number"; //table
			$result=mysqli_query($this->conn, $query);
			if(mysqli_num_rows($result)>0)
			{
			   $row = mysqli_fetch_array($result);
			   $workStation=$row['workStation'];
		    }

		   
		   return $workStation;
		}

	// this is for login function
	/*function userLogin($username,$password)
	{
		$data=array();
		$token=$this->generateApiKey();
		$query = "SELECT u.id AS id,u.user_role_id AS user_roleid,u.user_name AS user_name,u.user_password AS user_password, emp.emp_number AS emp_number,emp.emp_mobile AS mobile_number,emp.emp_work_email AS email,
		CONCAT(emp.emp_firstname,' ',emp.emp_lastname) AS emp_name FROM ohrm_user u LEFT JOIN hs_hr_employee emp ON emp.emp_number = u.emp_number WHERE u.deleted=0 and u.user_name ='$username'";
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{			
			$row=mysqli_fetch_assoc($count);
			$user_name = $row['user_name'];
			$user_password = $row['user_password'];
			$user_id = $row['id'];
			$mobileno = $row['mobile_number'];
			$email = $row['email'];
			$emp_name = $row['emp_name'];

			$verify = password_verify($password, $user_password);
			if($verify){

	    		$result=$this->smsConfig();
	    		$emailresult=$this->emailConfig();
	    		
	    		$rndno=rand(1000, 9999);
	    		$ss = new SmsService();
	    		$ss->otpSms($mobileno,$emp_name,$rndno);

				$query = "SELECT * FROM ohrm_user_token WHERE userId = $user_id";
				$count=mysqli_query($this->conn, $query);
				$otpnumber = md5($rndno);

				if(mysqli_num_rows($count) > 0)
				{
					$row=mysqli_fetch_assoc($count);
					$token_userid = $row['userid'];
						if($token_userid == $user_id){
							$updatesql ="UPDATE ohrm_user_token SET userid=$user_id, otp='$otpnumber',session_token='$token' WHERE userid=$user_id";
							if($result2 = mysqli_query($this->conn, $updatesql)){
								$data['session_token'] = $token;
						    	$data['user_id'] = $user_id;
						        $data['userDetails'] = $data;
						        $data['status']=1;
							}else{
							    $data['status']=0;
							}
						}else{
							$data['status']=0;
						}
				}else{
					$sql = "INSERT INTO ohrm_user_token (userid,otp,session_token) VALUES (?,?,?)";
							
					if($stmt = mysqli_prepare($this->conn, $sql)){
					    // Bind variables to the prepared statement as parameters
					     mysqli_stmt_bind_param($stmt, "iss" , $user_id,$otpnumber,$token);
					    			   
					    // Attempt to execute the prepared statement
					    if(mysqli_stmt_execute($stmt)){
					    	$data['session_token'] = $token;
					    	$data['user_id'] = $user_id;
					        $data['userDetails'] = $data;
					        $data['status']=1;
					    } else{
					        $data['status']=0;
					    }
					} else{
					    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
					    $data['status']=0;
					}	
				}
			}else{
				$data['status']=0;
			}
		}else{
			$data['status']=0;
		}
		return $data;
    }
*/

    // this is for login function using curl
	function userLogin($username,$password)
	{
		$data=array();
		$token=$this->generateApiKey();
		$query = "SELECT u.id AS id,u.user_role_id AS user_roleid,u.user_name AS user_name,u.user_password AS user_password, emp.emp_number AS emp_number,emp.emp_mobile AS mobile_number,emp.emp_work_email AS email FROM ohrm_user u LEFT JOIN hs_hr_employee emp ON emp.emp_number = u.emp_number WHERE u.deleted=0 and u.user_name ='$username'";
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{			
			$row=mysqli_fetch_assoc($count);
			$user_name = $row['user_name'];
			$user_password = $row['user_password'];
			$user_id = $row['id'];
			$mobileno = $row['mobile_number'];
			$email = $row['email'];

			$verify = password_verify($password, $user_password);
			if($verify){

	    		$result=$this->smsConfig();
	    		$emailresult=$this->emailConfig();
	    		
	    		$rndno=rand(1000, 9999);

	    		$mobile = $mobileno;
	    		//echo $rndno;
	    		$ch = curl_init();

	    		 //echo "before url";
	    		curl_setopt($ch, CURLOPT_URL, "$result[url]"."authKey=$result[user_name]&senderId=$result[sender_id]&tempId=1470&Phone=$mobile&F1=teejayadmin&F2=$rndno&F3=Plant Maintenance Admin&response=Y");
			  	
	    		//echo "after url";
			    // curl_setopt($ch, CURLOPT_POST, 1);// set post data to true
			    // curl_setopt($ch, CURLOPT_POSTFIELDS,"username=$result[user_name]&pass=$result[password]");   // post data
			    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			    $json = curl_exec($ch);
			    curl_close ($ch);
			    

				$query = "SELECT * FROM ohrm_user_token WHERE userId = $user_id";
				$count=mysqli_query($this->conn, $query);
				$otpnumber = md5($rndno);

				if(mysqli_num_rows($count) > 0)
				{
					$row=mysqli_fetch_assoc($count);
					$token_userid = $row['userid'];
						if($token_userid == $user_id){
							$updatesql ="UPDATE ohrm_user_token SET userid=$user_id, otp='$otpnumber',session_token='$token' WHERE userid=$user_id";
							if($result2 = mysqli_query($this->conn, $updatesql)){
								$data['session_token'] = $token;
						    	$data['user_id'] = $user_id;
						        $data['userDetails'] = $data;
						        $data['status']=1;
							}else{
							    $data['status']=0;
							}
						}else{
							$data['status']=0;
						}
				}else{
					$sql = "INSERT INTO ohrm_user_token (userid,otp,session_token) VALUES (?,?,?)";
							
					if($stmt = mysqli_prepare($this->conn, $sql)){
					    // Bind variables to the prepared statement as parameters
					     mysqli_stmt_bind_param($stmt, "iss" , $user_id,$otpnumber,$token);
					    			   
					    // Attempt to execute the prepared statement
					    if(mysqli_stmt_execute($stmt)){
					    	$data['session_token'] = $token;
					    	$data['user_id'] = $user_id;
					        $data['userDetails'] = $data;
					        $data['status']=1;
					    } else{
					        $data['status']=0;
					    }
					} else{
					    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
					    $data['status']=0;
					}	
				}
			}else{
				$data['status']=0;
			}
		}else{
			$data['status']=0;
		}
		return $data;
    }

// this is for login function
	function loginWtoutPasscode($username,$password,$path)
	{


		$data=array();
		$token=$this->generateApiKey();
		$query = "SELECT u.id AS id,u.user_role_id AS user_roleid,u.user_name AS user_name,u.user_password AS user_password, emp.emp_number AS emp_number,emp.emp_mobile AS mobile_number,emp.emp_work_email AS email,
		CONCAT(emp.emp_firstname,' ',emp.emp_lastname) AS emp_name FROM ohrm_user u LEFT JOIN hs_hr_employee emp ON emp.emp_number = u.emp_number WHERE u.deleted=0 and u.user_name ='$username'";
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{			
			$row=mysqli_fetch_assoc($count);
			$user_name = $row['user_name'];
			$user_password = $row['user_password'];
			$user_id = $row['id'];
			$mobileno = $row['mobile_number'];
			$email = $row['email'];
			$emp_name = $row['emp_name'];

			$passwordhash = md5($password);

			$verify = password_verify($password, $passwordhash);
			
			/*echo $password.'  '.$user_password.'  '.$passwordhash.'  '.$verify;
							exit();*/
			if($verify){

				
				$query = "SELECT * FROM ohrm_user_token WHERE userId = $user_id";
				$count=mysqli_query($this->conn, $query);
				
				$otpnumber = "";
				if(mysqli_num_rows($count) > 0)
				{
					$row=mysqli_fetch_assoc($count);
					$token_userid = $row['userid'];
						if($token_userid == $user_id){
							$updatesql ="UPDATE ohrm_user_token SET userid=$user_id, otp='$otpnumber',session_token='$token' WHERE userid=$user_id";
							if($result2 = mysqli_query($this->conn, $updatesql)){
								$data['session_token'] = $token;
						    	$data['user_id'] = $user_id;
						       
							}else{
							    $data['status']=0;
							}
						}else{
							$data['status']=0;
						}
				}else{

					
					$sql = "INSERT INTO ohrm_user_token (userid,otp,session_token) VALUES (?,?,?)";
							
					if($stmt = mysqli_prepare($this->conn, $sql)){
					    // Bind variables to the prepared statement as parameters
					     mysqli_stmt_bind_param($stmt, "iss" , $user_id,$otpnumber,$token);
					    			   
					    // Attempt to execute the prepared statement
					    if(mysqli_stmt_execute($stmt)){
					    	$data['session_token'] = $token;
					    	$data['user_id'] = $user_id;
					        
					    } else{
					        $data['status']=0;
					    }
					} else{
					    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
					    $data['status']=0;
					}	
				}



					$query2 = "SELECT u.id AS user_id,u.user_name AS user_name,u.user_role_id AS user_role_id,e.emp_number AS emp_number,CONCAT(e.emp_firstname,' ',e.emp_lastname) AS emp_name,CONCAT(e.emp_firstname,' ',e.emp_lastname) AS reported_by_name,ur.display_name AS role_name ,el.location_id AS location_id,l.name AS location_name,e.plant_id AS plant_id,p.plant_name AS plant_name,e.work_station AS department_id,s.name AS department_name FROM ohrm_user u LEFT JOIN hs_hr_employee e ON e.emp_number = u.emp_number LEFT JOIN hs_hr_emp_locations el ON el.emp_number = u.emp_number	LEFT JOIN ohrm_location l ON l.id = el.location_id LEFT JOIN ohrm_plant p ON p.id = e.plant_id LEFT JOIN ohrm_subunit s ON s.id = e.work_station LEFT JOIN ohrm_user_role ur ON ur.id = u.user_role_id WHERE u.id = $user_id";
						$count=mysqli_query($this->conn, $query2);

						if(mysqli_num_rows($count) > 0)
						{
							$row=mysqli_fetch_assoc($count);
							//$data['user_id'] = $row['user_id'];
							$data['user_name'] = $row['user_name'];
							$data['user_role_id'] = $row['user_role_id'];
							$data['emp_number'] = $row['emp_number'];
							$data['emp_name'] = $row['emp_name'];
							$data['reported_by_name'] = $row['reported_by_name'];
							$data['role_name'] = $row['role_name'];
							$data['location_id'] = $row['location_id'];
							$data['location_name'] = $row['location_name'];
							$data['department_id'] = $row['department_id'];
							$data['department_name'] = $row['department_name'];
							$data['plant_id'] = $row['plant_id'];
							$data['plant_name'] = $row['plant_name'];
							$emp_number = $data['emp_number'];
						
								$query3 ="SELECT epic_picture FROM hs_hr_emp_picture WHERE emp_number = $emp_number";
								$count=mysqli_query($this->conn, $query3);
								if(mysqli_num_rows($count) > 0)
								{
									$row1=mysqli_fetch_assoc($count);
									$value = $path.'get_image.php?id='.$emp_number;
									$data['image'] = $value;
								}else{
									$value = $path.'default-photo.png';
									$data['image'] = $value;
								}
						    $data['userDetails']=$data;
							$data['status']=1;
						}
			}else{

				
				$data['status']=0;
			}
		}else{

			
			$data['status']=0;
		}
		return $data;
    }

    //this is for otp verfication function
    function otpverify($user_id,$otp,$path)
	{
		$data=array();
		$query = "SELECT otp FROM ohrm_user_token WHERE userid = $user_id";
		// echo $query;exit;
		$count=mysqli_query($this->conn, $query);

		$query1 = "SELECT user_name,user_password from ohrm_user WHERE id = $user_id";
		$count1=mysqli_query($this->conn, $query1);
		if(mysqli_num_rows($count1) > 0)
		{			
			$row=mysqli_fetch_assoc($count1);
			$user_name=$row['user_name'];
			$user_password=$row['user_password'];



			$result=$this->loginWtoutPasscode($user_name,$user_password,$path);
						// $data['otpverified']=$result['userDetails'];
						// $data['status']=1;

		}

// echo "result";
//exit();


		if(mysqli_num_rows($count) > 0)
		{			
			$row=mysqli_fetch_assoc($count);
			$data['otp']=$row['otp'];
			if($row['otp'] == $otp){
				//
				$query = "SELECT u.id AS user_id,u.user_name AS user_name,u.user_role_id AS user_role_id,e.emp_number AS emp_number,CONCAT(e.emp_firstname,' ',e.emp_lastname) AS emp_name,CONCAT(e.emp_firstname,' ',e.emp_lastname) AS reported_by_name,ur.display_name AS role_name ,el.location_id AS location_id,l.name AS location_name,e.plant_id AS plant_id,p.plant_name AS plant_name,e.work_station AS department_id,s.name AS department_name FROM ohrm_user u LEFT JOIN hs_hr_employee e ON e.emp_number = u.emp_number LEFT JOIN hs_hr_emp_locations el ON el.emp_number = u.emp_number	LEFT JOIN ohrm_location l ON l.id = el.location_id LEFT JOIN ohrm_plant p ON p.id = e.plant_id LEFT JOIN ohrm_subunit s ON s.id = e.work_station LEFT JOIN ohrm_user_role ur ON ur.id = u.user_role_id WHERE u.id = $user_id";
						$count=mysqli_query($this->conn, $query);

						if(mysqli_num_rows($count) > 0)
						{
							$row=mysqli_fetch_assoc($count);
							$data['user_id'] = $row['user_id'];
							$data['user_name'] = $row['user_name'];
							$data['user_role_id'] = $row['user_role_id'];
							$data['emp_number'] = $row['emp_number'];
							$data['emp_name'] = $row['emp_name'];
							$data['reported_by_name'] = $row['reported_by_name'];
							$data['role_name'] = $row['role_name'];
							$data['location_id'] = $row['location_id'];
							$data['location_name'] = $row['location_name'];
							$data['department_id'] = $row['department_id'];
							$data['department_name'] = $row['department_name'];
							$data['plant_id'] = $row['plant_id'];
							$data['plant_name'] = $row['plant_name'];
							$emp_number = $data['emp_number'];


								$query="SELECT epic_picture FROM hs_hr_emp_picture WHERE emp_number = $emp_number";
								
								$count=mysqli_query($this->conn, $query);
								if(mysqli_num_rows($count) > 0)
								{
									$row1=mysqli_fetch_assoc($count);
									$value = $path.'get_image.php?id='.$emp_number;
									$data['image'] = $value;
								}else{
									$value = $path.'default-photo.png';
									$data['image'] = $value;
								}
						    $data['userDetails']=$data;
							$data['status']=1;
						}
				//
			}else{
				$data['status']=2;
			}
		}
			else{
				$data['status']=0;
			}
		
		return $data;
    }

	//this is for set passcode for the logged in user function
	function setpasscode($user_id,$passcodeentrVal,$path,$datetime,$imeino)
	{
		$data=array();
		$query = "SELECT user_id from ohrm_passcode WHERE user_id = $user_id";
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{			
			$row=mysqli_fetch_assoc($count);
			$userid = $row['user_id'];

			if($userid == $user_id){
				$updatesql ="UPDATE ohrm_passcode SET passcode='$passcodeentrVal',imei_number='$imeino', date_time='$datetime' WHERE user_id=$user_id";
				if($result2 = mysqli_query($this->conn, $updatesql)){
						$result=$this->passcodelogin($user_id,$passcodeentrVal,$path);
						$data['userDetails']=$result['userDetails'];
						$data['status']=1;
				}else{
					//echo "ERROR: Could not prepare query: $updatesql. " . mysqli_error($this->conn);
						//echo "failure updated";
					        $data['status']=3;
				}
			}else{
				        $data['status']=0;
			}
		}else{
				$sql = "INSERT INTO ohrm_passcode (user_id,passcode,imei_number,date_time) VALUES (?,?,?,?)";
			 								
					if($stmt = mysqli_prepare($this->conn, $sql)){
					    // Bind variables to the prepared statement as parameters
					     mysqli_stmt_bind_param($stmt, "isss" , $user_id,$passcodeentrVal,$imeino,$datetime);
					    			   
					    // Attempt to execute the prepared statement
					    if(mysqli_stmt_execute($stmt)){
					    		$result=$this->passcodelogin($user_id,$passcodeentrVal,$path);
					    		$data['userDetails']=$result['userDetails'];
								$data['status']=1;
					    } else{
					    	 //echo "ERROR: Could not prepare query: $stmt. " . mysqli_error($this->conn);
					    	//echo "failure inserted";
					        $data['status']=2;
					    }
					} else{
					    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
					    $data['status']=2;
					}	
			}
		return $data;
    }

    //this is for login with passcode function
    function passcodelogin($user_id,$passcodeentrVal,$path)
	{
		$data=array();
		$query = "SELECT p.passcode AS passcode, u.id FROM ohrm_passcode p LEFT JOIN ohrm_user u ON u.id = p.user_id WHERE p.user_id = $user_id";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
			$value = $row['passcode'];
				if($passcodeentrVal== $value){

					$query = "SELECT u.id AS user_id,u.user_name AS user_name,u.user_role_id AS user_role_id,e.emp_number AS emp_number,CONCAT(e.emp_firstname,' ',e.emp_lastname) AS emp_name,CONCAT(e.emp_firstname,' ',e.emp_lastname) AS reported_by_name,ur.display_name AS role_name ,el.location_id AS location_id,l.name AS location_name,e.plant_id AS plant_id,p.plant_name AS plant_name,e.work_station AS department_id,s.name AS department_name FROM ohrm_user u LEFT JOIN hs_hr_employee e ON e.emp_number = u.emp_number LEFT JOIN hs_hr_emp_locations el ON el.emp_number = u.emp_number	LEFT JOIN ohrm_location l ON l.id = el.location_id LEFT JOIN ohrm_plant p ON p.id = e.plant_id LEFT JOIN ohrm_subunit s ON s.id = e.work_station LEFT JOIN ohrm_user_role ur ON ur.id = u.user_role_id WHERE u.id = $user_id";
						$count=mysqli_query($this->conn, $query);

						if(mysqli_num_rows($count) > 0)
						{
							$row=mysqli_fetch_assoc($count);
							$data['user_id'] = $row['user_id'];
							$data['user_name'] = $row['user_name'];
							$data['user_role_id'] = $row['user_role_id'];
							$data['emp_number'] = $row['emp_number'];
							$data['emp_name'] = $row['emp_name'];
							$data['reported_by_name'] = $row['reported_by_name'];
							$data['role_name'] = $row['role_name'];
							$data['location_id'] = $row['location_id'];
							$data['location_name'] = $row['location_name'];
							$data['department_id'] = $row['department_id'];
							$data['department_name'] = $row['department_name'];
							$data['plant_id'] = $row['plant_id'];
							$data['plant_name'] = $row['plant_name'];
							$emp_number = $data['emp_number'];


								$query="SELECT epic_picture FROM hs_hr_emp_picture WHERE emp_number = $emp_number";
								
								$count=mysqli_query($this->conn, $query);
								if(mysqli_num_rows($count) > 0)
								{
									$row1=mysqli_fetch_assoc($count);
									$value = $path.'get_image.php?id='.$emp_number;
									$data['image'] = $value;
								}else{
									$value = $path.'default-photo.png';
									$data['image'] = $value;
								}
						    $data['userDetails']=$data;
							$data['status']=1;
						}
				}else{
					$data['status']=0;
				}
		}else
		{
			$data['status']=0;	
		}
		return $data;
    }

function loginMobNum($mobnumber,$username)
	{
		$data=array();
		$token=$this->generateApiKey();

		if($mobnumber!="")
		{
			
		$query = "SELECT emp.emp_number as empnumber FROM hs_hr_employee emp LEFT JOIN ohrm_emp_termination t ON emp.emp_number = t.emp_number WHERE emp.emp_mobile ='$mobnumber'";
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{			

			$row=mysqli_fetch_assoc($count);
			$empnumber = $row['empnumber'];

		$query1 = "SELECT usr.id as userid FROM ohrm_user usr WHERE usr.emp_number = '$empnumber'";
		$count1 =mysqli_query($this->conn, $query1);

			if(mysqli_num_rows($count1) > 0)
		{

			$row=mysqli_fetch_assoc($count1);
			$user_id = $row['userid'];

			$query2 = "SELECT * FROM ohrm_user_token WHERE userId = $user_id";
				$count2 =mysqli_query($this->conn, $query2);

				if(mysqli_num_rows($count2) > 0)
				{
					$row=mysqli_fetch_assoc($count2);
					$token_userid = $row['userid'];
						if($token_userid == $user_id){
							$updatesql ="UPDATE ohrm_user_token SET userid=$user_id,session_token='$token' WHERE userid=$user_id";
							if($result2 = mysqli_query($this->conn, $updatesql)){
								$data['session_token'] = $token;
						    	$data['user_id'] = $user_id;
						        $data['userMobLogin'] = $data;
						        $data['status']=1;
							}else{
							    $data['status']=0;
							}
						}else{
							$data['status']=0;
						}
				}else{

					/*echo "else";
					exit();*/
					$sql = "INSERT INTO ohrm_user_token (userid,session_token) VALUES (?,?)";
							
					if($stmt = mysqli_prepare($this->conn, $sql)){
					    // Bind variables to the prepared statement as parameters
					     mysqli_stmt_bind_param($stmt, "is" , $user_id,$token);
					    			   
					    // Attempt to execute the prepared statement
					    if(mysqli_stmt_execute($stmt)){
					    	$data['session_token'] = $token;
					    	$data['user_id'] = $user_id;
					        $data['userMobLogin'] = $data;
					        $data['status']=1;
					    } else{
					        $data['status']=0;
					    }
					} else{
					    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
					    $data['status']=0;
					}	
				}
			
		}
		else
		{
				
			$data['status']=0;
			
		}	

			
		}else{
			$data['status']=0;
		}

		}
		if($username != ""){

			//echo "username";
			//exit();

			$query3 = "SELECT u.id AS userid,u.user_role_id AS user_roleid,u.user_name AS user_name,u.user_password AS user_password, emp.emp_number AS emp_number,emp.emp_mobile AS mobile_number,emp.emp_work_email AS email FROM ohrm_user u LEFT JOIN hs_hr_employee emp ON emp.emp_number = u.emp_number WHERE u.deleted=0 and u.user_name ='$username'";
		$count3 = mysqli_query($this->conn, $query3);
		//echo $query3;exit();
		if(mysqli_num_rows($count3) > 0)
				{
					//echo "if";
						$row=mysqli_fetch_assoc($count3);
					$token_useridnew = $row['userid'];
					$user_name = $row['user_name'];
					
							if($user_name == $username){

								//echo "in if";

											$usertokenQuery = "SELECT * FROM `ohrm_user_token` WHERE userid = $token_useridnew";
											$usertokenQueryCount = mysqli_query($this->conn, $usertokenQuery);
												// echo $usertokenQuery;exit();
														if(mysqli_num_rows($usertokenQueryCount) > 0){

															//echo "update";
																$updatesql ="UPDATE ohrm_user_token SET userid=$token_useridnew,session_token='$token' WHERE userid=$token_useridnew";
																			if($result2 = mysqli_query($this->conn, $updatesql)){
																				$data['session_token'] = $token;
																		    	$data['user_id'] = $token_useridnew;
																		        $data['userMobLogin'] = $data;
																		        $data['status']=1;
																			}else{
																			    $data['status']=0;
																			}
														}else{


																	//echo $token_useridnew.''.$token;
																	//exit();
																	$sql = "INSERT INTO ohrm_user_token (userid,session_token) VALUES (?,?)";
																			
																			//echo $sql;
																	if($stmt = mysqli_prepare($this->conn, $sql)){
																	    // Bind variables to the prepared statement as parameters
																	     mysqli_stmt_bind_param($stmt, "is" , $token_useridnew,$token);
																	    			   
																	    // Attempt to execute the prepared statement
																	    if(mysqli_stmt_execute($stmt)){
																	    	$data['session_token'] = $token;
																	    	$data['user_id'] = $token_useridnew;
																	        $data['userMobLogin'] = $data;
																	        $data['status']=1;
																	    } else{
																	        $data['status']=0;
																	    }
																	} else{
																	    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
																	    $data['status']=0;
																	}

																}

												}
													else{

														 $data['status']=0;
													}

		}else{
			$data['status']=0;
		}
	}
		return $data;
    }


// loginwithmobNumorusrname
function loginWithMobNumOrUsrname($mobnumber,$username,$password,$path)
	{
		$data=array();
		$token=$this->generateApiKey();

		if(($username!="")&&($password!=""))
		{


						$query = "SELECT u.id AS id,u.user_role_id AS user_roleid,u.user_name AS user_name,u.user_password AS user_password, emp.emp_number AS emp_number,emp.emp_mobile AS mobile_number,emp.emp_work_email AS email FROM ohrm_user u LEFT JOIN hs_hr_employee emp ON emp.emp_number = u.emp_number WHERE u.deleted=0 and u.user_name ='$username'";
				$count=mysqli_query($this->conn, $query);
				if(mysqli_num_rows($count) > 0)
				{			
					$row=mysqli_fetch_assoc($count);
					$user_name = $row['user_name'];
					$user_password = $row['user_password'];
					$user_id = $row['id'];
					$mobileno = $row['mobile_number'];
					$email = $row['email'];

					$verify = password_verify($password, $user_password);
					if($verify){


			    		$query1 = "SELECT u.id AS user_id,u.user_name AS user_name,u.user_role_id AS user_role_id,e.emp_number AS emp_number,CONCAT(e.emp_firstname,' ',e.emp_lastname) AS emp_name,CONCAT(e.emp_firstname,' ',e.emp_lastname) AS reported_by_name,ur.display_name AS role_name ,el.location_id AS location_id,l.name AS location_name,e.plant_id AS plant_id,p.plant_name AS plant_name,e.work_station AS department_id,s.name AS department_name FROM ohrm_user u LEFT JOIN hs_hr_employee e ON e.emp_number = u.emp_number LEFT JOIN hs_hr_emp_locations el ON el.emp_number = u.emp_number	LEFT JOIN ohrm_location l ON l.id = el.location_id LEFT JOIN ohrm_plant p ON p.id = e.plant_id LEFT JOIN ohrm_subunit s ON s.id = e.work_station LEFT JOIN ohrm_user_role ur ON ur.id = u.user_role_id WHERE u.id = $user_id";

						$count1=mysqli_query($this->conn, $query1);

						if(mysqli_num_rows($count1) > 0)
						{
							$row=mysqli_fetch_assoc($count1);
							$data['user_id'] = $row['user_id'];
							$data['user_name'] = $row['user_name'];
							$data['user_role_id'] = $row['user_role_id'];
							$data['emp_number'] = $row['emp_number'];
							$data['emp_name'] = $row['emp_name'];
							$data['reported_by_name'] = $row['reported_by_name'];
							$data['role_name'] = $row['role_name'];
							$data['location_id'] = $row['location_id'];
							$data['location_name'] = $row['location_name'];
							$data['department_id'] = $row['department_id'];
							$data['department_name'] = $row['department_name'];
							$data['plant_id'] = $row['plant_id'];
							$data['plant_name'] = $row['plant_name'];
							$emp_number = $data['emp_number'];


								$query2="SELECT epic_picture FROM hs_hr_emp_picture WHERE emp_number = $emp_number";
								
								$count2=mysqli_query($this->conn, $query2);
								if(mysqli_num_rows($count2) > 0)
								{
									$row1=mysqli_fetch_assoc($count2);
									$value = $path.'get_image.php?id='.$emp_number;
									$data['image'] = $value;
								}else{
									$value = $path.'default-photo.png';
									$data['image'] = $value;
								}



								$rndno=rand(1000, 9999);

						$query3 = "SELECT * FROM ohrm_user_token WHERE userId = $user_id";

						$count3=mysqli_query($this->conn, $query3);
						$otpnumber = md5($rndno);

						
						    $data['userDetails']=$data;
							$data['status']=1;
						
						}	//mysqli_num_rows($count1) > 0

									else{
								$data['status']=0;
									}
					    
					}  //verify

					else{
						$data['status']=0;
					}
				} //mysqli_num_rows($count) > 0

				else{
					$data['status']=0;
				}



		} //username and pwd != ''

		else if(($mobnumber != "")&&($password != "")){

			/*echo "mobnumber";
			exit();*/

			$query4 = "SELECT u.id AS id,u.user_role_id AS user_roleid,u.user_name AS user_name,u.user_password AS user_password, emp.emp_number AS emp_number,emp.emp_mobile AS mobile_number,emp.emp_work_email AS email FROM ohrm_user u LEFT JOIN hs_hr_employee emp ON emp.emp_number = u.emp_number WHERE u.deleted=0 and emp.emp_mobile ='$mobnumber'";
		$count4 =mysqli_query($this->conn, $query4);

		if(mysqli_num_rows($count4) > 0)
		{			
					$row=mysqli_fetch_assoc($count4);
					$user_name = $row['user_name'];
					$user_password = $row['user_password'];
					$user_id = $row['id'];
					$mobileno = $row['mobile_number'];
					$email = $row['email'];

					$verify = password_verify($password, $user_password);
					if($verify){


									$query1 = "SELECT u.id AS user_id,u.user_name AS user_name,u.user_role_id AS user_role_id,e.emp_number AS emp_number,CONCAT(e.emp_firstname,' ',e.emp_lastname) AS emp_name,CONCAT(e.emp_firstname,' ',e.emp_lastname) AS reported_by_name,ur.display_name AS role_name ,el.location_id AS location_id,l.name AS location_name,e.plant_id AS plant_id,p.plant_name AS plant_name,e.work_station AS department_id,s.name AS department_name FROM ohrm_user u LEFT JOIN hs_hr_employee e ON e.emp_number = u.emp_number LEFT JOIN hs_hr_emp_locations el ON el.emp_number = u.emp_number	LEFT JOIN ohrm_location l ON l.id = el.location_id LEFT JOIN ohrm_plant p ON p.id = e.plant_id LEFT JOIN ohrm_subunit s ON s.id = e.work_station LEFT JOIN ohrm_user_role ur ON ur.id = u.user_role_id WHERE u.id = $user_id";

						$count1=mysqli_query($this->conn, $query1);

						if(mysqli_num_rows($count1) > 0)
						{
							$row=mysqli_fetch_assoc($count1);
							$data['user_id'] = $row['user_id'];
							$data['user_name'] = $row['user_name'];
							$data['user_role_id'] = $row['user_role_id'];
							$data['emp_number'] = $row['emp_number'];
							$data['emp_name'] = $row['emp_name'];
							$data['reported_by_name'] = $row['reported_by_name'];
							$data['role_name'] = $row['role_name'];
							$data['location_id'] = $row['location_id'];
							$data['location_name'] = $row['location_name'];
							$data['department_id'] = $row['department_id'];
							$data['department_name'] = $row['department_name'];
							$data['plant_id'] = $row['plant_id'];
							$data['plant_name'] = $row['plant_name'];
							$emp_number = $data['emp_number'];


								$query2="SELECT epic_picture FROM hs_hr_emp_picture WHERE emp_number = $emp_number";
								
								$count2=mysqli_query($this->conn, $query2);
								if(mysqli_num_rows($count2) > 0)
								{
									$row1=mysqli_fetch_assoc($count2);
									$value = $path.'get_image.php?id='.$emp_number;
									$data['image'] = $value;
								}else{
									$value = $path.'default-photo.png';
									$data['image'] = $value;
								}

									
										$data['userDetails']=$data;
									$data['status']=1;

							}	//mysqli_num_rows($count1) > 0
											
								} //verify

								else{
								$data['status']=0;
							}

			}//mysqli_num_rows($count4) > 0

			else{
			$data['status']=0;
		}
			

		}

		else
		{
			

			$data['status']=0;


		}
		return $data;
    }

     function sendOtp($user_id)
	{
		$data=array();
		$token=$this->generateApiKey();

		$query1 = "SELECT e.emp_mobile as mobilenumber FROM hs_hr_employee e LEFT JOIN ohrm_user u ON e.emp_number = u.emp_number WHERE u.id = $user_id";

		/*echo "$query1";
		exit();*/
		$count1=mysqli_query($this->conn, $query1);

		if(mysqli_num_rows($count1) > 0)
				{
					$row=mysqli_fetch_assoc($count1);

					$mobnumber = $row['mobilenumber'];

					/*echo $mobnumber;
					exit();*/

		}
		$query = "SELECT CONCAT(emp.emp_firstname,' ',emp.emp_lastname) AS emp_name,emp.emp_mobile as mobnumber,u.id as userId FROM ohrm_user u LEFT JOIN hs_hr_employee emp ON emp.emp_number = u.emp_number WHERE emp.emp_mobile = '$mobnumber'";
		$count=mysqli_query($this->conn, $query);

			if(mysqli_num_rows($count) > 0)
				{
					$row=mysqli_fetch_assoc($count);

					$mobileno = $row['mobnumber'];
				$emp_name = $row['emp_name'];
					$result=$this->smsConfig();
	    		$emailresult=$this->emailConfig();
	    		
	    		$rndno=rand(1000, 9999);
	    		$ss = new SmsService();
	    		$ss->otpSms($mobileno,$emp_name,$rndno);
				$otpnumber = md5($rndno);

					$tuser_id = $row['userId'];
						if($tuser_id){
							
							$updatesql ="UPDATE ohrm_user_token SET otp='$otpnumber' WHERE userid=$tuser_id";
							if($result2 = mysqli_query($this->conn, $updatesql)){
								
						    	$data['userId'] = $tuser_id;
						        $data['sendOtpDetails'] = $data;
						        $data['status']=1;
							}else{
								
							    $data['status']=0;
							}
						}else{
							$data['status']=0;
						}
				}else{
					$sql = "INSERT INTO ohrm_user_token (otp) VALUES (?)";
							
					if($stmt = mysqli_prepare($this->conn, $sql)){
					    // Bind variables to the prepared statement as parameters
					     mysqli_stmt_bind_param($stmt, "s" , $otpnumber);
					    			   
					    // Attempt to execute the prepared statement
					    if(mysqli_stmt_execute($stmt)){
					    	
					    	$data['userId'] = $user_id;
					        $data['sendOtpDetails'] = $data;
					        $data['status']=1;
					    } else{
					        $data['status']=0;
					    }
					} else{
					    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
					    $data['status']=0;
					}	
				}

		return $data;
    }

    function logout($user_id)
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
					$updatesql ="UPDATE ohrm_user_token SET session_token=' ' WHERE userid=$user_id";
					if($result2 = mysqli_query($this->conn, $updatesql)){
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

    //Ticket Status

	function getTicketStatusById($id){

		$query="SELECT * FROM ohrm_ticket_status where id = $id";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
			$row['status']=1;
		}else{
			$row['status']=0;
		}

		return $row; 
	}


 function ticketDetails($userIdPass)
	{
		$data= array();
		$userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$name = '';
		$emp_name = '';
		$i=0;

		
		  //$userDetails = $this->getUserRoleByUserId($user_id);
		$emp_number = $userDetails['empNumber'];
		//echo $emp_number;
		$userRoleId = $userDetails['id'];

		 if($userRoleId == 10){



		 	$query = "SELECT t.job_id AS job_id,t.id AS ticketId, t.functional_location_id AS funLocId, t.id AS id, t.subject AS subject, t.submitted_on AS calFromDate, t.submitted_on AS calToDate, t.submitted_on AS createdOn, ta.machine_status AS machineStatus, fl.name as functionallocation_name, fl.id as functionlocation_id,
		 	t.is_PreventiveMaintenance AS preventiveMaintenance,
                    toi.name AS typeOfIssue, toi.id AS typeOfIssueId, toi.sla AS sla,
                    loc.name AS location, loc.id AS locationId,
                    plnt.plant_name AS plantName, plnt.id AS plantId,
                    eq.name AS equipment, eq.id AS equipmentId,
                    ts.name AS status, ts.id AS statusId,
                    ta.ticket_id AS ticketId, t.submitted_by_name AS submittedByName, e.emp_number AS engineerId, e.emp_number AS technicianId,
                    tp.name AS priority, tp.id AS priorityId,
                    tsev.name AS severity, tsev.id AS severityId,
                    u.id AS uaerId,
                    msr.id AS scheduleId, msr.maintenance_type_id AS maintenanceType,
                    mt.id AS maintenanceId, mt.name AS maintenanceName,
                    cs.name AS subDivision, cs.id AS subDivisionId
				FROM ohrm_ticket t
                LEFT JOIN ohrm_functional_location fl ON fl.id = t.functional_location_id 
                LEFT JOIN ohrm_type_of_issue toi ON toi.id = t.type_of_issue_id 
                LEFT JOIN  ohrm_location loc ON loc.id = t.location_id 
                LEFT JOIN  ohrm_plant plnt ON plnt.id = t.plant_id 
                LEFT JOIN  ohrm_equipment eq ON eq.id = t.equipment_id 
                LEFT JOIN  ohrm_ticket_status ts ON ts.id = t.status_id
                LEFT JOIN  ohrm_ticket_acknowledgement_action_log ta ON ta.ticket_id = t.id
                LEFT JOIN  hs_hr_employee e ON e.emp_number = ta.submitted_by_emp_number
                LEFT JOIN  ohrm_user u ON u.id = ta.created_by_user_id 
                LEFT JOIN  ohrm_ticket_priority tp ON tp.id = t.priority_id
                LEFT JOIN  ohrm_ticket_severity tsev ON tsev.id = t.severity_id
                LEFT JOIN ohrm_subunit cs ON cs.id = t.user_department_id
                LEFT JOIN ohrm_maintenance_schedule msr ON msr.ticket_id = t.id
                LEFT JOIN ohrm_maintenance_type mt ON mt.id = msr.maintenance_type_id
                GROUP BY t.id
				ORDER BY `t`.`job_id`  DESC";


				$configDate = $this->dateFormat();

			$count=mysqli_query($this->conn, $query);

				if(mysqli_num_rows($count) > 0)
				{
					// $row=mysqli_fetch_all($count,MYSQLI_ASSOC);
					while($row = mysqli_fetch_assoc($count)) { 	

						$i=$i+1;
						
						if($row['preventiveMaintenance'] == 1){
							$data['sno']=$i;
							$data['ticketId']=$row['ticketId'];
						$data['job_id']=$row['job_id'];
						$data['subject']= 'PM - '.$row['equipment'];
						$data['issue']=$row['typeOfIssue'];
						$data['location']=$row['location'];
						$data['plant']= $row['plantName'];
						$data['department']=$row['subDivision'];
						$data['functionallocation']=$row['functionallocation_name'];
						$funLoc = $this->subfunctionalLocations($row['functionlocation_id']);
											if($funLoc['status'] == 1){
												$data['functionlocation_id']=$funLoc['id'];
												$data['functionallocation_name']=$funLoc['name'];
												$data['subfunctionlocation_id']=$row['functionlocation_id'];
												$data['subfunctionallocation_name']=$row['functionallocation_name'];
											}else{
												$data['functionlocation_id']=$row['functionlocation_id'];
												$data['functionallocation_name']=$row['functionallocation_name'];
												$data['subfunctionlocation_id']=0;
												$data['subfunctionallocation_name']='';
											}
						$data['equipment']= $row['equipment'];
						$data['submittedby']=$row['submittedByName'];
						$data['submittedon']=date($configDate, strtotime( $row['createdOn'] )).' '.date('H:i:s', strtotime( $row['createdOn'] ));
						$data['status']= $row['status'];
	
						}else{
							$data['sno']=$i;
							$data['ticketId']=$row['ticketId'];
						$data['job_id']=$row['job_id'];
						$text = $row['subject'];
						$data['subject']=iconv(mb_detect_encoding($text), "UTF-8//IGNORE", $text);
						//$data['subject']= iconv('UTF-8', 'ASCII//IGNORE', utf8_encode($text));
						$data['issue']=$row['typeOfIssue'];
						$data['location']=$row['location'];
						$data['plant']= $row['plantName'];
						$data['department']=$row['subDivision'];
						$data['functionallocation']=$row['functionallocation_name'];
						$funLoc = $this->subfunctionalLocations($row['functionlocation_id']);
											if($funLoc['status'] == 1){
												$data['functionlocation_id']=$funLoc['id'];
												$data['functionallocation_name']=$funLoc['name'];
												$data['subfunctionlocation_id']=$row['functionlocation_id'];
												$data['subfunctionallocation_name']=$row['functionallocation_name'];
											}else{
												$data['functionlocation_id']=$row['functionlocation_id'];
												$data['functionallocation_name']=$row['functionallocation_name'];
												$data['subfunctionlocation_id']=0;
												$data['subfunctionallocation_name']='';
											}
						$data['equipment']= $row['equipment'];
						$data['submittedby']=$row['submittedByName'];
						$data['submittedon']=date($configDate, strtotime( $row['createdOn'] )).' '.date('H:i:s', strtotime( $row['createdOn'] ));
						$data['status']= $row['status'];

						}				
						

						if ($row['statusId'] == 3) {
							$ticket_id = $row['ticketId'];

							$q1 = "SELECT s.id AS id,s.name AS name from ohrm_ticket_status s LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON l.status_id = s.id WHERE l.id = (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id GROUP BY ticket_id)"; 
							$res1=mysqli_query($this->conn, $q1);
							if(mysqli_num_rows($res1)>0)
							{
							   $row1 = mysqli_fetch_array($res1);
							   $id=$row1['id'];
							   $name=$row1['name'];
						    }

						    if($id == 4){
						    	$q2 = "SELECT e.emp_number AS emp_number, concat(e.emp_firstname,' ',e.emp_middle_name,' ',e.emp_lastname) AS emp_name from hs_hr_employee e LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON l.accepted_by = e.emp_number WHERE l.id = (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id GROUP BY ticket_id)"; 
							$res2=mysqli_query($this->conn, $q2);
							
							if(mysqli_num_rows($res2)>0)
							{
							   $row2 = mysqli_fetch_array($res2);
							   $emp_number=$row2['emp_number'];
							   $emp_name=$row2['emp_name'];
						    }

								$data['status']= $row['status'].'('.$name.' by '.$emp_name.')';
						    }else{

						    	$q2 = "SELECT e.emp_number AS emp_number, concat(e.emp_firstname,' ',e.emp_middle_name,' ',e.emp_lastname) AS emp_name from hs_hr_employee e LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON l.forward_to = e.emp_number WHERE l.id = (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id GROUP BY ticket_id)"; 
							$res2=mysqli_query($this->conn, $q2);
							
							if(mysqli_num_rows($res2)>0)
							{
							   $row2 = mysqli_fetch_array($res2);
							   $emp_number=$row2['emp_number'];
							   $emp_name=$row2['emp_name'];
						    }

						    	$data['status']= $row['status'].'('.$name.' to '.$emp_name.')';
						    }
						}
						
						$data1[] = $data;
					}
						$data['ticketDetails']=$data1;
						$data['status']=1;
							
				}else{
				$data['status']=0;
			}


		 }
		 else if($userRoleId == 11)
		 {

		 		$query = "SELECT t.job_id AS job_id, t.id AS ticketId, t.subject AS subject, t.submitted_on AS submittedon,t.is_PreventiveMaintenance AS preventiveMaintenance,ta.machine_status AS machineStatus, fl.name as functionallocation, fl.id as functionalLocationId,toi.name AS issue, toi.id AS typeOfIssueId, toi.sla AS sla,loc.name AS location, loc.id AS locationId, plnt.plant_name AS plant, plnt.id AS plantId, eq.name AS equipment, eq.id AS equipmentId, ts.name AS status, ts.id AS statusId, ta.ticket_id AS ticketId, t.submitted_by_name AS submittedby, e.emp_number AS engineerId, e.emp_number AS technicianId,tp.name AS priority, tp.id AS priorityId, tsev.name AS severity, tsev.id AS severityId, u.id AS uaerId, cs.name AS department, cs.id AS subDivisionId FROM ohrm_ticket t LEFT JOIN ohrm_functional_location fl ON fl.id = t.functional_location_id LEFT JOIN ohrm_type_of_issue toi ON toi.id = t.type_of_issue_id LEFT JOIN ohrm_location loc ON loc.id = t.location_id LEFT JOIN ohrm_plant plnt ON plnt.id = t.plant_id LEFT JOIN ohrm_equipment eq ON eq.id = t.equipment_id LEFT JOIN ohrm_ticket_status ts ON ts.id = t.status_id LEFT JOIN ohrm_ticket_acknowledgement_action_log ta ON ta.ticket_id = t.id LEFT JOIN hs_hr_employee e ON e.emp_number = ta.submitted_by_emp_number LEFT JOIN ohrm_user u ON u.id = ta.created_by_user_id LEFT JOIN ohrm_ticket_priority tp ON tp.id = t.priority_id LEFT JOIN ohrm_ticket_severity tsev ON tsev.id = t.severity_id LEFT JOIN ohrm_subunit cs ON cs.id = t.user_department_id WHERE ta.submitted_by_emp_number = $empNumber AND t.id NOT IN (select id from ohrm_ticket where status_id = 11 and submitted_by_emp_number != $empNumber) GROUP BY t.id
			ORDER BY `t`.`job_id`  DESC";


			$configDate = $this->dateFormat();

			$count=mysqli_query($this->conn, $query);

				if(mysqli_num_rows($count) > 0)
				{
					// $row=mysqli_fetch_all($count,MYSQLI_ASSOC);
					while($row = mysqli_fetch_assoc($count)) { 	

						$i=$i+1;
						
						if($row['preventiveMaintenance'] == 1){
							$data['sno']=$i;
							$data['ticketId']=$row['ticketId'];
						$data['job_id']=$row['job_id'];
						$data['subject']= 'PM - '.$row['equipment'];
						$data['issue']=$row['issue'];
						$data['location']=$row['location'];
						$data['plant']= $row['plant'];
						$data['department']=$row['department'];
						$data['functionallocation']=$row['functionallocation'];
						$data['equipment']= $row['equipment'];
						$data['submittedby']=$row['submittedby'];
						$data['submittedon']=date($configDate, strtotime( $row['submittedon'] )).' '.date('H:i:s', strtotime( $row['submittedon'] ));
						$data['status']= $row['status'];
	
						}else{
							$data['sno']=$i;
							$data['ticketId']=$row['ticketId'];
						$data['job_id']=$row['job_id'];
						$text = $row['subject'];
						$data['subject']=iconv(mb_detect_encoding($text), "UTF-8//IGNORE", $text);
						//$data['subject']= iconv('UTF-8', 'ASCII//IGNORE', utf8_encode($text));
						$data['issue']=$row['issue'];
						$data['location']=$row['location'];
						$data['plant']= $row['plant'];
						$data['department']=$row['department'];
						$data['functionallocation']=$row['functionallocation'];
						$data['equipment']= $row['equipment'];
						$data['submittedby']=$row['submittedby'];
						$data['submittedon']=date($configDate, strtotime( $row['submittedon'] )).' '.date('H:i:s', strtotime( $row['submittedon'] ));
						$data['status']= $row['status'];

						}				
						

						if ($row['statusId'] == 3) {
							$ticket_id = $row['ticketId'];

							$q1 = "SELECT s.id AS id,s.name AS name from ohrm_ticket_status s LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON l.status_id = s.id WHERE l.id = (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id GROUP BY ticket_id)"; 
							$res1=mysqli_query($this->conn, $q1);
							if(mysqli_num_rows($res1)>0)
							{
							   $row1 = mysqli_fetch_array($res1);
							   $id=$row1['id'];
							   $name=$row1['name'];
						    }

						    if($id == 4){
						    	$q2 = "SELECT e.emp_number AS emp_number, concat(e.emp_firstname,' ',e.emp_middle_name,' ',e.emp_lastname) AS emp_name from hs_hr_employee e LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON l.accepted_by = e.emp_number WHERE l.id = (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id GROUP BY ticket_id)"; 
							$res2=mysqli_query($this->conn, $q2);
							
							if(mysqli_num_rows($res2)>0)
							{
							   $row2 = mysqli_fetch_array($res2);
							   $emp_number=$row2['emp_number'];
							   $emp_name=$row2['emp_name'];
						    }

								$data['status']= $row['status'].'('.$name.' by '.$emp_name.')';
						    }else{

						    	$q2 = "SELECT e.emp_number AS emp_number, concat(e.emp_firstname,' ',e.emp_middle_name,' ',e.emp_lastname) AS emp_name from hs_hr_employee e LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON l.forward_to = e.emp_number WHERE l.id = (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id GROUP BY ticket_id)"; 
							$res2=mysqli_query($this->conn, $q2);
							
							if(mysqli_num_rows($res2)>0)
							{
							   $row2 = mysqli_fetch_array($res2);
							   $emp_number=$row2['emp_number'];
							   $emp_name=$row2['emp_name'];
						    }

						    	$data['status']= $row['status'].'('.$name.' to '.$emp_name.')';
						    }
						}
						
						$data1[] = $data;
					}
						$data['ticketDetails']=$data1;
						$data['status']=1;
							
				}else{
				$data['status']=0;
			}

		 }

		 else 

		 {

		 		$query = "SELECT t.job_id AS job_id, t.id AS ticketId, t.subject AS subject, t.submitted_on AS submittedon,t.is_PreventiveMaintenance AS preventiveMaintenance,ta.machine_status AS machineStatus, fl.name as functionallocation, fl.id as functionalLocationId,toi.name AS issue, toi.id AS typeOfIssueId, toi.sla AS sla,loc.name AS location, loc.id AS locationId, plnt.plant_name AS plant, plnt.id AS plantId, eq.name AS equipment, eq.id AS equipmentId, ts.name AS status, ts.id AS statusId, ta.ticket_id AS ticketId, t.submitted_by_name AS submittedby, e.emp_number AS engineerId, e.emp_number AS technicianId,tp.name AS priority, tp.id AS priorityId, tsev.name AS severity, tsev.id AS severityId, u.id AS uaerId, cs.name AS department, cs.id AS subDivisionId FROM ohrm_ticket t LEFT JOIN ohrm_functional_location fl ON fl.id = t.functional_location_id LEFT JOIN ohrm_type_of_issue toi ON toi.id = t.type_of_issue_id LEFT JOIN ohrm_location loc ON loc.id = t.location_id LEFT JOIN ohrm_plant plnt ON plnt.id = t.plant_id LEFT JOIN ohrm_equipment eq ON eq.id = t.equipment_id LEFT JOIN ohrm_ticket_status ts ON ts.id = t.status_id LEFT JOIN ohrm_ticket_acknowledgement_action_log ta ON ta.ticket_id = t.id LEFT JOIN hs_hr_employee e ON e.emp_number = ta.submitted_by_emp_number LEFT JOIN ohrm_user u ON u.id = ta.created_by_user_id LEFT JOIN ohrm_ticket_priority tp ON tp.id = t.priority_id LEFT JOIN ohrm_ticket_severity tsev ON tsev.id = t.severity_id LEFT JOIN ohrm_subunit cs ON cs.id = t.user_department_id WHERE ta.submitted_by_emp_number = $empNumber AND t.id NOT IN (select id from ohrm_ticket where status_id = 11 and submitted_by_emp_number != $empNumber) GROUP BY t.id
			ORDER BY `t`.`job_id`  DESC";


			$configDate = $this->dateFormat();

			$count=mysqli_query($this->conn, $query);

				if(mysqli_num_rows($count) > 0)
				{
					// $row=mysqli_fetch_all($count,MYSQLI_ASSOC);
					while($row = mysqli_fetch_assoc($count)) { 	

						$i=$i+1;
						
						if($row['preventiveMaintenance'] == 1){
							$data['sno']=$i;
							$data['ticketId']=$row['ticketId'];
						$data['job_id']=$row['job_id'];
						$data['subject']= 'PM - '.$row['equipment'];
						$data['issue']=$row['issue'];
						$data['location']=$row['location'];
						$data['plant']= $row['plant'];
						$data['department']=$row['department'];
						$data['functionallocation']=$row['functionallocation'];
						$data['equipment']= $row['equipment'];
						$data['submittedby']=$row['submittedby'];
						$data['submittedon']=date($configDate, strtotime( $row['submittedon'] )).' '.date('H:i:s', strtotime( $row['submittedon'] ));
						$data['status']= $row['status'];
	
						}else{
							$data['sno']=$i;
							$data['ticketId']=$row['ticketId'];
						$data['job_id']=$row['job_id'];
						$text = $row['subject'];
						$data['subject']=iconv(mb_detect_encoding($text), "UTF-8//IGNORE", $text);
						//$data['subject']= iconv('UTF-8', 'ASCII//IGNORE', utf8_encode($text));
						$data['issue']=$row['issue'];
						$data['location']=$row['location'];
						$data['plant']= $row['plant'];
						$data['department']=$row['department'];
						$data['functionallocation']=$row['functionallocation'];
						$data['equipment']= $row['equipment'];
						$data['submittedby']=$row['submittedby'];
						$data['submittedon']=date($configDate, strtotime( $row['submittedon'] )).' '.date('H:i:s', strtotime( $row['submittedon'] ));
						$data['status']= $row['status'];

						}				
						

						if ($row['statusId'] == 3) {
							$ticket_id = $row['ticketId'];

							$q1 = "SELECT s.id AS id,s.name AS name from ohrm_ticket_status s LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON l.status_id = s.id WHERE l.id = (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id GROUP BY ticket_id)"; 
							$res1=mysqli_query($this->conn, $q1);
							if(mysqli_num_rows($res1)>0)
							{
							   $row1 = mysqli_fetch_array($res1);
							   $id=$row1['id'];
							   $name=$row1['name'];
						    }

						    if($id == 4){
						    	$q2 = "SELECT e.emp_number AS emp_number, concat(e.emp_firstname,' ',e.emp_middle_name,' ',e.emp_lastname) AS emp_name from hs_hr_employee e LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON l.accepted_by = e.emp_number WHERE l.id = (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id GROUP BY ticket_id)"; 
							$res2=mysqli_query($this->conn, $q2);
							
							if(mysqli_num_rows($res2)>0)
							{
							   $row2 = mysqli_fetch_array($res2);
							   $emp_number=$row2['emp_number'];
							   $emp_name=$row2['emp_name'];
						    }

								$data['status']= $row['status'].'('.$name.' by '.$emp_name.')';
						    }else{

						    	$q2 = "SELECT e.emp_number AS emp_number, concat(e.emp_firstname,' ',e.emp_middle_name,' ',e.emp_lastname) AS emp_name from hs_hr_employee e LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON l.forward_to = e.emp_number WHERE l.id = (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id GROUP BY ticket_id)"; 
							$res2=mysqli_query($this->conn, $q2);
							
							if(mysqli_num_rows($res2)>0)
							{
							   $row2 = mysqli_fetch_array($res2);
							   $emp_number=$row2['emp_number'];
							   $emp_name=$row2['emp_name'];
						    }

						    	$data['status']= $row['status'].'('.$name.' to '.$emp_name.')';
						    }
						}
						
						$data1[] = $data;
					}
						$data['ticketDetails']=$data1;
						$data['status']=1;
							
				}else{
				$data['status']=0;
			}

		 }


		
		return $data;    
	}

    
	//ticketDetails
    function ticketDetailsOld($userIdPass)
	{
		$data= array();
		$userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$name = '';
		$emp_name = '';
		$i=0;

		
		
/*echo "<br/><br/>";
echo iconv(mb_detect_encoding($text), "UTF-8", $text);
		exit();*/

		/*$query = "SELECT t.id as ticketId,t.job_id as job_id,l.name as location,plnt.plant_name as plant,t.subject AS subject,sub.name as department,fnloc.name AS functionallocation, eqp.name as equipment, otyiss.name as 'issue', sta.name as status, slog.name as logStatus, t.submitted_by_name as submittedby, t.submitted_on as submittedon FROM ohrm_ticket t LEFT JOIN ohrm_location l ON l.id = t.location_id LEFT JOIN ohrm_ticket_acknowledgement_action_log log ON log.ticket_id = t.id LEFT JOIN ohrm_plant plnt ON t.plant_id = plnt.id LEFT JOIN ohrm_subunit sub ON t.user_department_id = sub.id LEFT JOIN ohrm_functional_location fnloc ON t.functional_location_id = fnloc.id LEFT JOIN ohrm_equipment eqp ON t.equipment_id = eqp.id LEFT JOIN ohrm_type_of_issue otyiss ON t.type_of_issue_id = otyiss.id LEFT JOIN ohrm_ticket_status sta ON t.status_id = sta.id LEFT JOIN ohrm_ticket_priority tktprty ON t.priority_id = tktprty.id LEFT JOIN ohrm_ticket_severity tktsvrty ON t.severity_id = tktsvrty.id LEFT JOIN hs_hr_employee emp ON t.reported_by = emp.emp_number LEFT JOIN hs_hr_employee empsub ON t.submitted_by_name =  emp.emp_number LEFT JOIN ohrm_user u
			ON u.id = $userIdPass LEFT JOIN hs_hr_emp_locations empl ON empl.emp_number = u.emp_number LEFT JOIN ohrm_ticket_status slog ON log.status_id = slog.id WHERE t.location_id = empl.location_id";*/



			/*SELECT t.job_id AS job_id, t.id AS ticketId, CONCAT('PM - ',eq.name) AS subject, t.submitted_on AS submittedon,ta.machine_status AS machineStatus, fl.name as functionallocation, fl.id as functionalLocationId,toi.name AS issue, toi.id AS typeOfIssueId, toi.sla AS sla,loc.name AS location, loc.id AS locationId, plnt.plant_name AS plant, plnt.id AS plantId, eq.name AS equipment, eq.id AS equipmentId, ts.name AS status, ts.id AS statusId, ta.ticket_id AS ticketId, t.submitted_by_name AS submittedby, e.emp_number AS engineerId, e.emp_number AS technicianId,tp.name AS priority, tp.id AS priorityId, tsev.name AS severity, tsev.id AS severityId, u.id AS uaerId, cs.name AS department, cs.id AS subDivisionId FROM ohrm_ticket t LEFT JOIN ohrm_functional_location fl ON fl.id = t.functional_location_id LEFT JOIN ohrm_type_of_issue toi ON toi.id = t.type_of_issue_id LEFT JOIN ohrm_location loc ON loc.id = t.location_id LEFT JOIN ohrm_plant plnt ON plnt.id = t.plant_id LEFT JOIN ohrm_equipment eq ON eq.id = t.equipment_id LEFT JOIN ohrm_ticket_status ts ON ts.id = t.status_id LEFT JOIN ohrm_ticket_acknowledgement_action_log ta ON ta.ticket_id = t.id LEFT JOIN hs_hr_employee e ON e.emp_number = ta.submitted_by_emp_number LEFT JOIN ohrm_user u ON u.id = ta.created_by_user_id LEFT JOIN ohrm_ticket_priority tp ON tp.id = t.priority_id LEFT JOIN ohrm_ticket_severity tsev ON tsev.id = t.severity_id LEFT JOIN ohrm_subunit cs ON cs.id = t.user_department_id WHERE ta.submitted_by_emp_number = 2 AND t.id NOT IN (select id from ohrm_ticket where status_id = 11 and submitted_by_emp_number != 2) GROUP BY t.id
			ORDER BY `t`.`job_id`  DESC*/

			$query = "SELECT t.job_id AS job_id, t.id AS ticketId, t.subject AS subject, t.submitted_on AS submittedon,t.is_PreventiveMaintenance AS preventiveMaintenance,ta.machine_status AS machineStatus, fl.name as functionallocation, fl.id as functionalLocationId,toi.name AS issue, toi.id AS typeOfIssueId, toi.sla AS sla,loc.name AS location, loc.id AS locationId, plnt.plant_name AS plant, plnt.id AS plantId, eq.name AS equipment, eq.id AS equipmentId, ts.name AS status, ts.id AS statusId, ta.ticket_id AS ticketId, t.submitted_by_name AS submittedby, e.emp_number AS engineerId, e.emp_number AS technicianId,tp.name AS priority, tp.id AS priorityId, tsev.name AS severity, tsev.id AS severityId, u.id AS uaerId, cs.name AS department, cs.id AS subDivisionId FROM ohrm_ticket t LEFT JOIN ohrm_functional_location fl ON fl.id = t.functional_location_id LEFT JOIN ohrm_type_of_issue toi ON toi.id = t.type_of_issue_id LEFT JOIN ohrm_location loc ON loc.id = t.location_id LEFT JOIN ohrm_plant plnt ON plnt.id = t.plant_id LEFT JOIN ohrm_equipment eq ON eq.id = t.equipment_id LEFT JOIN ohrm_ticket_status ts ON ts.id = t.status_id LEFT JOIN ohrm_ticket_acknowledgement_action_log ta ON ta.ticket_id = t.id LEFT JOIN hs_hr_employee e ON e.emp_number = ta.submitted_by_emp_number LEFT JOIN ohrm_user u ON u.id = ta.created_by_user_id LEFT JOIN ohrm_ticket_priority tp ON tp.id = t.priority_id LEFT JOIN ohrm_ticket_severity tsev ON tsev.id = t.severity_id LEFT JOIN ohrm_subunit cs ON cs.id = t.user_department_id WHERE ta.submitted_by_emp_number = $empNumber AND t.id NOT IN (select id from ohrm_ticket where status_id = 11 and submitted_by_emp_number != $empNumber) GROUP BY t.id
			ORDER BY `t`.`job_id`  DESC";


		/*$query = "SELECT t.job_id AS jobId, t.functional_location_id AS funLocId, t.id AS id, t.subject AS subject, t.submitted_on AS calFromDate, t.submitted_on AS calToDate, t.submitted_on AS createdOn, ta.machine_status AS machineStatus, fl.name as functionalLocation, fl.id as functionalLocationId,
                    toi.name AS typeOfIssue, toi.id AS typeOfIssueId, toi.sla AS sla,
                    loc.name AS location, loc.id AS locationId,
                    plnt.plant_name AS plantName, plnt.id AS plantId,
                    eq.name AS equipment, eq.id AS equipmentId,
                    ts.name AS status, ts.id AS statusId,
                    ta.ticket_id AS ticketId, t.submitted_by_name AS submittedByName, e.emp_number AS engineerId, e.emp_number AS technicianId,
                    tp.name AS priority, tp.id AS priorityId,
                    tsev.name AS severity, tsev.id AS severityId,
                    u.id AS uaerId,
                    msr.id AS scheduleId, msr.maintenance_type_id AS maintenanceType,
                    mt.id AS maintenanceId, mt.name AS maintenanceName,
                    cs.name AS subDivision, cs.id AS subDivisionId
				FROM ohrm_ticket t
                LEFT JOIN ohrm_functional_location fl ON fl.id = t.functional_location_id 
                LEFT JOIN ohrm_type_of_issue toi ON toi.id = t.type_of_issue_id 
                LEFT JOIN  ohrm_location loc ON loc.id = t.location_id 
                LEFT JOIN  ohrm_plant plnt ON plnt.id = t.plant_id 
                LEFT JOIN  ohrm_equipment eq ON eq.id = t.equipment_id 
                LEFT JOIN  ohrm_ticket_status ts ON ts.id = t.status_id
                LEFT JOIN  ohrm_ticket_acknowledgement_action_log ta ON ta.ticket_id = t.id
                LEFT JOIN  hs_hr_employee e ON e.emp_number = ta.submitted_by_emp_number
                LEFT JOIN  ohrm_user u ON u.id = ta.created_by_user_id 
                LEFT JOIN  ohrm_ticket_priority tp ON tp.id = t.priority_id
                LEFT JOIN  ohrm_ticket_severity tsev ON tsev.id = t.severity_id
                LEFT JOIN ohrm_subunit cs ON cs.id = t.user_department_id
                LEFT JOIN ohrm_maintenance_schedule msr ON msr.ticket_id = t.id
                LEFT JOIN ohrm_maintenance_type mt ON mt.id = msr.maintenance_type_id
                GROUP BY t.id
				ORDER BY `t`.`job_id`  DESC";*/

		$configDate = $this->dateFormat();

			$count=mysqli_query($this->conn, $query);

				if(mysqli_num_rows($count) > 0)
				{
					// $row=mysqli_fetch_all($count,MYSQLI_ASSOC);
					while($row = mysqli_fetch_assoc($count)) { 	

						$i=$i+1;
						
						if($row['preventiveMaintenance'] == 1){
							$data['sno']=$i;
							$data['ticketId']=$row['ticketId'];
						$data['job_id']=$row['job_id'];
						$data['subject']= 'PM - '.$row['equipment'];
						$data['issue']=$row['issue'];
						$data['location']=$row['location'];
						$data['plant']= $row['plant'];
						$data['department']=$row['department'];
						$data['functionallocation']=$row['functionallocation'];
						$data['equipment']= $row['equipment'];
						$data['submittedby']=$row['submittedby'];
						$data['submittedon']=date($configDate, strtotime( $row['submittedon'] )).' '.date('H:i:s', strtotime( $row['submittedon'] ));
						$data['status']= $row['status'];
	
						}else{
							$data['sno']=$i;
							$data['ticketId']=$row['ticketId'];
						$data['job_id']=$row['job_id'];
						$text = $row['subject'];
						$data['subject']=iconv(mb_detect_encoding($text), "UTF-8//IGNORE", $text);
						//$data['subject']= iconv('UTF-8', 'ASCII//IGNORE', utf8_encode($text));
						$data['issue']=$row['issue'];
						$data['location']=$row['location'];
						$data['plant']= $row['plant'];
						$data['department']=$row['department'];
						$data['functionallocation']=$row['functionallocation'];
						$data['equipment']= $row['equipment'];
						$data['submittedby']=$row['submittedby'];
						$data['submittedon']=date($configDate, strtotime( $row['submittedon'] )).' '.date('H:i:s', strtotime( $row['submittedon'] ));
						$data['status']= $row['status'];

						}				
						

						if ($row['statusId'] == 3) {
							$ticket_id = $row['ticketId'];

							$q1 = "SELECT s.id AS id,s.name AS name from ohrm_ticket_status s LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON l.status_id = s.id WHERE l.id = (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id GROUP BY ticket_id)"; 
							$res1=mysqli_query($this->conn, $q1);
							if(mysqli_num_rows($res1)>0)
							{
							   $row1 = mysqli_fetch_array($res1);
							   $id=$row1['id'];
							   $name=$row1['name'];
						    }

						    if($id == 4){
						    	$q2 = "SELECT e.emp_number AS emp_number, concat(e.emp_firstname,' ',e.emp_middle_name,' ',e.emp_lastname) AS emp_name from hs_hr_employee e LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON l.accepted_by = e.emp_number WHERE l.id = (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id GROUP BY ticket_id)"; 
							$res2=mysqli_query($this->conn, $q2);
							
							if(mysqli_num_rows($res2)>0)
							{
							   $row2 = mysqli_fetch_array($res2);
							   $emp_number=$row2['emp_number'];
							   $emp_name=$row2['emp_name'];
						    }

								$data['status']= $row['status'].'('.$name.' by '.$emp_name.')';
						    }else{

						    	$q2 = "SELECT e.emp_number AS emp_number, concat(e.emp_firstname,' ',e.emp_middle_name,' ',e.emp_lastname) AS emp_name from hs_hr_employee e LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON l.forward_to = e.emp_number WHERE l.id = (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id GROUP BY ticket_id)"; 
							$res2=mysqli_query($this->conn, $q2);
							
							if(mysqli_num_rows($res2)>0)
							{
							   $row2 = mysqli_fetch_array($res2);
							   $emp_number=$row2['emp_number'];
							   $emp_name=$row2['emp_name'];
						    }

						    	$data['status']= $row['status'].'('.$name.' to '.$emp_name.')';
						    }
						}
						
						$data1[] = $data;
					}
						$data['ticketDetails']=$data1;
						$data['status']=1;
							
				}else{
				$data['status']=0;
			}
		return $data;    
	}

	
function resolutionDetails($ticket_id)
	{
		$data= array();
		$configDate = $this->dateFormat();

		$query="SELECT ticket_id,accepted_by,forward_from,forward_to,comment,submitted_by_name as created_by,submitted_on FROM ohrm_ticket_acknowledgement_action_log
		 WHERE ticket_id = $ticket_id";
		$count=mysqli_query($this->conn, $query);
		//$EmpName = $this->getEmpnameByEmpNumber($emp_number);
		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
				
			do{
				$data['ticket_id']=$row['ticket_id'];
				if($row['accepted_by']){

					$res = 'Accepted By '.$this->getEmpnameByEmpNumber($row['accepted_by']);
					$data['submitted_on']=date($configDate, strtotime( $row['submitted_on'] )).' '.date('H:i', strtotime( $row['submitted_on'] ));
					$data['resolution']=$res;
					$data['comment']=$row['comment'];

					$data1[] = $data;
				}else if($row['forward_from'] && $row['forward_to']){

					$res = 'Forwarded from '.$this->getEmpnameByEmpNumber($row['forward_from']).' to '.$this->getEmpnameByEmpNumber($row['forward_to']);
					$data['submitted_on']=date($configDate, strtotime( $row['submitted_on'] )).' '.date('H:i', strtotime( $row['submitted_on'] ));
					$data['resolution']=$res;
					$data['comment']=$row['comment'];

					$data1[] = $data;
				}else{
					$res = 'Created By '.$row['created_by'];
					$data['submitted_on']=date($configDate, strtotime( $row['submitted_on'] )).' '.date('H:i', strtotime( $row['submitted_on'] ));
					$data['resolution']=$res;
					$data['comment']=$row['comment'];

					$data1[] = $data;
				}
			}while($row = mysqli_fetch_assoc($count)); 					
			$data['resolutionDetails']=$data1;
			$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
					
	}



	//ticketIdDetails
    function ticketIdDetails($ticket_id)
	{
		$data= array();
		//getUser Details

		$query ="SELECT is_PreventiveMaintenance from ohrm_ticket where id = $ticket_id";
		$query1="SELECT t.id,t.job_id as job_id,t.location_id,loc.name as location_name,t.plant_id,plnt.plant_name as plantname,t.subject, t.user_department_id as department_id,sub.name as department_name,t.functional_location_id as functionlocation_id,fnloc.name AS functionallocation_name, t.equipment_id as equipment_id, eqp.name as equipment_name, t.type_of_issue_id as typeofissue_id,otyiss.name as typeofissue,sta.id AS status_id, sta.name as status, t.priority_id as priority_id,tktprty.name as ticketpriority,t.severity_id as severityId,tktsvrty.name as ticketseverity,t.sla as sla, CONCAT(emp.emp_firstname,emp.emp_lastname) AS reportedby,t.reported_on as reporteddate, CONCAT(emp.emp_firstname,emp.emp_lastname) AS submittedby,t.submitted_on as submitteddate, t.submitted_by_emp_number AS submittedByEmp ,t.is_PreventiveMaintenance, log.accepted_by,log.forward_from,log.forward_to,log.created_by_user_id,
        log.comment,log.machine_status,log.root_cause_id,rc.name as root_cause,log.response_id FROM ohrm_ticket t 
		LEFT JOIN ohrm_ticket_acknowledgement_action_log log ON log.ticket_id = t.id 
  		LEFT join ohrm_location loc ON t.location_id = loc.id
        LEFT JOIN ohrm_plant plnt ON t.plant_id = plnt.id
        LEFT JOIN ohrm_subunit sub ON t.user_department_id = sub.id
        LEFT JOIN ohrm_functional_location fnloc ON t.functional_location_id = fnloc.id
        LEFT JOIN ohrm_equipment eqp ON t.equipment_id = eqp.id
        LEFT JOIN ohrm_type_of_issue otyiss ON t.type_of_issue_id = otyiss.id
        LEFT JOIN ohrm_ticket_status sta ON t.status_id = sta.id
        LEFT JOIN ohrm_ticket_priority tktprty ON t.priority_id = tktprty.id
        LEFT JOIN ohrm_ticket_severity tktsvrty ON t.severity_id = tktsvrty.id
        LEFT JOIN hs_hr_employee emp ON t.reported_by = emp.emp_number
        LEFT JOIN hs_hr_employee empsub ON t.submitted_by_name =  emp.emp_number
        LEFT JOIN ohrm_root_cause rc ON rc.id = log.root_cause_id
		 WHERE log.ticket_id = $ticket_id ORDER BY log.id DESC LIMIT 1";

		 $configDate = $this->dateFormat();

		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0){
			$row1=mysqli_fetch_assoc($count);
			$data1['is_PreventiveMaintenance']=$row1['is_PreventiveMaintenance'];

				if($data1['is_PreventiveMaintenance'] == "1"){
				
					$query2 ="SELECT p.ticket_id as ticketId,  c.name as checkList , g.name as groupName, n.name AS chklstname,p.value as value,p.comment as comment FROM ohrm_preventive_check_list p LEFT JOIN ohrm_ticket t ON t.id = p.ticket_id LEFT JOIN ohrm_check_list_name n ON n.id = p.check_list_item_id LEFT JOIN ohrm_group g ON g.id = n.group_id LEFT JOIN ohrm_check_list c ON c.id = g.check_list_id WHERE p.ticket_id = $ticket_id";
					$count=mysqli_query($this->conn, $query2);
					
					if(mysqli_num_rows($count) > 0)
						{
						while($row4 = mysqli_fetch_assoc($count)) { 						
						$data4['ticketId']=$row4['ticketId'];
						$data4['checkList']=$row4['checkList'];
						$data4['groupName']=$row4['groupName'];
						$data4['chklstname']=$row4['chklstname'];
						$data4['value']=$row4['value'];
						$data4['comment']=$row4['comment'];
						
						$data2[] = $data4;
						}
						$data3['checkLists']=$data2;
						
					}else{
						$data3['checkLists']=[];
					}
				
				}
							$count1=mysqli_query($this->conn, $query1);
							if(mysqli_num_rows($count1) > 0)
							{
										$row2=mysqli_fetch_assoc($count1);					
											$data['id']				=$row2['id'];
											$data['job_id']			=$row2['job_id'];
											$data['location_id']	=$row2['location_id'];
											$data['location_name']	=$row2['location_name'];
											$data['plant_id']		=$row2['plant_id'];
											$data['plant_name']		= $row2['plantname'];

											if($data1['is_PreventiveMaintenance'] == "1"){
													$data['subject']		='PM - '.$row2['equipment_name'];

											}else{
													//$data['subject']		=$row2['subject'];
													$text = $row2['subject'];
													$data['subject']=iconv(mb_detect_encoding($text), "UTF-8//IGNORE", $text);

											}
											$data['department_id']	=$row2['department_id'];
											$data['department_name']=$row2['department_name'];

											$funLoc = $this->subfunctionalLocations($row2['functionlocation_id']);
											if($funLoc['status'] == 1){
												$data['functionlocation_id']=$funLoc['id'];
												$data['functionallocation_name']=$funLoc['name'];
												$data['subfunctionlocation_id']=$row2['functionlocation_id'];
												$data['subfunctionallocation_name']=$row2['functionallocation_name'];
											}else{
												$data['functionlocation_id']=$row2['functionlocation_id'];
												$data['functionallocation_name']=$row2['functionallocation_name'];
												$data['subfunctionlocation_id']=0;
												$data['subfunctionallocation_name']='';
											}
											$data['equipment_id']	=$row2['equipment_id'];
											$data['equipment_name']	= $row2['equipment_name'];
											$data['typeofissue_id']	=$row2['typeofissue_id'];
											$data['typeofissue']	=$row2['typeofissue'];
											$data['status_id']		= $row2['status_id'];
											$data['status_name']	= $row2['status'];
											$data['priority_id']	= $row2['priority_id'];
											$data['ticketpriority']	=$row2['ticketpriority'];
											$data['severityId']	= $row2['severityId'];
											$data['ticketseverity']	=$row2['ticketseverity'];
											$data['sla']			=$row2['sla'];
											$data['reportedby']		=$row2['reportedby'];
											$data['reporteddate']	=date($configDate, strtotime( $row2['reporteddate'] )).' '.date('H:i:s', strtotime( $row2['reporteddate'] ));
											$data['submittedby']	=$row2['submittedby'];
											$data['submittedByEmp']	=$row2['submittedByEmp'];
											$data['submitteddate']	= date($configDate, strtotime( $row2['submitteddate'] )).' '.date('H:i:s', strtotime( $row2['submitteddate'] ));
											$data['is_PreventiveMaintenance']= $row2['is_PreventiveMaintenance'];
											$data['accepted_by']	=$row2['accepted_by'];
											$data['forward_from']	= $row2['forward_from'];
											$data['forward_to']		= $row2['forward_to'];
											$data['created_by_user_id']= $row2['created_by_user_id'];
											$data['comment']		= $row2['comment'];
											$data['machine_status']	= $row2['machine_status'];
											$data['root_cause_id']	= $row2['root_cause_id'];
											$data['root_cause']	= $row2['root_cause'];
											$data['response_id']	= $row2['response_id'];
							}
										if($data1['is_PreventiveMaintenance'] == 1){
											$data['checkLists'] = $data3['checkLists'];
											$data['ticket_Details']=$data ;
										}else{
											$data['ticket_Details']=$data;
										}
												
										$data['status']=1;
		}else{
				$data['status']=0;
		}
		return $data;    
	}

	//Sub Functional Location

	function subfunctionalLocations($id){
		$data= array();
		$query="SELECT * FROM ohrm_functional_location where id = $id";

		/*echo $query;
		exit();*/
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
			$parent_id = $row['parent_id'];
		}	

		$query="SELECT * FROM ohrm_functional_location where id = $parent_id";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			$row1=mysqli_fetch_assoc($count);
			$row1['status']=1;
		}else{
			$row1['status']=0;
		}

		return $row1; 
	}

	//checklist
    function checklist($equipmentId)
	{

		$equipmentList = $this->equipmentbyId($equipmentId);
		$category_type_id = $equipmentList['category_type_id'];
		$category_sub_type_id = $equipmentList['category_sub_type_id'];
		$equipment_type_id = $equipmentList['equipment_type_id'];
		$equipment_sub_type_id = $equipmentList['equipment_sub_type_id'];


		$data= array();
		$query="SELECT l.id,l.name AS list_name,g.id AS group_id, g.name AS group_name,n.id AS chklstid,n.name AS chklstname FROM ohrm_check_list l LEFT JOIN ohrm_group g ON g.check_list_id = l.id LEFT JOIN ohrm_check_list_name n ON n.group_id = g.id WHERE n.is_deleted = 0 AND l.is_deleted = 0 AND g.is_deleted = 0";
		if($category_type_id)
			$query .= " AND l.category_type_id = $category_type_id";
		if($category_sub_type_id)
			$query .= " AND l.category_sub_type_id = $category_sub_type_id";
		if($equipment_type_id)
			$query .= " AND l.equipment_type_id = $equipment_type_id";
		if($equipment_sub_type_id)
			$query .= " AND l.equipment_sub_type_id = $equipment_sub_type_id";

		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
					while($row = mysqli_fetch_assoc($count)) { 						
						$data['id']=$row['id'];
						$data['list_name']=$row['list_name'];
						$data['group_id']=$row['group_id'];
						$data['group_name']=$row['group_name'];
						$data['chklstid']=$row['chklstid'];
						$data['chklstname']= $row['chklstname'];
						
						$data1[] = $data;
					}
						$data['checklist']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}

//checklist
    function equipmentbyId($equipment_id)
	{
		$data= array();
		$query="SELECT * FROM ohrm_equipment e WHERE id= $equipment_id";

		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			$row = mysqli_fetch_array($count);						
			$data['id']=$row['id'];
			$data['name']=$row['name'];
			$data['category_type_id']=$row['category_type_id'];
			$data['category_sub_type_id']=$row['category_sub_type_id'];
			$data['equipment_type_id']=$row['equipment_type_id'];
			$data['equipment_sub_type_id']= $row['equipment_sub_type_id'];
			$data['location_id']=$row['location_id'];
			$data['parent_id']= $row['parent_id'];
			$data['functional_location_id']=$row['functional_location_id'];
			$data['plant_id']= $row['plant_id'];
			$data['department_id']=$row['department_id'];
			$data['status']= $row['status'];
			$data['asset_number']= $row['asset_number'];
			$data['cost_center_id']= $row['cost_center_id'];
			$data['acquistn_value']= $row['acquistn_value'];
			$data['acquistion_date']= $row['acquistion_date'];
			$data['manufacturer']= $row['manufacturer'];
			$data['model_number']= $row['model_number'];
			$data['manufacturer_country']= $row['manufacturer_country'];
			$data['manufacturer_part_number']= $row['manufacturer_part_number'];
			$data['manufacturer_serial_number']= $row['manufacturer_serial_number'];
			$data['reference_equipment_id']= $row['reference_equipment_id'];
			$data['level']= $row['level'];
			$data['is_assembly']= $row['is_assembly'];
			$data['reportable']= $row['reportable'];
			$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}
	//routecause
    function routecause($type_of_issue_id)
	{
		$data= array();
		$query="SELECT * FROM ohrm_root_cause WHERE type_of_issue_id = $type_of_issue_id AND is_deleted = 0";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
					$row=mysqli_fetch_assoc($count);
					do { 						
						$data['id']=$row['id'];
						$data['name']=$row['name'];
						$data['type_of_issue_id']=$row['type_of_issue_id'];
						$data['is_deleted']=$row['is_deleted'];
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count));
						$data['routecause']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}

	//dateformat configuration
    function dateFormat()
	{
		$data= array();
		$query="SELECT * FROM hs_hr_config WHERE `hs_hr_config`.`key` = 'admin.localization.default_date_format'";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
			$configDate = $row['value'];

		}	

		return $configDate;   
	}


	function equipmentlist($location_id,$plant_id/*,$department_id*/,$functional_location_id)
	{
		$data= array();
		// $query="SELECT id,name FROM `ohrm_equipment` WHERE functional_location_id = $functional_location_id and department_id = $department_id and location_id = $location_id and plant_id = $plant_id ORDER BY name ASC";
		$query="SELECT id,name FROM `ohrm_equipment` WHERE functional_location_id = $functional_location_id and location_id = $location_id and plant_id = $plant_id ORDER BY name ASC";
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
						$row=mysqli_fetch_assoc($count);
					do{ 
						$data['id']=$row['id'];
						$data['name']=$row['name'];	
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count)); 					
						$data['equipmentlist']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}


	//location
    function location()
	{
		$data= array();
		$query="SELECT id, name FROM ohrm_location";
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
					$row=mysqli_fetch_assoc($count);
					do{ 

						$data['id']=$row['id'];
						$data['name']=$row['name'];	
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count)); 				
						$data['location']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}



	//equipmentiddetails
    function equipmentiddetails($eqipmentid)
	{
		$data= array();
		$query="SELECT id,name FROM ohrm_equipment WHERE id = $eqipmentid";
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
			$data['id']=$row['id'];
			$data['name']=$row['name'];					
			$data['equipmentiddetails']=$data;
			$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}

	//typeofissue
    function typeofissue($eqipmentid)
	{
		$data= array();
		$query="SELECT i.id,i.name FROM ohrm_type_of_issue i LEFT JOIN ohrm_equipment e ON e.equipment_type_id = i.equipment_type_id  LEFT JOIN ohrm_category_type ct ON ct.id = e.category_type_id LEFT JOIN ohrm_category_sub_type cst ON cst.id = e.category_sub_type_id LEFT JOIN ohrm_equipment_type et ON et.id = i.equipment_type_id LEFT JOIN ohrm_equipment_sub_type est ON est.id = i.equipment_sub_type_id WHERE e.id = $eqipmentid AND i.category_type_id = e.category_type_id AND i.category_sub_type_id = e.category_sub_type_id AND i.equipment_type_id = e.equipment_type_id AND i.equipment_sub_type_id = e.equipment_sub_type_id ORDER by name ASC";
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
			do{ 
				$data['id']=$row['id'];
				$data['name']=$row['name'];		
				$data1[]= $data;
			}while($row = mysqli_fetch_assoc($count));
								
			$data['typeofissue']=$data1;
			$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;       
	}

	//plantlst
    function plantlst($locationid)
	{
		$data= array();
		$query="SELECT id, plant_name FROM ohrm_plant WHERE location_id = $locationid";
		$count=mysqli_query($this->conn, $query);
		
		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
			$data['id']=$row['id'];
			$data['plant_name']=$row['plant_name'];					
			$data['plantlst']=$data;
			$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;     
	}

	
	//tktcnvrstns
    function tktcnvrstns($ticketid)
	{
		$data= array();
		$query="SELECT cnvs.emp_number as emp_number,CONCAT(emp.emp_firstname,emp.emp_lastname) as empname, cnvs.ticket_id as ticket_id, cnvs.date_time as date_time, cnvs.comments as comments FROM ohrm_ticket_conversations cnvs JOIN hs_hr_employee emp ON emp.emp_number = cnvs.emp_number WHERE ticket_id = $ticketid";
		$count=mysqli_query($this->conn, $query);
		
		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
			$data['emp_number']=$row['emp_number'];
			$data['empname']=$row['empname'];	
			$data['ticket_id']=$row['ticket_id'];
			$data['date_time']=$row['date_time'];
			$data['comments']=$row['comments'];							
			$data['tktcnvrstns']=$data;
			$data['status']=1;
		}else{
				$data['status']=0;
			}
		return $data;     
	}

	//unitmeasure
    function unitmeasure()
	{
		$data= array();
		$query="SELECT * from ohrm_product_unit WHERE is_deleted = 0";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			while($row = mysqli_fetch_assoc($count)) { 						
				$data['id']=$row['id'];
				$data['name']=$row['name'];	
				$data['name_plrl']=$row['name_plrl'];
				$data['isDeleted']=$row['is_deleted'];
				$data1[] = $data;
			}
			$data['unitmeasure']=$data1;
			$data['status']=1;
							
		}else{
			$data['status']=0;
		}
		return $data;  	
	}

	//parts
    function parts()
	{

		$data= array();
		$query="SELECT * from ohrm_product_unit WHERE is_deleted =0 ";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
			do{ 
				$data['id']=$row['id'];
				$data['name']=$row['name'];
				$data['name_plrl']=$row['name_plrl'];
				$data['is_deleted']=$row['is_deleted'];
				$data1[]= $data;
			}while($row = mysqli_fetch_assoc($count)) ;
			$data['parts']=$data1;
			$data['status']=1;			
		}else{
				$data['status']=0;
			}
		return $data;  	
	}

	//partid($ticket_id)
    function partid($ticket_id)
	{
		$data= array();
		$query="SELECT p.*,u.name as unit_name,u.name_plrl as unit_name_plrl from ohrm_parts p LEFT JOIN ohrm_product_unit u ON u.id = p.unit_id WHERE ticket_id = $ticket_id";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
			do{ 
				$data['id']=$row['id'];
				$data['ticket_id']=$row['ticket_id'];	
				$data['emp_number']=$row['emp_number'];
				$data['part_name']=$row['part_name'];
				$data['part_number']=$row['part_number'];
				$data['quantity']=$row['quantity'];
				$data['unit_id']=$row['unit_id'];
				$data['unit_name']=$row['unit_name'];
				$data['unit_name_plrl']=$row['unit_name_plrl'];
				$data1[]= $data;
			}while($row = mysqli_fetch_assoc($count)) ;
			$data['partdetail']=$data1;
			$data['status']=1;
		}else{
				$data['status']=0;
		}
		return $data;  	
	}

function getResolvedAction($ticket_id)
{
	$query = "SELECT MAX(id) AS id,COUNT(*) AS count FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id AND status_id = 14";
	$count=mysqli_query($this->conn, $query);

	if(mysqli_num_rows($count) > 0)
		{
			$row = mysqli_fetch_assoc($count);
				$datacount=$row['count'];		
		}
			

	return $datacount;

}
	/*//ticketstatus
    function ticketstatus($user_id,$user_role_id,$ticket_id,$response_id)
	{
		$data= array();

		$userDetails = $this->getUserRoleByUserId($user_id);
		$empNumber = $userDetails['empNumber'];

		
	
		$assgn = array(2,3);

		if($user_role_id == 11 && $response_id == 2){
			$empresult=$this->engLists();

										for ($i=0; $i < sizeof($empresult['englist']) ; $i++) { 
							        	$engList[] = $empresult['englist'][$i];
							        	//to convert Array into string the following implode method is used
							        	$engLists = implode(',', $engList);
							        }
		$countquery = "SELECT COUNT(*) as count FROM ohrm_ticket_acknowledgement_action_log WHERE forward_from IN ($engLists) AND ticket_id = $ticket_id";

		$rowcount = mysqli_query($this->conn, $countquery);

	if(mysqli_num_rows($rowcount) > 0)
		{
			$row = mysqli_fetch_assoc($rowcount);
				$datacount=$row['count'];		
		}

		//echo $datacount;
		//exit();

		if($datacount > 1)

		{
			$maxquery = "SELECT * FROM ohrm_ticket_acknowledgement_action_log WHERE id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE forward_from IN ($engLists) AND ticket_id = $ticket_id)";

				$rowcount1 = mysqli_query($this->conn, $maxquery);

				if(mysqli_num_rows($rowcount1) > 0)
					{
						$row1 = mysqli_fetch_assoc($rowcount1);
							$datacount1 = $row1['forward_from'];	
							//echo '$datacount1'.' '.$datacount1;	
					}


						$minquery = "SELECT * FROM ohrm_ticket_acknowledgement_action_log WHERE id IN (SELECT MIN(id) FROM ohrm_ticket_acknowledgement_action_log WHERE forward_from IN ($engLists) AND ticket_id = $ticket_id)";

						$rowcount2 = mysqli_query($this->conn, $minquery);

							if(mysqli_num_rows($rowcount2) > 0)
								{
									$row2 = mysqli_fetch_assoc($rowcount2);
										$datacount2=$row2['forward_from'];
										//echo 'datacount2'. ' '.$datacount2;		
								}


								    if($datacount1 == $empNumber){

								    	$query="SELECT * FROM `ohrm_ticket_status` WHERE id IN (2,14)";

								    }else{
								    	$query="SELECT * FROM `ohrm_ticket_status` WHERE id IN (3,10)";
								    }

		}else{
			$query="SELECT * FROM `ohrm_ticket_status` WHERE id IN (3,10)";
		}

		}
		if($user_role_id == 11 && $response_id == 1){
			$reslv = $this->getResolvedAction($ticket_id);
			$status = $reslv > 0 ? array(3,10) : array(3,10);
			$tktst = implode(',', $status);
			$query="SELECT * FROM `ohrm_ticket_status` WHERE id IN ($tktst)";

			
		}

		if($user_role_id == 11 && in_array($response_id, $assgn, TRUE) || $user_role_id == 12 && $response_id == 3){
			$query="SELECT * FROM `ohrm_ticket_status` WHERE id IN (2)";
		}

		if($user_role_id == 12 && $response_id == 1){
			$query="SELECT * FROM `ohrm_ticket_status` WHERE id IN (3,14)";
		}

		if($user_role_id == 12 && $response_id == 2){
			$query="SELECT * FROM `ohrm_ticket_status` WHERE id IN (2,7,8,9)";
		}

		if($user_role_id == 10 && $response_id == 1 || $user_role_id == 2 && $response_id == 1){
			$query="SELECT * FROM `ohrm_ticket_status` WHERE id IN (5,6)";
		}



		
		
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			while($row = mysqli_fetch_assoc($count)) { 
				$data['id']=$row['id'];
				$data['name']=$row['name'];	
				$data1[]= $data;
			}
			$data['ticketstatus']=$data1;
			$data['status']=1;
		}else{
				$data['status']=0;
		}
		return $data;  	
	}*/


		//ticketstatus
    function ticketstatus($user_id,$user_role_id,$ticket_id,$response_id)
	{
		$data= array();

		$userDetails = $this->getUserRoleByUserId($user_id);
		$empNumber = $userDetails['empNumber'];

		
	
		$assgn = array(2,3);

		
		if($user_role_id == 11 && $response_id == 1){
			$reslv = $this->getResolvedAction($ticket_id);
			$status = $reslv > 0 ? array(3,10) : array(3,10);
			$tktst = implode(',', $status);
			$query="SELECT * FROM `ohrm_ticket_status` WHERE id IN ($tktst)";

			
		}

		if($user_role_id == 11 && in_array($response_id, $assgn, TRUE) || $user_role_id == 12 && $response_id == 3){


			$empresult=$this->engLists();

										for ($i=0; $i < sizeof($empresult['englist']) ; $i++) { 
							        	$engList[] = $empresult['englist'][$i];
							        	//to convert Array into string the following implode method is used
							        	$engLists = implode(',', $engList);
							        }
		$countquery = "SELECT COUNT(*) as count FROM ohrm_ticket_acknowledgement_action_log WHERE forward_from IN ($engLists) AND ticket_id = $ticket_id";

		$rowcount = mysqli_query($this->conn, $countquery);

	if(mysqli_num_rows($rowcount) > 0)
		{
			$row = mysqli_fetch_assoc($rowcount);
				$datacount=$row['count'];		
		}

		//echo $datacount;
		//exit();

		if($datacount > 1 && $response_id == 2 )

		{ 
			$maxquery = "SELECT * FROM ohrm_ticket_acknowledgement_action_log WHERE id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE forward_from IN ($engLists) AND ticket_id = $ticket_id)";

				$rowcount1 = mysqli_query($this->conn, $maxquery);

				if(mysqli_num_rows($rowcount1) > 0)
					{

						$row1 = mysqli_fetch_assoc($rowcount1);
							$datacount1 = $row1['forward_from'];	
							//echo '$datacount1'.' '.$datacount1;	
					}


						$minquery = "SELECT * FROM ohrm_ticket_acknowledgement_action_log WHERE id IN (SELECT MIN(id) FROM ohrm_ticket_acknowledgement_action_log WHERE forward_from IN ($engLists) AND ticket_id = $ticket_id)";

						$rowcount2 = mysqli_query($this->conn, $minquery);

							if(mysqli_num_rows($rowcount2) > 0)
								{
									$row2 = mysqli_fetch_assoc($rowcount2);
										$datacount2=$row2['forward_from'];
										//echo 'datacount2'. ' '.$datacount2;		
								}

									// echo $datacount1.' '.$empNumber;
									// exit();

								    if($datacount1 == $empNumber){

								    	$query="SELECT * FROM `ohrm_ticket_status` WHERE id IN (2,14)";

								    }else{
								    	$query="SELECT * FROM `ohrm_ticket_status` WHERE id IN (2)";
								    }

		}else{
			$query="SELECT * FROM `ohrm_ticket_status` WHERE id IN (2)";
		}
			
		}

		if($user_role_id == 12 && $response_id == 1){
			$query="SELECT * FROM `ohrm_ticket_status` WHERE id IN (3,14)";
		}

		if($user_role_id == 12 && $response_id == 2){
			$query="SELECT * FROM `ohrm_ticket_status` WHERE id IN (2,7,8,9)";
		}

		if($user_role_id == 10 && $response_id == 1 || $user_role_id == 2 && $response_id == 1){
			$query="SELECT * FROM `ohrm_ticket_status` WHERE id IN (5,6)";
		}



		
		
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			while($row = mysqli_fetch_assoc($count)) { 
				$data['id']=$row['id'];
				$data['name']=$row['name'];	
				$data1[]= $data;
			}
			$data['ticketstatus']=$data1;
			$data['status']=1;
		}else{
				$data['status']=0;
		}
		return $data;  	
	}

	//ticketpriority
    function ticketpriority($typeOfIssueId)
	{	
		$priorityId = $this->getPriorityByTypeOfIssueId($typeOfIssueId);
		$data= array();
		$query="SELECT * from ohrm_ticket_priority where id = $priorityId";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			while($row = mysqli_fetch_assoc($count)) { 
				$data['id']=$row['id'];
				$data['name']=$row['name'];	
				$data1[]= $data;
			}
			$data['ticketpriority']=$data1;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;  	
	}

	//ticketseverity
    function ticketseverity()
	{
		$data= array();
		$query="SELECT id,name FROM `ohrm_ticket_severity` WHERE is_deleted = 0 ORDER BY name ASC";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
			do{ 
				$data['id']=$row['id'];
				$data['name']=$row['name'];	
				$data1[]= $data;
			}while($row = mysqli_fetch_assoc($count));
			$data['ticketseverity']=$data1;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;  	
	}

	//machinestatus
    function machineStatus()
	{
		$data= array();
		$query="SELECT id,name FROM `ohrm_machine_status` WHERE is_deleted = 0 ORDER BY name ASC";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
			do{ 
				$data['id']=$row['id'];
				$data['name']=$row['name'];	
				$data1[]= $data;
			}while($row = mysqli_fetch_assoc($count));
			$data['machineStatus']=$data1;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;  	
	}
 
	//five Star Rating loop
    function fiveStarRating($starJsonAddObj)
    {
    	// echo sizeof($starJsonAddObj["startrating"]);
    	for ($i=0; $i < sizeof($starJsonAddObj["startrating"]); $i++) { 
	         $ticket_id = $starJsonAddObj["startrating"][$i]['ticket_id'];
	         $star_id = $starJsonAddObj["startrating"][$i]['star_id'];
	         $rating = $starJsonAddObj["startrating"][$i]['rating'];

	         $data = $this->fiveStarAdd($ticket_id,$star_id,$rating);
	     }
        
        return $data;
    }
    //Star Rating Add
    function fiveStarAdd($ticket_id,$star_id,$rating)
    {
        $data=array();
   			// Prepare an insert statement
			$sql = "INSERT INTO ohrm_five_star_rating (ticket_id,star_id,rating) VALUES (?,?,?)";
			 
			if($stmt = mysqli_prepare($this->conn, $sql)){
			    // Bind variables to the prepared statement as parameters
			     mysqli_stmt_bind_param($stmt, "iii" , $ticket_id,$star_id,$rating);
			    			   
			    // Attempt to execute the prepared statement
			    if(mysqli_stmt_execute($stmt)){

			      $data['fiveStarAdd'] = "star added successfully";
			        $data['status']=1;
			    } else{
			        //echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
			        $data['status']=0;
			    }
			} else{
			    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
			    $data['status']=0;
			}



		
        return $data;
    }

//star rating based on ticket Id
    function starRatingByTicktId($ticket_id)
	{
		$data= array();
		$query="SELECT fstr.id,fstr.star_id, fstr.ticket_id, fstr.rating,fs.name as star_name 
					FROM ohrm_five_star_rating fstr
					LEFT JOIN ohrm_five_s fs ON fs.id = fstr.star_id WHERE ticket_id = $ticket_id";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
			do{ 
				$data['id']=$row['id'];
				$data['star_id']=$row['star_id'];
				$data['ticket_id']=$row['ticket_id'];
				$data['rating']=$row['rating'];	
				$data['star_name']=$row['star_name'];	
				$data1[]= $data;
			}while($row = mysqli_fetch_assoc($count));
			$data['RatingByTicketId']=$data1;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;  	
	}
	//partAdd
    function partAdd($ticket_id,$emp_number,$part_name,$part_number,$quantity,$unit_id,$created_on)
    {
        $data=array();
   			// Prepare an insert statement
			$sql = "INSERT INTO ohrm_parts (ticket_id,emp_number,part_name,part_number,quantity,unit_id,created_on) VALUES (?,?,?,?,?,?,?)";
			 
			if($stmt = mysqli_prepare($this->conn, $sql)){
			    // Bind variables to the prepared statement as parameters
			     mysqli_stmt_bind_param($stmt, "iisssis" , $ticket_id,$emp_number,$part_name,$part_number,$quantity,$unit_id,$created_on);
			    			   
			    // Attempt to execute the prepared statement
			    if(mysqli_stmt_execute($stmt)){
			        $data['parts'] = "Part added successfully";
			        $data['status']=1;
			    } else{
			        //echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
			        $data['status']=0;
			    }
			} else{
			    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
			    $data['status']=0;
			}	

		
        return $data;
    }

     function partDelete($id)
    {
        $data=array();
   			// Prepare an insert statement
			$query = "DELETE FROM ohrm_parts WHERE id = $id";
			 
			if($count=mysqli_query($this->conn, $query)){
			    // Bind variables to the prepared statement as parameters
			    
			    			   
			    // Attempt to execute the prepared statement
			    if($count){
			        $data['parts'] = "Part deleted successfully";
			        $data['status']=1;
			    } else{
			        //echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
			        $data['status']=0;
			    }
			} else{
			    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
			    $data['status']=0;
			}	

		
        return $data;
    }


    function getPartById($id)
	{
		$data= array();

		$query = "SELECT pr.id AS id, pr.part_name as name, pr.part_number as part_number, pr.quantity AS quantity, pr.unit_id AS unit_id, pu.name AS unit_name, pu.name_plrl AS unit_name_plrl, pr.emp_number AS emp_number, pr.created_on AS created_on, pr.ticket_id AS ticket_id FROM ohrm_parts pr LEFT JOIN ohrm_product_unit pu ON pu.id = pr.unit_id WHERE pr.id=$id";

		$configDate = $this->dateFormat();
		/*$query="SELECT * FROM `ohrm_parts` WHERE is_deleted = 0 ORDER BY name ASC";*/
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
			do{ 
				$data['id']=$row['id'];
				$data['name']=$row['name'];	
				$data['part_number']=$row['part_number'];
				$data['quantity']=$row['quantity'];
				$data['unit_id']=$row['unit_id'];
				$data['unit_name']=$row['unit_name'];
				$data['unit_name_plrl']=$row['unit_name_plrl'];
				$data['emp_number']=$row['emp_number'];
				$data['created_on']=date($configDate, strtotime( $row['created_on'] )).' '.date('H:i:s', strtotime( $row['created_on'] ));
				$data['ticket_id']=$row['ticket_id'];
				$data1[]= $data;
			}while($row = mysqli_fetch_assoc($count));
			$data['getPartById']=$data1;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;  	
	}

	function getStarList()
    {
        $data=array();
  			
		$query="SELECT * FROM ohrm_five_s";

		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
				$row=mysqli_fetch_assoc($count);
			do{ 
				$data['id'] = $row['id'];
				$data['name'] = $row['name'];

				$data1[]= $data;
			}while($row = mysqli_fetch_assoc($count));
					$data['getStarList']=$data1;
						$data['status'] = 1;
		}
		else
		{
			 //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
			$data['status']=0;
		}		

		/*$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
				$row=mysqli_fetch_assoc($count);
					do{ 
						$data1['id'] = $row['id'];
						$data1['name'] = $row['name'];

					}while($row = mysqli_fetch_assoc($count));
						$data['getStarList']=$data1;
						$data['status'] = 1;
		}*/
		return $data;
    }

// to get engineer or technician response based on ticked id
	 function getEngorTechRespByTktId($ticket_id,$user_role_id)
	{
		$data= array();

		$query = "SELECT ta.ticket_id,prty.name as priority,concat(emp1.emp_firstname,' ',emp1.emp_lastname) as forwardfrom,concat(emp2.emp_firstname,' ',emp2.emp_lastname) as forwardTo,concat(emp.emp_firstname,' ',emp.emp_lastname) as acceptedBy, ta.submitted_on as submittedOn, st.name as status, ta.comment as comment,rc.name as rootname FROM ohrm_ticket_acknowledgement_action_log ta
			LEFT JOIN ohrm_user usr ON ta.created_by_user_id = usr.id
			LEFT JOIN ohrm_root_cause rc ON ta.root_cause_id = rc.id
			LEFT JOIN ohrm_ticket_priority prty ON prty.id = ta.priority_id
			LEFT JOIN hs_hr_employee emp ON emp.emp_number = ta.accepted_by
			LEFT JOIN hs_hr_employee emp1 ON emp1.emp_number = ta.forward_from
			LEFT JOIN hs_hr_employee emp2 ON emp2.emp_number = ta.forward_to
			LEFT JOIN ohrm_ticket_status st ON st.id = ta.status_id
			WHERE ticket_id = $ticket_id and usr.user_role_id = $user_role_id";

			$configDate = $this->dateFormat();
		/*$query="SELECT * FROM `ohrm_parts` WHERE is_deleted = 0 ORDER BY name ASC";*/
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);

			if($user_role_id = 12)
			{
				do{ 
				$data['ticket_id']=$row['ticket_id'];
				$data['priority']=$row['priority'];	
				$data['forwardfrom']=$row['forwardfrom'];
				$data['forwardTo']=$row['forwardTo'];
				$data['acceptedBy']=$row['acceptedBy'];
				$data['submittedOn']=date($configDate, strtotime( $row['submittedOn'] )).' '.date('H:i', strtotime( $row['submittedOn'] ));
				$data['status']=$row['status'];
				$rootname = $row['rootname'];
				if($rootname)
				{
					$data['comment']= $row['comment'].' rootcause: '.$row['rootname'];
				}
				else
				{
					$data['comment']= $row['comment'];
				}
				
				//$data['comment'].=$row['comment'];
				//$rootname =$row['rootname'];
				//$data['comment']=$row['comment'].'('.$rootname.')';
				$data1[]= $data;
			}while($row = mysqli_fetch_assoc($count));
			$data['getEngorTechRespByTktId']=$data1;
			$data['status']=1;

			}
			else

			{


					do{ 
						$data['ticket_id']=$row['ticket_id'];
						$data['priority']=$row['priority'];	
						$data['forwardfrom']=$row['forwardfrom'];
						$data['forwardTo']=$row['forwardTo'];
						$data['acceptedBy']=$row['acceptedBy'];
						$data['submittedOn']=date($configDate, strtotime( $row['submittedOn'] )).' '.date('H:i', strtotime( $row['submittedOn'] ));
						$data['status']=$row['status'];
						$data['comment']= $row['comment'];
						
						//$data['comment'].=$row['comment'];
						//$rootname =$row['rootname'];
						//$data['comment']=$row['comment'].'('.$rootname.')';
						$data1[]= $data;
					}while($row = mysqli_fetch_assoc($count));
					$data['getEngorTechRespByTktId']=$data1;
					$data['status']=1;
				}
		}else{
			$data['status']=0;
		}
		return $data;  	
	}

	 function getReqstrRespByTktId($ticket_id)
	{
		$data= array();

		$query = "SELECT ta.ticket_id,prty.name as priority,concat(emp1.emp_firstname,' ',emp1.emp_lastname) as forwardfrom,concat(emp2.emp_firstname,' ',emp2.emp_lastname) as forwardTo,ta.submitted_by_name AS submittedBy, ta.submitted_on as submittedOn, st.name as status, ta.comment as comment FROM ohrm_ticket_acknowledgement_action_log ta LEFT JOIN ohrm_ticket t ON t.id = ta.ticket_id LEFT JOIN ohrm_user usr ON ta.created_by_user_id = usr.id LEFT JOIN ohrm_ticket_priority prty ON prty.id = ta.priority_id LEFT JOIN hs_hr_employee emp ON emp.emp_number = ta.submitted_by_emp_number LEFT JOIN hs_hr_employee emp1 ON emp1.emp_number = ta.forward_from LEFT JOIN hs_hr_employee emp2 ON emp2.emp_number = ta.forward_to LEFT JOIN ohrm_ticket_status st ON st.id = ta.status_id WHERE ticket_id = $ticket_id AND ta.status_id IN (1,5,6)";
 
			$configDate = $this->dateFormat();
		/*$query="SELECT * FROM `ohrm_parts` WHERE is_deleted = 0 ORDER BY name ASC";*/
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
			do{ 
				$data['ticket_id']=$row['ticket_id'];
				$data['priority']=$row['priority'];	
				$data['forwardfrom']=$row['forwardfrom'];
				$data['forwardTo']=$row['forwardTo'];
				$data['submittedBy']=$row['submittedBy'];
				$data['submittedOn']=date($configDate, strtotime( $row['submittedOn'] )).' '.date('H:i', strtotime( $row['submittedOn'] ));
				$data['status']=$row['status'];
				$data['comment']=$row['comment'];
				$data1[]= $data;
			}while($row = mysqli_fetch_assoc($count));
			$data['getReqstrRespByTktId']=$data1;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;  	
	}

	
	function getEmpnumberByUsrId($user_id)
		{
			/*echo $user_id;
			exit();*/
			$query = "SELECT emp_number FROM ohrm_user WHERE id = $user_id"; //table
			$result=mysqli_query($this->conn, $query);
			if(mysqli_num_rows($result)>0)
			{
			   $row = mysqli_fetch_array($result);
			   $emp_number=$row['emp_number'];
			   /*echo $emp_number;
			   exit();*/
		    }
		   return $emp_number;
		}

    //uploadImage($ticket_id,$image,$fileName,$fileType,$fileSize)
    function uploadImage($ticket_id,$file_name,$file_type,$file_size,$file_content,$created_on,$user_id)
    {
        $data=array();

        $created_by = $this->getEmpnumberByUsrId($user_id);
   			// Prepare an insert statement
			$sql = "INSERT INTO ohrm_ticket_attachment (ticket_id,file_name,file_type,file_size,file_content,created_on,created_by) VALUES (?,?,?,?,?,?,?)";
			 
			if($stmt = mysqli_prepare($this->conn, $sql)){
			    // Bind variables to the prepared statement as parameters
			     mysqli_stmt_bind_param($stmt, "ississs" , $ticket_id,$file_name,$file_type,$file_size,
			     	$file_content,$created_on,$created_by);
			    			   
			    // Attempt to execute the prepared statement
			    if(mysqli_stmt_execute($stmt)){
			        $data['uploadImage'] = "Image added successfully";
			        $data['status']=1;
			    } else{
			        //echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
			        $data['status']=0;
			    }
			} else{
			    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
			    $data['status']=0;
			}	

        return $data;
    }


function getAcknowledgementId($ticket_id)
		{
			$query = "SELECT MAX(id) as Id FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id"; //table
			$result=mysqli_query($this->conn, $query);
			if(mysqli_num_rows($result)>0)
			{
			   $row = mysqli_fetch_array($result);
			   $Id=$row['Id'];
			  
		    }
		   return $Id;
		}

    //uploadImage($ticket_id,$image,$fileName,$fileType,$fileSize)
    function respupload($ticket_id,$file_name,$file_type,$file_size,$file_content,$created_on,$user_id)
    {
        $data=array();
   			// Prepare an insert statement
       
			$ticket_action_log_id = $this->getAcknowledgementId($ticket_id);

			$created_by = $this->getEmpnumberByUsrId($user_id);

			$sql = "INSERT INTO ohrm_ticket_action_log_attachment (ticket_action_log_id,file_name,file_type,file_size,file_content,created_on,created_by) VALUES (?,?,?,?,?,?,?)";
			 
			if($stmt = mysqli_prepare($this->conn, $sql)){
			    // Bind variables to the prepared statement as parameters
			     mysqli_stmt_bind_param($stmt, "ississs" , $ticket_action_log_id,$file_name,$file_type,$file_size,$file_content,$created_on,$created_by);
			    			   
			    // Attempt to execute the prepared statement
			    if(mysqli_stmt_execute($stmt)){
			        $data['respuploadImage'] = "upload is successfull";
			        $data['status']=1;
			    } else{
			        //echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
			        $data['status']=0;
			    }
			} else{
			    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
			    $data['status']=0;
			}	

        return $data;
    }


  //getTicketAttachment
    function getTicketAttachment($ticket_id,$path)
    {
       $data= array();
		//getUser Details
		$query="SELECT id,file_name,file_type,file_content FROM ohrm_ticket_attachment WHERE ticket_id = $ticket_id";
		$count=mysqli_query($this->conn, $query);

			
			if(mysqli_num_rows($count) > 0)
					{
								$row=mysqli_fetch_assoc($count);
								do{ 
										$id = $data['id']=$row['id'];
										$data['file_name']=$row['file_name'];
										$data['file_type']=$row['file_type'];
										//$data['file_content']=$row['file_content'];	
										$value = $path.'get_ticket_attachment.php?id='.$id;
										$data['attachment'] = $value;				
										$data1[] = $data;
								}while($row = mysqli_fetch_assoc($count)); 
										$data['getTicketAttachment']=$data1;
										$data['status']=1;
																											
					}else {
								//echo "ERROR: Could not able to execute $query. " . mysqli_error($this->conn);
																     $data['status']=0;
						 }
	
			
		return $data;   
    }

//uploadImage($ticket_id,$image,$fileName,$fileType,$fileSize)
    function getTicketAttachmentActionLog($ticket_id,$path)
    {
       $data= array();
	
		$query="SELECT a.id as id,a.ticket_action_log_id as ticket_action_log_id,a.file_name as file_name,a.file_type as file_type,a.file_content as file_content FROM ohrm_ticket_action_log_attachment a LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON l.id = a.ticket_action_log_id WHERE l.ticket_id = $ticket_id";
		$count=mysqli_query($this->conn, $query);

			
			if(mysqli_num_rows($count) > 0)
					{

								$row=mysqli_fetch_assoc($count);
								do{ 

										$id = $data['id']=$row['id'];
										$data['ticket_action_log_id']=$row['ticket_action_log_id'];
										$data['file_name']=$row['file_name'];
										$data['file_type']=$row['file_type'];
										
										$value = $path.'get_actionlog_attachment.php?id='.$id;
										$data['attachment'] = $value;				
										$data1[] = $data;
        											
										
								}while($row = mysqli_fetch_assoc($count)); 
										$data['getTicketAttachmentActionLog']=$data1;
										$data['status']=1;
																											
					}else {
								//echo "ERROR: Could not able to execute $query. " . mysqli_error($this->conn);
																     $data['status']=0;
						 }
	
			
		return $data;   
    }


    function base64_to_jpeg($base64_string, $output_file) {
    // open the output file for writing
    $ifp = fopen( $output_file, 'wb' ); 

    // split the string on commas
    // $data[ 0 ] == "data:image/png;base64"
    // $data[ 1 ] == <actual base64 string>
    $data = explode( ',', $base64_string );

    // we could add validation here with ensuring count( $data ) > 1
    fwrite( $ifp, base64_decode( $data[ 1 ] ) );

    // clean up the file resource
    fclose( $ifp ); 

    return $output_file; 
}


    //ticketAdd
	function ticketAdd($locationId,$plantId,$usrdeptId,$notifytoId,$statusId,$funclocId,$eqipmntId,$typofisId,$subject,$description,$prtyId,$svrtyId,$reportedBy,$submitted_by_emp_number,$submitted_by_name,$reportedOn,$submitted_on,$user_id,$attachmentId)
    {
        $data=array();

     /*  $base64_string = "";
       $image = $this->base64_to_jpeg($base64_string,'tmp.jpg' );
       echo $image;
       exit();*/

		$query="SELECT ui.last_id FROM hs_hr_unique_id ui WHERE ui.table_name = 'ohrm_ticket' AND ui.field_name='job_id'";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);

			$data['last_id']=$row['last_id'];
			$jobinc = $row['last_id']+1;

			$sql = "UPDATE hs_hr_unique_id SET last_id = ".$jobinc." WHERE table_name = 'ohrm_ticket' AND field_name='job_id'";
			if(mysqli_query($this->conn, $sql)){

				$query="SELECT ui.last_id FROM hs_hr_unique_id ui WHERE ui.table_name = 'ohrm_ticket' AND ui.field_name='job_id'";
				$count=mysqli_query($this->conn, $query);
					if(mysqli_num_rows($count) > 0){
						$row=mysqli_fetch_assoc($count);
						$prefix = date('Ymd');
						$NewJobId = $row['last_id'];
						$jobIdNew = $prefix . str_pad($NewJobId, 3, "0", STR_PAD_LEFT);
					}

				$source = 1;
	   			// Prepare an insert statement
				$sql = "INSERT INTO ohrm_ticket (job_id,location_id,plant_id,user_department_id,notify_to,status_id,functional_location_id,equipment_id,type_of_issue_id,subject,description,priority_id,severity_id,reported_by,submitted_by_name,submitted_by_emp_number,reported_on,submitted_on,source) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
				 
				/* echo $reportedOn." , ".$submitted_on;
				 exit();*/
				if($stmt = mysqli_prepare($this->conn, $sql)){
				    // Bind variables to the prepared statement as parameters
				     mysqli_stmt_bind_param($stmt, "siiisiiiissiiisissi" ,$jobIdNew,$locationId,$plantId,$usrdeptId,$notifytoId,$statusId,$funclocId,$eqipmntId,$typofisId,$subject,$description,$prtyId,$svrtyId,$reportedBy,$submitted_by_name,$submitted_by_emp_number,$reportedOn,$submitted_on,$source);
				    			   
					    // Attempt to execute the prepared statement
					    if(mysqli_stmt_execute($stmt)){

				    		$query="SELECT MAX(id) AS ticket_id  FROM ohrm_ticket";
							$count=mysqli_query($this->conn, $query);
								
								if(mysqli_num_rows($count) > 0)	{
											$row=mysqli_fetch_assoc($count);
											$data['ticket_id'] = $row['ticket_id'];
											$ticket_id = $data['ticket_id'];
											$result=$this->logAdd($user_id,$ticket_id,' ',' ',' ',' ',$user_id,$statusId,$prtyId,$svrtyId,$subject,' ',' ',' ',$submitted_by_name,$submitted_by_emp_number,' ',' ',$submitted_on);
											$sql = "UPDATE ohrm_ticket_attachment SET ticket_id = ".$ticket_id." WHERE ticket_id = ".$attachmentId;
											mysqli_query($this->conn, $sql);
								}

				        $data['ticketid'] = $data['ticket_id'];
				        $data['status']=1;
					    } else{
					        //echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
					        $data['status']=0;
					    }
				} else{
				    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
				    $data['status']=0;
					}	

			} else {
					    //echo "ERROR: Could not able to execute $sql. " . mysqli_error($this->conn);
					     $data['status']=0;
			}
		} else{
				    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
				    $data['status']=0;
		}
		
        return $data;
    }


    //funcLocation
    function funcLocation($department_id,$parent_id,$level)
	{
		$data= array();
		//getUser Details
		// $query="SELECT name FROM ohrm_location WHERE id = $location_id";

		// $count=mysqli_query($this->conn, $query);

		// if(mysqli_num_rows($count) > 0)
		// {
			
		// 	$row=mysqli_fetch_assoc($count);
		// 		$data['name']=$row['name'];	
		// 		$locname = $data['name'];

		// 		$query1="SELECT lft,rgt FROM ohrm_subunit WHERE name = '".$locname."'";
		// 		$count1=mysqli_query($this->conn, $query1);



		// 		if(mysqli_num_rows($count1) > 0)
		// 		{
		// 			$row1=mysqli_fetch_assoc($count1);
		// 			$data['left']=$row1['lft'];
		// 			$data['right']=$row1['rgt'];	
		// 			$lft = $data['left'];
		// 			$rgt = $data['right']; 
									
		// 			$query2="select plant_name from ohrm_plant WHERE id = $plant_id";
		// 			$count2=mysqli_query($this->conn, $query2);

		// 				if(mysqli_num_rows($count2) > 0)
		// 				{
		// 					$row=mysqli_fetch_assoc($count2);
		// 					$data['plant_name']=$row['plant_name'];
		// 					$plantname = $data['plant_name'];
							
		// 						$query3="SELECT lft,rgt FROM ohrm_subunit WHERE name = '$plantname' and lft > ". $lft." and rgt < ".$rgt;
		// 								$count=mysqli_query($this->conn, $query3);

		// 									if(mysqli_num_rows($count) > 0)
		// 									{
		// 										$row=mysqli_fetch_assoc($count);
		// 										$data['lft']=$row['lft'];
		// 										$data['rgt']=$row['rgt'];	
		// 										$lft = $data['lft'];
		// 										$rgt = $data['rgt']; 

											
		// 											$query4="SELECT id FROM ohrm_subunit WHERE lft > ". $lft." and rgt < ".$rgt;
		// 											$count=mysqli_query($this->conn, $query4);

		// 											if(mysqli_num_rows($count) > 0)
		// 											{
		// 												$row=mysqli_fetch_assoc($count);
		// 												$data['id']=$row['id'];
		// 												$depId = $data['id'];
														$query5="SELECT id,name FROM ohrm_functional_location WHERE user_department_id = $department_id and is_deleted = 0";
														if($parent_id){
															$query5 .= " and parent_id = $parent_id";
														}

														if($level == 0){
															$query5 .= " and level = $level";
														}

														if($level == 1){
															$query5 .= " and level = $level";
														}

														$count=mysqli_query($this->conn, $query5);

														if(mysqli_num_rows($count) > 0)
														{
																$row=mysqli_fetch_assoc($count);
																do{ 
																	$data3['id']=$row['id'];
																	$data3['name']=$row['name'];					
																	$data4[] = $data3;
																}while($row = mysqli_fetch_assoc($count)); 
																$data['funcLocation']=$data4;
																$data['status']=1;
																											
														}else {
															//echo "ERROR: Could not able to execute $query5. " . mysqli_error($this->conn);
																     $data['status']=0;
														}
												
		// 											}else {
		// 													//echo "ERROR: Could not able to execute $query4. " . mysqli_error($this->conn);
		// 														     $data['status']=0;
		// 												}
		// 									}else {
		// 											    //echo "ERROR: Could not able to execute $query3. " . mysqli_error($this->conn);
		// 											     $data['status']=0;
		// 									}
					
		// 				}else {
		// 					    //echo "ERROR: Could not able to execute $query2. " . mysqli_error($this->conn);
		// 					     $data['status']=0;
		// 				}	

		// 		}else {
		// 		    //echo "ERROR: Could not able to execute $query1. " . mysqli_error($this->conn);
		// 		     $data['status']=0;
		// 			}
				
		// }else{
		// 		$data['status']=0;
		// 	}
			
		return $data;     
	}


//funcLocation
    function funcLocationDrpDown($dept_id)
	{
		$data= array();
		//getUser Details
		$query="SELECT id,name FROM ohrm_functional_location WHERE user_department_id = $dept_id ORDER by name asc";
		$count=mysqli_query($this->conn, $query);

			
			if(mysqli_num_rows($count) > 0)
					{
								$row=mysqli_fetch_assoc($count);
								do{ 
										$data['id']=$row['id'];
										$data['name']=$row['name'];					
										$data1[] = $data;
								}while($row = mysqli_fetch_assoc($count)); 
										$data['funcLocationDrpDownLst']=$data1;
										$data['status']=1;
																											
					}else {
								//echo "ERROR: Could not able to execute $query. " . mysqli_error($this->conn);
																     $data['status']=0;
						 }
	
			
		return $data;     
	}


	function userIdbyUserRoleId($user_id)
	{
		$data= array();

		$query="SELECT  u.user_role_id,u.user_name FROM ohrm_user u WHERE u.id = $user_id";

		$count=mysqli_query($this->conn, $query);


			if(mysqli_num_rows($count) > 0)
					{
							$row=mysqli_fetch_assoc($count);
							$user_role_id = $row['user_role_id'];				
					}

		return $user_role_id; 
	}

	function getSubordinateByEmp($emp_number)
	{

		$data= array();

		$query="SELECT erep_sup_emp_number FROM `hs_hr_emp_reportto` WHERE erep_sub_emp_number = $emp_number AND erep_reporting_mode = 1";

		$count=mysqli_query($this->conn, $query);


			if(mysqli_num_rows($count) > 0)
					{
							$row=mysqli_fetch_assoc($count);
							$erep_sup_emp_number = $row['erep_sup_emp_number'];				
					}

		return $erep_sup_emp_number; 


	}

	function getDepartmentByTktId($ticket_id)
	{

		$data= array();

		$query="SELECT t.user_department_id as usrDeptId FROM ohrm_ticket t WHERE t.id = $ticket_id";

		$count=mysqli_query($this->conn, $query);


			if(mysqli_num_rows($count) > 0)
					{
							$row=mysqli_fetch_assoc($count);
							$usrDeptId = $row['usrDeptId'];				
					}

		return $usrDeptId; 


	}


	function defEngTechResponse($ticket_id,$user_id)
	{


		$data= array();

		$userDetails = $this->getUserRoleByUserId($user_id);
		$empNumber = $userDetails['empNumber'];
		$roleId = $userDetails['id'];
		/*echo $roleId.'/'.$empNumber;
		exit();*/
		/*$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];*/

		$empNumber = $this->getEmpnumberByUsrId($user_id);
		$deptId = $this->getDepartmentByTktId($ticket_id);


		// echo $deptId.'/';
		// echo $empNumber;
		// exit();
		
		/*$empNumber = $empdet['empNumber'];
		echo $empNumber;
		exit();*/

		$query1 = "SELECT MAX(ta.forward_from) as empFrom FROM ohrm_ticket_acknowledgement_action_log ta WHERE ta.forward_to = $empNumber and ta.ticket_id = $ticket_id";
		$count1=mysqli_query($this->conn, $query1);

			
			if(mysqli_num_rows($count1) > 0)
					{
							//echo "if";
								$row1=mysqli_fetch_assoc($count1);
							
										$forwardFrom = $row1['empFrom'];
										if($forwardFrom){
											$userDetails = $this->getUserRoleByEmpNumber($forwardFrom);
											$userRolId = $userDetails['id'];
										}else{
											/*echo "else";
											exit();*/
											$userRolId = 11;
										}
										// echo $forwardFrom;
										// exit();					
										
								
																					
					}


					// if($userRolId  == 10){

									//echo "if 10";

								if($userRolId  == 10 || $userRolId  == 2 || $userRolId  == 19){


										$query = "SELECT h.emp_number AS emp_number, concat(h.emp_firstname,h.emp_middle_name,h.emp_lastname) AS emp_name  FROM hs_hr_employee h LEFT JOIN ohrm_user o ON h.emp_number = o.emp_number WHERE (o.user_role_id = 11 AND h.work_station = $deptId)";

								}else{

									$empresult=$this->engLists();

										for ($i=0; $i < sizeof($empresult['englist']) ; $i++) { 
							        	$engList[] = $empresult['englist'][$i];
							        	//to convert Array into string the following implode method is used
							        	$engLists = implode(',', $engList);
							        }
										/*$query = "SELECT e.emp_number AS emp_number, concat(e.emp_firstname,e.emp_middle_name,e.emp_lastname) AS emp_name FROM hs_hr_employee e LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON e.emp_number = l.forward_from WHERE l.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id AND forward_from !=0)";*/
										$query = "SELECT e.emp_number AS emp_number, concat(e.emp_firstname,e.emp_middle_name,e.emp_lastname) AS emp_name FROM hs_hr_employee e LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON e.emp_number = l.forward_from WHERE l.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id AND forward_from IN ($engLists))";

								}
									
									// echo $query;

									$count=mysqli_query($this->conn, $query);

						
										if(mysqli_num_rows($count) > 0)
											{

														$row=mysqli_fetch_assoc($count);
														do{ 
																$data['emp_number']=$row['emp_number'];
																$data['emp_name']=$row['emp_name'];					
																$data1[] = $data;
														}while($row = mysqli_fetch_assoc($count)); 
																$data['defEngforTechres']=$data1;
																$data['status']=1;
																														
												}else {
															//echo "ERROR: Could not able to execute $query. " . mysqli_error($this->conn);
																							     $data['status']=0;
													 }
								

		// 					}else{


		// 						$data= array();

		// $query = "SELECT e.emp_number AS emp_number, concat(e.emp_firstname,e.emp_middle_name,e.emp_lastname) AS emp_name FROM hs_hr_employee e LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON e.emp_number = l.forward_from WHERE l.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id AND forward_from !=0)";
		// echo $query;
		 
		// $count=mysqli_query($this->conn, $query);

			
		// 	if(mysqli_num_rows($count) > 0)
		// 			{
		// 						$row=mysqli_fetch_assoc($count);
		// 						do{ 
		// 								$data['emp_number']=$row['emp_number'];
		// 								$data['emp_name']=$row['emp_name'];					
		// 								$data1[] = $data;
		// 						}while($row = mysqli_fetch_assoc($count)); 
		// 								$data['defEngforTechres']=$data1;
		// 								$data['status']=1;
																											
		// 			}else {
		// 						//echo "ERROR: Could not able to execute $query. " . mysqli_error($this->conn);
		// 														     $data['status']=0;
		// 				 }
	
			
		// return $data;   
		// 					}

					
		return $data;     

	}
	//EngTechLists
    function EngTechLists($user_role_id,$user_id)
	{
		$data= array();
		//getUser Details
		//echo $user_id;
		$userDetails = $this->getUserRoleByUserId($user_id);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		 $department_Id = $empresult['work_station'];
		/*echo $department_Id.''.$empNumber;
		exit();*/
		$usrRolId =$this->userIdbyUserRoleId($user_id);

		// echo $usrRolId['user_role_id'];
		// exit();

		if($user_role_id == 11){

			/*echo "11";
			exit();*/
			$query="SELECT e.emp_number AS emp_number, concat(e.emp_firstname,' ',e.emp_lastname) as emp_name,e.emp_mobile as mobile FROM hs_hr_employee e LEFT JOIN ohrm_user u ON u.emp_number = e.emp_number LEFT JOIN ohrm_plant p ON p.id = e.plant_id WHERE u.user_role_id = $user_role_id AND u.id != $user_id AND p.id != 0 AND e.termination_id IS NULL";
		}
			else if($usrRolId == 10 || $usrRolId == 19){

					//echo "10";
			//exit();
				if($user_role_id == 11)
				{

					$query= "SELECT e.emp_number AS emp_number, concat(e.emp_firstname,' ',e.emp_lastname) as emp_name,e.emp_mobile as mobile FROM hs_hr_employee e LEFT JOIN ohrm_user u ON u.emp_number = e.emp_number LEFT JOIN ohrm_plant p ON p.id = e.plant_id WHERE u.user_role_id = 11 AND p.id != 0 AND e.termination_id IS NULL";
				}
				else
				{

					$query="SELECT e.emp_number AS emp_number, concat(e.emp_firstname,' ',e.emp_lastname) as emp_name,e.emp_mobile as mobile FROM hs_hr_employee e LEFT JOIN ohrm_user u ON u.emp_number = e.emp_number LEFT JOIN ohrm_plant p ON p.id = e.plant_id WHERE u.user_role_id = 12 AND p.id != 0 AND e.termination_id IS NULL";
				}
					
				
				}else if($user_role_id == 12){


				//echo "12";
			//exit();
					// $SubordinateByEmp = $this->getSubordinateByEmp($empNumber);
					
					if($usrRolId == 12){
						$query = "SELECT e.emp_number AS emp_number,concat(e.emp_firstname,' ',e.emp_lastname) as emp_name,e.emp_mobile as mobile FROM hs_hr_employee e LEFT JOIN ohrm_user u ON u.emp_number = e.emp_number WHERE u.user_role_id = 12 AND e.work_station = $department_Id AND e.emp_number != $empNumber";

					}else{

					$query="SELECT emp.emp_number AS emp_number, concat(emp.emp_firstname,' ',emp.emp_lastname) as emp_name,emp.emp_mobile as mobile FROM hs_hr_employee emp LEFT JOIN hs_hr_emp_reportto rep ON rep.erep_sub_emp_number = emp.emp_number WHERE rep.erep_sup_emp_number  = $empNumber AND rep.erep_reporting_mode = 1 AND emp.emp_number != $empNumber";
					}
					// $query = "SELECT e.emp_number AS emp_number,concat(e.emp_firstname,' ',e.emp_lastname) as emp_name,e.emp_mobile as mobile FROM hs_hr_employee e
					//  LEFT JOIN ohrm_user u ON u.emp_number = e.emp_number WHERE u.user_role_id = 12 AND e.work_station = $department_Id AND e.emp_number != $empNumber";

					// $query = " SELECT e.emp_number AS emp_number,concat(e.emp_firstname,' ',e.emp_lastname) as emp_name,e.emp_mobile as mobile FROM hs_hr_employee e
					//  LEFT JOIN ohrm_user u ON u.emp_number = e.emp_number WHERE u.user_role_id = 12 AND e.work_station = 7 AND e.emp_number != 10";
				}else{


					/*echo "13";
			exit();*/
					$query="SELECT emp.emp_number AS emp_number, concat(emp.emp_firstname,' ',emp.emp_lastname) as emp_name,emp.emp_mobile as mobile FROM hs_hr_employee emp LEFT JOIN hs_hr_emp_reportto rep ON rep.erep_sub_emp_number = emp.emp_number LEFT JOIN ohrm_user u ON u.emp_number = rep.erep_sup_emp_number WHERE u.id = $user_id AND rep.erep_reporting_mode = 1";
				}
			
		
		$count=mysqli_query($this->conn, $query);

		/*echo $count;

		exit();*/
			
			if(mysqli_num_rows($count) > 0)
					{
								$row=mysqli_fetch_assoc($count);
								do{ 
										$data['emp_number']=$row['emp_number'];
										if($row['mobile'])
											$data['emp_name']=$row['emp_name'].'('.$row['mobile'].')';
										else	
											$data['emp_name']=$row['emp_name'];			
										$data1[] = $data;
								}while($row = mysqli_fetch_assoc($count)); 
										$data['EngTechLists']=$data1;
										$data['status']=1;
																											
					}else {
								//echo "ERROR: Could not able to execute $query. " . mysqli_error($this->conn);
																     $data['status']=0;
						 }
	
			
		return $data; 
	}

	//ActionlogAdd
function logAdd($user_id,$ticket_id,$accepted_by,$rejected_by,$forward_from,$forward_to,$created_by_user_id,$status_id,$priority_id,$severity_id,$comment,$machine_status,$assigned_date,$due_date,$submitted_by_name,$submitted_by_emp_number,$root_cause_id,$response_id,$submitted_on)
    {

    	$empresult=$this->engLists();

		for ($i=0; $i < sizeof($empresult['englist']) ; $i++) { 
	    	$engList[] = $empresult['englist'][$i];
	    	//to convert Array into string the following implode method is used
	    	$engLists = implode(',', $engList);
	    }
		$minquery = "SELECT * FROM ohrm_ticket_acknowledgement_action_log WHERE id IN (SELECT MIN(id) FROM ohrm_ticket_acknowledgement_action_log WHERE forward_from IN ($engLists) AND ticket_id = $ticket_id)";

		$rowcount2 = mysqli_query($this->conn, $minquery);

		if(mysqli_num_rows($rowcount2) > 0)
			{
				$row2 = mysqli_fetch_assoc($rowcount2);
					$datacount2=$row2['forward_from'];
			}

        $data=array();

        $userDetails = $this->getUserRoleByUserId($user_id);
        $empNumber = $userDetails['empNumber'];
		$roleId = $userDetails['id'];

    	if($status_id == 14)
    	{
    		if($roleId == 11){
    			$forward_from = $empNumber;
	    		$forward_to = $datacount2;
	    		$accepted_by = 0;
    		}else{
    			$forward_from = $accepted_by;
	    		$forward_to = $this->getAcceptedEngId($ticket_id);
	    		$accepted_by = 0;
    		}
    	}

   			// Prepare an insert statement

    	$source = 1;     
     	$sql = "INSERT INTO ohrm_ticket_acknowledgement_action_log (ticket_id,accepted_by,rejected_by,forward_from,forward_to,created_by_user_id,status_id,priority_id,severity_id,comment,machine_status,submitted_by_name,submitted_by_emp_number,root_cause_id,response_id,submitted_on,source) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
			 
			if($stmt = mysqli_prepare($this->conn, $sql)){
				
			    // Bind variables to the prepared statement as parameters
			     mysqli_stmt_bind_param($stmt, "iiiiiisiisisiiisi" ,$ticket_id,$accepted_by,$rejected_by,$forward_from,$forward_to,$created_by_user_id,$status_id,$priority_id,$severity_id,$comment,$machine_status,$submitted_by_name,$submitted_by_emp_number,$root_cause_id,$response_id,$submitted_on,$source);
			    			   
			    // Attempt to execute the prepared statement
			    if(mysqli_stmt_execute($stmt)){
			       
			        if($status_id == 2 || $status_id == 4)
    				 {

     				$updatesql = "UPDATE ohrm_ticket SET status_id = 3 WHERE id = $ticket_id";
     					if($result2 = mysqli_query($this->conn, $updatesql)){
						/*$data['session_token'] = $token;*/
						if($status_id == 2)
						{
							$data['log'] = "Job status changed to Assigned";
				        	$data['status']=1;
						}
						else
						{
							$data['log'] = "Job status changed to Accepted";
				        	$data['status']=1;
						}
						
						}
							else{
							//echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
					    	$data['status']=0;
							}

     				}

     				else if($status_id == 14)
    				 {

    				 		/*echo $status_id;
    				 		echo $ticket_id;
    				 		exit;*/
     				$updatesql = "UPDATE ohrm_ticket SET status_id = 14 WHERE id = $ticket_id";
     					if($result2 = mysqli_query($this->conn, $updatesql)){
						/*$data['session_token'] = $token;*/
						$data['log'] = "Job Status Changed to Resolved";
				        $data['status']=1;
						}
							else{
							//echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
					    	$data['status']=0;
							}

     				}

     				else if($status_id == 12)
    				 {

    				 		/*echo $status_id;
    				 		echo $ticket_id;
    				 		exit;*/
     				$updatesql = "UPDATE ohrm_ticket SET status_id = 12 WHERE id = $ticket_id";
     					if($result2 = mysqli_query($this->conn, $updatesql)){
						/*$data['session_token'] = $token;*/
						$data['log'] = "Job Status Changed to Paused";
				        $data['status']=1;
						}
							else{
							//echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
					    	$data['status']=0;
							}

     				}

     				else if($status_id == 6)
    				 {

    				 		/*echo $status_id;
    				 		echo $ticket_id;
    				 		exit;*/
     				$updatesql = "UPDATE ohrm_ticket SET status_id = 6 WHERE id = $ticket_id";
     					if($result2 = mysqli_query($this->conn, $updatesql)){
						/*$data['session_token'] = $token;*/
						$data['log'] = "Job Status Changed to Reopened";
				        $data['status']=1;
						}
							else{
							//echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
					    	$data['status']=0;
							}

     				}

     				else if($status_id == 13)
    				 {

    				 		/*echo $status_id;
    				 		echo $ticket_id;
    				 		exit;*/
     				$updatesql = "UPDATE ohrm_ticket SET status_id = 13 WHERE id = $ticket_id";
     					if($result2 = mysqli_query($this->conn, $updatesql)){
						/*$data['session_token'] = $token;*/
						$data['log'] = "Job Status Changed to Resumed";
						$data['forward_to']=1;
				        $data['status']=1;
						}
							else{
							//echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
					    	$data['status']=0;
							}

     				}

     				else if($status_id == 7 || $status_id == 8 || $status_id == 9 || $status_id == 15 || $status_id == 10 || $status_id == 5)
    				 {

    				 		/*echo $status_id;
    				 		echo $ticket_id;
    				 		exit;*/
     				$updatesql = "UPDATE ohrm_ticket SET status_id = $status_id WHERE id = $ticket_id";
     					if($result2 = mysqli_query($this->conn, $updatesql)){
						/*$data['session_token'] = $token;*/
						$data['log'] = "Job Status Changed";
				        $data['status']=1;
						}
							else{
							//echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
					    	$data['status']=0;
							}

     				}

     				else if($status_id == 16)
    				 {


    				 		$userDetails = $this->getUserRoleByUserId($user_id);
							$empNumber = $userDetails['empNumber'];

    				 		/*echo $empNumber;
    				 		exit();*/
							$rolid = $userDetails['id'];

							//echo $rolid;

							//exit();
							if($rolid == 12)
							{

											$updatesql = "UPDATE ohrm_ticket SET status_id = 2 WHERE id = $ticket_id";
			     					if($result2 = mysqli_query($this->conn, $updatesql)){
									/*$data['session_token'] = $token;*/
									$data['log'] = "Job Status Changed to Rejected";
							        $data['status']=1;
										}
										else{
										//echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
								    	$data['status']=0;
										}
							}
							else
							{

								$updatesql = "UPDATE ohrm_ticket SET status_id = $status_id WHERE id = $ticket_id";
			     					if($result2 = mysqli_query($this->conn, $updatesql)){
									/*$data['session_token'] = $token;*/
									$data['log'] = "Job Status Changed to Rejected";
							        $data['status']=1;
										}
										else{
										//echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
								    	$data['status']=0;
										}

							}
     				

     				}

     				else
     				{
     					$data['log'] = "Job Created Successfully";
			        	$data['status']=1;

     				}

			        
			    } else{
			        //echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
			        $data['status']=0;
			    }
			} else{
			    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
			    $data['status']=0;
			}
     
     
        return $data;
    }

//five Star Rating loop
    function TicketcheckListAddObj($checkListAddObj)
    {
    	// echo sizeof($starJsonAddObj["startrating"]);
    	for ($i=0; $i < sizeof($checkListAddObj["checklistAdd"]); $i++) { 
	         $ticket_id = $checkListAddObj["checklistAdd"][$i]['ticket_id'];
	         $check_list_item_id = $checkListAddObj["checklistAdd"][$i]['check_list_item_id'];
	         $value = $checkListAddObj["checklistAdd"][$i]['value'];
	         $comment = $checkListAddObj["checklistAdd"][$i]['comment'];

	         $data = $this->checkListAdd($ticket_id,$check_list_item_id,$comment,$value);
	     }
        
        return $data;
    }
	//checkListAdd
    function checkListAdd($ticket_id,$check_list_item_id,$comment,$value)
    {
        $data=array();

        	
   					// Prepare an insert statement
					$sql = "INSERT INTO ohrm_preventive_check_list (ticket_id,check_list_item_id,comment,value) VALUES (?,?,?,?)";
					 
					if($stmt = mysqli_prepare($this->conn, $sql)){
					    // Bind variables to the prepared statement as parameters
					     mysqli_stmt_bind_param($stmt, "iisi" ,$ticket_id,$check_list_item_id,$comment,$value);
					    			   
					    // Attempt to execute the prepared statement
					    if(mysqli_stmt_execute($stmt)){
					       
					        $data['checkListAdd'] = "CheckLists added successfully";
			        		$data['status']=1;
					    } else{
					        //echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
					        $data['status']=0;
					    }
					} else{
					    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
					    $data['status']=0;
					}	

           		   

        return $data;
    }

	//tktcnvrstnsAdd
    function tktcnvrstnsAdd($ticket_id,$emp_number,$date_time,$comments)
    {
        $data=array();
   			// Prepare an insert statement
			$sql = "INSERT INTO ohrm_ticket_conversations (ticket_id,emp_number,date_time,comments) VALUES (?,?,?,?)";
			 
			if($stmt = mysqli_prepare($this->conn, $sql)){
			    // Bind variables to the prepared statement as parameters
			     mysqli_stmt_bind_param($stmt, "iiss" , $ticket_id,$emp_number,$date_time,$comments);
			    			   
			    // Attempt to execute the prepared statement
			    if(mysqli_stmt_execute($stmt)){
			        $data['tktcnvrstns'] = "Ticket conversation added successfully";
			        $data['status']=1;
			    } else{
			        //echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
			        $data['status']=0;
			    }
			} else{
			    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
			    $data['status']=0;
			}	

		
        return $data;
    }

	//tktcnvrstns
    function tktconvrstns($ticket_id)
    {
        $data=array();
   			
		//getUser Details
		$query="SELECT cnvs.emp_number as emp_number,CONCAT(emp.emp_firstname,emp.emp_lastname) as empname, cnvs.ticket_id as ticket_id, cnvs.date_time as date_time, cnvs.comments as comments FROM ohrm_ticket_conversations cnvs JOIN hs_hr_employee emp ON emp.emp_number = cnvs.emp_number WHERE ticket_id = $ticket_id";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
						$row=mysqli_fetch_assoc($count);
					do{ 						
						$data['emp_number']=$row['emp_number'];
						$data['empname']=$row['empname'];
						$data['ticket_id']=$row['ticket_id'];
						$data['date_time']=$row['date_time'];
						$data['comments']=$row['comments'];
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count));
						$data['tktconvrstns']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
        return $data;
    }

	//alltktconvrstns
    function alltktconvrstns()
    {
        $data=array();
   			
		//getUser Details
		$query="SELECT cnvs.emp_number as emp_number,CONCAT(emp.emp_firstname,emp.emp_lastname) as empname, cnvs.ticket_id as ticket_id, cnvs.date_time as date_time, cnvs.comments as comments FROM ohrm_ticket_conversations cnvs JOIN hs_hr_employee emp ON emp.emp_number = cnvs.emp_number JOIN ohrm_ticket tkt ON tkt.id = cnvs.ticket_id";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
						$row=mysqli_fetch_assoc($count);
					do{ 						
						$data['emp_number']=$row['emp_number'];
						$data['empname']=$row['empname'];
						$data['ticket_id']=$row['ticket_id'];
						$data['date_time']=$row['date_time'];
						$data['comments']=$row['comments'];
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count));
						$data['alltktconvrstns']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
        return $data;
    }


	//EnMNewTasks($plant_id)
    function EnMNewTasks($user_id,$location_id)
    {
        $data=array();
		//getUser Details
       	
       	$i=0;
		$empNumber = $this->getEmpnumberByUserId($user_id);
       	$empresult=$this->employeeDetails($empNumber);

       	$plantId = $empresult['plant_id'];
  //      	echo "<pre>";
  //      	print_r($empresult);
		// exit();
		$query="SELECT o.id AS id, o.job_id AS job_id, o.location_id AS location_id, o.plant_id AS plant_id, o.user_department_id AS user_department_id, o.functional_location_id AS functional_location_id, o.equipment_id AS equipment_id, o.type_of_issue_id AS type_of_issue_id, o.status_id AS status_id, o.sla AS sla, o.subject AS subject, o.description AS description, o.priority_id AS priority_id, o.severity_id AS severity_id, o.reported_by AS reported_by, o.reported_on AS reported_on, o.submitted_by_name AS submitted_by_name, o.submitted_by_emp_number AS submitted_by_emp_number, o.submitted_on AS submitted_on, o.modified_by_name AS modified_by_name, o.modified_by_emp_number AS modified_by_emp_number, o.modified_on AS modified_on, o.is_preventivemaintenance AS is_preventivemaintenance, o.is_deleted AS is_deleted FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id WHERE (o2.status_id IN (1, 6) AND o.location_id = $location_id AND o.plant_id = $plantId AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) ORDER BY o.id DESC";

		

		

		$count=mysqli_query($this->conn, $query);

		
		if(mysqli_num_rows($count) > 0)
		{
			
						$row=mysqli_fetch_assoc($count);
					do{ 	

						$i=$i+1;
						$data['sno']=$i;				
						$data['id']=$row['id'];
						$data['job_id']=$row['job_id'];
						$text = $row['subject'];
						$data['subject']=iconv(mb_detect_encoding($text), "UTF-8//IGNORE", $text);
						
						
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count));
						$data['emTasks']=$data1;
						$data['status']=1;
							
		}else{
				/*$data1[] = $data;
				$data['emTasks']=$data1;*/
				$data['status']=0;
				$data['count']=0;	
			}
        return $data;
    }


    //EnMENgTechTasks($plant_id)
    function EnMEngTechTasks($user_id,$type_id)
    {
        $data=array();
        	$response = array();
			$db = new DbHandler();

			$j = 0;

			$empNumber = $this->getEmpnumberByUserId($user_id);
			$locationid = $this->getLocationByUserId($user_id);
       		$empresult1=$this->employeeDetails($empNumber);
			$plantId = $empresult1['plant_id'];
			$usrRolId =$this->userIdbyUserRoleId($user_id);
			// echo $usrRolId;
			// exit();
			if($usrRolId == 11 &&  $type_id == 12){	
				$empresult=$this->subordinateByEmpList($empNumber);

				if($empresult = ' ')
				{
					
			        	//echo $empNumber;
			        	$empLists = $empNumber;

					}
					
				else
				{

					for ($i=0; $i < sizeof($empresult['emplist']) ; $i++) { 
			        	$empList[] = $empresult['emplist'][$i];
			        	//to convert Array into string the following implode method is used
			        	$empLists = implode(',', $empList);
			        }

				}
						
			}else{
					$empresult=$this->empList($type_id);
	        for ($i=0; $i < sizeof($empresult['emplist']) ; $i++) { 
	        	$empList[] = $empresult['emplist'][$i];
	        	//to convert Array into string the following implode method is used
	        	$empLists = implode(',', $empList);
	        }
			}
	        

			

			
			
	        // print_r($empLists1);
	        // exit();
	        
	        	
	        if($type_id == 11){
	        	$query = "SELECT o.id AS id, o.job_id AS job_id, o.location_id AS location_id, o.plant_id AS plant_id, o.user_department_id AS user_department_id, o.functional_location_id AS functional_location_id, o.equipment_id AS equipment_id, o.type_of_issue_id AS type_of_issue_id, o.status_id AS status_id, o.sla AS sla, o.subject AS subject, o.description AS description, o.priority_id AS priority_id, o.severity_id AS severity_id, o.reported_by AS reported_by, o.reported_on AS reported_on, o.submitted_by_name AS submitted_by_name, o.submitted_by_emp_number AS submitted_by_emp_number, o.submitted_on AS submitted_on, o.modified_by_name AS modified_by_name, o.modified_by_emp_number AS modified_by_emp_number, o.modified_on AS modified_on, o.is_preventivemaintenance AS is_preventivemaintenance FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id WHERE ((o2.accepted_by IN ($empLists) OR o2.forward_to IN ($empLists)) AND o.location_id = $locationid AND o.plant_id = $plantId AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) ORDER BY o.id DESC";

	        	/*echo $query;
	        	exit();*/
	        }
	        else if($type_id == 12){
	        	$query = "SELECT o.id AS id, o.job_id AS job_id, o.location_id AS location_id, o.plant_id AS plant_id, o.user_department_id AS user_department_id, o.functional_location_id AS functional_location_id, o.equipment_id AS equipment_id, o.type_of_issue_id AS type_of_issue_id, o.status_id AS status_id, o.sla AS sla, o.subject AS subject, o.description AS description, o.priority_id AS priority_id, o.severity_id AS severity_id, o.reported_by AS reported_by, o.reported_on AS reported_on, o.submitted_by_name AS submitted_by_name, o.submitted_by_emp_number AS submitted_by_emp_number, o.submitted_on AS submitted_on, o.modified_by_name AS modified_by_name, o.modified_by_emp_number AS modified_by_emp_number, o.modified_on AS modified_on FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id WHERE ((o.status_id = 3 OR o2.status_id = 7 OR o2.status_id = 9 OR o2.status_id = 8) AND (o2.accepted_by IN (4, 18, 19, 20, 64, 192, 209) OR o2.forward_to IN (4, 18, 19, 20, 64, 192, 209)) OR o2.forward_to IN (4, 18, 19, 20, 64, 192, 209) AND o.location_id = 3 AND o.plant_id = 1 AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) GROUP BY o.id ORDER BY o.id DESC";
	        	/*echo $query;
	        	exit();*/
	        }
		
		//echo $query;
		//exit();
   		  
   		  				$configDate = $this->dateFormat();
							$count=mysqli_query($this->conn, $query);
						
							if(mysqli_num_rows($count) > 0)
							{
											$row=mysqli_fetch_assoc($count);
										do{ 		

											$j = $j+1;

											$data['sno']=$j;				
											$data['id']=$row['id'];
											$data['job_id']=$row['job_id'];
											$text = $row['subject'];
											$data['subject']=iconv(mb_detect_encoding($text), "UTF-8//IGNORE", $text);
											
											
											$data1[] = $data;
										}while($row = mysqli_fetch_assoc($count));
											
											$data['taskslist']=$data1;
											$data['status']=1;
												
							}else{
									$data['status']=0;
							}               	
                	

		return $data;
    }
  
	//TechTsksLstEmpnum($emp_number)
    function TechTsksLstEmpnum($emp_number)
    {
        $data=array();

         $empresult=$this->empList(12);
		 // echo "<pre>";
		 /*print_r($empresult);
		 exit();*/
	        for ($i=0; $i < sizeof($empresult['emplist']) ; $i++) { 
	        	$empList[] = $empresult['emplist'][$i];
	        	//to convert Array into string the following implode method is used
	        	$empLists = implode(',', $empList);
	        }

        $i=0;
        $query = "SELECT o.id AS id, o.job_id AS job_id, o.location_id AS o__location_id, o.plant_id AS o__plant_id, o.user_department_id AS o__user_department_id, o.notify_to AS o__notify_to, o.functional_location_id AS o__functional_location_id, o.equipment_id AS o__equipment_id, o.type_of_issue_id AS o__type_of_issue_id, o.status_id AS o__status_id, o.sla AS o__sla, o.subject AS subject, o.description AS o__description, o.priority_id AS o__priority_id, o.severity_id AS o__severity_id, o.reported_by AS o__reported_by, o.reported_on AS o__reported_on, o.submitted_by_name AS o__submitted_by_name, o.submitted_by_emp_number AS o__submitted_by_emp_number, o.submitted_on AS o__submitted_on, o.modified_by_name AS o__modified_by_name, o.modified_by_emp_number AS o__modified_by_emp_number, o.modified_on AS o__modified_on, o.is_preventivemaintenance AS o__is_preventivemaintenance, o.is_deleted AS o__is_deleted, o2.id AS o2__id, o2.ticket_id AS o2__ticket_id, o2.status_id AS o2__status_id, o2.priority_id AS o2__priority_id, o2.severity_id AS o2__severity_id, o2.comment AS o2__comment, o2.machine_status AS o2__machine_status, o2.assigned_date AS o2__assigned_date, o2.due_date AS o2__due_date, o2.accepted_by AS o2__accepted_by, o2.rejected_by AS o2__rejected_by, o2.submitted_on AS o2__submitted_on, o2.forward_from AS o2__forward_from, o2.forward_to AS o2__forward_to, o2.submitted_by_name AS o2__submitted_by_name, o2.submitted_by_emp_number AS o2__submitted_by_emp_number, o2.created_by_user_id AS o2__created_by_user_id, o2.root_cause_id AS o2__root_cause_id, o2.response_id AS o2__response_id FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id WHERE ((o2.accepted_by IN ($empLists) OR o2.forward_to IN ($empLists)) AND o.location_id = 3 AND o.plant_id = 1 AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) ORDER BY o.id DESC";


       /* $query = "SELECT o.id AS id, o.job_id AS job_id, o.location_id AS o__location_id, o.plant_id AS o__plant_id, o.user_department_id AS o__user_department_id, o.notify_to AS o__notify_to, o.functional_location_id AS o__functional_location_id, o.equipment_id AS o__equipment_id, o.type_of_issue_id AS o__type_of_issue_id, o.status_id AS o__status_id, o.sla AS o__sla, o.subject AS subject, o.description AS o__description, o.priority_id AS o__priority_id, o.severity_id AS o__severity_id, o.reported_by AS o__reported_by, o.reported_on AS o__reported_on, o.submitted_by_name AS o__submitted_by_name, o.submitted_by_emp_number AS o__submitted_by_emp_number, o.submitted_on AS o__submitted_on, o.modified_by_name AS o__modified_by_name, o.modified_by_emp_number AS o__modified_by_emp_number, o.modified_on AS o__modified_on, o.is_preventivemaintenance AS o__is_preventivemaintenance, o.is_deleted AS o__is_deleted, o2.id AS o2__id, o2.ticket_id AS o2__ticket_id, o2.status_id AS o2__status_id, o2.priority_id AS o2__priority_id, o2.severity_id AS o2__severity_id, o2.comment AS o2__comment, o2.machine_status AS o2__machine_status, o2.assigned_date AS o2__assigned_date, o2.due_date AS o2__due_date, o2.accepted_by AS o2__accepted_by, o2.rejected_by AS o2__rejected_by, o2.submitted_on AS o2__submitted_on, o2.forward_from AS o2__forward_from, o2.forward_to AS o2__forward_to, o2.submitted_by_name AS o2__submitted_by_name, o2.submitted_by_emp_number AS o2__submitted_by_emp_number, o2.created_by_user_id AS o2__created_by_user_id, o2.root_cause_id AS o2__root_cause_id, o2.response_id AS o2__response_id FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id WHERE ((o.status_id = 3 OR o2.status_id = 7 OR o2.status_id = 9 OR o2.status_id = 8) AND (o2.accepted_by IN ($empLists) OR o2.forward_to IN ($empLists)) OR o2.forward_to IN ($empLists) AND o.location_id = 3 AND o.plant_id = 1 AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) ORDER BY o.id DESC";*/

        /*echo $query;
        exit();*/
        $configDate = $this->dateFormat();
		$count=mysqli_query($this->conn, $query);

		//$jobsCountNew = mysqli_num_rows($count);

			//echo $jobsCountNew;
			//exit();

		if(mysqli_num_rows($count) > 0)
		{	

						$row=mysqli_fetch_assoc($count);
					do{ 

						$i=$i+1;

						$data['sno']=$i;					
						$data['id']=$row['id'];
						$data['job_id']=$row['job_id'];
						$text = $row['subject'];
						$data['subject']=iconv(mb_detect_encoding($text), "UTF-8//IGNORE", $text);
						
						$data['status']="New";
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count));
						$data['technicianTasks']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
        return $data;
    }


//ReslvdTsksLst($emp_number)
    function ReslvdTsksLst($emp_number)
    {
        $data=array();

        $i = 0;
        $query = "SELECT t.id as id,t.job_id AS job_id,t.subject AS subject,tp.name AS priority,tsv.name AS severity,t.sla AS sla,
		CONCAT(e.emp_firstname,' ',e.emp_lastname) AS raised_by,t.reported_on AS raised_on,ta.submitted_by_name AS acknowledged_by,ta.submitted_on AS acknowledged_on,ts.name AS status
			FROM ohrm_ticket_acknowledgement_action_log ta
			LEFT JOIN ohrm_ticket t ON t.id = ta.ticket_id
			LEFT JOIN ohrm_ticket_priority tp ON tp.id = ta.priority_id
			LEFT JOIN ohrm_ticket_severity tsv ON tsv.id = ta.severity_id
			LEFT JOIN hs_hr_employee e ON e.emp_number = t.reported_by
			 LEFT JOIN ohrm_ticket_status ts ON ts.id = ta.status_id
			WHERE ta.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) and ta.forward_to = $emp_number AND ta.status_id = 14
			order by id desc";
			$configDate = $this->dateFormat();
		
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
						$row=mysqli_fetch_assoc($count);
					do{ 		
						$i = $i+1;

						$data['sno']=$i;				
						$data['id']=$row['id'];
						$data['job_id']=$row['job_id'];
						//$data['subject']=$row['subject'];
						$text = $row['subject'];
						$data['subject']=iconv(mb_detect_encoding($text), "UTF-8//IGNORE", $text);
						$data['priority']=$row['priority'];
						$data['severity']=$row['severity'];
						$data['sla']=$row['sla'];
						$data['raised_by']=$row['raised_by'];
						$data['raised_on']=date($configDate, strtotime( $row['raised_on'] )).' '.date('H:i', strtotime( $row['raised_on'] ));
						$data['acknowledged_by']=$row['acknowledged_by'];
						$data['acknowledged_on']=date($configDate, strtotime( $row['acknowledged_on'] )).' '.date('H:i', strtotime( $row['acknowledged_on'] ));
						$data['status']=$row['status'];
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count));
						$data['resolvedTasks']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
        return $data;
    }

    function RejectTaskList($emp_number)
    {
        $data=array();

        $i = 0;
        $query = "SELECT t.id as id,t.job_id AS job_id,t.subject AS subject,tp.name AS priority,tsv.name AS severity,t.sla AS sla,
		CONCAT(e.emp_firstname,' ',e.emp_lastname) AS raised_by,t.reported_on AS raised_on,ta.submitted_by_name AS acknowledged_by,ta.submitted_on AS acknowledged_on,ts.name AS status, ta.comment as comment
			FROM ohrm_ticket_acknowledgement_action_log ta
			LEFT JOIN ohrm_ticket t ON t.id = ta.ticket_id
			LEFT JOIN ohrm_ticket_priority tp ON tp.id = ta.priority_id
			LEFT JOIN ohrm_ticket_severity tsv ON tsv.id = ta.severity_id
			LEFT JOIN hs_hr_employee e ON e.emp_number = t.reported_by
			 LEFT JOIN ohrm_ticket_status ts ON ts.id = ta.status_id
			WHERE ta.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) and ta.forward_to = $emp_number AND ta.status_id = 16
			order by id desc";
			//echo $query;
			$configDate = $this->dateFormat();
		
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
						$row=mysqli_fetch_assoc($count);
					do{ 		
						$i = $i+1;

						$data['sno']=$i;				
						$data['id']=$row['id'];
						$data['job_id']=$row['job_id'];
						//$data['subject']=$row['subject'];
						$text = $row['subject'];
						$data['subject']=iconv(mb_detect_encoding($text), "UTF-8//IGNORE", $text);
						$data['priority']=$row['priority'];
						$data['severity']=$row['severity'];
						$data['sla']=$row['sla'];
						$data['comment']=$row['comment'];
						$data['raised_by']=$row['raised_by'];
						$data['raised_on']=date($configDate, strtotime( $row['raised_on'] )).' '.date('H:i', strtotime( $row['raised_on'] ));
						$data['acknowledged_by']=$row['acknowledged_by'];
						$data['acknowledged_on']=date($configDate, strtotime( $row['acknowledged_on'] )).' '.date('H:i', strtotime( $row['acknowledged_on'] ));
						$data['status']=$row['status'];
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count));
						$data['rejectedTasksList']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
        return $data;
    }
	//EngNewTskEmpnum($emp_number)
   function EngNewTskEmpnum($emp_number)
    {
        $data=array();
    $j = 0;
    $result=$this->multipledeptList($emp_number);

    $empresult=$this->employeeDetails($emp_number);

    $issueresult=$this->typeofissuelist($emp_number);

    $multidept[] = $empresult['work_station'];
    $multi_dept = implode(',', $multidept);
    if($result['status']== 1){
        for ($i=0; $i < sizeof($result['deptmultlist']) ; $i++) { 
            $multidept[] = $result['deptmultlist'][$i];
            //to convert Array into string the following implode method is used
            $multi_dept = implode(',', $multidept);
        }
    }
    if($issueresult['status']== 1){
        for ($i=0; $i < sizeof($issueresult['typeid']) ; $i++) { 
            $issueList[] = $issueresult['typeid'][$i];
            //to convert Array into string the following implode method is used
            $multi_issue = implode(',', $issueList);
        }
    }else{
        $multi_issue = -1;
    }


		$empresult1=$this->empList(11);
		 
	        for ($i=0; $i < sizeof($empresult1['emplist']) ; $i++) { 
	        	$empList1[] = $empresult1['emplist'][$i];
	        	//to convert Array into string the following implode method is used
	        	$empLists1 = implode(',', $empList1);

	        }

	$query = "SELECT o.id AS id, o.job_id AS job_id, o.location_id AS o__location_id, o.plant_id AS o__plant_id, o.user_department_id AS o__user_department_id, o.notify_to AS o__notify_to, o.functional_location_id AS o__functional_location_id, o.equipment_id AS o__equipment_id, o.type_of_issue_id AS o__type_of_issue_id, o.status_id AS o__status_id, o.sla AS sla, o.subject AS subject, o.description AS o__description, o.priority_id AS o__priority_id, o.severity_id AS o__severity_id, o.reported_by AS reported_by, o.reported_on AS reported_on, o.submitted_by_name AS submitted_by, o.submitted_by_emp_number AS o__submitted_by_emp_number, o.submitted_on AS submitted_on, o.modified_by_name AS o__modified_by_name, o.modified_by_emp_number AS o__modified_by_emp_number, o.modified_on AS o__modified_on, o.is_preventivemaintenance AS o__is_preventivemaintenance, o.is_deleted AS o__is_deleted, o2.id AS o2__id, o2.ticket_id AS o2__ticket_id, o2.status_id AS o2__status_id, o2.priority_id AS o2__priority_id, o2.severity_id AS o2__severity_id, o2.comment AS o2__comment, o2.machine_status AS o2__machine_status, o2.assigned_date AS o2__assigned_date, o2.due_date AS o2__due_date, o2.accepted_by AS o2__accepted_by, o2.rejected_by AS o2__rejected_by, o2.submitted_on AS o2__submitted_on, o2.forward_from AS o2__forward_from, o2.forward_to AS o2__forward_to, o2.submitted_by_name AS o2__submitted_by_name, o2.submitted_by_emp_number AS o2__submitted_by_emp_number, o2.created_by_user_id AS o2__created_by_user_id, o2.root_cause_id AS o2__root_cause_id, o2.response_id AS o2__response_id,svrty.name as severity,prty.name as priority FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id
		LEFT JOIN ohrm_ticket_severity svrty ON svrty.id = o.severity_id
		LEFT JOIN ohrm_ticket_priority prty ON prty.id = o.priority_id
		WHERE((o.user_department_id IN ($multi_dept) OR o2.forward_to = $emp_number OR o.type_of_issue_id IN ($multi_issue) OR o2.forward_to = $emp_number) AND o2.status_id IN (1,2,6) AND o2.forward_from != $emp_number AND (o2.forward_to IN ($empLists1) OR o2.forward_to IS NULL OR o2.forward_to = 0) AND o.location_id = 3 AND o.plant_id = 1 AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) ORDER BY o.id DESC";

	/*echo $query;
	exit();*/
    
    $configDate = $this->dateFormat();
    $count=mysqli_query($this->conn, $query);

    
    $numofrows = mysqli_num_rows($count);
    if(mysqli_num_rows($count) > 0)
    {
        $row=mysqli_fetch_assoc($count);
        do{ 

            $j = $j+1;

            $data['sno'] = $j;    
            //$data['count'] = $numofrows;                    
            $data['id']=$row['id'];
            $data['job_id']=$row['job_id'];
            //$data['subject']=$row['subject'];
            $text = $row['subject'];
            $data['subject']=iconv(mb_detect_encoding($text), "UTF-8//IGNORE", $text);
            $data['priority']=$row['priority'];
            $data['severity']=$row['severity'];
            $data['sla']=$row['sla'];
            $data['reported_by']=$row['reported_by'];
            $data['reported_on']=date($configDate, strtotime( $row['reported_on'] )).' '.date('H:i', strtotime( $row['reported_on'] ));
            $data['submitted_by']=$row['submitted_by'];
            $data['submitted_on']=date($configDate, strtotime( $row['submitted_on'] )).' '.date('H:i', strtotime( $row['submitted_on'] ));
            $data['status']='New';
            
    
            $data1[] = $data;
        }while($row = mysqli_fetch_assoc($count));

        $data['engNewTasks']=$data1;
        $data['status']=1;
                        
    }else{
        $data['status']=0;
    }
    return $data;
}


	//InPrgTsksLstEmpnum($emp_number)
    function InPrgTsksLstEmpnum($emp_number)
    {
        $data=array();
   		
   		$i = 0;	
		$query="SELECT ta.ticket_id AS ticket_id,t.job_id as job_id,t.sla AS sla,sta.name as status,t.subject as subject,tktprty.name as priority,tktsvrty.name as severity,CONCAT(emp.emp_firstname,emp.emp_lastname) AS raised_by,t.reported_on as raised_on,CONCAT(emp.emp_firstname,emp.emp_lastname) AS acknowledged_by,ta.submitted_on AS acknowledged_on FROM ohrm_ticket_acknowledgement_action_log ta LEFT JOIN ohrm_ticket t ON t.id = ta.ticket_id LEFT JOIN hs_hr_employee emp ON  emp.emp_number = ta.submitted_by_emp_number LEFT JOIN ohrm_location loc ON loc.id = t.location_id  LEFT JOIN ohrm_plant plant ON plant.id = t.plant_id LEFT JOIN ohrm_subunit sub ON sub.id = t.user_department_id  LEFT JOIN ohrm_functional_location func ON func.id = t.functional_location_id LEFT JOIN ohrm_equipment eqp ON eqp.id = t.equipment_id LEFT JOIN ohrm_type_of_issue iss ON iss.id = t.type_of_issue_id LEFT JOIN ohrm_ticket_status sta ON sta.id = t.status_id LEFT JOIN ohrm_ticket_priority tktprty ON tktprty.id = t.priority_id LEFT JOIN ohrm_ticket_severity tktsvrty ON tktsvrty.id = t.severity_id LEFT JOIN hs_hr_employee empsub ON empsub.emp_number  = t.submitted_by_name  WHERE ta.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) and ta.accepted_by = $emp_number AND ta.status_id IN (3,4) ORDER BY t.job_id DESC";
      
      	$configDate = $this->dateFormat();
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
						$row=mysqli_fetch_assoc($count);
					do{ 
						$i = $i+1;
						$data['sno'] = $i;						
						$data['ticket_id']=$row['ticket_id'];
						$data['job_id']=$row['job_id'];
						//$data['subject']=$row['subject'];
						$text = $row['subject'];
						$data['subject']=iconv(mb_detect_encoding($text), "UTF-8//IGNORE", $text);
						$data['priority']=$row['priority'];
						$data['severity']=$row['severity'];
						$data['sla']=$row['sla'];
						$data['raised_by']=$row['raised_by'];
						$data['raised_on']=date($configDate, strtotime( $row['raised_on'] )).' '.date('H:i', strtotime( $row['raised_on'] ));
						$data['acknowledged_by']=$row['acknowledged_by'];
						$data['acknowledged_on']=date($configDate, strtotime( $row['acknowledged_on'] )).' '.date('H:i', strtotime( $row['acknowledged_on'] ));
						$data['status']=$row['status'];
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count));
						$data['inprogressTasks']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
        return $data;
    }

	
    //employeelist($user_role_id)
	 function empList($user_role_id)
    {
        $data=array();
  			
		$query="SELECT e.emp_number as emp_number, u.user_role_id as role_id,l.location_id as location_id,e.plant_id as plant_id ,e.work_station as  work_station FROM hs_hr_employee e LEFT JOIN hs_hr_emp_locations l ON l.emp_number = e.emp_number LEFT JOIN ohrm_user u ON u.emp_number = e.emp_number WHERE u.user_role_id = $user_role_id";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
				$row=mysqli_fetch_assoc($count);
					do{ 
						$data1[] = $row['emp_number'];
					}while($row = mysqli_fetch_assoc($count));
						$data['emplist']=$data1;
						$data['status'] = 1;
		}
		return $data;
    }

    //englist($user_role_id)
	 function engLists()
    {
        $data=array();
  			
		$query="SELECT e.emp_number as emp_number,concat(e.emp_firstname,e.emp_lastname) as name, u.user_role_id as role_id,l.location_id as location_id,e.plant_id as plant_id ,e.work_station as work_station FROM hs_hr_employee e LEFT JOIN hs_hr_emp_locations l ON l.emp_number = e.emp_number LEFT JOIN ohrm_user u ON u.emp_number = e.emp_number WHERE u.user_role_id = 11";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
				$row=mysqli_fetch_assoc($count);
					do{ 
						$data1[] = $row['emp_number'];
					}while($row = mysqli_fetch_assoc($count));
						$data['englist']=$data1;
						$data['status'] = 1;
		}
		return $data;
    }


    function techLists()
    {
        $data=array();
  			
		$query="SELECT e.emp_number as emp_number, u.user_role_id as role_id,l.location_id as location_id,e.plant_id as plant_id ,e.work_station as  work_station FROM hs_hr_employee e LEFT JOIN hs_hr_emp_locations l ON l.emp_number = e.emp_number LEFT JOIN ohrm_user u ON u.emp_number = e.emp_number WHERE u.user_role_id = 12";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
				$row=mysqli_fetch_assoc($count);
					do{ 
						$data1[] = $row['emp_number'];
					}while($row = mysqli_fetch_assoc($count));
						$data['techlist']=$data1;
						$data['status'] = 1;
		}
		return $data;
    }

    //employeelist($user_role_id)
	 function subordinateByEmpList($empnumber)
    {
        $data=array();
  			
		$query="SELECT h.erep_sub_emp_number as emp_number FROM hs_hr_emp_reportto h WHERE (h.erep_sup_emp_number = $empnumber)";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
				$row=mysqli_fetch_assoc($count);
					do{ 
						$data1[] = $row['emp_number'];
					}while($row = mysqli_fetch_assoc($count));
						$data['emplist']=$data1;
						$data['status'] = 1;
		}

		else{
			$data['status'] = 0;
		}
		return $data;
    }

     //smsConfig()
	 function smsConfig()
    {
        $data=array();
  			
		$query="SELECT * FROM ohrm_sms_configuration";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
				$row=mysqli_fetch_assoc($count);
					do{ 
						$data['id'] = $row['id'];
						$data['url'] = $row['url'];
						$data['sender_id'] = $row['sender_id'];
						$data['user_name'] = $row['user_name'];
						$data['password'] = $row['password'];
						$data['smtp_auth_type'] = $row['smtp_auth_type'];
						$data['smtp_security_type'] = $row['smtp_security_type'];
					}while($row = mysqli_fetch_assoc($count));
						$data['sms']=$data;
						$data['status'] = 1;
		}
		return $data;
    }

     //emailConfig()
	 function emailConfig()
    {
        $data=array();
  			
		$query="SELECT * FROM ohrm_email_configuration";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
				$row=mysqli_fetch_assoc($count);
					do{ 
						$data['id'] = $row['id'];
						$data['mail_type'] = $row['mail_type'];
						$data['sent_as'] = $row['sent_as'];
						$data['sendmail_path'] = $row['sendmail_path'];
						$data['smtp_host'] = $row['smtp_host'];
						$data['smtp_port'] = $row['smtp_port'];
						$data['smtp_username'] = $row['smtp_username'];
						$data['smtp_password'] = $row['smtp_password'];
						$data['smtp_auth_type'] = $row['smtp_auth_type'];
						$data['smtp_security_type'] = $row['smtp_security_type'];
					}while($row = mysqli_fetch_assoc($count));
						$data['email']=$data;
						$data['status'] = 1;
		}
		return $data;
    }

    // Employee Mails
    //multipledeptList($user_role_id)
	 function employeeEmails($emp_numbers)
    {	
    	$empLists = implode(',', $emp_numbers);
        $data=array();
  			
		$query="SELECT emp_work_email,emp_mobile,emp_oth_email,CONCAT(emp_firstname,' ',emp_lastname) AS emp_name FROM hs_hr_employee WHERE emp_number IN ($empLists) ";
		$count=mysqli_query($this->conn, $query);

		$data = array();
		if(mysqli_num_rows($count) > 0)
		{
				$row=mysqli_fetch_assoc($count);
				do{ 
					if(!empty($row['emp_work_email'])){
						$data['mail'][] = $row['emp_work_email'];
						$data['mobile'][] = $row['emp_mobile'];
						$data['name'][] = $row['emp_name'];
						$data['status'][] = 1;
					}else{
						$data['mail'][] = $row['emp_oth_email'];
						$data['mobile'][] = $row['emp_mobile'];
						$data['name'][] = $row['emp_name'];
						$data['status'][] = 1;
					}
				}while($row = mysqli_fetch_assoc($count));
		}else{
			 $data['status'][] = 0;
		}
		return $data1['details'] = $data;
    }



    //multipledeptList($user_role_id)
	 function multipledeptList($user_role_id)
    {
        $data=array();
  			
		$query="SELECT user_department_id AS dept_id FROM ohrm_multiple_department WHERE emp_number = $user_role_id ";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
				$row=mysqli_fetch_assoc($count);
					do{ 
						$data1[] = $row['dept_id'];

					}while($row = mysqli_fetch_assoc($count));

					$data['deptmultlist']=$data1;
					$data['status'] = 1;
		}else{
			$data['status'] = 0;
		}
		return $data;
    }

    //Get Employess based on location and Plant
    function empNumByLocPlnt($loc_id,$plant_id,$user_id,$user_role_id)
    {
        $data=array();

        if(strcmp($user_role_id,2)){
        	$query="SELECT u.id AS userId,e.emp_number as empNumber,CONCAT(e.emp_firstname,' ',e.emp_lastname) AS emp_name FROM hs_hr_employee e LEFT JOIN hs_hr_emp_locations l ON l.emp_number = e.emp_number LEFT JOIN ohrm_user u ON u.emp_number = e.emp_number WHERE l.location_id = $loc_id and e.plant_id = $plant_id ORDER BY FIELD(userId, $user_id) DESC";
        }else{
        	$query="SELECT u.id AS userId,e.emp_number as empNumber,CONCAT(e.emp_firstname,' ',e.emp_lastname) AS emp_name FROM hs_hr_employee e LEFT JOIN hs_hr_emp_locations l ON l.emp_number = e.emp_number LEFT JOIN ohrm_user u ON u.emp_number = e.emp_number WHERE u.id = $user_id";
        }
   
        $count=mysqli_query($this->conn, $query);
    if(mysqli_num_rows($count) > 0)
    {
            $row=mysqli_fetch_assoc($count);
            do {
	            $data['id'] = $row['empNumber'];
	            $data['name'] = $row['emp_name'];
	            $data1[] = $data;
			}while($row = mysqli_fetch_assoc($count));
			
			$data['emp_details']=$data1;
			$data['status']=1;
	     
    }else{
        $data['status'] = 0;
    }
    return $data;
}

    //multipledeptList($user_role_id)
	 function typeofissuelist($emp_number)
    {
        $data=array();
  			
		$query="SELECT * FROM ohrm_type_of_issue WHERE engineer_id = $emp_number";
		// exit();
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
				$row=mysqli_fetch_assoc($count);
					do{ 
						$data1[] = $row['id'];
					}while($row = mysqli_fetch_assoc($count));
					$data['typeid']=$data1;
					$data['status'] = 1;
		}else{
			$data['status'] = 0;
		}
		return $data;
    }

     //multipledeptList($user_role_id)
	 function employeeDetails($emp_number)
    {
        $data=array();
  			
		$query="SELECT *,CONCAT(emp_firstname,' ',emp_lastname) AS emp_name FROM hs_hr_employee WHERE emp_number = $emp_number ";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
				$row=mysqli_fetch_assoc($count);
				
					
						$data['emp_number'] = $row['emp_number'];
						$data['employee_id'] = $row['employee_id'];
						$data['emp_name'] = $row['emp_name'];
						$data['emp_nick_name'] = $row['emp_nick_name'];
						$data['emp_fathername'] = $row['emp_fathername'];
						$data['emp_mothername'] = $row['emp_mothername'];
						$data['emp_smoker'] = $row['emp_smoker'];
						$data['ethnic_race_code'] = $row['ethnic_race_code'];	
						$data['emp_birthday'] = $row['emp_birthday'];
						$data['nation_code'] = $row['nation_code'];
						$data['emp_gender'] = $row['emp_gender'];
						$data['emp_marital_status'] = $row['emp_marital_status'];
						$data['emp_ssn_num'] = $row['emp_ssn_num'];
						$data['emp_sin_num'] = $row['emp_sin_num'];
						$data['emp_other_id'] = $row['emp_other_id'];
						$data['emp_pancard_id'] = $row['emp_pancard_id'];
						$data['emp_uan_num'] = $row['emp_uan_num'];
						$data['emp_pf_num'] = $row['emp_pf_num'];
						$data['emp_dri_lice_num'] = $row['emp_dri_lice_num'];
						$data['emp_dri_lice_exp_date'] = $row['emp_dri_lice_exp_date'];
						$data['emp_military_service'] = $row['emp_military_service'];
						$data['blood_group'] = $row['blood_group'];
						$data['emp_hobbies'] = $row['emp_hobbies'];
						$data['emp_status'] = $row['emp_status'];
						$data['job_title_code'] = $row['job_title_code'];
						$data['eeo_cat_code'] = $row['eeo_cat_code'];
						$data['work_station'] = $row['work_station'];
						$data['department'] = $row['department'];
						$data['emp_street1'] = $row['emp_street1'];
						$data['emp_street2'] = $row['emp_street2'];
						$data['city_code'] = $row['city_code'];
						$data['coun_code'] = $row['coun_code'];
						$data['provin_code'] = $row['provin_code'];
						$data['emp_zipcode'] = $row['emp_zipcode'];
						$data['emp_hm_telephone'] = $row['emp_hm_telephone'];
						$data['emp_mobile'] = $row['emp_mobile'];
						$data['emp_work_telephone'] = $row['emp_work_telephone'];
						$data['emp_work_email'] = $row['emp_work_email'];
						$data['sal_grd_code'] = $row['sal_grd_code'];
						$data['joined_date'] = $row['joined_date'];
						$data['emp_oth_email'] = $row['emp_oth_email'];
						$data['termination_id'] = $row['termination_id'];
						$data['emp_ctc'] = $row['emp_ctc'];
						$data['emp_cost_of_company'] = $row['emp_cost_of_company'];
						$data['emp_gross_salary'] = $row['emp_gross_salary'];
						$data['custom1'] = $row['custom1'];
						$data['custom2'] = $row['custom2'];
						$data['custom3'] = $row['custom3'];
						$data['custom4'] = $row['custom4'];
						$data['custom5'] = $row['custom5'];
						$data['custom6'] = $row['custom6'];
						$data['custom7'] = $row['custom7'];
						$data['custom8'] = $row['custom8'];
						$data['custom9'] = $row['custom9'];
						$data['custom10'] = $row['custom10'];
						$data['plant_id'] = $row['plant_id'];




					$data['empdetails']=$data;
					$data['status'] = 1;
		}else{
			$data['status'] = 0;
		}
		return $data;
    }

// dept lists
     function deptLists($location_id,$plant_id)
  {
    $data= array();
    $result = array();
    //getUser Details
    $query="SELECT name FROM ohrm_location WHERE id = $location_id";
    $count=mysqli_query($this->conn, $query);

    if(mysqli_num_rows($count) > 0)
    {
      
      $row=mysqli_fetch_assoc($count);
        $data['name']=$row['name']; 
        $locname = $data['name'];

        $query1="SELECT lft,rgt FROM ohrm_subunit WHERE name = '".$locname."'";
        $count1=mysqli_query($this->conn, $query1);

        if(mysqli_num_rows($count1) > 0)
        {
          $row1=mysqli_fetch_assoc($count1);
          $data['left']=$row1['lft'];
          $data['right']=$row1['rgt'];  
          $lft = $data['left'];
          $rgt = $data['right']; 
                  
          $query2="select plant_name from ohrm_plant WHERE id = $plant_id";
          $count2=mysqli_query($this->conn, $query2);

            if(mysqli_num_rows($count2) > 0)
            {
              $row=mysqli_fetch_assoc($count2);
              $data['plant_name']=$row['plant_name'];
              $plantname = $data['plant_name'];
              
                $query3="SELECT lft,rgt FROM ohrm_subunit WHERE name = '$plantname' and lft > ". $lft." and rgt < ".$rgt;
                    $count=mysqli_query($this->conn, $query3);

                      if(mysqli_num_rows($count) > 0)
                      {
                        $row=mysqli_fetch_assoc($count);
                        $data['lft']=$row['lft'];
                        $data['rgt']=$row['rgt']; 
                        $lft = $data['lft'];
                        $rgt = $data['rgt']; 

                      
                          $query4="SELECT * FROM ohrm_subunit WHERE lft > ". $lft." and rgt < ".$rgt. " ORDER BY name ASC";
                          
                          $count=mysqli_query($this->conn, $query4);

                          if(mysqli_num_rows($count) > 0)
                          {
                        
                            $row=mysqli_fetch_assoc($count);
                            do{ 
                              $result['id']=$row['id'];
                              $result['name']=$row['name'];
                              $data1[] = $result;
                            }while($row = mysqli_fetch_assoc($count));
                              $data['deptlst']=$data1;
                              $data['status'] = 1;
                            
                        
                          }else {
                              //echo "ERROR: Could not able to execute $query4. " . mysqli_error($this->conn);
                                     $data['status']=0;
                            }
                      }else {
                              //echo "ERROR: Could not able to execute $query3. " . mysqli_error($this->conn);
                               $data['status']=0;
                      }
          
            }else {
                  //echo "ERROR: Could not able to execute $query2. " . mysqli_error($this->conn);
                   $data['status']=0;
            } 

        }else {
            //echo "ERROR: Could not able to execute $query1. " . mysqli_error($this->conn);
             $data['status']=0;
          }
        
    }else{
        $data['status']=0;
      }
      
    return $data;     
  }



   /*//punch in or punch out
    function punchInOrOut($user_id,$punch_in_utc_time,$punch_in_note,$punch_in_time_offset,$punch_in_user_time,$punch_out_utc_time,$punch_out_note,$punch_out_time_offset,$punch_out_user_time,$state)
    {
        $data=array();

        $EmpNumber = $this->getEmpnumberByUserId($user_id);

        $query="SELECT * FROM ohrm_attendance_record WHERE employee_id = $EmpNumber";
		
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			$state = "PUNCHED OUT";
				$row=mysqli_fetch_assoc($count);
					$updatesql ="UPDATE ohrm_attendance_record 
					SET punch_out_utc_time = $punch_out_utc_time,
						punch_out_note = $punch_out_note,
						punch_out_time_offset = $punch_out_time_offset,
						punch_out_user_time = $punch_out_user_time,
						state = $state
					 WHERE employee_id = $EmpNumber";
					if($result2 = mysqli_query($this->conn, $updatesql)){
						$data['punch_out_utc_time'] = $row['punch_out_utc_time'];
						$data['punch_out_note'] = $row['punch_out_note'];
						$data['punch_out_time_offset'] = $row['punch_out_time_offset'];
						$data['punch_out_user_time'] = $row['punch_out_user_time'];
						$data['state'] = $row['state'];
				        $data['status']=1;
					}else{
					    $data['status']=0;
					}
		}

		else
		{
			$statein = "PUNCHED IN";
   			// Prepare an insert statement
			$sql = "INSERT INTO ohrm_attendance_record (employee_id,punch_in_utc_time,punch_in_note,punch_in_time_offset,punch_in_user_time,state) VALUES (?,?,?,?,?)";
			 
			  $configDate = $this->dateFormat();	
			if($stmt = mysqli_prepare($this->conn, $sql)){
			    // Bind variables to the prepared statement as parameters
			     mysqli_stmt_bind_param($stmt, "issss" , $employee_id,$punch_in_utc_time,$punch_in_note,$punch_in_time_offset,$punch_in_user_time,$statein);
			   		   
			    // Attempt to execute the prepared statement
			    if(mysqli_stmt_execute($stmt)){

			    	$data['punch_in_utc_time'] = $row['punch_in_utc_time'];
			    	date($configDate, strtotime( $row['punch_in_utc_time'] )).' '.date('H:i', strtotime( $row['punch_in_utc_time'] ));
						$data['punch_in_note'] = $row['punch_in_note'];
						$data['punch_in_time_offset'] = date($configDate, strtotime( $row['punch_in_time_offset'] )).' '.date('H:i', strtotime( $row['punch_in_time_offset'] ));
						$data['punch_in_user_time'] = date($configDate, strtotime( $row['punch_in_time_offset'] )).' '.date('H:i', strtotime( $row['punch_in_time_offset'] ));
						$data['state'] = $row['state'];
						$data['punchInOrOut'] = $data;
			     
			        $data['status']=1;
			    } else{
			        //echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
			        $data['status']=0;
			    }
			} else{
			    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
			    $data['status']=0;
			}

		}
        return $data;
    }

*/
  function getEmpnumberByUserId($user_id)
        {
            $query = "SELECT emp_number FROM ohrm_user WHERE id = $user_id";
            $result=mysqli_query($this->conn, $query);
            if(mysqli_num_rows($result)>0)
            {
               $row = mysqli_fetch_array($result);
               $emp_number=$row['emp_number'];
            }
       
       return $emp_number;
    }

     function getLocationByUserId($user_id)
        {
            $query = "SELECT l.location_id as location_id FROM ohrm_user u LEFT JOIN hs_hr_emp_locations l ON l.emp_number = u.emp_number WHERE u.id = $user_id";
            $result=mysqli_query($this->conn, $query);
            if(mysqli_num_rows($result)>0)
            {
               $row = mysqli_fetch_array($result);
               $emp_number=$row['location_id'];
            }
       
       return $emp_number;
    }

  //Attendance
    function attendance($userId)
	{	
		$empId = $this->getEmpnumberByUserId($userId);
		$data= array();
		$query="SELECT * FROM ohrm_attendance_record WHERE (employee_id = $empId AND state IN ('PUNCHED IN'))";

		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
			$data['id']=$row['id'];
			$data['state']=$row['state'];	
			$data['attendancedetails']=$data;			
			$data['status']=1;
							
		}else{
				$data['id']='';
				$data['state']= 'PUNCHED OUT';	
				$data['status']=0;
				$data['attendancedetails']=$data;
			}
		return $data;    
	}

	function punchInOrOut($id,$user_id,$punch_note,$punch_in_user_time1,$punch_out_user_time1)
    {
      	$data=array();

		    //$EmpNumber = $this->getEmpnumberByUserId($user_id);
		    $punch_out_utc_time1 = date('Y-m-d H:i:s');
		    $punch_out_time_offset1 = 5.5;
		    //$punch_out_user_time1 = date('Y-m-d H:i:s');
		    $state = "PUNCHED OUT";
		    $punch_in_utc_time1 = date('Y-m-d H:i:s');
		    $statein = "PUNCHED IN";
		    $punch_in_time_offset1 = 5.5;
		    //$punch_in_user_time1   = date('Y-m-d H:i:s');
		    $punchId = '';

		    $empId = $this->getEmpnumberByUserId($user_id);
		    $data= array();


				    $query1 ="SELECT id,punch_in_note FROM ohrm_attendance_record WHERE id = $id";
				     if($result1 = mysqli_query($this->conn, $query1))
				     {

				     	$row=mysqli_fetch_assoc($result1);
				     	$punchId = $row['id'];
				     	$punch_in_note = $row['punch_in_note'];

				     }


				     if($punchId == '')
				     {
				     	$query="SELECT ui.last_id FROM hs_hr_unique_id ui WHERE ui.table_name = 'ohrm_attendance_record' AND ui.field_name='id'";
						$count=mysqli_query($this->conn, $query);

						if(mysqli_num_rows($count) > 0)
						{
							$row=mysqli_fetch_assoc($count);

							$data['last_id']=$row['last_id'];
							$id = $row['last_id']+1;

							$sql = "UPDATE hs_hr_unique_id SET last_id = ".$id." WHERE table_name = 'ohrm_attendance_record' AND field_name='id'";
							
							$result=mysqli_query($this->conn, $sql);
						}


						$sql = "INSERT INTO ohrm_attendance_record (id,employee_id,punch_in_utc_time,punch_in_note,
				     	punch_in_time_offset,punch_in_user_time,state) VALUES (?,?,?,?,?,?,?)";


							
					if($stmt1213 = mysqli_prepare($this->conn, $sql)){

						// echo "if";
						// exit();

					    // Bind variables to the prepared statement as parameters
					     mysqli_stmt_bind_param($stmt1213, "iisssss" , $id,$empId,$punch_in_utc_time1,$punch_note,$punch_in_time_offset1,$punch_in_user_time1,$statein);

					    // Attempt to execute the prepared statement
					    if(mysqli_stmt_execute($stmt1213)){
					    	
					    	$data['punchInpunchOutdetails']= "Punched in Successfully";          
				        	$data['status']=1;
					    } else{

					        $data['status']=0;
					        //echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
					    }
					} else{
						
					    $data['status']=0;
					    //echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
					}	
			
				     }
				     else
				     {

				     		$query="UPDATE ohrm_attendance_record 
                SET punch_out_utc_time = '$punch_out_utc_time1',
                    punch_out_note = '$punch_note',
                    punch_out_time_offset = '$punch_out_time_offset1',
                    punch_out_user_time = '$punch_out_user_time1',
                    state = '$state'
                 WHERE id = $id";


                  $query23="SELECT * FROM ohrm_attendance_record WHERE (employee_id = $empId AND state IN ('PUNCHED IN'))";
				    $count=mysqli_query($this->conn, $query23);
				    if(mysqli_num_rows($count) > 0)
				    {
				        $row=mysqli_fetch_assoc($count);
				        $data2['id']=$row['id'];
				        $data2['state']=$row['state'];  

				        if($result2 = mysqli_query($this->conn, $query))
				    		{
				        		/*echo "punched out";
				    					exit();*/
				            // $row=mysqli_fetch_assoc($result2);
				                
				                
				                    $data['punch_out_utc_time'] = $result2['punch_out_utc_time'];
				                    $data['punch_out_note'] = $result2['punch_out_note'];
				                    $data['punch_out_time_offset'] = $result2['punch_out_time_offset'];
				                    $data['punch_out_user_time'] = $result2['punch_out_user_time'];
				                    $data['state'] = $result2['state'];
				                   
							    }else{
							    	
							                    $data['status']=0;
							                    

							    }

				        $data['punchInpunchOutdetails']="Punched Out Successfully";            
				        $data['status']=1;
				                        
				    }else{

				    	/*echo "already punched out";
				    	exit();*/
				            $data['punchInpunchOutdetails']="Already Punched Out";            
				            $data['status']=0;
			
				        }
    		

		     }

				   


    			return $data;

		}


		//machinebreakdown details webservice
    function machineBreakDownList()
	{	
		
		$data= array();
		/*$query="SELECT t.id as ticket_id,act.machine_status as status_id,st.name as machineStatus,eq.id as eqpId,eq.name as equipment_name,rc.id as root_cause_id, rc.name as root_cause FROM ohrm_ticket t LEFT JOIN ohrm_ticket_acknowledgement_action_log act ON act.ticket_id = t.id LEFT JOIN ohrm_machine_status st ON act.machine_status = st.id LEFT JOIN ohrm_equipment eq ON t.equipment_id = eq.id LEFT JOIN ohrm_root_cause rc ON act.root_cause_id = rc.id WHERE act.machine_status = $machine_status_id";*/

		$query="SELECT t.id as ticket_id,act.machine_status as status_id,st.name as machineStatus,eq.id as eqpId,eq.name as equipment_name,rc.id as root_cause_id, rc.name as root_cause FROM ohrm_ticket t LEFT JOIN ohrm_ticket_acknowledgement_action_log act ON act.ticket_id = t.id LEFT JOIN ohrm_machine_status st ON act.machine_status = st.id LEFT JOIN ohrm_equipment eq ON t.equipment_id = eq.id LEFT JOIN ohrm_root_cause rc ON act.root_cause_id = rc.id WHERE act.machine_status = 1";

		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			while($row = mysqli_fetch_assoc($count)) { 
				$data['ticket_id']=$row['ticket_id'];
				$data['status_id']=$row['status_id'];	
				$data['machineStatus']=$row['machineStatus'];
				$data['eqpId']=$row['eqpId'];
				$data['equipment_name']=$row['equipment_name'];
				$data['root_cause']=$row['root_cause'];
				$data1[]= $data;
			}

			$data['machineBrkDwnDetails']=$data1;
			$data['status']=1;

		}else{

			$data['status']=0;
		}

		return $data;  	
	}

	//Password Generator
    function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

	function forgotPassword($username)
	{
		$data=array();
		$token=$this->generateApiKey();

		$userEmail = $this->getEmailByUsrname($username);

		$user_id = $this->getUserIdByUsrname($username);
		
			$rndno = $this->randomPassword();
			$data['status']=1;
			$data['user_id']=$user_id;
			$data['password'] = $rndno;
			$data['email'] = $userEmail;
			
		return $data;
	}

	function updatePassword($userId,$password){

		$hashPassword = $this->hashPassword($password);
		$query ="UPDATE ohrm_user SET user_password= '$hashPassword' WHERE id=$userId";
										
		$count=mysqli_query($this->conn, $query);
		return $count;
	}

	public function hashPassword($password) {
        return $this->getPasswordHasher()->hash($password);
    }

	public function getPasswordHasher() {
        if (empty($this->passwordHasher)) {
            $this->passwordHasher = new PasswordHash();
        }        
        return $this->passwordHasher;
    }

    public function setPasswordHasher($passwordHasher) {
        $this->passwordHasher = $passwordHasher;
    }

	    
    //this is for otp verfication function
    function pwdOtpVerify($user_id,$otp)
	{
		$data=array();
		$query = "SELECT otp FROM ohrm_user_token WHERE userid = $user_id";
		// echo $query;exit;
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{			
			$row=mysqli_fetch_assoc($count);
			$data['otp']=$row['otp'];
			if($row['otp'] == $otp){
				$data['otpverified']="Successfully";
				$data['status']=1;
			}else{
				$data['status']=2;
			}
		}
			else{
				$data['status']=0;
			}
		
		return $data;
    }


    //this is for otp verfication function
    function passwordChange($user_id,$oldPassword,$newPassword)
	{

	
		$data=array();

		$query = "SELECT u.user_password AS user_password FROM ohrm_user u WHERE u.deleted=0 and u.id =$user_id";

		$count=mysqli_query($this->conn, $query);

		// echo $query;
		// exit();
		if(mysqli_num_rows($count) > 0)
		{
				
			$row=mysqli_fetch_assoc($count);
			$userPassword = $row['user_password'];

			$verify = password_verify($oldPassword,$userPassword);

			if($verify){
				$result = $this->updatePassword($user_id,$newPassword);
				if($result){
					$data['status']=1;
					$data['message']="Password Changed Successfully";
				}else{
					$data['status']=1;
					$data['message']="Password Changed unsuccessful";
				}
			}else{
				
				$data['status']=0;
				$data['message']="Password Incorrect";
			   
	    	}

		}else{
			$data['status']=0;
			$data['message']="User does not exist";
    	}

		return $data;
    }



 function JobsBasedonDept($userIdPass)
    {
        $data=array();
		//getUser Details

		$userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$departmentId = $empresult['work_station'];

		/*echo $department;
		exit();*/
       	
       $i=0;
		$query = "SELECT * FROM ohrm_ticket WHERE user_department_id = $departmentId";	

		
		$configDate = $this->dateFormat();

		$count=mysqli_query($this->conn, $query);

		
		if(mysqli_num_rows($count) > 0)
		{
			
						$row=mysqli_fetch_assoc($count);
					do{ 	

						$i=$i+1;
						$data['sno']=$i;
						//$data['count']=$count;					
						$data['id']=$row['id'];
						$data['job_id']=$row['job_id'];
						/*$data['location_id']=$row['location_id'];
						$data['user_department_id']=$row['user_department_id'];
						$data['notify_to']=$row['notify_to'];
						$data['functional_location_id']=$row['functional_location_id'];
						$data['equipment_id']=$row['equipment_id'];
						$data['plant_id']=$row['plant_id'];
						$data['type_of_issue_id']=$row['type_of_issue_id'];*/
						//$data['status_id']=$row['status_id'];
						/*$data['machine_status']=$row['machine_status'];*/
						//$data['subject']=$row['subject'];
						$text = $row['subject'];
						$data['subject']=iconv(mb_detect_encoding($text), "UTF-8//IGNORE", $text);
						/*$data['priority_id']=$row['priority_id'];
						$data['severity_id']=$row['severity_id'];
						$data['sla']=$row['sla'];
						$data['reported_by']=$row['reported_by'];
						$data['reported_on']=$row['reported_on'];
						$data['submitted_by']=$row['submitted_by_name'];
						$data['submitted_on']=date($configDate, strtotime( $row['submitted_on'] )).' '.date('H:i', strtotime( $row['submitted_on'] ));
						$data['modified_by_name']=$row['modified_by_name'];
						$data['modified_by_emp_number']=$row['modified_by_emp_number'];
						$data['modified_on']=$row['modified_on'];
						$data['is_PreventiveMaintenance']=$row['is_PreventiveMaintenance'];*/
						
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count));
						$data['JobsByDept']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
        return $data;
    }




//JobsHistory($emp_number)
    function JobsHistory($user_id)
    {
        $data=array();

        $usrRolId =$this->userIdbyUserRoleId($user_id);

        $userDetails = $this->getUserRoleByUserId($user_id);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$departmentId = $empresult['work_station'];
		$plantId = $empresult['plant_id'];



        if($usrRolId == 11)
        {

        	$i = 0;
        	$query1 = "SELECT t.id AS id, st.id as statusId,st.name as statusName,count(*) AS status_count from ohrm_ticket t LEFT JOIN ohrm_ticket_status st on t.status_id = st.id where t.status_id NOT IN (1,11) and t.user_department_id = $departmentId GROUP BY t.status_id";

						$configDate = $this->dateFormat();
		
		$count1 = mysqli_query($this->conn, $query1);

		if(mysqli_num_rows($count1) > 0)
		{
						$row=mysqli_fetch_assoc($count1);
					do{ 		
						$i = $i+1;

						$data['sno']=$i;				
						$data['statusId']=$row['statusId'];
						$data['status_count']=$row['status_count'];
						$data['statusName']=$row['statusName'];

						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count1));
						$data['JobsHistory']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}


        }
        else if($usrRolId == 10)
        {


        	$i = 0;
        	$query1 = "SELECT t.id AS id, st.id as statusId,st.name as statusName,count(*) AS status_count from ohrm_ticket t LEFT JOIN ohrm_ticket_status st on t.status_id = st.id where t.status_id NOT IN (1,11) and t.plant_id = $plantId GROUP BY t.status_id";

						$configDate = $this->dateFormat();
		
		$count1 = mysqli_query($this->conn, $query1);

		if(mysqli_num_rows($count1) > 0)
		{
						$row=mysqli_fetch_assoc($count1);
					do{ 		
						$i = $i+1;

						$data['sno']=$i;				
						$data['statusId']=$row['statusId'];
						$data['statusName']=$row['statusName'];
						$data['status_count']=$row['status_count'];
						

						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count1));
						$data['JobsHistory']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}



        }

        else
        {

        	$data['status']=0;

        }
       
       
			
        return $data;
    }


    function JobsDeptHistory($user_id,$status_id)
    {
        $data=array();

        $usrRolId =$this->userIdbyUserRoleId($user_id);

        $userDetails = $this->getUserRoleByUserId($user_id);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$departmentId = $empresult['work_station'];
		$plantId = $empresult['plant_id'];

		/*echo $departmentId. ''.$status_id;
		exit();*/

		 if($usrRolId == 11)
		 {


        	$i = 0;
        	$query1 = "SELECT t.user_department_id AS deptId, su.name as departmentName,COUNT(*) AS subJobscount FROM ohrm_ticket t LEFT JOIN ohrm_subunit su ON t.user_department_id = su.id WHERE t.status_id = $status_id AND t.user_department_id = $departmentId GROUP BY t.user_department_id";

        	/*echo $query1;
        	exit();*/

						$configDate = $this->dateFormat();
		
		$count1 = mysqli_query($this->conn, $query1);

		if(mysqli_num_rows($count1) > 0)
		{
						$row=mysqli_fetch_assoc($count1);
					do{ 		
						$i = $i+1;

						$data['sno']=$i;				
						$data['deptId']=$row['deptId'];
						$data['departmentName']=$row['departmentName'];
						$data['subJobsCount']=$row['subJobsCount'];

						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count1));
						$data['JobsDeptHistory']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}

		}

		else if($usrRolId == 10)

		{


			$i = 0;
        	$query1 = "SELECT t.user_department_id AS deptId, su.name as departmentName,COUNT(*) AS subJobscount FROM ohrm_ticket t LEFT JOIN ohrm_subunit su ON t.user_department_id = su.id WHERE t.status_id = $status_id AND t.plant_id = $plantId GROUP BY t.user_department_id";

						$configDate = $this->dateFormat();
		
		$count1 = mysqli_query($this->conn, $query1);

		if(mysqli_num_rows($count1) > 0)
		{
						$row=mysqli_fetch_assoc($count1);
					do{ 		
						$i = $i+1;

						$data['sno']=$i;				
						$data['deptId']=$row['deptId'];
						$data['departmentName']=$row['departmentName'];
						$data['subJobscount']=$row['subJobscount'];

						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count1));
						$data['JobsDeptHistory']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}


		}


        			
        return $data;
    }



    function JobsDeptTechHistory($user_id,$status_id,$deptId)
    {
        $data=array();

        $usrRolId =$this->userIdbyUserRoleId($user_id);

        $userDetails = $this->getUserRoleByUserId($user_id);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$departmentId = $empresult['work_station'];
		$plantId = $empresult['plant_id'];


		 if($usrRolId == 11)
		 {


        	$i = 0;
        	$query1 = "SELECT emp.emp_number AS employeeNumber, concat(emp.emp_firstname,' ',emp.emp_middle_name,' ',emp.emp_lastname) AS name, usr.id as userId,COUNT(*) as engineer_count FROM ohrm_ticket t 
          LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON l.ticket_id = t.id
          LEFT JOIN hs_hr_employee emp ON emp.emp_number IN (IF(l.accepted_by!=0,l.accepted_by,l.forward_from))
          LEFT JOIN ohrm_user usr ON usr.emp_number = emp.emp_number
          WHERE t.status_id = $status_id and t.user_department_id = $deptId AND l.id IN (
              SELECT MIN(log.id) FROM ohrm_ticket_acknowledgement_action_log log 
              LEFT JOIN hs_hr_employee e ON e.emp_number IN (IF(log.accepted_by!=0,log.accepted_by,log.forward_from))
            LEFT JOIN ohrm_user ou ON ou.emp_number = e.emp_number
              WHERE ou.user_role_id IN (12)
              GROUP BY log.ticket_id)
              GROUP BY emp.emp_number";

						$configDate = $this->dateFormat();
		
		$count1 = mysqli_query($this->conn, $query1);

		if(mysqli_num_rows($count1) > 0)
		{
						$row=mysqli_fetch_assoc($count1);
					do{ 		
						$i = $i+1;

						$data['sno']=$i;				
						$data['employeeNumber']=$row['employeeNumber'];
						$data['userId']=$row['userId'];
						$data['name']=$row['name'];
						$data['engineer_count']=$row['engineer_count'];

						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count1));
						$data['JobsDeptTechHistory']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}

		}

		else

		{


			$i = 0;
        	$query2 = "SELECT emp.emp_number AS employeeNumber, concat(emp.emp_firstname,' ',emp.emp_middle_name,' ',emp.emp_lastname) AS name, usr.id as userId,COUNT(*) as engineer_count FROM ohrm_ticket t LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON l.ticket_id = t.id LEFT JOIN hs_hr_employee emp ON emp.emp_number IN (IF(l.accepted_by!=0,l.accepted_by,l.forward_from)) LEFT JOIN ohrm_user usr ON usr.emp_number = emp.emp_number WHERE t.status_id = $status_id and t.user_department_id = $deptId AND l.id IN ( SELECT MIN(log.id) FROM ohrm_ticket_acknowledgement_action_log log LEFT JOIN hs_hr_employee e ON e.emp_number IN (IF(log.accepted_by!=0,log.accepted_by,log.forward_from)) LEFT JOIN ohrm_user ou ON ou.emp_number = e.emp_number WHERE ou.user_role_id IN (10,11,13) GROUP BY log.ticket_id) GROUP BY emp.emp_number";

						$configDate = $this->dateFormat();
		
		$count2 = mysqli_query($this->conn, $query2);

		if(mysqli_num_rows($count2) > 0)
		{
						$row=mysqli_fetch_assoc($count2);
					do{ 		
						$i = $i+1;

						$data['sno']=$i;				
						$data['employeeNumber']=$row['employeeNumber'];
						$data['userId']=$row['userId'];
						$data['name']=$row['name'];
						$data['engineer_count']=$row['engineer_count'];

						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count2));
						$data['JobsDeptTechHistory']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}


		}


        			
        return $data;
    }



    


     function JobsTechncnHistory($user_id,$status_id,$employee_number)
    {
        $data=array();

        $usrRolId =$this->userIdbyUserRoleId($user_id);

        $userDetails = $this->getUserRoleByUserId($user_id);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$departmentId = $empresult['work_station'];
		$plantId = $empresult['plant_id'];


		if($usrRolId == 11)

		{

			$i = 0;
        	$query1 = "SELECT t.id,t.job_id as job_id, t.subject as subject, t.description AS description, fl.name AS fun_loc,e.name AS equ_name FROM ohrm_ticket t LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id LEFT JOIN ohrm_functional_location fl ON fl.id = t.functional_location_id LEFT JOIN ohrm_equipment e ON e.id = t.equipment_id LEFT JOIN hs_hr_employee emp ON emp.emp_number IN (IF(tal.accepted_by!=0,tal.accepted_by,tal.forward_from)) WHERE t.status_id = $status_id AND t.user_department_id = $departmentId AND emp.emp_number = $employee_number GROUP BY t.id";



						$configDate = $this->dateFormat();
		
		$count1 = mysqli_query($this->conn, $query1);

		if(mysqli_num_rows($count1) > 0)
		{
						$row=mysqli_fetch_assoc($count1);
					do{ 		
						$i = $i+1;

						$data['sno']=$i;				
						$data['job_id']=$row['job_id'];
						$data['subject']=$row['subject'];
						$data['description']=$row['description'];
						$data['fun_loc']=$row['fun_loc'];
						$data['equ_name']=$row['equ_name'];

						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count1));
						$data['JobsTechncnHistory']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}



		}

		else
		{


				$i = 0;
        	$query = "SELECT t.id,t.job_id as job_id, t.subject as subject, t.description AS description, fl.name AS fun_loc,e.name AS equ_name FROM ohrm_ticket t LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id LEFT JOIN ohrm_functional_location fl ON fl.id = t.functional_location_id LEFT JOIN ohrm_equipment e ON e.id = t.equipment_id LEFT JOIN hs_hr_employee emp ON emp.emp_number IN (IF(tal.accepted_by!=0,tal.accepted_by,tal.forward_from)) WHERE t.status_id = $status_id
        		AND t.plant_id = $plantId AND emp.emp_number = $employee_number GROUP BY t.id";



						$configDate = $this->dateFormat();
		
		$count = mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
						$row=mysqli_fetch_assoc($count);
					do{ 		
						$i = $i+1;

						$data['sno']=$i;				
						//$data['sno']=$i;				
						$data['job_id']=$row['job_id'];
						//$data['subject']=$row['subject'];
						$text = $row['subject'];
						$data['subject']=iconv(mb_detect_encoding($text), "UTF-8//IGNORE", $text);
						$data['description']=$row['description'];
						$data['fun_loc']=$row['fun_loc'];
						$data['equ_name']=$row['equ_name'];

						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count));
						$data['JobsTechncnHistory']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}

		}

        
        			
        return $data;
    }


     function MachineWiseBrkDwnAndCount($user_id)
    {
        $data=array();

        $i=0;
        /*$query1 = "SELECT t.equipment_id as eqId,eqp.name as equipmentName,t.job_id as JobId FROM ohrm_ticket t LEFT JOIN ohrm_equipment eqp ON t.equipment_id = eqp.id WHERE t.status_id IN (10,5) GROUP BY t.equipment_id";*/

       /* $query1 = "SELECT t.equipment_id as eqId,eqp.name as equipmentName,t.submitted_on as createdOn, COUNT(*) as jobEqCount FROM ohrm_ticket t LEFT JOIN ohrm_equipment eqp ON t.equipment_id = eqp.id WHERE t.status_id IN (10,5) AND t.equipment_id IN ( SELECT t.equipment_id FROM ohrm_ticket t LEFT JOIN ohrm_equipment eqp ON t.equipment_id = eqp.id WHERE t.status_id IN (10,5) GROUP BY t.equipment_id ) GROUP BY t.equipment_id ORDER BY createdOn DESC";*/


        $query1 = "SELECT t.equipment_id as eqId,eqp.name as equipmentName,t.submitted_on as createdOn, COUNT(*) as jobEqCount,loc.name as 			functionallocation_name,loc.id as functionlocation_id FROM ohrm_ticket t
					LEFT JOIN ohrm_equipment eqp ON t.equipment_id = eqp.id
					LEFT JOIN ohrm_functional_location loc ON loc.id = t.functional_location_id 
					WHERE t.status_id IN (10,5)
					AND t.equipment_id IN ( SELECT t.equipment_id FROM ohrm_ticket t LEFT JOIN ohrm_equipment eqp ON t.equipment_id = eqp.id
					WHERE t.status_id IN (10,5) GROUP BY t.equipment_id ) GROUP BY t.equipment_id ORDER BY createdOn DESC";

						$configDate = $this->dateFormat();
		
		$count1 = mysqli_query($this->conn, $query1);

		$Count = mysqli_num_rows($count1);

		//echo $Count;

		if(mysqli_num_rows($count1) > 0)
		{
						$row=mysqli_fetch_assoc($count1);

					do{ 		
						//$i = $i+1;

						//$data['sno']=$i;	
						$data['eqId']= $row['eqId'];
						
						$data['equipmentName']=$row['equipmentName'];

						$data['jobEqCount']= $row['jobEqCount'];


						$funLoc = $this->subfunctionalLocations($row['functionlocation_id']);
				if($funLoc['status'] == 1){
					$data['functionlocation_id']=$funLoc['id'];
					$data['functionallocation_name']=$funLoc['name'];
					$data['subfunctionlocation_id']=$row['functionlocation_id'];
					$data['subfunctionallocation_name']=$row['functionallocation_name'];
				}else{
					$data['functionlocation_id']=$row['functionlocation_id'];
					$data['functionallocation_name']=$row['functionallocation_name'];
					$data['subfunctionlocation_id']=0;
					$data['subfunctionallocation_name']='';
				}			
						
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count1));
						$data['machineWiseBreakdown']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}

        			
        return $data;
    }


    function EqpmntJobCountEqId($user_id, $equipmntId)
    {
        $data=array();

        $i=0;
        $query1 = "SELECT t.equipment_id as eqId,eqp.name as equipmentName,t.job_id as JobId, t.submitted_on as createdDate FROM ohrm_ticket t LEFT JOIN ohrm_equipment eqp ON t.equipment_id = eqp.id WHERE t.status_id IN (10,5) AND t.equipment_id = $equipmntId";


						$configDate = $this->dateFormat();
		
		$count1 = mysqli_query($this->conn, $query1);

		$Count = mysqli_num_rows($count1);

		//echo $Count;

		if(mysqli_num_rows($count1) > 0)
		{
						$row=mysqli_fetch_assoc($count1);

					//do{ 		
						//$i = $i+1;

						//$data['sno']=$i;	
						$data['eqmntJobCount']= $Count;
						
						//$data['equipmentName']=$row['equipmentName'];	
						//$data['createdDate']= $row['createdDate'];		
						
						$data1[] = $data;
					//}while($row = mysqli_fetch_assoc($count1));
						$data['EqpmntJobCountEqId']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}

        			
        return $data;
    }



    function EqpmntDetailsBasedOnEqId($user_id, $equipmntId)
    {
        $data=array();

        $i=0;
        $query1 = "SELECT t.equipment_id as eqId,eqp.name as equipmentName,t.job_id as JobId, t.submitted_on as createdDate,t.id as ticket_id FROM ohrm_ticket t LEFT JOIN ohrm_equipment eqp ON t.equipment_id = eqp.id WHERE t.status_id IN (10,5) AND t.equipment_id = $equipmntId";

        $count1 = mysqli_query($this->conn, $query1);

        if(mysqli_num_rows($count1) > 0)

		{

			//echo "if";
						$row=mysqli_fetch_assoc($count1);

					
						

						//echo $ticket_id;

						do{ 

							//echo "do";

							$ticket_id= $row['ticket_id'];
							//echo $ticket_id."\n";
							$job_id = $row['JobId'];
							//echo $job_id."\n";
							$eq_id = $row['eqId'];
							//echo $eq_id."\n";
							$eq_name = $row['equipmentName'];
							//echo $eq_name."\n";
							$created_date = $row['createdDate'];
							//echo $created_date."\n";

								$i = $i+1;
							//$data['eqId']= $row['eqId'];
							$data['sno']=$i;	
								$data['eqId']= $row['eqId'];
								//echo $eqId;
								$data['JobId']= $row['JobId'];
								//echo $eqId;
								$data['ticket_id']= $row['ticket_id'];
								$data['equipmentName']=$row['equipmentName'];	
								$data['createdDate']= $row['createdDate'];

							
							

												$configDate = $this->dateFormat();

								$query2 = "SELECT submitted_on as suboncom FROM `ohrm_ticket_acknowledgement_action_log` WHERE ticket_id = $ticket_id AND status_id = 14";

									 $count2 = mysqli_query($this->conn, $query2);

									 $Count = mysqli_num_rows($count2);

									 //echo $Count;
									 //exit();
									  if(mysqli_num_rows($count2) > 0)
								{

									
												$row = mysqli_fetch_assoc($count2);

											
													$suboncom = $row['suboncom']; 
													//echo $suboncom;

												
								}else{

										$suboncom = '';
										$data['status']=0;
									}


									$query3 = "SELECT submitted_on as subonnew FROM `ohrm_ticket_acknowledgement_action_log` WHERE ticket_id = $ticket_id AND status_id = 1";

						       $count3 = mysqli_query($this->conn, $query3);

						       if(mysqli_num_rows($count3) > 0)
								{

										
												$row = mysqli_fetch_assoc($count3);

											
													$subonnew = $row['subonnew']; 
													/*echo $subonnew;
												exit();*/	
												
								}else{

										
										$data['status']=0;
									}


									
							//echo $ticket_id."\n";

									 $query4 = "SELECT submitted_on as subonEndcom FROM `ohrm_ticket_acknowledgement_action_log` WHERE ticket_id = $ticket_id AND status_id = 10";

									 $count4 = mysqli_query($this->conn, $query4);

									  if(mysqli_num_rows($count4) > 0)
								{

									//echo "count4 if";
												$row = mysqli_fetch_assoc($count4);

											
													$subonEndcom = $row['subonEndcom']; 
													//echo $suboncom;

												
								}else{

										//echo "count4 else";

											$subonEndcom = '';
										$data['status']=0;
									}


									/*echo 'com'.$suboncom.' ';
									echo 'new'.$subonnew.' ';
									echo 'endcom'.$subonEndcom.' ';
									exit();*/
									if(($suboncom != '') && ($subonEndcom != ''))
									{


										$dteStart = new DateTime($subonnew); 

											
								   			$dteEnd   = new DateTime($suboncom);

								   			

								   			$dteDiff  = $dteEnd->diff($dteStart); 


								   			
								   			$resolvedDuration = $dteDiff->format("%H:%I"); 
								   			
								   			$interval = $dteStart->diff($dteEnd);
											
											$dys = $interval->format('%a');
											
								                                    $hrs = $interval->format('%h');
								            
								                                    $mins = $interval->format('%i');

								                                    $secs = $interval->format('%s');

								                //echo 'mins'.$mins;

								                 $hrs += $dys * 24;

								                 $ResolvedTime = $hrs.':'.$mins.':'.$secs;

								                 $dteComEnd   = new DateTime($subonEndcom);
								                 $dteDiff1  = $dteComEnd->diff($dteEnd); 

												$resolvedDuration1 = $dteDiff1->format("%H:%I"); 
								   			
								   			$interval = $dteEnd->diff($dteComEnd);
											
											$dys = $interval->format('%a');
											
								                                    $hrs = $interval->format('%h');
								            
								             //echo 'hrs'.$hrs. "\n";
								                                    $mins = $interval->format('%i');

								                                     $secs = $interval->format('%s');
								                //echo 'mins'.$mins. "\n";

								                 $hrs += $dys * 24;

								                 //echo 'hrs'.$hrs. "\n";

								                 $CompletedTime = $hrs.':'.$mins.':'.$secs;

								                 //$dteComEnd   = new DateTime($subonEndcom);
								                 $dteDiff2  = $dteComEnd->diff($dteStart); 

												$resolvedDuration1 = $dteDiff2->format("%H:%I"); 
								   			
								   			$interval = $dteStart->diff($dteComEnd);
											
											$dys = $interval->format('%a');
											
								                                    $hrs = $interval->format('%h');
								            
								                                    $mins = $interval->format('%i');

								                                    $secs = $interval->format('%s');

								                //echo 'mins'.$mins;

								                 $hrs += $dys * 24;

								                 $TotalCompletedTime = $hrs.':'.$mins.':'.$secs;


								                 $data['ResolvedTime']= $ResolvedTime;
						$data['CompletedTime']= $CompletedTime;	
						$data['TotalCompletedTime']= $TotalCompletedTime;

									}

									else
									{

										$dteStart1 = new DateTime($subonnew); 

										//echo 'sub'.$subonnew;
										

										  $dteComEnd1   = new DateTime($subonEndcom);

										  //echo 'end'.$subonEndcom;
										//exit();


										 $dteDiff3  = $dteComEnd1->diff($dteStart1); 

												$resolvedDuration2 = $dteDiff3->format("%H:%I"); 
								   			
								   			$interval1 = $dteStart1->diff($dteComEnd1);
											
											$dys1 = $interval1->format('%a');

											 //echo 'dys1'.$dys1. "\n";
											
								                                    $hrs1 = $interval1->format('%h');
								            
								                                    $mins1 = $interval1->format('%i');

								                                     $secs1 = $interval->format('%s');

								                //echo 'mins1'.$mins1. "\n";

								                 $hrs1 += $dys1 * 24;

								                   //echo 'hrs1'.$hrs1. "\n";

								                 $ResolvedTime1 = '';

								                  $CompletedTime1 = $hrs1.':'.$mins1.':'.$secs1;

								                 $TotalCompletedTime1 = $hrs1.':'.$mins1.':'.$secs1;
										/*$ResolvedTime = '';
										 $CompletedTime = '';

										 $TotalCompletedTime = '';
*/

										 $data['ResolvedTime']= $ResolvedTime1;
						$data['CompletedTime']= $CompletedTime1;	
						$data['TotalCompletedTime']= $TotalCompletedTime1;
										//exit();

									}
						
							
						//$data['eqmntJobCount']= $Count;
						
						
						
								
								$data1[] = $data;
							}while($row = mysqli_fetch_assoc($count1));
								$data['EqpmntDetailsBasedOnEqId']=$data1;
								$data['status']=1;


						
						
						
							
		}else{
				//echo "last else";
				$data['status']=0;
			}

			

			
        			
        return $data;
    }


     function MaintenanceTypeReport($user_id)
    {
        $data=array();

        $query = "SELECT t.job_id AS jobId, t.id AS ticket_id, t.subject AS subject, t.submitted_on AS calFromDate, t.submitted_on AS calToDate, t.submitted_on AS createdOn, ta.machine_status AS machineStatus, fl.name as functionalLocation, fl.id as functionalLocationId,toi.name AS typeOfIssue, toi.id AS typeOfIssueId, toi.sla AS sla,loc.name AS location, loc.id AS locationId, plnt.plant_name AS plantName, plnt.id AS plantId, eq.name AS equipment, eq.id AS equipmentId, ts.name AS status, ts.id AS statusId, ta.ticket_id AS ticketId, t.submitted_by_name AS submittedByName, e.emp_number AS engineerId, e.emp_number AS technicianId,tp.name AS priority, tp.id AS priorityId, tsev.name AS severity, tsev.id AS severityId, u.id AS uaerId, msr.id AS scheduleId, msr.maintenance_type_id AS maintenanceType,mt.id AS maintenanceId, mt.name AS maintenanceName,cs.name AS subDivision, cs.id AS subDivisionId FROM ohrm_ticket t LEFT JOIN ohrm_functional_location fl ON fl.id = t.functional_location_id LEFT JOIN ohrm_type_of_issue toi ON toi.id = t.type_of_issue_id LEFT JOIN ohrm_location loc ON loc.id = t.location_id LEFT JOIN ohrm_plant plnt ON plnt.id = t.plant_id LEFT JOIN ohrm_equipment eq ON eq.id = t.equipment_id LEFT JOIN ohrm_ticket_status ts ON ts.id = t.status_id LEFT JOIN ohrm_ticket_acknowledgement_action_log ta ON ta.ticket_id = t.id LEFT JOIN hs_hr_employee e ON e.emp_number = ta.submitted_by_emp_number LEFT JOIN ohrm_user u ON u.id = ta.created_by_user_id LEFT JOIN ohrm_ticket_priority tp ON tp.id = t.priority_id LEFT JOIN ohrm_ticket_severity tsev ON tsev.id = t.severity_id LEFT JOIN ohrm_subunit cs ON cs.id = t.user_department_id LEFT JOIN ohrm_maintenance_schedule msr ON msr.ticket_id = t.id LEFT JOIN ohrm_maintenance_type mt ON mt.id = msr.maintenance_type_id WHERE t.status_id != 11 AND t.is_PreventiveMaintenance = 1 AND t.status_id IN (10, 5) GROUP BY t.id ORDER BY createdOn DESC";



        	 $count = mysqli_query($this->conn, $query);

       
			if(mysqli_num_rows($count) > 0)
		{
						$row=mysqli_fetch_assoc($count);

						$i = 0;

					do{ 	

						$i = $i+1;

						$data['sno']=$i;

						$ticket_id= $row['ticket_id'];
						//$ticket_id= 553;
						//echo $ticket_id;
						$data['jobId']=$row['jobId'];	
						$data['equipment']= $row['equipment'];
						$data['createDate']= $row['createdOn'];
						$data['maintenanceName']= $row['maintenanceName'];

						

       $query2 = "SELECT submitted_on as suboncom FROM `ohrm_ticket_acknowledgement_action_log` WHERE ticket_id = $ticket_id AND status_id = 14";

									 $count2 = mysqli_query($this->conn, $query2);

									  if(mysqli_num_rows($count2) > 0)
								{

									//echo "count2 if";
												$row = mysqli_fetch_assoc($count2);

											
													$suboncom = $row['suboncom']; 
													//echo 'suboncom'.$suboncom;

												
								}else{

										//echo "count2 else";
										$suboncom = ' ';
										//$data['status']=0;
									}

									//$ticket_id1 = $ticket_id;
									//echo 'ticket_id1'.$ticket_id1;
									//echo 'ticket_id1'.$ticket_id;
									$query3 = "SELECT submitted_on as subonnew FROM `ohrm_ticket_acknowledgement_action_log` WHERE ticket_id = $ticket_id AND status_id = 1";

						       $count3 = mysqli_query($this->conn, $query3);

						       if(mysqli_num_rows($count3) > 0)
								{

										//echo "count3 if";
												$row = mysqli_fetch_assoc($count3);

											
													$subonnew = $row['subonnew']; 
													//echo 'subonnew'.$subonnew;	
												
								}



									//$ticket_id2 = $ticket_id;
									//echo 'ticket_id2'.$ticket_id2;
									//echo 'ticket_id2'.$ticket_id;
									 $query4 = "SELECT submitted_on as subonEndcom FROM `ohrm_ticket_acknowledgement_action_log` WHERE ticket_id = $ticket_id AND status_id = 10";

									 $count4 = mysqli_query($this->conn, $query4);

									  if(mysqli_num_rows($count4) > 0)
								{

									//echo "count4 if";
												$row = mysqli_fetch_assoc($count4);

											
													$subonEndcom = $row['subonEndcom']; 
													//echo 'subonEndcom'.$subonEndcom;

												
								}else{

										//echo "count4 else";
										$subonEndcom =  ' ';
										//echo $subonEndcom;
									}


										//echo 'subonnew'.$subonnew;
							//$dteStart = new DateTime($subonnew); 

										//echo $suboncom;
										//exit();
									
									if($suboncom == ' ')
									{
										//echo "if";
										$ResolvedTime = '--:--';


								                //echo 'after';
								   		//echo 'subonEndcom1'.$subonEndcom;
								   		$dteComEnd   = new DateTime($subonEndcom);

								   		$dteEnd   = new DateTime($suboncom);
								   		$dteStart   = new DateTime($subonnew);
								   		$dteDiff  = $dteEnd->diff($dteStart);
								                 $dteDiff1  = $dteComEnd->diff($dteEnd); 

												$resolvedDuration1 = $dteDiff1->format("%H:%I"); 
								   			
								   			$interval = $dteEnd->diff($dteComEnd);
											
											$dys = $interval->format('%a');
											
								                                    $hrs = $interval->format('%h');
								            
								                                    $mins = $interval->format('%i');

								                                     $secs = $interval->format('%s');

								                //echo 'mins'.$mins;

								                 $hrs += $dys * 24;

								                 $CompletedTime = $hrs.':'.$mins.':'.$secs;

								                 //$dteComEnd   = new DateTime($subonEndcom);
								                 $dteDiff2  = $dteComEnd->diff($dteStart); 

												$resolvedDuration1 = $dteDiff2->format("%H:%I"); 
								   			
								   			$interval = $dteStart->diff($dteComEnd);
											
											$dys = $interval->format('%a');
											
								                                    $hrs = $interval->format('%h');
								            
								                                    $mins = $interval->format('%i');

								                                    $secs = $interval->format('%s');

								                //echo 'mins'.$mins;

								                 $hrs += $dys * 24;

								                 $TotalCompletedTime = $hrs.':'.$mins.':'.$secs;	 

									}	

									else 
									{

										//echo "else";
										$dteEnd   = new DateTime($suboncom);

								   			$dteStart   = new DateTime($subonnew);

								   			$dteDiff  = $dteEnd->diff($dteStart); 


								   			
								   			$resolvedDuration = $dteDiff->format("%H:%I"); 
								   			
								   			$interval = $dteStart->diff($dteEnd);
											
											$dys = $interval->format('%a');
											
								                                    $hrs = $interval->format('%h');
								            
								                                    $mins = $interval->format('%i');

								                                    $secs = $interval->format('%s');
								                //echo 'mins'.$mins;

								                 $hrs += $dys * 24;

								                 $ResolvedTime = $hrs.':'.$mins.':'.$secs;

								                //echo 'after';
								   		//echo $subonEndcom;
								   		$dteComEnd   = new DateTime($subonEndcom);
								                 $dteDiff1  = $dteComEnd->diff($dteEnd); 

												$resolvedDuration1 = $dteDiff1->format("%H:%I"); 
								   			
								   			$interval = $dteEnd->diff($dteComEnd);
											
											$dys = $interval->format('%a');
											
								                                    $hrs = $interval->format('%h');
								            
								                                    $mins = $interval->format('%i');

								                                    $secs = $interval->format('%s');
								                //echo 'mins'.$mins;

								                 $hrs += $dys * 24;

								                 $CompletedTime = $hrs.':'.$mins.':'.$secs;

								                 //$dteComEnd   = new DateTime($subonEndcom);
								                 $dteDiff2  = $dteComEnd->diff($dteStart); 

												$resolvedDuration1 = $dteDiff2->format("%H:%I"); 
								   			
								   			$interval = $dteStart->diff($dteComEnd);
											
											$dys = $interval->format('%a');
											
								                                    $hrs = $interval->format('%h');
								            
								                                    $mins = $interval->format('%i');

								                                     $secs = $interval->format('%s');

								                //echo 'mins'.$mins;

								                 $hrs += $dys * 24;

								                 $TotalCompletedTime = $hrs.':'.$mins.':'.$secs;	 
							

									}	
								   			
						//$data['eqmntJobCount']= $Count;
						
						
						$data['ResolvedTime']= $ResolvedTime;
						$data['CompletedTime']= $CompletedTime;	
						$data['TotalCompletedTime']= $TotalCompletedTime;	
								
						
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count));
						$data['MaintenanceTypeReport']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}

        return $data;

    }



    //ticketDetails
    function ticketDetByTypeOfIssue($userIdPass)
	{
		$data= array();
		$userDetails = $this->getUserRoleByUserId(6);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$name = '';
		$emp_name = '';
		$i=0;

		

			/*$query = "SELECT t.job_id AS job_id, t.id AS ticketId, t.subject AS subject, t.submitted_on AS submittedon,t.is_PreventiveMaintenance AS preventiveMaintenance,ta.machine_status AS machineStatus, fl.name as functionallocation, fl.id as functionalLocationId,toi.name AS issue, toi.id AS typeOfIssueId, toi.sla AS sla,loc.name AS location, loc.id AS locationId, plnt.plant_name AS plant, plnt.id AS plantId, eq.name AS equipment, eq.id AS equipmentId, ts.name AS status, ts.id AS statusId, ta.ticket_id AS ticketId, t.submitted_by_name AS submittedby, e.emp_number AS engineerId, e.emp_number AS technicianId,tp.name AS priority, tp.id AS priorityId, tsev.name AS severity, tsev.id AS severityId, u.id AS uaerId, cs.name AS department, cs.id AS subDivisionId FROM ohrm_ticket t LEFT JOIN ohrm_functional_location fl ON fl.id = t.functional_location_id LEFT JOIN ohrm_type_of_issue toi ON toi.id = t.type_of_issue_id LEFT JOIN ohrm_location loc ON loc.id = t.location_id LEFT JOIN ohrm_plant plnt ON plnt.id = t.plant_id LEFT JOIN ohrm_equipment eq ON eq.id = t.equipment_id LEFT JOIN ohrm_ticket_status ts ON ts.id = t.status_id LEFT JOIN ohrm_ticket_acknowledgement_action_log ta ON ta.ticket_id = t.id LEFT JOIN hs_hr_employee e ON e.emp_number = ta.submitted_by_emp_number LEFT JOIN ohrm_user u ON u.id = ta.created_by_user_id LEFT JOIN ohrm_ticket_priority tp ON tp.id = t.priority_id LEFT JOIN ohrm_ticket_severity tsev ON tsev.id = t.severity_id LEFT JOIN ohrm_subunit cs ON cs.id = t.user_department_id WHERE ta.submitted_by_emp_number = $empNumber AND t.id NOT IN (select id from ohrm_ticket where status_id = 11 and submitted_by_emp_number != $empNumber) GROUP BY t.id
			ORDER BY `t`.`job_id`  DESC";*/

			$query = "SELECT t.job_id AS job_id, t.functional_location_id AS funLocId, t.id AS ticketId, t.subject AS subject, t.submitted_on AS calFromDate, t.submitted_on AS calToDate, t.submitted_on AS createdOn, ta.machine_status AS machineStatus, fl.name as functionallocation_name, fl.id as functionlocation_id,toi.name AS issue, toi.id AS typeOfIssueId, toi.sla AS sla,loc.name AS location, loc.id AS locationId, plnt.plant_name AS plant, plnt.id AS plantId, eq.name AS equipment, eq.id AS equipmentId, ts.name AS status, ts.id AS statusId, ta.ticket_id AS ticketId, t.submitted_by_name AS submittedby, e.emp_number AS engineerId, e.emp_number AS technicianId,tp.name AS priority, tp.id AS priorityId, tsev.name AS severity, tsev.id AS severityId, u.id AS uaerId, msr.id AS scheduleId, msr.maintenance_type_id AS maintenanceType,mt.id AS maintenanceId, mt.name AS maintenanceName,cs.name AS department, cs.id AS subDivisionId,t.is_PreventiveMaintenance AS preventiveMaintenance,t.submitted_on AS submittedon FROM ohrm_ticket t LEFT JOIN ohrm_functional_location fl ON fl.id = t.functional_location_id LEFT JOIN ohrm_type_of_issue toi ON toi.id = t.type_of_issue_id LEFT JOIN ohrm_location loc ON loc.id = t.location_id LEFT JOIN ohrm_plant plnt ON plnt.id = t.plant_id LEFT JOIN ohrm_equipment eq ON eq.id = t.equipment_id LEFT JOIN ohrm_ticket_status ts ON ts.id = t.status_id LEFT JOIN ohrm_ticket_acknowledgement_action_log ta ON ta.ticket_id = t.id LEFT JOIN hs_hr_employee e ON e.emp_number = ta.submitted_by_emp_number LEFT JOIN ohrm_user u ON u.id = ta.created_by_user_id LEFT JOIN ohrm_ticket_priority tp ON tp.id = t.priority_id LEFT JOIN ohrm_ticket_severity tsev ON tsev.id = t.severity_id LEFT JOIN ohrm_subunit cs ON cs.id = t.user_department_id LEFT JOIN ohrm_maintenance_schedule msr ON msr.ticket_id = t.id LEFT JOIN ohrm_maintenance_type mt ON mt.id = msr.maintenance_type_id WHERE t.location_id = 3 AND t.status_id NOT IN (11)
				GROUP BY t.job_id ORDER BY `t`.`job_id` DESC";

		$configDate = $this->dateFormat();

			$count=mysqli_query($this->conn, $query);

			$jobsCountNew = mysqli_num_rows($count);

			/*echo $jobsCountNew;
			exit();*/

				if(mysqli_num_rows($count) > 0)
				{
					// $row=mysqli_fetch_all($count,MYSQLI_ASSOC);
					while($row = mysqli_fetch_assoc($count)) { 	

						$i=$i+1;
						
						if($row['preventiveMaintenance'] == 1){
							$data['sno']=$i;
							$data['ticketId']=$row['ticketId'];
						$data['job_id']=$row['job_id'];
						$data['subject']= 'PM - '.$row['equipment'];
						$data['issue']=$row['issue'];
						$data['location']=$row['location'];
						$data['plant']= $row['plant'];
						$data['department']=$row['department'];
						$data['functionallocation']=$row['functionallocation_name'];
						$funLoc = $this->subfunctionalLocations($row['functionlocation_id']);
											if($funLoc['status'] == 1){
												$data['functionlocation_id']=$funLoc['id'];
												$data['functionallocation_name']=$funLoc['name'];
												$data['subfunctionlocation_id']=$row['functionlocation_id'];
												$data['subfunctionallocation_name']=$row['functionallocation_name'];
											}else{
												$data['functionlocation_id']=$row['functionlocation_id'];
												$data['functionallocation_name']=$row['functionallocation_name'];
												$data['subfunctionlocation_id']=0;
												$data['subfunctionallocation_name']='';
											}
						$data['equipment']= $row['equipment'];
						$data['submittedby']=$row['submittedby'];
						$data['submittedon']=date($configDate, strtotime( $row['submittedon'] )).' '.date('H:i:s', strtotime( $row['submittedon'] ));
						$data['status']= $row['status'];
	
						}else{
							$data['sno']=$i;
							$data['ticketId']=$row['ticketId'];
						$data['job_id']=$row['job_id'];
						$text = $row['subject'];
						$data['subject']=iconv(mb_detect_encoding($text), "UTF-8//IGNORE", $text);
						//$data['subject']= iconv('UTF-8', 'ASCII//IGNORE', utf8_encode($text));
						$data['issue']=$row['issue'];
						$data['location']=$row['location'];
						$data['plant']= $row['plant'];
						$data['department']=$row['department'];
						$data['functionallocation']=$row['functionallocation_name'];
						$data['equipment']= $row['equipment'];
						$data['submittedby']=$row['submittedby'];
						$data['submittedon']=date($configDate, strtotime( $row['submittedon'] )).' '.date('H:i:s', strtotime( $row['submittedon'] ));
						$data['status']= $row['status'];

						}				
						

						if ($row['statusId'] == 3) {
							$ticket_id = $row['ticketId'];

							$q1 = "SELECT s.id AS id,s.name AS name from ohrm_ticket_status s LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON l.status_id = s.id WHERE l.id = (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id GROUP BY ticket_id)"; 
							$res1=mysqli_query($this->conn, $q1);
							if(mysqli_num_rows($res1)>0)
							{
							   $row1 = mysqli_fetch_array($res1);
							   $id=$row1['id'];
							   $name=$row1['name'];
						    }

						    if($id == 4){
						    	$q2 = "SELECT e.emp_number AS emp_number, concat(e.emp_firstname,' ',e.emp_middle_name,' ',e.emp_lastname) AS emp_name from hs_hr_employee e LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON l.accepted_by = e.emp_number WHERE l.id = (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id GROUP BY ticket_id)"; 
							$res2=mysqli_query($this->conn, $q2);
							
							if(mysqli_num_rows($res2)>0)
							{
							   $row2 = mysqli_fetch_array($res2);
							   $emp_number=$row2['emp_number'];
							   $emp_name=$row2['emp_name'];
						    }

								$data['status']= $row['status'].'('.$name.' by '.$emp_name.')';
						    }else{

						    	$q2 = "SELECT e.emp_number AS emp_number, concat(e.emp_firstname,' ',e.emp_middle_name,' ',e.emp_lastname) AS emp_name from hs_hr_employee e LEFT JOIN ohrm_ticket_acknowledgement_action_log l ON l.forward_to = e.emp_number WHERE l.id = (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log WHERE ticket_id = $ticket_id GROUP BY ticket_id)"; 
							$res2=mysqli_query($this->conn, $q2);
							
							if(mysqli_num_rows($res2)>0)
							{
							   $row2 = mysqli_fetch_array($res2);
							   $emp_number=$row2['emp_number'];
							   $emp_name=$row2['emp_name'];
						    }

						    	$data['status']= $row['status'].'('.$name.' to '.$emp_name.')';
						    }
						}
						
						$data1[] = $data;
					}
						$data['ticketDetByTypeOfIssue']=$data1;
						$data['status']=1;
							
				}else{
				$data['status']=0;
			}
		return $data;    
	}


	function ticketsDownTimeReport($userIdPass)
	{
		$data= array();
		
		
		$i = 0;
			$query1 = "SELECT t.job_id as jobId,t.id as ticket_id,t.subject,loc.name as location,p.plant_name as plant,sub.name as department,flc.name as functionallocation_name,flc.id as functionlocation_id,eqp.name as equipmment,t.submitted_on as createdDate FROM ohrm_ticket t
				LEFT JOIN ohrm_subunit sub ON sub.id = t.user_department_id
				LEFT JOIN ohrm_location loc ON loc.id = t.location_id
				LEFT JOIN ohrm_functional_location flc ON flc.id = t.functional_location_id
				LEFT JOIN ohrm_plant p ON p.id = t.plant_id
				LEFT JOIN ohrm_equipment eqp ON eqp.id = t.equipment_id 
				WHERE t.status_id = 5
				ORDER BY t.job_id DESC";

				/*echo $query1;
				exit();*/

		$configDate = $this->dateFormat();

			$count1=mysqli_query($this->conn, $query1);

			$jobsCountNew = mysqli_num_rows($count1);

			/*echo $jobsCountNew;
			exit();*/

			//$Count = mysqli_num_rows($count);

		//echo $Count;



		if(mysqli_num_rows($count1) > 0)
		{

			//echo "if";
						$row=mysqli_fetch_assoc($count1);

						
					do{ 		

							//echo "do";

							$i = $i+1;

							//echo $i;

							$ticket_id = $row['ticket_id'];

							//echo $ticket_id;
							//exit();

							$data['sno']=$i;	
							$data['jobId']=$row['jobId'];
							$jobId = $row['jobId'];
																
							$data['subject']= $row['subject'];	
							$data['location']= $row['location'];	
							$data['plant']= $row['plant'];	
							$data['department']= $row['department'];
							$data['functionalLocation']= $row['functionallocation_name'];
							$funLoc = $this->subfunctionalLocations($row['functionlocation_id']);

							if($funLoc['status'] == 1){

											$data['functionlocation_id']=$funLoc['id'];
																	
											$data['functionallocation_name']=$funLoc['name'];
											$data['subfunctionlocation_id']=$row['functionlocation_id'];
											$data['subfunctionallocation_name']=$row['functionallocation_name'];
									}else{
											$data['functionlocation_id']=$row['functionlocation_id'];
											$data['functionallocation_name']=$row['functionallocation_name'];
											$data['subfunctionlocation_id']=0;
											$data['subfunctionallocation_name']='';
										 }		 
										$data['equipmment']= $row['equipmment'];

							$query10 = "SELECT modified_on FROM ohrm_ticket WHERE id = $ticket_id";

							$count10 = mysqli_query($this->conn, $query10);

							if(mysqli_num_rows($count10) > 0)
							{

								//echo "count3 if";
								$row = mysqli_fetch_assoc($count10);

																		
								$modifiedOn = $row['modified_on']; 
								$subonnew = $modifiedOn;
													

								if($modifiedOn)
								{

														
									$query4 = "SELECT submitted_on as subonverclsd FROM `ohrm_ticket_acknowledgement_action_log` WHERE ticket_id = $ticket_id AND status_id = 5";

									$count4 = mysqli_query($this->conn, $query4);

									if(mysqli_num_rows($count4) > 0)
									{

										//echo "count4 if";
										$row = mysqli_fetch_assoc($count4);

																			
										$subonverclsd = $row['subonverclsd']; 
										//echo 'subonverclsd'.$subonverclsd;
										//exit();	
																				
									 }



								 }
								 else
								 {

										$configDate = $this->dateFormat();
	
										$query3 = "SELECT submitted_on as subonnew FROM `ohrm_ticket_acknowledgement_action_log`
									 			WHERE ticket_id = $ticket_id AND status_id = 1";

												 $count3 = mysqli_query($this->conn, $query3);

												  if(mysqli_num_rows($count3) > 0)
												{

													//echo "count3 if";
													$row = mysqli_fetch_assoc($count3);
																	
													$subonnew = $row['subonnew']; 
													//echo 'subonnew'.$subonnew;	
																		
												}


										$query4 = "SELECT submitted_on as subonverclsd FROM `ohrm_ticket_acknowledgement_action_log` WHERE ticket_id = $ticket_id AND status_id = 5";

												$count4 = mysqli_query($this->conn, $query4);

													if(mysqli_num_rows($count4) > 0)
													{

														//echo "count4 if";
														$row = mysqli_fetch_assoc($count4);

																			
														$subonverclsd = $row['subonverclsd']; 
														//echo 'subonverclsd'.$subonverclsd;
														//exit();	
																				
													}


									}						

															
							}


								$dteStart = new DateTime($subonnew); 

											
								 $dteEnd   = new DateTime($subonverclsd);

								   			

								  $dteDiff  = $dteEnd->diff($dteStart); 


								   			
								  $resolvedDuration = $dteDiff->format("%H:%I"); 
								   			
								  $interval = $dteStart->diff($dteEnd);
											
								$dys = $interval->format('%a');
											
								$hrs = $interval->format('%h');
								            
								$mins = $interval->format('%i');

								$secs = $interval->format('%s');

								                //echo 'mins'.$mins;

								$hrs += $dys * 24;

								$DownTime = $hrs.':'.$mins.':'.$secs;

								$data['createdDate']= $subonnew;

							    $data['ClosedDate']= $subonverclsd;

								$data['DownTime']= $DownTime;

	
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count1));
						$data['ticketsDownTimeReport']=$data1;
						$data['status']=1;
							
		}else{

			//echo "else";
				$data['status']=0;
			}
		
		//echo $data;		
		return $data;    
	}

			

		function machineDownTimeReport($user_id)
		{

				//echo "machinebreakdown";

				$data = array();

				$i = 0;

			$query = "SELECT t.job_id as jobId,t.id as ticket_id,t.subject as subject,loc.name as location,p.plant_name as plant,sub.name as department,flc.name as functionalLocation,eqp.name as equipmment,t.submitted_on as createdDate FROM ohrm_ticket t LEFT JOIN ohrm_subunit sub ON sub.id = t.user_department_id LEFT JOIN ohrm_location loc ON loc.id = t.location_id LEFT JOIN ohrm_functional_location flc ON flc.id = t.functional_location_id LEFT JOIN ohrm_plant p ON p.id = t.plant_id LEFT JOIN ohrm_equipment eqp ON eqp.id = t.equipment_id WHERE t.status_id = 5 ORDER BY t.job_id DESC LIMIT 278";


				$count = mysqli_query($this->conn, $query);

				//$jobsCount = mysqli_num_rows($count);
				//echo $jobsCount;

		if(mysqli_num_rows($count) > 0)
		{
			// echo 'if';
						$row1 = mysqli_fetch_assoc($count);

					do{ 		
						
							// echo 'do';
						/*$i = $i+1;

						$data['sno']=$i;*/


						$data['jobId']= $row1['jobId'];
						
						$data['subject']=$row1['subject'];

						$data['location']= $row1['location'];

						$data['ticket_id']= $row1['ticket_id'];

						$ticket_id = $row1['ticket_id'];


						$query3 = "SELECT submitted_on as subonnew FROM `ohrm_ticket_acknowledgement_action_log` WHERE ticket_id = $ticket_id AND status_id = 1";

						       $count3 = mysqli_query($this->conn, $query3);

						       if(mysqli_num_rows($count3) > 0)
								{

										//echo "count3 if";
												$row = mysqli_fetch_assoc($count3);

											
													$subonnew = $row['subonnew']; 
													//echo 'subonnew'.$subonnew;	
												
								}



									 $query4 = "SELECT submitted_on as subonEndcom FROM `ohrm_ticket_acknowledgement_action_log` WHERE ticket_id = $ticket_id AND status_id = 5";

									 $count4 = mysqli_query($this->conn, $query4);

									  if(mysqli_num_rows($count4) > 0)
								{

									//echo "count4 if";
												$row = mysqli_fetch_assoc($count4);

											
													$subonEndcom = $row['subonEndcom']; 
													//echo 'subonEndcom'.$subonEndcom;

												
								}else{

										//echo "count4 else";
										$subonEndcom =  ' ';
										//echo $subonEndcom;
									}

						
								   		
								   			$dteStart   = new DateTime($subonnew);

								   			$dteComEnd   = new DateTime($subonEndcom);

								                 $dteDiff2  = $dteComEnd->diff($dteStart); 

												$resolvedDuration1 = $dteDiff2->format("%H:%I"); 
								   			
								   			$interval = $dteStart->diff($dteComEnd);
											
											$dys = $interval->format('%a');
											
								                                    $hrs = $interval->format('%h');
								            
								                                    $mins = $interval->format('%i');

								                //echo 'mins'.$mins;

								                 $hrs += $dys * 24;

								                 $DownTime = $hrs.':'.$mins;	

								                 
							$data['CreatedDate'] = $subonnew;


						$data['ClosedDate'] = $subonEndcom;
						
						$data['DownTime']= $DownTime;	
								
						
						$data1[] = $data;

						

					}while($row1 = mysqli_fetch_assoc($count));

						$data['machineDownTimeReport']=$data1;
					// print_r($data['machineDownTimeReport']);
						$data['status']=1;
							
		}else{

			//echo 'else';
				$data['status']=0;
			}


					// print_r( $data);		
		return $data;   

        }



	


 function jobsByStatus($user_id)
    {
        $data=array();

        $i=0;
        $query1 = "SELECT t.job_id as jobId, t.submitted_on as subOn, loc.name as location, t.subject as subject,p.plant_name as plant,s.name as department,fl.name as functionallocation_name,eqp.name as equipment,ts.name as status,fl.id AS functionlocation_id FROM ohrm_ticket t LEFT JOIN ohrm_location loc ON loc.id = t.location_id LEFT JOIN ohrm_plant p ON p.id = t.plant_id LEFT JOIN ohrm_subunit s ON s.id = t.user_department_id LEFT JOIN ohrm_functional_location fl ON fl.id = t.functional_location_id LEFT JOIN ohrm_equipment eqp ON eqp.id = t.equipment_id LEFT JOIN ohrm_ticket_status ts ON ts.id = t.status_id WHERE t.status_id NOT IN (11) ORDER BY t.submitted_on DESC LIMIT 278";


						$configDate = $this->dateFormat();
		
		$count1 = mysqli_query($this->conn, $query1);

		$Count = mysqli_num_rows($count1);

		//echo $Count;

		if(mysqli_num_rows($count1) > 0)
		{
						$row=mysqli_fetch_assoc($count1);

					do{ 		
						$i = $i+1;

						$data['sno']=$i;	
						$data['jobId']= $row['jobId'];
						$data['subject']= $row['subject'];
						
						$data['plant']=$row['plant'];	
						$data['department']= $row['department'];	
						$data['functionalLocation']= $row['functionallocation_name'];
						$funLoc = $this->subfunctionalLocations($row['functionlocation_id']);
											if($funLoc['status'] == 1){
												$data['functionlocation_id']=$funLoc['id'];
												$data['functionallocation_name']=$funLoc['name'];
												$data['subfunctionlocation_id']=$row['functionlocation_id'];
												$data['subfunctionallocation_name']=$row['functionallocation_name'];
											}else{
												$data['functionlocation_id']=$row['functionlocation_id'];
												$data['functionallocation_name']=$row['functionallocation_name'];
												$data['subfunctionlocation_id']=0;
												$data['subfunctionallocation_name']='';
											}
						$data['equipment']= $row['equipment'];	
						$data['status']= $row['status'];
						
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count1));
						$data['jobsByTicketStatus']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}

        			
        return $data;
    }	


    function jobsHandledByEngineer($user_id)
    {
        $data=array();

        $j=0;


        $empresult=$this->engLists();

				for ($i=0; $i < sizeof($empresult['englist']) ; $i++) { 
	        	$engList[] = $empresult['englist'][$i];
	        	//to convert Array into string the following implode method is used
	        	$engLists = implode(',', $engList);
	        }

        $query1 = "SELECT count(*) AS equip_count, t.job_id AS jobId, t.id AS id, t.subject AS subject, t.submitted_on AS calFromDate, t.submitted_on AS calToDate, t.submitted_on AS createdOn, ta.machine_status AS machineStatus, fl.name as functionallocation_name, fl.id as functionlocation_id,toi.name AS typeOfIssue, toi.id AS typeOfIssueId, toi.sla AS sla,loc.name AS location, loc.id AS locationId, plnt.plant_name AS plantName, plnt.id AS plantId, eq.name AS equipment, eq.id AS equipmentId, ts.name AS status, ts.id AS statusId, ta.ticket_id AS ticketId, t.submitted_by_name AS submittedByName, e.emp_number AS engineerId, e.emp_number AS technicianId,tp.name AS priority, tp.id AS priorityId, tsev.name AS severity, tsev.id AS severityId, u.id AS uaerId, msr.id AS scheduleId, msr.maintenance_type_id AS maintenanceType,mt.id AS maintenanceId, mt.name AS maintenanceName,cs.name AS department, cs.id AS subDivisionId FROM ohrm_ticket t LEFT JOIN ohrm_functional_location fl ON fl.id = t.functional_location_id LEFT JOIN ohrm_type_of_issue toi ON toi.id = t.type_of_issue_id LEFT JOIN ohrm_location loc ON loc.id = t.location_id LEFT JOIN ohrm_plant plnt ON plnt.id = t.plant_id LEFT JOIN ohrm_equipment eq ON eq.id = t.equipment_id LEFT JOIN ohrm_ticket_status ts ON ts.id = t.status_id LEFT JOIN ohrm_ticket_acknowledgement_action_log ta ON ta.ticket_id = t.id LEFT JOIN hs_hr_employee e ON e.emp_number = ta.submitted_by_emp_number LEFT JOIN ohrm_user u ON u.id = ta.created_by_user_id LEFT JOIN ohrm_ticket_priority tp ON tp.id = t.priority_id LEFT JOIN ohrm_ticket_severity tsev ON tsev.id = t.severity_id LEFT JOIN ohrm_subunit cs ON cs.id = t.user_department_id LEFT JOIN ohrm_maintenance_schedule msr ON msr.ticket_id = t.id LEFT JOIN ohrm_maintenance_type mt ON mt.id = msr.maintenance_type_id WHERE t.location_id = 3 AND t.id IN (select ticket_id from ohrm_ticket_acknowledgement_action_log where submitted_by_emp_number IN ($engLists)) AND t.id IN (select ticket_id from ohrm_ticket_acknowledgement_action_log where status_id NOT IN (1,11)) AND t.status_id != 5 GROUP BY t.id ORDER BY id DESC";


						$configDate = $this->dateFormat();
		
		$count1 = mysqli_query($this->conn, $query1);

		$Count = mysqli_num_rows($count1);

		//echo $Count;

		if(mysqli_num_rows($count1) > 0)
		{
						$row=mysqli_fetch_assoc($count1);

					do{ 		
						$j = $j+1;

						$data['sno']=$j;
						//$data['equip_count']= $row['equip_count'];
						$data['jobId']= $row['jobId'];
						$data['subject']= $row['subject'];
						
						$data['location']=$row['location'];	
						$data['plantName']= $row['plantName'];	
						$data['department']= $row['department'];
						$data['functionalLocation']= $row['functionallocation_name'];
						$funLoc = $this->subfunctionalLocations($row['functionlocation_id']);
											if($funLoc['status'] == 1){
												$data['functionlocation_id']=$funLoc['id'];
												$data['functionallocation_name']=$funLoc['name'];
												$data['subfunctionlocation_id']=$row['functionlocation_id'];
												$data['subfunctionallocation_name']=$row['functionallocation_name'];
											}else{
												$data['functionlocation_id']=$row['functionlocation_id'];
												$data['functionallocation_name']=$row['functionallocation_name'];
												$data['subfunctionlocation_id']=0;
												$data['subfunctionallocation_name']='';
											}	
						$data['equipment']= $row['equipment'];
						
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count1));
						$data['jobsHandledByEngineer']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}

        			
        return $data;
    }	


    function jobsHandledByTechnician($user_id)
    {
        $data=array();

        $j=0;


        $empresult=$this->techLists();

				for ($i=0; $i < sizeof($empresult['techlist']) ; $i++) { 
	        	$techList[] = $empresult['techlist'][$i];
	        	//to convert Array into string the following implode method is used
	        	$techLists = implode(',', $techList);
	        }

        $query1 = "SELECT count(*) AS equip_count, t.job_id AS jobId, t.id AS id, t.subject AS subject, t.submitted_on AS calFromDate, t.submitted_on AS calToDate, t.submitted_on AS createdOn, ta.machine_status AS machineStatus, fl.name as functionallocation_name, fl.id as functionlocation_id,toi.name AS typeOfIssue, toi.id AS typeOfIssueId, toi.sla AS sla,loc.name AS location, loc.id AS locationId, plnt.plant_name AS plantName, plnt.id AS plantId, eq.name AS equipment, eq.id AS equipmentId, ts.name AS status, ts.id AS statusId, ta.ticket_id AS ticketId, t.submitted_by_name AS submittedByName, e.emp_number AS engineerId, e.emp_number AS technicianId,tp.name AS priority, tp.id AS priorityId, tsev.name AS severity, tsev.id AS severityId, u.id AS uaerId, msr.id AS scheduleId, msr.maintenance_type_id AS maintenanceType,mt.id AS maintenanceId, mt.name AS maintenanceName,cs.name AS department, cs.id AS subDivisionId FROM ohrm_ticket t LEFT JOIN ohrm_functional_location fl ON fl.id = t.functional_location_id LEFT JOIN ohrm_type_of_issue toi ON toi.id = t.type_of_issue_id LEFT JOIN ohrm_location loc ON loc.id = t.location_id LEFT JOIN ohrm_plant plnt ON plnt.id = t.plant_id LEFT JOIN ohrm_equipment eq ON eq.id = t.equipment_id LEFT JOIN ohrm_ticket_status ts ON ts.id = t.status_id LEFT JOIN ohrm_ticket_acknowledgement_action_log ta ON ta.ticket_id = t.id LEFT JOIN hs_hr_employee e ON e.emp_number = ta.submitted_by_emp_number LEFT JOIN ohrm_user u ON u.id = ta.created_by_user_id LEFT JOIN ohrm_ticket_priority tp ON tp.id = t.priority_id LEFT JOIN ohrm_ticket_severity tsev ON tsev.id = t.severity_id LEFT JOIN ohrm_subunit cs ON cs.id = t.user_department_id LEFT JOIN ohrm_maintenance_schedule msr ON msr.ticket_id = t.id LEFT JOIN ohrm_maintenance_type mt ON mt.id = msr.maintenance_type_id WHERE t.location_id = 3 AND t.id IN (select ticket_id from ohrm_ticket_acknowledgement_action_log where submitted_by_emp_number IN ($techLists)) AND t.id IN (select ticket_id from ohrm_ticket_acknowledgement_action_log where status_id NOT IN (1,11)) AND t.status_id != 5 GROUP BY t.id ORDER BY id DESC";


						$configDate = $this->dateFormat();
		
		$count1 = mysqli_query($this->conn, $query1);

		$Count = mysqli_num_rows($count1);

		//echo $Count;

		if(mysqli_num_rows($count1) > 0)
		{
						$row=mysqli_fetch_assoc($count1);

					do{ 		
						$j = $j+1;

						$data['sno']=$j;
						//$data['equip_count']= $row['equip_count'];
						$data['jobId']= $row['jobId'];
						$data['subject']= $row['subject'];
						
						$data['location']=$row['location'];	
						$data['plantName']= $row['plantName'];	
						$data['department']= $row['department'];
						$data['functionalLocation']= $row['functionallocation_name'];
						$funLoc = $this->subfunctionalLocations($row['functionlocation_id']);
											if($funLoc['status'] == 1){
												$data['functionlocation_id']=$funLoc['id'];
												$data['functionallocation_name']=$funLoc['name'];
												$data['subfunctionlocation_id']=$row['functionlocation_id'];
												$data['subfunctionallocation_name']=$row['functionallocation_name'];
											}else{
												$data['functionlocation_id']=$row['functionlocation_id'];
												$data['functionallocation_name']=$row['functionallocation_name'];
												$data['subfunctionlocation_id']=0;
												$data['subfunctionallocation_name']='';
											}	
						$data['equipment']= $row['equipment'];
						
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count1));
						$data['jobsHandledByTechnician']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}

        			
        return $data;
    }

 
 	//TechNewTsksLstEmpnum($emp_number)
    function TechNewTsksLstEmpnum($emp_number)
    {
        $data=array();

         $empresult=$this->empList(12);
		 // echo "<pre>";
		 // print_r($empresult);
		 // exit();
	        for ($i=0; $i < sizeof($empresult['emplist']) ; $i++) { 
	        	$empList[] = $empresult['emplist'][$i];
	        	//to convert Array into string the following implode method is used
	        	$empLists = implode(',', $empList);
	        }

        $i=0;
        $query = "SELECT o.id AS id, o.job_id AS job_id, o.location_id AS location_id, o.plant_id AS plant_id, o.user_department_id AS user_department_id, o.functional_location_id AS functional_location_id, o.equipment_id AS equipment_id, o.type_of_issue_id AS type_of_issue_id, o.status_id AS status_id, o.sla AS sla, o.subject AS subject, o.description AS description, o.priority_id AS priority_id, o.severity_id AS severity_id, o.reported_by AS reported_by, o.reported_on AS reported_on, o.submitted_by_name AS submitted_by_name, o.submitted_by_emp_number AS submitted_by_emp_number, o.submitted_on AS submitted_on, o.modified_by_name AS modified_by_name, o.modified_by_emp_number AS modified_by_emp_number, o.modified_on AS modified_on, o.is_preventivemaintenance AS is_preventivemaintenance, o.is_deleted AS is_deleted FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id
WHERE (o2.forward_to = $emp_number AND o2.status_id = 2 AND o2.forward_to IN ($empLists)
AND o.location_id = 3 
AND o.plant_id = 1 AND
o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) ORDER BY o.id DESC";


        $configDate = $this->dateFormat();
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{	

						$row=mysqli_fetch_assoc($count);
					do{ 

						$i=$i+1;

						$data['sno']=$i;					
						$data['id']=$row['id'];
						$data['job_id']=$row['job_id'];
						$text = $row['subject'];
						$data['subject']=iconv(mb_detect_encoding($text), "UTF-8//IGNORE", $text);
						
						$data['status']="New";
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count));
						$data['technicianNewTasks']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
        return $data;
    }


 //    function JobsCountNew($userId,$userRoleId)
	// {



	// 		$data=array();
	// 	$i = 0;
	// 	$userDetails = $this->getUserRoleByUserId($userId);
	// 	$emp_number = $userDetails['empNumber'];
	// 	//echo $emp_number;
	// 	$userRoleId = $userDetails['id'];
	// 	//echo $userRoleId;

	// 	$location_id = $this->getLocByEmpNumber($emp_number);
	// 	//echo "loc".' ' .$location_id;
		
	// 	//$i=0;
	// 	$empNumber = $this->getEmpnumberByUserId($userId);
 //       	$empresult=$this->employeeDetails($empNumber);

 //       	$plantId = $empresult['plant_id'];
		
	// 	if($userRoleId == 10)
	// 	{


	// 		//echo $userRoleId;

			

 //       	$query="SELECT o.id AS id, o.job_id AS job_id, o.location_id AS location_id, o.plant_id AS plant_id, o.user_department_id AS user_department_id, o.functional_location_id AS functional_location_id, o.equipment_id AS equipment_id, o.type_of_issue_id AS type_of_issue_id, o.status_id AS status_id, o.sla AS sla, o.subject AS subject, o.description AS description, o.priority_id AS priority_id, o.severity_id AS severity_id, o.reported_by AS reported_by, o.reported_on AS reported_on, o.submitted_by_name AS submitted_by_name, o.submitted_by_emp_number AS submitted_by_emp_number, o.submitted_on AS submitted_on, o.modified_by_name AS modified_by_name, o.modified_by_emp_number AS modified_by_emp_number, o.modified_on AS modified_on, o.is_preventivemaintenance AS is_preventivemaintenance, o.is_deleted AS is_deleted FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id WHERE (o2.status_id IN (1, 6) AND o.location_id = $location_id AND o.plant_id = $plantId AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) ORDER BY o.id DESC";	

	// 	//echo $query;
	// 	//exit();

	// 	$configDate = $this->dateFormat();

	// 	$count=mysqli_query($this->conn, $query);

	// 	$jobCountNew = mysqli_num_rows($count);

	// 						$typeEng = 11;
	// 						$typeTech =12;


	// 								$empresult=$this->empList($typeEng);
	// 				        for ($i=0; $i < sizeof($empresult['emplist']) ; $i++) { 
	// 				        	$empList[] = $empresult['emplist'][$i];
	// 				        	//to convert Array into string the following implode method is used
	// 				        	$empLists1 = implode(',', $empList);
	// 				        }



	// 				        $queryeng = "SELECT o.id AS id, o.job_id AS job_id, o.location_id AS location_id, o.plant_id AS plant_id, o.user_department_id AS user_department_id, o.functional_location_id AS functional_location_id, o.equipment_id AS equipment_id, o.type_of_issue_id AS type_of_issue_id, o.status_id AS status_id, o.sla AS sla, o.subject AS subject, o.description AS description, o.priority_id AS priority_id, o.severity_id AS severity_id, o.reported_by AS reported_by, o.reported_on AS reported_on, o.submitted_by_name AS submitted_by_name, o.submitted_by_emp_number AS submitted_by_emp_number, o.submitted_on AS submitted_on, o.modified_by_name AS modified_by_name, o.modified_by_emp_number AS modified_by_emp_number, o.modified_on AS modified_on, o.is_preventivemaintenance AS is_preventivemaintenance FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id WHERE ((o2.accepted_by IN ($empLists1) OR o2.forward_to IN ($empLists1)) AND o.location_id = $location_id AND o.plant_id = $plantId AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) ORDER BY o.id DESC";

	// 				        $counteng = mysqli_query($this->conn, $queryeng);

	// 								$jobEnMEng = mysqli_num_rows($counteng);
									

	// 				         $empresult1=$this->empList(12);
				
	// 		        for ($j=0; $j < sizeof($empresult1['emplist']) ; $j++) { 
	// 		        	$empList1[] = $empresult1['emplist'][$j];
	// 		        	//to convert Array into string the following implode method is used
	// 		        	$empLists2 = implode(',', $empList1);
	// 		        }


			        
	// 				        //$i=0;
	// 				        $querytech = "SELECT o.id AS id, o.job_id AS job_id, o.location_id AS o__location_id, o.plant_id AS o__plant_id, o.user_department_id AS o__user_department_id, o.notify_to AS o__notify_to, o.functional_location_id AS o__functional_location_id, o.equipment_id AS o__equipment_id, o.type_of_issue_id AS o__type_of_issue_id, o.status_id AS o__status_id, o.sla AS o__sla, o.subject AS subject, o.description AS o__description, o.priority_id AS o__priority_id, o.severity_id AS o__severity_id, o.reported_by AS o__reported_by, o.reported_on AS o__reported_on, o.submitted_by_name AS o__submitted_by_name, o.submitted_by_emp_number AS o__submitted_by_emp_number, o.submitted_on AS o__submitted_on, o.modified_by_name AS o__modified_by_name, o.modified_by_emp_number AS o__modified_by_emp_number, o.modified_on AS o__modified_on, o.is_preventivemaintenance AS o__is_preventivemaintenance, o.is_deleted AS o__is_deleted, o2.id AS o2__id, o2.ticket_id AS o2__ticket_id, o2.status_id AS o2__status_id, o2.priority_id AS o2__priority_id, o2.severity_id AS o2__severity_id, o2.comment AS o2__comment, o2.machine_status AS o2__machine_status, o2.assigned_date AS o2__assigned_date, o2.due_date AS o2__due_date, o2.accepted_by AS o2__accepted_by, o2.rejected_by AS o2__rejected_by, o2.submitted_on AS o2__submitted_on, o2.forward_from AS o2__forward_from, o2.forward_to AS o2__forward_to, o2.submitted_by_name AS o2__submitted_by_name, o2.submitted_by_emp_number AS o2__submitted_by_emp_number, o2.created_by_user_id AS o2__created_by_user_id, o2.root_cause_id AS o2__root_cause_id, o2.response_id AS o2__response_id FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id WHERE ((o2.accepted_by IN ($empLists2) OR o2.forward_to IN ($empLists2)) AND o.location_id = 3 AND o.plant_id = 1 AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) ORDER BY o.id DESC";

	// 				        	//echo $querytech;
	// 				        	//exit();
	// 							        $counttech = mysqli_query($this->conn, $querytech);

	// 											$jobEnMTech = mysqli_num_rows($counttech);

												

	// 							if(mysqli_num_rows($count) > 0)
	// 							{
									
	// 											$row = mysqli_fetch_assoc($count);
	// 											/*$i=$i+1;
	// 											$data['sno']=$i;*/	
	// 											$data['EnMNewTasks']=$jobCountNew;
	// 											$data['EnMEngTasks']=$jobEnMEng;
	// 											$data['EnMTechTasks']=$jobEnMTech;
	// 											$jobCountEngNewTasks = ' ';			
	// 											$jobCountTech = ' ';
										

										
	// 							}else{
	// 									$data['status']=0;
	// 								}

						        

					
	// 	}

	// 	else if($userRoleId == 11)
	// 	{

	// 			//echo $userRoleId;

	// 			//echo "roleId11".' '.$emp_number;

	// 								$result=$this->multipledeptList($emp_number);

	// 						$empresult=$this->employeeDetails($emp_number);

	// 						$issueresult=$this->typeofissuelist($emp_number);

	// 						$multidept[] = $empresult['work_station'];
	// 						$multi_dept = implode(',', $multidept);
	// 						if($result['status']== 1){
	// 					        for ($i=0; $i < sizeof($result['deptmultlist']) ; $i++) { 
	// 					        	$multidept[] = $result['deptmultlist'][$i];
	// 					        	//to convert Array into string the following implode method is used
	// 					        	$multi_dept = implode(',', $multidept);
	// 					        }
	// 					    }
	// 						if($issueresult['status']== 1){
	// 					        for ($i=0; $i < sizeof($issueresult['typeid']) ; $i++) { 
	// 					        	$issueList[] = $issueresult['typeid'][$i];
	// 					        	//to convert Array into string the following implode method is used
	// 					        	$multi_issue = implode(',', $issueList);
	// 					        }
	// 					    }else{
	// 					    	$multi_issue = -1;
	// 					    }

	// 			$query5 = "SELECT t.id AS id,t.job_id AS job_id,t.subject AS subject ,tp.name AS priority,ts.name AS severity,t.sla AS sla,CONCAT(e.emp_firstname,' ',e.emp_lastname) AS reported_by,t.reported_on AS reported_on,ta.submitted_by_name AS submitted_by,ta.submitted_on AS submitted_on,tks.name AS status from ohrm_ticket t LEFT JOIN ohrm_ticket_acknowledgement_action_log ta ON t.id = ta.ticket_id LEFT JOIN ohrm_ticket_priority tp ON tp.id = ta.priority_id LEFT JOIN ohrm_ticket_severity ts ON ts.id = ta.severity_id LEFT JOIN hs_hr_employee e ON e.emp_number = t.reported_by LEFT JOIN ohrm_ticket_status tks ON tks.id = ta.status_id LEFT JOIN ohrm_type_of_issue tpi ON tpi.engineer_id WHERE  t.type_of_issue_id IN ($multi_issue) AND t.status_id IN (1,2,6) GROUP BY ta.ticket_id UNION 
 //            SELECT t.id AS id,t.job_id AS job_id,t.subject AS subject ,tp.name AS priority,ts.name AS severity, t.sla AS sla,CONCAT(e.emp_firstname,' ',e.emp_lastname) AS reported_by,t.reported_on AS reported_on,ta.submitted_by_name AS submitted_by,ta.submitted_on AS submitted_on,tks.name AS status from ohrm_ticket t LEFT JOIN ohrm_ticket_acknowledgement_action_log ta ON t.id = ta.ticket_id LEFT JOIN ohrm_ticket_priority tp ON tp.id = ta.priority_id LEFT JOIN ohrm_ticket_severity ts ON ts.id = ta.severity_id LEFT JOIN ohrm_ticket_status tks ON tks.id = ta.status_id LEFT JOIN hs_hr_employee e ON e.emp_number = t.reported_by LEFT JOIN hs_hr_employee emp ON t.user_department_id = emp.work_station WHERE  t.user_department_id IN ($multi_dept) AND emp.emp_number = $emp_number AND t.status_id IN (1,2,6) GROUP BY ta.ticket_id UNION 
 //    SELECT t.id AS id,t.job_id AS job_id,t.subject AS subject ,tp.name AS priority,ts.name AS severity,t.sla AS sla,CONCAT(e.emp_firstname,' ',e.emp_lastname) AS reported_by,t.reported_on AS reported_on,ta.submitted_by_name AS submitted_by,ta.submitted_on AS submitted_on,tks.name AS status from ohrm_ticket t LEFT JOIN ohrm_ticket_acknowledgement_action_log ta ON t.id = ta.ticket_id LEFT JOIN ohrm_ticket_priority tp ON tp.id = ta.priority_id LEFT JOIN ohrm_ticket_severity ts ON ts.id = ta.severity_id LEFT JOIN hs_hr_employee e ON e.emp_number = t.reported_by LEFT JOIN ohrm_ticket_status tks ON tks.id = ta.status_id LEFT JOIN ohrm_type_of_issue tpi ON tpi.engineer_id WHERE ta.forward_to = $emp_number AND ta.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND ta.status_id IN (1,2,6) GROUP BY ta.ticket_id ORDER BY id  DESC";

 //    	echo $query5;
		
	// 	$configDate = $this->dateFormat();
	// 	$count5 = mysqli_query($this->conn, $query5);	

	// 	$jobCountEngNewTasks = mysqli_num_rows($count5);


	// 	$empresult=$this->empList(12);
	//         for ($i=0; $i < sizeof($empresult['emplist']) ; $i++) { 
	//         	$empList[] = $empresult['emplist'][$i];
	//         	//to convert Array into string the following implode method is used
	//         	$empLists = implode(',', $empList);
	//         }


	// 	$query1 = "SELECT o.id AS id, o.job_id AS job_id, o.location_id AS location_id, o.plant_id AS plant_id, o.user_department_id AS user_department_id, o.functional_location_id AS functional_location_id, o.equipment_id AS equipment_id, o.type_of_issue_id AS type_of_issue_id, o.status_id AS status_id, o.sla AS sla, o.subject AS subject, o.description AS description, o.priority_id AS priority_id, o.severity_id AS severity_id, o.reported_by AS reported_by, o.reported_on AS reported_on, o.submitted_by_name AS submitted_by_name, o.submitted_by_emp_number AS submitted_by_emp_number, o.submitted_on AS submitted_on, o.modified_by_name AS modified_by_name, o.modified_by_emp_number AS modified_by_emp_number, o.modified_on AS modified_on FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id WHERE ((o.status_id = 3 OR o2.status_id = 7 OR o2.status_id = 9 OR o2.status_id = 8) AND (o2.accepted_by IN ($empLists) OR o2.forward_to IN ($empLists)) OR o2.forward_to IN ($empLists) AND o.location_id = 3 AND o.plant_id = 1 AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) GROUP BY o.id ORDER BY o.id DESC";

	// 	//echo $query1;

	// 	$count1 = mysqli_query($this->conn, $query1);

	// 	$jobCountTech = mysqli_num_rows($count1);


	// 	if(mysqli_num_rows($count5) > 0)
	// 	{
			
	// 					$row5 = mysqli_fetch_assoc($count5);
	// 					/*$i=$i+1;
	// 					$data['sno']=$i;*/	
						
	// 					$data['EngNewTasks']=$jobCountEngNewTasks;	
	// 					$data['EngTechTasks']=$jobCountTech;			
						

	// 					//$data['JobsCount']=$data1;
	// 					$data['status']=1;

					
	// 	}else{
	// 			$data['status']=0;
	// 		}

	// 	}

	// 	else if($userRoleId == 12)
	// 	{

	// 		//echo $userRoleId;

	// 		 $empresult=$this->empList(12);
	// 	 // echo "<pre>";
	// 	 // print_r($empresult);
	// 	 // exit();
	//         for ($i=0; $i < sizeof($empresult['emplist']) ; $i++) { 
	//         	$empList[] = $empresult['emplist'][$i];
	//         	//to convert Array into string the following implode method is used
	//         	$empLists = implode(',', $empList);
	//         }

	// 		        $i=0;
	// 		        $query = "SELECT o.id AS id, o.job_id AS job_id, o.location_id AS location_id, o.plant_id AS plant_id, o.user_department_id AS user_department_id, o.functional_location_id AS functional_location_id, o.equipment_id AS equipment_id, o.type_of_issue_id AS type_of_issue_id, o.status_id AS status_id, o.sla AS sla, o.subject AS subject, o.description AS description, o.priority_id AS priority_id, o.severity_id AS severity_id, o.reported_by AS reported_by, o.reported_on AS reported_on, o.submitted_by_name AS submitted_by_name, o.submitted_by_emp_number AS submitted_by_emp_number, o.submitted_on AS submitted_on, o.modified_by_name AS modified_by_name, o.modified_by_emp_number AS modified_by_emp_number, o.modified_on AS modified_on, o.is_preventivemaintenance AS is_preventivemaintenance, o.is_deleted AS is_deleted FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id
	// 		WHERE (o2.forward_to = $emp_number AND o2.status_id = 2 AND o2.forward_to IN ($empLists)
	// 		AND o.location_id = 3 
	// 		AND o.plant_id = 1 AND
	// 		o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) ORDER BY o.id DESC";


	//         $configDate = $this->dateFormat();
	// 		$count=mysqli_query($this->conn, $query);

	// 		$TechNewTasks = mysqli_num_rows($count);




	// 	}
		



	// 	$query6 = "SELECT ta.ticket_id AS ticket_id,t.job_id as job_id,t.sla AS sla,sta.name as status,t.subject as subject,tktprty.name as priority,tktsvrty.name as severity,CONCAT(emp.emp_firstname,emp.emp_lastname) AS raised_by,t.reported_on as raised_on,CONCAT(emp.emp_firstname,emp.emp_lastname) AS acknowledged_by,ta.submitted_on AS acknowledged_on FROM ohrm_ticket_acknowledgement_action_log ta LEFT JOIN ohrm_ticket t ON t.id = ta.ticket_id LEFT JOIN hs_hr_employee emp ON  emp.emp_number = ta.submitted_by_emp_number LEFT JOIN ohrm_location loc ON loc.id = t.location_id  LEFT JOIN ohrm_plant plant ON plant.id = t.plant_id LEFT JOIN ohrm_subunit sub ON sub.id = t.user_department_id  LEFT JOIN ohrm_functional_location func ON func.id = t.functional_location_id LEFT JOIN ohrm_equipment eqp ON eqp.id = t.equipment_id LEFT JOIN ohrm_type_of_issue iss ON iss.id = t.type_of_issue_id LEFT JOIN ohrm_ticket_status sta ON sta.id = t.status_id LEFT JOIN ohrm_ticket_priority tktprty ON tktprty.id = t.priority_id LEFT JOIN ohrm_ticket_severity tktsvrty ON tktsvrty.id = t.severity_id LEFT JOIN hs_hr_employee empsub ON empsub.emp_number  = t.submitted_by_name  WHERE ta.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) and ta.accepted_by = $emp_number AND ta.status_id IN (3,4) ORDER BY t.job_id DESC";


	// 	$count6 = mysqli_query($this->conn, $query6);	

	// 	$jobCountInPrgTasks = mysqli_num_rows($count6);



	// 				$query3 = "SELECT t.id as id,t.job_id AS job_id,t.subject AS subject,tp.name AS priority,tsv.name AS severity,t.sla AS sla,
	// 	CONCAT(e.emp_firstname,' ',e.emp_lastname) AS raised_by,t.reported_on AS raised_on,ta.submitted_by_name AS acknowledged_by,ta.submitted_on AS acknowledged_on,ts.name AS status
	// 		FROM ohrm_ticket_acknowledgement_action_log ta
	// 		LEFT JOIN ohrm_ticket t ON t.id = ta.ticket_id
	// 		LEFT JOIN ohrm_ticket_priority tp ON tp.id = ta.priority_id
	// 		LEFT JOIN ohrm_ticket_severity tsv ON tsv.id = ta.severity_id
	// 		LEFT JOIN hs_hr_employee e ON e.emp_number = t.reported_by
	// 		 LEFT JOIN ohrm_ticket_status ts ON ts.id = ta.status_id
	// 		WHERE ta.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) and ta.forward_to = $emp_number AND ta.status_id = 14
	// 		order by id desc";
		

	// 				$count3 = mysqli_query($this->conn, $query3);

	// 				$jobCountRslvd = mysqli_num_rows($count3);



	// 	$query4 = "SELECT t.id as id,t.job_id AS job_id,t.subject AS subject,tp.name AS priority,tsv.name AS severity,t.sla AS sla,
	// 	CONCAT(e.emp_firstname,' ',e.emp_lastname) AS raised_by,t.reported_on AS raised_on,ta.submitted_by_name AS acknowledged_by,ta.submitted_on AS acknowledged_on,ts.name AS status
	// 		FROM ohrm_ticket_acknowledgement_action_log ta
	// 		LEFT JOIN ohrm_ticket t ON t.id = ta.ticket_id
	// 		LEFT JOIN ohrm_ticket_priority tp ON tp.id = ta.priority_id
	// 		LEFT JOIN ohrm_ticket_severity tsv ON tsv.id = ta.severity_id
	// 		LEFT JOIN hs_hr_employee e ON e.emp_number = t.reported_by
	// 		 LEFT JOIN ohrm_ticket_status ts ON ts.id = ta.status_id
	// 		WHERE ta.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) and ta.forward_to = $emp_number AND ta.status_id = 16
	// 		order by id desc";


	// 				$count4 = mysqli_query($this->conn, $query4);

	// 				$jobCountRejected = mysqli_num_rows($count4);

	// 	if(mysqli_num_rows($count6) > 0 || mysqli_num_rows($count3) > 0 || mysqli_num_rows($count4) > 0 || mysqli_num_rows($count) > 0)
	// 	{
			
	// 					//$row = mysqli_fetch_assoc($count);
	// 					/*$i=$i+1;
	// 					$data['sno']=$i;*/	
	// 					/*$data['EnMNewTasks']=$jobCountNew;
	// 					$data['EnMEngTasks']=$jobEnMEng;
	// 					$data['EnMTechTasks']=$jobEnMTech;*/
	// 					//$data['EngNewTasks']=$jobCountEngNewTasks;			
						
	// 					//$data['TechNewTasks']=$TechNewTasks;
	// 					$data['ResolvedTasks']=$jobCountRslvd;
	// 					$data['RejectedTasks']=$jobCountRejected;
	// 					$data['InProgressTasks'] = $jobCountInPrgTasks;
	// 					$data1[] = $data;

	// 					$data['JobsCount']=$data1;
	// 					$data['status']=1;

							
	// 	}else{
	// 			$data['status']=0;
	// 		}
	// 	return $data;
	// }


	/*function pdfDwnLoad($path1)
	{


		echo $path1;
	}
*/
	function pdfDwnLoad($userIdPass)
	{


		//echo $userIdPass;


				$userDetails = $this->ticketDetByTypeOfIssue($userIdPass);

		

		

		$data['tktdetails'] = $userDetails;
		

		return $data;
	}


	function pdfStatusDwnLoad($userIdPass)
	{


		//echo $userIdPass;


				$userDetails = $this->jobsByStatus($userIdPass);

		

		

		$data['tktdetails'] = $userDetails;
		

		return $data;
	}

	function xlsDwnLoad($userIdPass)
	{


		//echo $userIdPass;
		$userDetails = $this->ticketDetByTypeOfIssue($userIdPass);

	
		$data['tktdetails'] = $userDetails;
		

		return $data;
	}

function excelStatusDwnLoad($userIdPass)
	{


		//echo $userIdPass;
		$userDetails = $this->jobsByStatus($userIdPass);

		
		$data['tktdetails'] = $userDetails;
		
		return $data;
	}

	function pdfMainTypRepDwnLoad($userIdPass)
	{


		//echo $userIdPass;


				$userDetails = $this->MaintenanceTypeReport($userIdPass);

		

		

		$data['tktdetails'] = $userDetails;
		

		return $data;
	}


	function excelMainTypRepDwnLoad($userIdPass)
	{


		//echo $userIdPass;
		$userDetails = $this->MaintenanceTypeReport($userIdPass);

		
		$data['tktdetails'] = $userDetails;
		

		return $data;
	}


	function EqpmntDetailsBasedOnEqIdAll($userIdPass)
	{


		$data=array();

        $i=0;
        $query1 = "SELECT t.equipment_id as eqId,eqp.name as equipmentName,t.job_id as JobId, t.submitted_on as createdDate,t.id as ticket_id FROM ohrm_ticket t LEFT JOIN ohrm_equipment eqp ON t.equipment_id = eqp.id WHERE t.status_id IN (10,5)
GROUP BY t.equipment_id ORDER BY t.job_id ASC";

        $count1 = mysqli_query($this->conn, $query1);

        if(mysqli_num_rows($count1) > 0)

		{

			//echo "if";
						$row=mysqli_fetch_assoc($count1);


						do{ 

							//echo "do";

							$ticket_id= $row['ticket_id'];
							//echo $ticket_id."\n";
							$job_id = $row['JobId'];
							//echo $job_id."\n";
							$eq_id = $row['eqId'];
							//echo $eq_id."\n";
							$eq_name = $row['equipmentName'];
							//echo $eq_name."\n";
							$created_date = $row['createdDate'];
							//echo $created_date."\n";

								$i = $i+1;
							//$data['eqId']= $row['eqId'];
							$data['sno']=$i;	
								$data['eqId']= $row['eqId'];
								//echo $eqId;
								$data['JobId']= $row['JobId'];
								//echo $eqId;
								$data['ticket_id']= $row['ticket_id'];
								$data['equipmentName']=$row['equipmentName'];	
								$data['createdDate']= $row['createdDate'];

							
							

												$configDate = $this->dateFormat();

								$query2 = "SELECT submitted_on as suboncom FROM `ohrm_ticket_acknowledgement_action_log` WHERE ticket_id = $ticket_id AND status_id = 14";

									 $count2 = mysqli_query($this->conn, $query2);

									 $Count = mysqli_num_rows($count2);

									 //echo $Count;
									 //exit();
									  if(mysqli_num_rows($count2) > 0)
								{

									
												$row = mysqli_fetch_assoc($count2);

											
													$suboncom = $row['suboncom']; 
													//echo $suboncom;

												
								}else{

										$suboncom = '';
										$data['status']=0;
									}


									$query3 = "SELECT submitted_on as subonnew FROM `ohrm_ticket_acknowledgement_action_log` WHERE ticket_id = $ticket_id AND status_id = 1";

						       $count3 = mysqli_query($this->conn, $query3);

						       if(mysqli_num_rows($count3) > 0)
								{

										
												$row = mysqli_fetch_assoc($count3);

											
													$subonnew = $row['subonnew']; 
													/*echo $subonnew;
												exit();*/	
												
								}else{

										
										$data['status']=0;
									}


									
							//echo $ticket_id."\n";

									 $query4 = "SELECT submitted_on as subonEndcom FROM `ohrm_ticket_acknowledgement_action_log` WHERE ticket_id = $ticket_id AND status_id = 10";

									 $count4 = mysqli_query($this->conn, $query4);

									  if(mysqli_num_rows($count4) > 0)
								{

									//echo "count4 if";
												$row = mysqli_fetch_assoc($count4);

											
													$subonEndcom = $row['subonEndcom']; 
													//echo $suboncom;

												
								}else{

										//echo "count4 else";

											$subonEndcom = '';
										$data['status']=0;
									}


									/*echo 'com'.$suboncom.' ';
									echo 'new'.$subonnew.' ';
									echo 'endcom'.$subonEndcom.' ';
									exit();*/
									if(($suboncom != '') && ($subonEndcom != ''))
									{


										$dteStart = new DateTime($subonnew); 

											
								   			$dteEnd   = new DateTime($suboncom);

								   			

								   			$dteDiff  = $dteEnd->diff($dteStart); 


								   			
								   			$resolvedDuration = $dteDiff->format("%H:%I"); 
								   			
								   			$interval = $dteStart->diff($dteEnd);
											
											$dys = $interval->format('%a');
											
								                                    $hrs = $interval->format('%h');
								            
								                                    $mins = $interval->format('%i');

								                //echo 'mins'.$mins;

								                 $hrs += $dys * 24;

								                 $ResolvedTime = $hrs.':'.$mins;

								                 $dteComEnd   = new DateTime($subonEndcom);
								                 $dteDiff1  = $dteComEnd->diff($dteEnd); 

												$resolvedDuration1 = $dteDiff1->format("%H:%I"); 
								   			
								   			$interval = $dteEnd->diff($dteComEnd);
											
											$dys = $interval->format('%a');
											
								                                    $hrs = $interval->format('%h');
								            
								             //echo 'hrs'.$hrs. "\n";
								                                    $mins = $interval->format('%i');

								                //echo 'mins'.$mins. "\n";

								                 $hrs += $dys * 24;

								                 //echo 'hrs'.$hrs. "\n";

								                 $CompletedTime = $hrs.':'.$mins;

								                 //$dteComEnd   = new DateTime($subonEndcom);
								                 $dteDiff2  = $dteComEnd->diff($dteStart); 

												$resolvedDuration1 = $dteDiff2->format("%H:%I"); 
								   			
								   			$interval = $dteStart->diff($dteComEnd);
											
											$dys = $interval->format('%a');
											
								                                    $hrs = $interval->format('%h');
								            
								                                    $mins = $interval->format('%i');

								                //echo 'mins'.$mins;

								                 $hrs += $dys * 24;

								                 $TotalCompletedTime = $hrs.':'.$mins;


								                 $data['ResolvedTime']= $ResolvedTime;
						$data['CompletedTime']= $CompletedTime;	
						$data['TotalCompletedTime']= $TotalCompletedTime;

									}

									else
									{

										$dteStart1 = new DateTime($subonnew); 

										//echo 'sub'.$subonnew;
										

										  $dteComEnd1   = new DateTime($subonEndcom);

										  //echo 'end'.$subonEndcom;
										//exit();


										 $dteDiff3  = $dteComEnd1->diff($dteStart1); 

												$resolvedDuration2 = $dteDiff3->format("%H:%I"); 
								   			
								   			$interval1 = $dteStart1->diff($dteComEnd1);
											
											$dys1 = $interval1->format('%a');

											 //echo 'dys1'.$dys1. "\n";
											
								                                    $hrs1 = $interval1->format('%h');
								            
								                                    $mins1 = $interval1->format('%i');

								                //echo 'mins1'.$mins1. "\n";

								                 $hrs1 += $dys1 * 24;

								                   //echo 'hrs1'.$hrs1. "\n";

								                 $ResolvedTime1 = '';

								                  $CompletedTime1 = $hrs1.':'.$mins1;

								                 $TotalCompletedTime1 = $hrs1.':'.$mins1;

										 $data['ResolvedTime']= $ResolvedTime1;
						$data['CompletedTime']= $CompletedTime1;	
						$data['TotalCompletedTime']= $TotalCompletedTime1;
										//exit();

									}
						
				$data1[] = $data;
							}while($row = mysqli_fetch_assoc($count1));
								$data['EqpmntDetailsBasedOnEqIdAll']=$data1;
								$data['status']=1;


						
						
						
							
		}else{
				//echo "last else";
				$data['status']=0;
			}

			

		return $data;
	}


function pdfMchnwiseBrkDwnLoad($userIdPass)
	{


		//echo $userIdPass;


				$userDetails = $this->EqpmntDetailsBasedOnEqIdAll($userIdPass);

		

		

		$data['tktdetails'] = $userDetails;
		

		return $data;
	}


	function excelMchnwiseBrkDwnLoad($userIdPass)
	{


		//echo $userIdPass;
		$userDetails = $this->EqpmntDetailsBasedOnEqIdAll($userIdPass);

		
		$data['tktdetails'] = $userDetails;
		

		return $data;
	}


	function pdfJobsHndlByTechDwnLoad($userIdPass)
	{


		//echo $userIdPass;


				$userDetails = $this->jobsHandledByTechnician($userIdPass);

		

		

		$data['tktdetails'] = $userDetails;
		

		return $data;
	}

	function excelJobsHndlByTechDwnLoad($userIdPass)
	{


		//echo $userIdPass;
		$userDetails = $this->jobsHandledByTechnician($userIdPass);

		
		$data['tktdetails'] = $userDetails;
		

		return $data;
	}


	function pdfJobsHndlByEngDwnLoad($userIdPass)
	{


		//echo $userIdPass;


				$userDetails = $this->jobsHandledByEngineer($userIdPass);

		

		

		$data['tktdetails'] = $userDetails;
		

		return $data;
	}


	function excelJobsHndlByEngDwnLoad($userIdPass)
	{


		//echo $userIdPass;
		$userDetails = $this->jobsHandledByEngineer($userIdPass);

		
		$data['tktdetails'] = $userDetails;
		

		return $data;
	}


	function pdfDwnTimeRptDwnLoad($userIdPass)
	{


		//echo $userIdPass;


				$userDetails = $this->ticketsDownTimeReport($userIdPass);

		

		

		$data['tktdetails'] = $userDetails;
		

		return $data;
	}





	function excelDowntimeReportDwnLoad($userIdPass)
	{


		//echo $userIdPass;
		$userDetails = $this->ticketsDownTimeReport($userIdPass);

		
		$data['tktdetails'] = $userDetails;
		

		return $data;
	}


function ticketUpd($job_id,$locationId,$plantId,$usrdeptId,$notifytoId,$statusId,$funclocId,$eqipmntId,$typofisId,$subject,$description,$prtyId,$svrtyId,$reportedBy,$submitted_by_emp_number,$submitted_by_name,$reportedOn,$submitted_on,$user_id,$attachmentId)
	{
		$data= array();
		
		//echo $ticket_id;
		//exit();

			$updatesql1 ="UPDATE ohrm_ticket SET location_id = '$locationId',plant_id='$plantId', user_department_id='$usrdeptId',notify_to = '$notifytoId',status_id='$statusId',functional_location_id='$funclocId',equipment_id='$eqipmntId',type_of_issue_id='$typofisId',subject='$subject',description='$description',
				priority_id='$prtyId',severity_id='$svrtyId',reported_by='$reportedBy',submitted_by_name='$submitted_by_name',submitted_by_emp_number='$submitted_by_emp_number',reported_on='$reportedOn',submitted_on ='$submitted_on' WHERE job_id = $job_id";

				//echo $query1;
				//exit();

				if($result = mysqli_query($this->conn, $updatesql1)){

							//echo $job_id;
								$data['job_id'] = $job_id;
			
						        
						        $data1[] = $data;
						        $data['ticketupdid'] = $data1;
						        $data['status']=1;
							}else{
							    $data['status']=0;
							}


							//$count1 = mysqli_query($this->conn, $query1);

		//$Count = mysqli_num_rows($count1);

		//echo $Count;

	/*if(mysqli_num_rows($count1) > 0)
		{
						$row=mysqli_fetch_assoc($count1);

					
						$data['job_id'] = $job_id;
						
						
						
						$data1[] = $data;
					
						$data['ticketupdid']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}*/

			return $data;

		}

function persnlDetails($userIdPass)
	{
		$data= array();
		

		$userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];

		$query="SELECT concat(emp.emp_firstname,' ',emp.emp_middle_name,' ',emp.emp_lastname) as empname, emp.emp_fathername as fathername,
			emp.emp_mothername as mothername,emp.emp_pancard_id,emp.emp_uan_num,emp.emp_pf_num,emp.emp_dri_lice_num,emp.emp_dri_lice_exp_date,emp.blood_group,emp.emp_hobbies,emp.nation_code,emp.emp_gender,emp.emp_marital_status,emp.emp_birthday FROM hs_hr_employee emp WHERE emp.emp_number = $empNumber" ;
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
					$row=mysqli_fetch_assoc($count);
					do{ 

						$data['empName']=$row['empname'];
						$data['fatherName']=$row['fathername'];	
						$data['motherName']=$row['mothername'];
						$data['emp_pancard_id']=$row['emp_pancard_id'];	
						$data['emp_uan_num']=$row['emp_uan_num'];
						$data['emp_pf_num']=$row['emp_pf_num'];	
						$data['emp_dri_lice_num']=$row['emp_dri_lice_num'];
						$data['emp_dri_lice_exp_date']=$row['emp_dri_lice_exp_date'];	
						$data['blood_group']=$row['blood_group'];
						$data['emp_hobbies']=$row['emp_hobbies'];
						$data['nation_code']=$row['nation_code'];
						$data['emp_gender']=$row['emp_gender'];
						$data['emp_marital_status']=$row['emp_marital_status'];
						$data['emp_birthday']=$row['emp_birthday'];
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count)); 				
						$data['persnlDetails']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}



function contactDetails($userIdPass)
	{
		$data= array();

		$userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];

		$query="SELECT * FROM hs_hr_employee WHERE emp_number = $empNumber" ;
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
					$row=mysqli_fetch_assoc($count);
					do{ 

						$data['add_strt1']=$row['emp_street1'];
						$data['add_strt2']=$row['emp_street2'];
						$data['city_code']=$row['city_code'];
						$data['coun_code']=$row['coun_code'];
						$data['emp_zipcode']= $row['emp_zipcode'];
						$data['emp_hm_telephone'] = $row['emp_hm_telephone'];
						$data['emp_mobile']= $row['emp_mobile'];
						$data['emp_work_telephone'] = $row['emp_work_telephone'];
						$data['emp_work_email']= $row['emp_work_email'];
						$data['emp_oth_email'] = $row['emp_oth_email'];
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count)); 				
						$data['contactDetails']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}


function emergencyContacts($userIdPass)
	{
		$data= array();

		$userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];

		$query="SELECT * FROM hs_hr_emp_emergency_contacts WHERE emp_number = $empNumber" ;
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
					$row=mysqli_fetch_assoc($count);
					do{ 

						$data['name']=$row['eec_name'];
						$data['relationship']=$row['eec_relationship'];
						$data['hmtelphne']=$row['eec_home_no'];
						$data['mobile']=$row['eec_mobile_no'];
						$data['wrktelphn']= $row['eec_office_no'];
						
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count)); 				
						$data['emergencyContacts']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}



function asigndDepdents($userIdPass)
	{
		$data= array();

		$userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];

		$query="SELECT * FROM hs_hr_emp_dependents WHERE emp_number = $empNumber";
		
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
					$row=mysqli_fetch_assoc($count);
					do{ 

						$data['name']=$row['ed_name'];
						//$data['relationship']=$row['ed_relationship_type'];
						$data['relationship']=$row['ed_relationship'];
						$data['dteOfBrth']=$row['ed_date_of_birth'];
						
						
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count)); 				
						$data['asigndDepdents']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}


	function imgrtnRcrds($userIdPass)
	{
		$data= array();

		$userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];

		$query="SELECT * FROM hs_hr_emp_passport WHERE emp_number = $empNumber";
		
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
					$row=mysqli_fetch_assoc($count);
					do{ 

						$data['ep_passport_num']=$row['ep_passport_num'];
					$data['issdDate']=$row['ep_passportissueddate'];
					$data['expiryDate']=$row['ep_passportexpiredate'];
						//$data['dteOfBrth']=$row['ed_date_of_birth'];
						
						
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count)); 				
						$data['imgrtnRcrds']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}



		function jobDetails($userIdPass)
	{
		$data= array();

		$userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];

		$query="SELECT * FROM  hs_hr_employee WHERE emp_number = $empNumber";
		
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
					$row=mysqli_fetch_assoc($count);
					do{ 

						$data['job_title_code']=$row['job_title_code'];
					$data['emplmntStatus']=$row['emp_status'];
					$data['joinedDate']=$row['joined_date'];
					$data['subUnit']=$row['work_station'];
					$data['eeo_cat_code']=$row['eeo_cat_code'];
					$data['plant_id']=$row['plant_id'];
					$data['emp_ctc']=$row['emp_ctc'];	
					$data['emp_cost_of_company']=$row['emp_cost_of_company'];	
					$data['emp_gross_salary']=$row['emp_gross_salary'];		
						
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count)); 				
						$data['jobDetails']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}

	function salaryComponents($userIdPass)
	{
		$data= array();

		$userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];

		$query="SELECT * FROM  hs_hr_emp_basicsalary WHERE emp_number = $empNumber";
		
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
					$row=mysqli_fetch_assoc($count);
					do{ 

						$data['emp_number']=$row['emp_number'];
					$data['sal_grd_code']=$row['sal_grd_code'];
					$data['currency_id']=$row['currency_id'];
					$data['ebsal_basic_salary']=$row['ebsal_basic_salary'];
					$data['payperiod_code']=$row['payperiod_code'];
					$data['salary_component']=$row['salary_component'];
					$data['comments']=$row['comments'];	
						
						
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count)); 				
						$data['salaryComponents']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}


	function reportTo($userIdPass)
	{
		$data= array();

		$userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];

		$query="SELECT concat(emp.emp_firstname,emp.emp_middle_name,emp.emp_lastname) as name, rep.reporting_method_name as reptType FROM hs_hr_emp_reportto hrs LEFT JOIN hs_hr_employee emp ON emp.emp_number = hrs.erep_sup_emp_number
				   LEFT JOIN ohrm_emp_reporting_method rep ON rep.reporting_method_id = hrs.erep_reporting_mode WHERE hrs.erep_sub_emp_number = $empNumber";
		
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
					$row=mysqli_fetch_assoc($count);
					do{ 

						$data['name']=$row['name'];
					    $data['reptType']=$row['reptType'];
					   
						
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count)); 				
						$data['reportTo']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}


	function empSubordinatesrepTo($userIdPass)
	{
		$data= array();

		$userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];

			$query="SELECT concat(emp.emp_firstname,' ',emp.emp_lastname) as name, rep.reporting_method_name as reptType FROM
						 	hs_hr_emp_reportto hrs
							LEFT JOIN hs_hr_employee emp ON emp.emp_number = hrs.erep_sub_emp_number
							LEFT JOIN ohrm_emp_reporting_method rep ON rep.reporting_method_id = hrs.erep_reporting_mode
							WHERE hrs.erep_sup_emp_number = $empNumber
							ORDER BY hrs.erep_sub_emp_number ASC";
		
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
					$row=mysqli_fetch_assoc($count);
					do{ 

						$data['name']=$row['name'];
					    $data['reptType']=$row['reptType'];
					   
						
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count)); 				
						$data['empSubordinates']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}



	function workExp($userIdPass)
	{
		$data= array();

		$userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];

		$query="SELECT exp.eexp_employer AS company, exp.eexp_jobtit AS jobTitle, exp.eexp_from_date AS fromDate, exp.eexp_to_date
		AS toDate,exp.eexp_comments as comment FROM hs_hr_emp_work_experience exp WHERE exp.emp_number = $empNumber";
		
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
					$row=mysqli_fetch_assoc($count);
					do{ 

						$data['company']=$row['company'];
					    $data['jobTitle']=$row['jobTitle'];
					     $data['fromDate']=$row['fromDate'];
					      $data['toDate']=$row['toDate'];
					       $data['comment']=$row['comment'];
					   
						
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count)); 				
						$data['workExp']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}


	function emplEductn($userIdPass)
	{
		$data= array();

		$userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];

		$query="SELECT e.name as level, ed.year as year,ed.score as score FROM ohrm_emp_education ed LEFT JOIN ohrm_education e ON e.id = ed.education_id WHERE ed.emp_number = $empNumber";
		
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
					$row=mysqli_fetch_assoc($count);
					do{ 

						$data['level']=$row['level'];
					    $data['year']=$row['year'];
					     $data['score']=$row['score'];
					     
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count)); 				
						$data['emplEductn']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}



	function empSkills($userIdPass)
	{
		$data= array();

		$userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];

		$query="SELECT sk.name as skill,epski.years_of_exp as exp FROM hs_hr_emp_skill epski LEFT JOIN ohrm_skill sk ON sk.id = epski.skill_id WHERE epski.emp_number = $empNumber";
		
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
					$row=mysqli_fetch_assoc($count);
					do{ 

						$data['skill']=$row['skill'];
					    $data['exp']=$row['exp'];
					   
					     
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count)); 				
						$data['empSkills']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}

	function empLang($userIdPass)
	{
		$data= array();

		$userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];

		$query="SELECT ln.name as language,lng.fluency as flncy,lng.competency as cmptncy,lng.comments as cmnts FROM hs_hr_emp_language lng LEFT JOIN ohrm_language ln ON ln.id = lng.lang_id WHERE lng.emp_number = $empNumber";
		
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
					$row=mysqli_fetch_assoc($count);
					do{ 

						$data['language']=$row['language'];
					    $data['flncy']=$row['flncy'];
					   $data['cmptncy']=$row['cmptncy'];
					     $data['cmnts']=$row['cmnts'];

						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count)); 				
						$data['empLang']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}


	function empLicense($userIdPass)
	{
		$data= array();

		$userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];

		$query="SELECT hrli.name as type,li.license_issued_date as issdDate, li.license_expiry_date as expDate FROM ohrm_emp_license li LEFT JOIN ohrm_license hrli ON hrli.id = li.license_id WHERE li.emp_number = $empNumber";
		
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
					$row=mysqli_fetch_assoc($count);
					do{ 

						$data['type']=$row['type'];
					    $data['issdDate']=$row['issdDate'];
					   $data['expDate']=$row['expDate'];
					   
					     
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count)); 				
						$data['empLicense']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}


	function empMbrshp($userIdPass)
	{
		$data= array();

		$userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];

		$query="SELECT ms.name as membership,mem.ememb_subscript_ownership as subPaidBy, mem.ememb_subscript_amount as amount, mem.				ememb_subs_currency as cur,mem.ememb_commence_date cmnsDate,mem.ememb_renewal_date as renDate
						FROM hs_hr_emp_member_detail mem
						LEFT JOIN ohrm_membership ms ON ms.id = mem.membship_code
						WHERE mem.emp_number = $empNumber";
		
		$count=mysqli_query($this->conn, $query);
		if(mysqli_num_rows($count) > 0)
		{
					$row=mysqli_fetch_assoc($count);
					do{ 

						$data['membership']=$row['membership'];
					    $data['subPaidBy']=$row['subPaidBy'];
					   $data['amount']=$row['amount'];
					    $data['cur']=$row['cur'];
					     $data['cmnsDate']=$row['cmnsDate'];
					      $data['renDate']=$row['renDate'];
					     
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count)); 				
						$data['empMbrshp']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
		return $data;    
	}


	function deleteAttachment($ticket_id,$attach_id)
	{

		if($ticket_id!="" && $attach_id !=""){


		$data= array();

	
		$query="SELECT id,ticket_id FROM ohrm_ticket_attachment WHERE id = $attach_id AND ticket_id = $ticket_id";
		
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			//echo "if";
					
						$deletesql1 ="DELETE FROM ohrm_ticket_attachment
							WHERE id = $attach_id";


				$result = mysqli_query($this->conn, $deletesql1);

							//$row=mysqli_fetch_assoc($result);

								$data['ticket_id'] = $ticket_id;

							$data1[] = $data;

						$data['delAttach']=$data1;
						$data['status']=1;
							
		}else{

			//echo "else";
				$data['status']=0;
			}

			
		}else{
		
				$data['status']=0;	
				}

			return $data;
	}

	function deleteActionLogAttachment($ticket_action_log_id,$attach_id)
	{

		if($ticket_action_log_id!="" && $attach_id !=""){


		$data= array();

	
		$query="SELECT id,ticket_action_log_id FROM ohrm_ticket_action_log_attachment WHERE id = $attach_id AND $ticket_action_log_id = $ticket_action_log_id";
		
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			//echo "if";
					
			$deletesql1 ="DELETE FROM ohrm_ticket_action_log_attachment
							WHERE id = $attach_id";


				$result = mysqli_query($this->conn, $deletesql1);

							//$row=mysqli_fetch_assoc($result);

					$data['ticket_action_log_id'] = $ticket_action_log_id;

							$data1[] = $data;

						$data['delActnLogAttach']=$data1;
						$data['status']=1;
							
		}else{

			//echo "else";
				$data['status']=0;
			}

			
		}else{
		
				$data['status']=0;	
				}

			return $data;
	}



	 function empEngTechList($emp_number)
    {
        $data=array();
  			
		$query="SELECT h.erep_sub_emp_number as emp_number FROM hs_hr_emp_reportto h 
						LEFT JOIN ohrm_user u ON u.emp_number = h.erep_sub_emp_number
						WHERE u.user_role_id = 12 AND h.erep_sup_emp_number = $emp_number";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
				$row=mysqli_fetch_assoc($count);
					do{ 
						$data1[] = $row['emp_number'];
					}while($row = mysqli_fetch_assoc($count));
						$data['empTechlist']=$data1;
						$data['status'] = 1;
		}
		return $data;
    }


	function jobCountAll($user_id)
	{

			$data=array();
		$i = 0;
		$userDetails = $this->getUserRoleByUserId($user_id);
		$emp_number = $userDetails['empNumber'];
		//echo $emp_number;
		$userRoleId = $userDetails['id'];
		//echo $userRoleId;

		$location_id = $this->getLocByEmpNumber($emp_number);
		//echo "loc".' ' .$location_id;
		
		//$i=0;
		$empNumber = $this->getEmpnumberByUserId($user_id);
       	$empresult=$this->employeeDetails($empNumber);

       	/*$EngSubTechDetails = $this->getSubTechListofEngineer($empNumber);

		$empNumber = $EngSubTechDetails['empNumber'];*/

       	$plantId = $empresult['plant_id'];
       	//echo "plantId".' ' .$plantId;


       	// echo $user_id.','.$userRoleId.','.$location_id.','.$emp_number;
       	// exit();

       	if($userRoleId == 10 || $userRoleId == 19){

       		//EnM New Tsks COunt
       			$querynewTsk="SELECT COUNT(o.id) as contnew FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id WHERE (o2.status_id IN (1, 6) AND o.location_id = 3 AND o.plant_id = 1 AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) ORDER BY o.id DESC";

       			//$configDate = $this->dateFormat();

		$EnMnewTsk=mysqli_query($this->conn, $querynewTsk);

								 if(mysqli_num_rows($EnMnewTsk) > 0)
								{

												$row = mysqli_fetch_assoc($EnMnewTsk);
												$EnMnewTskCount = $row['contnew']; 
												
								}else{
											$EnMnewTskCount = 0;
									}

       			//EnM Eng Tsks COunt
			$empresult=$this->empList(11);
	        for ($i=0; $i < sizeof($empresult['emplist']) ; $i++) { 
	        	$empList[] = $empresult['emplist'][$i];
	        	//to convert Array into string the following implode method is used
	        	$empLists = implode(',', $empList);
	        }
		
	        

       			$queryEngTsk="SELECT COUNT(o.id) as contEng FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id WHERE ((o2.accepted_by IN ($empLists) OR o2.forward_to IN ($empLists)) AND o.location_id = $location_id AND o.plant_id = $plantId AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) ORDER BY o.id DESC";


       			
				$EnMengTsk=mysqli_query($this->conn, $queryEngTsk);

		 						if(mysqli_num_rows($EnMengTsk) > 0)
								{
												$row1 = mysqli_fetch_assoc($EnMengTsk);
												$EnMengTskCount = $row1['contEng']; 
								}else{
												$EnMengTskCount = 0;
								}

       			//EnM Tech Tsks COunt
       		$empresult1=$this->empList(12);
		 
	        for ($i=0; $i < sizeof($empresult1['emplist']) ; $i++) { 
	        	$empList1[] = $empresult1['emplist'][$i];
	        	//to convert Array into string the following implode method is used
	        	$empLists1 = implode(',', $empList1);
	        }

       		$queryTechTsk = "SELECT COUNT(o.id) AS contTech FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id WHERE ((o2.accepted_by IN ($empLists1) OR o2.forward_to IN ($empLists1)) AND o.location_id = $location_id AND o.plant_id = $plantId AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) ORDER BY o.id DESC";

       		

				$EnMtechTsk=mysqli_query($this->conn, $queryTechTsk);

								if(mysqli_num_rows($EnMtechTsk) > 0)
								{
												$row2 = mysqli_fetch_assoc($EnMtechTsk);
												$EnMtechTskCount = $row2['contTech']; 
								}else{
											$EnMtechTskCount = 0;
								}



			$queryMyJbs="";
       		$queryDeptJbs="";

					$data['EnMNewTasks']=$EnMnewTskCount;
					$data['EnMEngTasks']=$EnMengTskCount;
					$data['EnMTechTasks']=$EnMtechTskCount;
					$data1[] = $data;
					$data['jobCountAll']=$data1;
					$data['status']=1;

       	}else if($userRoleId == 11){

       		/*Eng New Tasks Job Count*/

       		 $result=$this->multipledeptList($empNumber);

    $empresult=$this->employeeDetails($empNumber);

    $issueresult=$this->typeofissuelist($empNumber);

    $multidept[] = $empresult['work_station'];
    $multi_dept = implode(',', $multidept);
    if($result['status']== 1){
        for ($i=0; $i < sizeof($result['deptmultlist']) ; $i++) { 
            $multidept[] = $result['deptmultlist'][$i];
            //to convert Array into string the following implode method is used
            $multi_dept = implode(',', $multidept);
        }
    }
    if($issueresult['status']== 1){
        for ($i=0; $i < sizeof($issueresult['typeid']) ; $i++) { 
            $issueList[] = $issueresult['typeid'][$i];
            //to convert Array into string the following implode method is used
            $multi_issue = implode(',', $issueList);
        }
    }else{
        $multi_issue = -1;
    }
    
		$empresult1=$this->empList(11);
		 
	        for ($i=0; $i < sizeof($empresult1['emplist']) ; $i++) { 
	        	$empList1[] = $empresult1['emplist'][$i];
	        	//to convert Array into string the following implode method is used
	        	$empLists1 = implode(',', $empList1);

	        }

    	

    		$queryEngNewTsk = "SELECT COUNT(o.id) AS contEngNew FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id WHERE((o.user_department_id IN ($multi_dept) OR o2.forward_to = $empNumber OR o.type_of_issue_id IN ($multi_issue) OR o2.forward_to = $empNumber) AND o2.status_id IN (1,2,6) AND o2.forward_from != $empNumber AND (o2.forward_to IN ($empLists1) OR o2.forward_to IS NULL OR o2.forward_to = 0) AND o.location_id = $location_id AND o.plant_id = $plantId AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) ORDER BY o.id DESC";

    		/*echo $queryEngNewTsk1;
    		exit();*/


       		$EngNewTsk=mysqli_query($this->conn, $queryEngNewTsk);

		 						if(mysqli_num_rows($EngNewTsk) > 0)
								{
												$row1 = mysqli_fetch_assoc($EngNewTsk);
												$EngNewTskCount = $row1['contEngNew']; 
								}else{
												$EngNewTskCount = 0;
								}


       		/*Eng Inprogress Jobs Count */
       		$queryEngInprgTsk="SELECT COUNT(ta.id) AS contEngIng FROM ohrm_ticket_acknowledgement_action_log ta LEFT JOIN ohrm_ticket t ON t.id = ta.ticket_id LEFT JOIN hs_hr_employee emp ON  emp.emp_number = ta.submitted_by_emp_number LEFT JOIN ohrm_location loc ON loc.id = t.location_id  LEFT JOIN ohrm_plant plant ON plant.id = t.plant_id LEFT JOIN ohrm_subunit sub ON sub.id = t.user_department_id  LEFT JOIN ohrm_functional_location func ON func.id = t.functional_location_id LEFT JOIN ohrm_equipment eqp ON eqp.id = t.equipment_id LEFT JOIN ohrm_type_of_issue iss ON iss.id = t.type_of_issue_id LEFT JOIN ohrm_ticket_status sta ON sta.id = t.status_id LEFT JOIN ohrm_ticket_priority tktprty ON tktprty.id = t.priority_id LEFT JOIN ohrm_ticket_severity tktsvrty ON tktsvrty.id = t.severity_id LEFT JOIN hs_hr_employee empsub ON empsub.emp_number = t.submitted_by_name  WHERE ta.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) and ta.accepted_by = $empNumber AND ta.status_id IN (3,4) ORDER BY t.job_id DESC";


       		$EngInprgTsk=mysqli_query($this->conn, $queryEngInprgTsk);

		 						if(mysqli_num_rows($EngInprgTsk) > 0)
								{
												$row2 = mysqli_fetch_assoc($EngInprgTsk);
												$EngInprgTskCount = $row2['contEngIng']; 
								}else{
												$EngInprgTskCount = 0;
								}

       		/*Eng Resolved Tasks Count*/
				   $queryEngResolvedTsk="SELECT COUNT(t.id) as id,t.job_id AS job_id,t.subject AS subject,tp.name AS priority,tsv.name AS 						severity,t.sla AS sla,CONCAT(e.emp_firstname,' ',e.emp_lastname) AS raised_by,
				   							t.reported_on AS raised_on,
				   							ta.submitted_by_name AS acknowledged_by,ta.submitted_on AS acknowledged_on,ts.name AS status
											FROM ohrm_ticket_acknowledgement_action_log ta
											LEFT JOIN ohrm_ticket t ON t.id = ta.ticket_id
											LEFT JOIN ohrm_ticket_priority tp ON tp.id = ta.priority_id
											LEFT JOIN ohrm_ticket_severity tsv ON tsv.id = ta.severity_id
											LEFT JOIN hs_hr_employee e ON e.emp_number = t.reported_by
							 				LEFT JOIN ohrm_ticket_status ts ON ts.id = ta.status_id
											WHERE ta.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id)
											and ta.forward_to = $empNumber AND ta.status_id = 14
											order by id desc";

				$EngReslvdTsk=mysqli_query($this->conn, $queryEngResolvedTsk);

		 						if(mysqli_num_rows($EngReslvdTsk) > 0)
								{
												$row2 = mysqli_fetch_assoc($EngReslvdTsk);
												$EngResvdTaskCount = $row2['id']; 
								}else{
												$EngResvdTaskCount = 0;
								}

       		/*Eng Reject Tasks Count*/
       		$queryEngRejectTsk="SELECT COUNT(t.id) as id,t.job_id AS job_id,t.subject AS subject,tp.name AS priority,tsv.name AS severity,t.sla AS sla,CONCAT(e.emp_firstname,' ',e.emp_lastname) AS raised_by,t.reported_on AS raised_on,ta.submitted_by_name AS acknowledged_by,ta.submitted_on AS acknowledged_on,ts.name AS status, ta.comment as comment
			FROM ohrm_ticket_acknowledgement_action_log ta
			LEFT JOIN ohrm_ticket t ON t.id = ta.ticket_id
			LEFT JOIN ohrm_ticket_priority tp ON tp.id = ta.priority_id
			LEFT JOIN ohrm_ticket_severity tsv ON tsv.id = ta.severity_id
			LEFT JOIN hs_hr_employee e ON e.emp_number = t.reported_by
			 LEFT JOIN ohrm_ticket_status ts ON ts.id = ta.status_id
			WHERE ta.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) and ta.forward_to = $empNumber
			AND ta.status_id = 16
			order by id desc";

				$EngRejctdTsk=mysqli_query($this->conn, $queryEngRejectTsk);

		 						if(mysqli_num_rows($EngRejctdTsk) > 0)
								{
												$row3 = mysqli_fetch_assoc($EngRejctdTsk);
												$EngRejctdTaskCount = $row3['id']; 
								}else{
												$EngRejctdTaskCount = 0;
								}

       		/*Eng Tech Tasks Count*/

       			//$EngSubTechDetails = $this->getSubTechListofEngineer($empNumber);

       				$empresult5=$this->empEngTechList($emp_number);
       				/*echo "<pre>";
		 print_r($empresult5);
		 exit();*/
		 
	        for ($i=0; $i < sizeof($empresult5['empTechlist']) ; $i++) { 
	        	$empList5[] = $empresult5['empTechlist'][$i];
	        	//to convert Array into string the following implode method is used
	        	$empLists5 = implode(',', $empList5);
	        	/*print_r($empLists5);
	        	exit();*/

	        }

       	
       		$queryEngTechTsk="SELECT COUNT(o.id) AS id, o.job_id AS job_id, o.location_id AS o__location_id, o.plant_id AS o__plant_id, o.user_department_id AS o__user_department_id, o.notify_to AS o__notify_to, o.functional_location_id AS o__functional_location_id, o.equipment_id AS o__equipment_id, o.type_of_issue_id AS o__type_of_issue_id, o.status_id AS o__status_id, o.sla AS o__sla, o.subject AS subject, o.description AS o__description, o.priority_id AS o__priority_id, o.severity_id AS o__severity_id, o.reported_by AS o__reported_by, o.reported_on AS o__reported_on, o.submitted_by_name AS o__submitted_by_name, o.submitted_by_emp_number AS o__submitted_by_emp_number, o.submitted_on AS o__submitted_on, o.modified_by_name AS o__modified_by_name, o.modified_by_emp_number AS o__modified_by_emp_number, o.modified_on AS o__modified_on, o.is_preventivemaintenance AS o__is_preventivemaintenance, o.is_deleted AS o__is_deleted, o2.id AS o2__id, o2.ticket_id AS o2__ticket_id, o2.status_id AS o2__status_id, o2.priority_id AS o2__priority_id, o2.severity_id AS o2__severity_id, o2.comment AS o2__comment, o2.machine_status AS o2__machine_status, o2.assigned_date AS o2__assigned_date, o2.due_date AS o2__due_date, o2.accepted_by AS o2__accepted_by, o2.rejected_by AS o2__rejected_by, o2.submitted_on AS o2__submitted_on, o2.forward_from AS o2__forward_from, o2.forward_to AS o2__forward_to, o2.submitted_by_name AS o2__submitted_by_name, o2.submitted_by_emp_number AS o2__submitted_by_emp_number, o2.created_by_user_id AS o2__created_by_user_id, o2.root_cause_id AS o2__root_cause_id, o2.response_id AS o2__response_id FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id WHERE ((o2.accepted_by IN ($empLists5) OR o2.forward_to IN ($empLists5)) AND o.location_id = 3 AND o.plant_id = 1 AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) ORDER BY o.id DESC";

       		/*$queryEngTechTsk="SELECT COUNT(o.id) AS id, o.job_id AS o__job_id, o.location_id AS o__location_id, o.plant_id AS o__plant_id, o.user_department_id AS o__user_department_id, o.notify_to AS o__notify_to, o.functional_location_id AS o__functional_location_id, o.equipment_id AS o__equipment_id, o.type_of_issue_id AS o__type_of_issue_id, o.status_id AS o__status_id, o.sla AS o__sla, o.subject AS o__subject, o.description AS o__description, o.priority_id AS o__priority_id, o.severity_id AS o__severity_id, o.reported_by AS o__reported_by, o.reported_on AS o__reported_on, o.submitted_by_name AS o__submitted_by_name, o.submitted_by_emp_number AS o__submitted_by_emp_number, o.submitted_on AS o__submitted_on, o.modified_by_name AS o__modified_by_name, o.modified_by_emp_number AS o__modified_by_emp_number, o.modified_on AS o__modified_on, o.is_preventivemaintenance AS o__is_preventivemaintenance, o.is_deleted AS o__is_deleted, o2.id AS o2__id, o2.ticket_id AS o2__ticket_id, o2.status_id AS o2__status_id, o2.priority_id AS o2__priority_id, o2.severity_id AS o2__severity_id, o2.comment AS o2__comment, o2.machine_status AS o2__machine_status, o2.assigned_date AS o2__assigned_date, o2.due_date AS o2__due_date, o2.accepted_by AS o2__accepted_by, o2.rejected_by AS o2__rejected_by, o2.submitted_on AS o2__submitted_on, o2.forward_from AS o2__forward_from, o2.forward_to AS o2__forward_to, o2.submitted_by_name AS o2__submitted_by_name, o2.submitted_by_emp_number AS o2__submitted_by_emp_number, o2.created_by_user_id AS o2__created_by_user_id, o2.root_cause_id AS o2__root_cause_id, o2.response_id AS o2__response_id FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id WHERE ((o.status_id = 3 OR o2.status_id = 7 OR o2.status_id = 9 OR o2.status_id = 8) AND (o2.accepted_by IN ($empLists5) OR o2.forward_to IN ($empLists5)) OR o2.forward_to IN ($empLists5) AND o.location_id = 3 AND o.plant_id = 1 AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) ORDER BY o.id DESC";*/


       		/*echo $queryEngTechTsk;
       		exit();*/
       		$EngTechTsks=mysqli_query($this->conn, $queryEngTechTsk);

		 						if(mysqli_num_rows($EngTechTsks) > 0)
								{
												$row4 = mysqli_fetch_assoc($EngTechTsks);
												$EngTechTasksCount = $row4['id']; 
								}else{
												$EngTechTasksCount = 0;
								}


       		$queryMyJbs="";
       		$queryDeptJbs="";

       		$data['EngNewTasks']=$EngNewTskCount;
       		$data['EngInprgTasks']=$EngInprgTskCount;
       		$data['EngResvdTasks']= $EngResvdTaskCount;
       		$data['EngRejctTasks']= $EngRejctdTaskCount;
       		$data['EngTechnTasks']= $EngTechTasksCount;
       		$data1[] = $data;
					$data['jobCountAll']=$data1;
					$data['status']=1;


       	}else if($userRoleId == 12){

       		
       		$empresult=$this->empList(12);
		 // echo "<pre>";
		 // print_r($empresult);
		 // exit();
	        for ($i=0; $i < sizeof($empresult['emplist']) ; $i++) { 
	        	$empList[] = $empresult['emplist'][$i];
	        	//to convert Array into string the following implode method is used
	        	$empLists = implode(',', $empList);
	        }

        $i=0;
        $queryTechNewTsk = "SELECT COUNT(o.id) AS id, o.job_id AS job_id, o.location_id AS location_id, o.plant_id AS plant_id, o.user_department_id AS user_department_id, o.functional_location_id AS functional_location_id, o.equipment_id AS equipment_id, o.type_of_issue_id AS type_of_issue_id, o.status_id AS status_id, o.sla AS sla, o.subject AS subject, o.description AS description, o.priority_id AS priority_id, o.severity_id AS severity_id, o.reported_by AS reported_by, o.reported_on AS reported_on, o.submitted_by_name AS submitted_by_name, o.submitted_by_emp_number AS submitted_by_emp_number, o.submitted_on AS submitted_on, o.modified_by_name AS modified_by_name, o.modified_by_emp_number AS modified_by_emp_number, o.modified_on AS modified_on, o.is_preventivemaintenance AS is_preventivemaintenance, o.is_deleted AS is_deleted FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id
WHERE (o2.forward_to = $emp_number AND o2.status_id = 2 AND o2.forward_to IN ($empLists)
AND o.location_id = 3 
AND o.plant_id = 1 AND
o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) ORDER BY o.id DESC";

	$TechNewTsks=mysqli_query($this->conn, $queryTechNewTsk);

		 						if(mysqli_num_rows($TechNewTsks) > 0)
								{
												$row4 = mysqli_fetch_assoc($TechNewTsks);
												$TechNewTasksCount = $row4['id']; 
								}else{
												$TechNewTasksCount = 0;
								}


       		$queryTechInprgTsk="SELECT COUNT(ta.ticket_id) AS id,t.job_id as job_id,t.sla AS sla,sta.name as status,t.subject as subject,tktprty.name as priority,tktsvrty.name as severity,CONCAT(emp.emp_firstname,emp.emp_lastname) AS raised_by,t.reported_on as raised_on,CONCAT(emp.emp_firstname,emp.emp_lastname) AS acknowledged_by,ta.submitted_on AS acknowledged_on FROM ohrm_ticket_acknowledgement_action_log ta LEFT JOIN ohrm_ticket t ON t.id = ta.ticket_id LEFT JOIN hs_hr_employee emp ON  emp.emp_number = ta.submitted_by_emp_number LEFT JOIN ohrm_location loc ON loc.id = t.location_id  LEFT JOIN ohrm_plant plant ON plant.id = t.plant_id LEFT JOIN ohrm_subunit sub ON sub.id = t.user_department_id  LEFT JOIN ohrm_functional_location func ON func.id = t.functional_location_id LEFT JOIN ohrm_equipment eqp ON eqp.id = t.equipment_id LEFT JOIN ohrm_type_of_issue iss ON iss.id = t.type_of_issue_id LEFT JOIN ohrm_ticket_status sta ON sta.id = t.status_id LEFT JOIN ohrm_ticket_priority tktprty ON tktprty.id = t.priority_id LEFT JOIN ohrm_ticket_severity tktsvrty ON tktsvrty.id = t.severity_id LEFT JOIN hs_hr_employee empsub ON empsub.emp_number  = t.submitted_by_name  WHERE ta.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) and ta.accepted_by = $emp_number AND ta.status_id IN (3,4) ORDER BY t.job_id DESC";

       		$TechInprgTsks=mysqli_query($this->conn, $queryTechInprgTsk);

		 						if(mysqli_num_rows($TechInprgTsks) > 0)
								{
												$row5 = mysqli_fetch_assoc($TechInprgTsks);
												$TechInprgTasksCount = $row5['id']; 
								}else{
												$TechInprgTasksCount = 0;
								}


       		$queryMyJbs="";
       		$queryDeptJbs="";


       		$data['TechNewTasks']=$TechNewTasksCount;
       		$data['TechInprgTasks']=$TechInprgTasksCount;
       		
       		$data1[] = $data;
					$data['jobCountAll']=$data1;
					$data['status']=1;
       	}else{

       		$queryMyJbs="";
       		$queryDeptJbs="";

       	}


     

	return $data;
	}


	//EngTechTsksLst webservice to get techincian tasks for engineer login
    function EngTechTsksLst($emp_number)
    {
        $data=array();

        $empresult5=$this->empEngTechList($emp_number);
       				/*echo "<pre>";
		 print_r($empresult5);
		 exit();*/
		 
	        for ($i=0; $i < sizeof($empresult5['empTechlist']) ; $i++) { 
	        	$empList5[] = $empresult5['empTechlist'][$i];
	        	//to convert Array into string the following implode method is used
	        	$empLists5 = implode(',', $empList5);
	        	/*print_r($empLists5);
	        	exit();*/

	        }
        $i=0;
        $query = "SELECT o.id AS id, o.job_id AS job_id, o.location_id AS o__location_id, o.plant_id AS o__plant_id, o.user_department_id AS o__user_department_id, o.notify_to AS o__notify_to, o.functional_location_id AS o__functional_location_id, o.equipment_id AS o__equipment_id, o.type_of_issue_id AS o__type_of_issue_id, o.status_id AS o__status_id, o.sla AS o__sla, o.subject AS subject, o.description AS o__description, o.priority_id AS o__priority_id, o.severity_id AS o__severity_id, o.reported_by AS o__reported_by, o.reported_on AS o__reported_on, o.submitted_by_name AS o__submitted_by_name, o.submitted_by_emp_number AS o__submitted_by_emp_number, o.submitted_on AS o__submitted_on, o.modified_by_name AS o__modified_by_name, o.modified_by_emp_number AS o__modified_by_emp_number, o.modified_on AS o__modified_on, o.is_preventivemaintenance AS o__is_preventivemaintenance, o.is_deleted AS o__is_deleted, o2.id AS o2__id, o2.ticket_id AS o2__ticket_id, o2.status_id AS o2__status_id, o2.priority_id AS o2__priority_id, o2.severity_id AS o2__severity_id, o2.comment AS o2__comment, o2.machine_status AS o2__machine_status, o2.assigned_date AS o2__assigned_date, o2.due_date AS o2__due_date, o2.accepted_by AS o2__accepted_by, o2.rejected_by AS o2__rejected_by, o2.submitted_on AS o2__submitted_on, o2.forward_from AS o2__forward_from, o2.forward_to AS o2__forward_to, o2.submitted_by_name AS o2__submitted_by_name, o2.submitted_by_emp_number AS o2__submitted_by_emp_number, o2.created_by_user_id AS o2__created_by_user_id, o2.root_cause_id AS o2__root_cause_id, o2.response_id AS o2__response_id FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id WHERE ((o2.accepted_by IN ($empLists5) OR o2.forward_to IN ($empLists5)) AND o.location_id = 3 AND o.plant_id = 1 AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id) AND o.is_deleted = 0) ORDER BY o.id DESC";


    
        /*echo $query;
        exit();*/
        $configDate = $this->dateFormat();
		$count=mysqli_query($this->conn, $query);

		//$jobsCountNew = mysqli_num_rows($count);

			//echo $jobsCountNew;
			//exit();

		if(mysqli_num_rows($count) > 0)
		{	

						$row=mysqli_fetch_assoc($count);
					do{ 

						$i=$i+1;

						$data['sno']=$i;					
						$data['id']=$row['id'];
						$data['job_id']=$row['job_id'];
						$text = $row['subject'];
						$data['subject']=iconv(mb_detect_encoding($text), "UTF-8//IGNORE", $text);
						
						//$data['status']="New";
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count));
						$data['EngTechTasks']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}
        return $data;
    }


     function MyjbsandDeptJbsCount($user_id)
    {
        

        $data=array();

       
       $userDetails = $this->getUserRoleByUserId($user_id);
		$emp_number = $userDetails['empNumber'];
		//echo $emp_number;
		$userRoleId = $userDetails['id'];
		$empresult=$this->employeeDetails($emp_number);
		$departmentId = $empresult['work_station'];

       if($userRoleId == 10){
        //$query = "SELECT COUNT(t.id) as myJob FROM ohrm_ticket t";

         $query = "SELECT * FROM ohrm_ticket t";

		$count=mysqli_query($this->conn, $query);

		$myJobs = mysqli_num_rows($count);

			/*echo $myJobs;
			exit();*/

			$userDetails = $this->getUserRoleByUserId($user_id);
		$empNumber = $userDetails['empNumber'];
		

      
		$query1 = "SELECT * FROM ohrm_ticket WHERE user_department_id = $departmentId";	


		$count1 = mysqli_query($this->conn, $query1);

		$deptJobs = mysqli_num_rows($count1);

	

						if(mysqli_num_rows($count) > 0)
		{				
						$data['myJobs'] = $myJobs;

						$data['deptJobs'] = $deptJobs;


						
						$data1[] = $data;
				//	}while($row = mysqli_fetch_assoc($count));
						$data['MyjbsandDeptJbsCount']=$data1;
						$data['status']=1;
			}
			else
			{

				$data['status']=0;
			}
			

		}

		else if($userRoleId == 11)	

		{

			$userDetails = $this->getUserRoleByUserId($user_id);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$departmentId = $empresult['work_station'];

		//echo $departmentId;
		//exit();

		$engMyJobQuery = "SELECT t.job_id AS job_id, t.id AS ticketId, t.subject AS subject, t.submitted_on AS submittedon,t.is_PreventiveMaintenance AS preventiveMaintenance,ta.machine_status AS machineStatus, fl.name as functionallocation, fl.id as functionalLocationId,toi.name AS issue, toi.id AS typeOfIssueId, toi.sla AS sla,loc.name AS location, loc.id AS locationId, plnt.plant_name AS plant, plnt.id AS plantId, eq.name AS equipment, eq.id AS equipmentId, ts.name AS status, ts.id AS statusId, ta.ticket_id AS ticketId, t.submitted_by_name AS submittedby, e.emp_number AS engineerId, e.emp_number AS technicianId,tp.name AS priority, tp.id AS priorityId, tsev.name AS severity, tsev.id AS severityId, u.id AS uaerId, cs.name AS department, cs.id AS subDivisionId FROM ohrm_ticket t LEFT JOIN ohrm_functional_location fl ON fl.id = t.functional_location_id LEFT JOIN ohrm_type_of_issue toi ON toi.id = t.type_of_issue_id LEFT JOIN ohrm_location loc ON loc.id = t.location_id LEFT JOIN ohrm_plant plnt ON plnt.id = t.plant_id LEFT JOIN ohrm_equipment eq ON eq.id = t.equipment_id LEFT JOIN ohrm_ticket_status ts ON ts.id = t.status_id LEFT JOIN ohrm_ticket_acknowledgement_action_log ta ON ta.ticket_id = t.id LEFT JOIN hs_hr_employee e ON e.emp_number = ta.submitted_by_emp_number LEFT JOIN ohrm_user u ON u.id = ta.created_by_user_id LEFT JOIN ohrm_ticket_priority tp ON tp.id = t.priority_id LEFT JOIN ohrm_ticket_severity tsev ON tsev.id = t.severity_id LEFT JOIN ohrm_subunit cs ON cs.id = t.user_department_id WHERE ta.submitted_by_emp_number = $empNumber AND t.id NOT IN (select id from ohrm_ticket where status_id = 11 and submitted_by_emp_number != $empNumber) GROUP BY t.id
			ORDER BY `t`.`job_id` DESC";

			$engMyJobcount = mysqli_query($this->conn, $engMyJobQuery);

			$myjbcnt = mysqli_num_rows($engMyJobcount);


						if(mysqli_num_rows($engMyJobcount) > 0)
							{				

									$data['myJobs'] = $myjbcnt;

							}

							else
							{

								$data['status']=0;
							}
			
			$engDeptQuery = "SELECT * FROM ohrm_ticket WHERE user_department_id = $departmentId";

		$engDeptcount=mysqli_query($this->conn, $engDeptQuery);

		
			$deptjbcnt = mysqli_num_rows($engDeptcount);

						//$row=mysqli_fetch_assoc($engDeptcount);

						if(mysqli_num_rows($engDeptcount) > 0)
		{				
			
						$data['deptJobs'] = $deptjbcnt;
						
		}else{
					$data['deptJobs'] = 0;
		}



			$empresult6=$this->empShiftSupvsrList($emp_number);
       				/*echo "<pre>";
		 print_r($empresult6);
		 exit();*/
		 
	       for ($i=0; $i < sizeof($empresult6['empShiftSupvsrList']) ; $i++) { 
	        	$empList6[] = $empresult6['empShiftSupvsrList'][$i];
	        	//to convert Array into string the following implode method is used
	        	$empLists6 = implode(',', $empList6);

	        	/*echo "<pre>";
		 print_r($empresult6);
		 exit();*/
	        	

	        }

		$shftJbsQuery = "SELECT COUNT(o.id) AS shftCont
						FROM ohrm_ticket o
						LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id
						WHERE (o.user_department_id = $departmentId AND o2.status_id = 2 AND o2.forward_from IN ($empLists6) AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id))";


						$shftJbscount=mysqli_query($this->conn, $shftJbsQuery);

		
			//$deptjbcnt = mysqli_num_rows($engDeptcount);

						

						if(mysqli_num_rows($shftJbscount) > 0)
							{				

								$row=mysqli_fetch_assoc($shftJbscount);
								
											$data['shftJobs'] = $row['shftCont'];
											
							}else{
										$data['shftJobs'] = 0;
							}



		 				$data1[] = $data;
		 				$data['MyjbsandDeptJbsCount']=$data1;
						$data['status']=1;

	}
	 else 

		 {

		 		$queryothrs = "SELECT t.job_id AS job_id FROM ohrm_ticket t LEFT JOIN ohrm_functional_location fl ON fl.id = t.functional_location_id LEFT JOIN ohrm_type_of_issue toi ON toi.id = t.type_of_issue_id LEFT JOIN ohrm_location loc ON loc.id = t.location_id LEFT JOIN ohrm_plant plnt ON plnt.id = t.plant_id LEFT JOIN ohrm_equipment eq ON eq.id = t.equipment_id LEFT JOIN ohrm_ticket_status ts ON ts.id = t.status_id LEFT JOIN ohrm_ticket_acknowledgement_action_log ta ON ta.ticket_id = t.id LEFT JOIN hs_hr_employee e ON e.emp_number = ta.submitted_by_emp_number LEFT JOIN ohrm_user u ON u.id = ta.created_by_user_id LEFT JOIN ohrm_ticket_priority tp ON tp.id = t.priority_id LEFT JOIN ohrm_ticket_severity tsev ON tsev.id = t.severity_id LEFT JOIN ohrm_subunit cs ON cs.id = t.user_department_id WHERE ta.submitted_by_emp_number = $emp_number AND t.id NOT IN (select id from ohrm_ticket where status_id = 11 and submitted_by_emp_number != $emp_number) GROUP BY t.id
			ORDER BY `t`.`job_id`  DESC";



			$countothrs=mysqli_query($this->conn, $queryothrs);
			$myjbcnt = mysqli_num_rows($countothrs);

				if(mysqli_num_rows($countothrs) > 0)
				{
						$data['myJobs']=$myjbcnt;
	
				}else{
				$data['myJobs']=0;
			

				 }
		 		$engDeptQuery = "SELECT * FROM ohrm_ticket WHERE user_department_id = $departmentId";

		$engDeptcount=mysqli_query($this->conn, $engDeptQuery);

		
			$deptjbcnt = mysqli_num_rows($engDeptcount);

						//$row=mysqli_fetch_assoc($engDeptcount);

						if(mysqli_num_rows($engDeptcount) > 0)
					{				
						
									$data['deptJobs'] = $deptjbcnt;
									
					}else{
							$data['deptJobs'] = 0;
						}

		 				$data1[] = $data;
		 				$data['MyjbsandDeptJbsCount']=$data1;
						$data['status']=1;
		}



		
        return $data;
    }


    	 function empShiftSupvsrList($emp_number)
    {
        $data=array();
  			
		$query="SELECT * FROM ohrm_user WHERE user_role_id = 19";

		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
				$row=mysqli_fetch_assoc($count);
					do{ 
						$data1[] = $row['emp_number'];
					}while($row = mysqli_fetch_assoc($count));
						$data['empShiftSupvsrList']=$data1;
						$data['status'] = 1;
		}
		return $data;
    }

    //ShiftJobsList webservice
    function ShiftJobsList($user_id)
    {
        $data=array();

         $userDetails = $this->getUserRoleByUserId($user_id);
		$emp_number = $userDetails['empNumber'];
		//echo $emp_number;
		$userRoleId = $userDetails['id'];
		$empresult=$this->employeeDetails($emp_number);
		$departmentId = $empresult['work_station'];

        $empresult6=$this->empShiftSupvsrList($emp_number);
       				/*echo "<pre>";
		 print_r($empresult6);
		 exit();*/
		 
	       for ($i=0; $i < sizeof($empresult6['empShiftSupvsrList']) ; $i++) { 
	        	$empList6[] = $empresult6['empShiftSupvsrList'][$i];
	        	//to convert Array into string the following implode method is used
	        	$empLists6 = implode(',', $empList6);

	        	/*echo "<pre>";
		 print_r($empresult6);
		 exit();*/
	        	

	        }


	        if($userRoleId == 11)

	        {
	        		$i=0;
        $query = "SELECT o.id AS id, o.job_id AS job_id, o.location_id AS o__location_id, o.plant_id AS o__plant_id, o.user_department_id AS o__user_department_id, o.notify_to AS o__notify_to, o.functional_location_id AS o__functional_location_id, o.equipment_id AS o__equipment_id, o.type_of_issue_id AS o__type_of_issue_id, o.status_id AS o__status_id, o.sla AS o__sla, o.subject AS subject, o.description AS o__description, o.priority_id AS o__priority_id, o.severity_id AS o__severity_id, o.reported_by AS o__reported_by, o.reported_on AS o__reported_on, o.submitted_by_name AS o__submitted_by_name, o.submitted_by_emp_number AS o__submitted_by_emp_number, o.submitted_on AS o__submitted_on, o.modified_by_name AS o__modified_by_name, o.modified_by_emp_number AS o__modified_by_emp_number, o.modified_on AS o__modified_on, o.is_preventivemaintenance AS o__is_preventivemaintenance, o.is_deleted AS o__is_deleted, o2.id AS o2__id, o2.ticket_id AS o2__ticket_id, o2.status_id AS o2__status_id, o2.priority_id AS o2__priority_id, o2.severity_id AS o2__severity_id, o2.comment AS o2__comment, o2.machine_status AS o2__machine_status, o2.assigned_date AS o2__assigned_date, o2.due_date AS o2__due_date, o2.accepted_by AS o2__accepted_by, o2.rejected_by AS o2__rejected_by, o2.submitted_on AS o2__submitted_on, o2.forward_from AS o2__forward_from, o2.forward_to AS o2__forward_to, o2.submitted_by_name AS o2__submitted_by_name, o2.submitted_by_emp_number AS o2__submitted_by_emp_number, o2.created_by_user_id AS o2__created_by_user_id, o2.root_cause_id AS o2__root_cause_id, o2.response_id AS o2__response_id FROM ohrm_ticket o LEFT JOIN ohrm_ticket_acknowledgement_action_log o2 ON o.id = o2.ticket_id WHERE (o.user_department_id = $departmentId AND o2.status_id = 2 AND o2.forward_from IN ($empLists6) AND o2.id IN (SELECT MAX(o3.id) AS o3__0 FROM ohrm_ticket_acknowledgement_action_log o3 GROUP BY o3.ticket_id))";


    
        /*echo $query;
        exit();*/
        $configDate = $this->dateFormat();
		$count=mysqli_query($this->conn, $query);

		//$jobsCountNew = mysqli_num_rows($count);

			//echo $jobsCountNew;
			//exit();

		if(mysqli_num_rows($count) > 0)
		{	

						$row=mysqli_fetch_assoc($count);
					do{ 

						$i=$i+1;

						$data['sno']=$i;					
						$data['id']=$row['id'];
						$data['job_id']=$row['job_id'];
						$text = $row['subject'];
						$data['subject']=iconv(mb_detect_encoding($text), "UTF-8//IGNORE", $text);
						
						//$data['status']="New";
						$data1[] = $data;
					}while($row = mysqli_fetch_assoc($count));
						$data['ShiftJobsList']=$data1;
						$data['status']=1;
							
		}else{
				$data['status']=0;
			}


	        }

	        else
	        {

	        		$data['status']=0;

	        }
	        //$forwardFrom = 133;
        
        return $data;
    }



     function overAllNewJobs($userIdPass)
    {
       
        $data=array();

        $i=0;

        $userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$plantId = $empresult['plant_id'];



		$queryAll = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = 1 and t.status_id = 1 and t.status_id != 11";


        $configDate = $this->dateFormat();
		$countAll=mysqli_query($this->conn, $queryAll);

		$overNewAll = mysqli_num_rows($countAll);

        $query = "SELECT * FROM ohrm_ticket t WHERE t.location_id = 3 AND t.plant_id = $plantId AND t.status_id = 1 AND t.status_id != 11 AND t.submitted_on < NOW() - INTERVAL 30 DAY";


    
        /*echo $query;
        exit();*/
        $configDate = $this->dateFormat();
		$count=mysqli_query($this->conn, $query);

		$overNewGtr30 = mysqli_num_rows($count);

			//echo $overNew;
			//exit();

		 $query1 = "SELECT * FROM ohrm_ticket t WHERE t.location_id = 3 AND t.plant_id = $plantId AND t.status_id = 1 AND t.status_id != 11 AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 30 DAY ) AND NOW()";

		$count1 = mysqli_query($this->conn, $query1);

		$overNewBtwn30 = mysqli_num_rows($count1);	



		$query2 = "SELECT * FROM ohrm_ticket t WHERE t.location_id = 3 AND t.plant_id = $plantId AND t.status_id = 1 AND t.status_id != 11 AND t.submitted_on AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 15 DAY ) AND NOW()";

		$count2 = mysqli_query($this->conn, $query2);

		$overNewBtwn15 = mysqli_num_rows($count2);	 


		$query3 = "SELECT * FROM ohrm_ticket t WHERE t.location_id = 3 AND t.plant_id = $plantId AND t.status_id = 1 AND t.status_id != 11 AND t.submitted_on AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 7 DAY ) AND NOW()";

		$count3 = mysqli_query($this->conn, $query3);

		$overNewBtwn7 = mysqli_num_rows($count3);


		$query4 = "SELECT * FROM ohrm_ticket t WHERE t.location_id = 3 AND t.plant_id = $plantId AND t.status_id = 1 AND t.status_id != 11 AND t.submitted_on AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 24 HOUR ) AND NOW()";

		$count4 = mysqli_query($this->conn, $query4);

		$overNewBtwn24Hrs = mysqli_num_rows($count4);

						$data['overNewAll'] = $overNewAll;				
						$data['overNewGtr30'] = $overNewGtr30;
						$data['overNewBtwn30'] = $overNewBtwn30;
						$data['overNewBtwn15'] = $overNewBtwn15;
						$data['overNewBtwn7'] = $overNewBtwn7;
						$data['overNewBtwn24Hrs'] = $overNewBtwn24Hrs;

						$data1[] = $data;
					
						$data['overAllNewJobs']=$data1;
						$data['status']=1;
	
        return $data;
    }


     function statusLists()
    {
        $data=array();
  			
		$query="SELECT id FROM ohrm_ticket_status WHERE id IN (2,3,4,6,7,8,9,12,13,14,16)";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
				$row=mysqli_fetch_assoc($count);
					do{ 
						$data1[] = $row['id'];
					}while($row = mysqli_fetch_assoc($count));
						$data['stslist']=$data1;
						$data['status'] = 1;
		}
		return $data;
    }

     function overAllPendingJobs($userIdPass)
    {
       
        $data=array();

        $i=0;

        $userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$plantId = $empresult['plant_id'];


		$stsresult=$this->statusLists();

										for ($i=0; $i < sizeof($stsresult['stslist']) ; $i++) { 
							        	$stsList[] = $stsresult['stslist'][$i];
							        	//to convert Array into string the following implode method is used
							        	$stsLists = implode(',', $stsList);
							        }


		$queryAll = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId and t.status_id IN ($stsLists)";

		/*echo $queryAll;
		exit();*/

		$countAll = mysqli_query($this->conn, $queryAll);

		$overPndngAll = mysqli_num_rows($countAll);


        $query = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId and t.status_id IN ($stsLists) AND t.submitted_on < NOW() - INTERVAL 30 DAY";


    
        //echo $query;
        //exit();
        $configDate = $this->dateFormat();
		$count=mysqli_query($this->conn, $query);

		$overPndgGtr30 = mysqli_num_rows($count);

			//echo $overNew;
			//exit();

		$query1 = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId AND t.status_id IN ($stsLists) AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 30 DAY ) AND NOW()";

		/*echo $query1;
		exit();*/

		$count1 = mysqli_query($this->conn, $query1);

		$overPndngBtwn30 = mysqli_num_rows($count1);	



		$query5 = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId AND t.status_id IN ($stsLists) AND t.submitted_on BETWEEN DATE_SUB(NOW(), INTERVAL 7 DAY) AND NOW()";

		$count5 = mysqli_query($this->conn, $query5);

		$overPndngBtwn7 = mysqli_num_rows($count5);

		$query6 = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId AND t.status_id IN ($stsLists) AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 15 DAY ) AND NOW()";

		//echo $query6;
		//exit();

		$count6 = mysqli_query($this->conn, $query6);

		$overPndngBtwn15 = mysqli_num_rows($count6);

		/*echo $overPndngBtwn15;
			exit();*/

		$query4 = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId AND t.status_id IN ($stsLists) AND t.submitted_on BETWEEN DATE_SUB(NOW(), INTERVAL 24 HOUR) AND NOW()";

		$count4 = mysqli_query($this->conn, $query4);

		$overPndngBtwn24Hrs = mysqli_num_rows($count4);

						$data['overPndngAll'] = $overPndngAll;	
						$data['overPndgGtr30'] = $overPndgGtr30;
						$data['overPndngBtwn30'] = $overPndngBtwn30;
						$data['overPndngBtwn15'] = $overPndngBtwn15;
						$data['overPndngBtwn7'] = $overPndngBtwn7;
						$data['overPndngBtwn24Hrs'] = $overPndngBtwn24Hrs;
						
						$data1[] = $data;
				
						$data['overAllPendingJobs']=$data1;
						$data['status']=1;
		
        return $data;
    }



    function overAllCompletedJobs($userIdPass)
    {
       
        $data=array();

        $i=0;

        $userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$plantId = $empresult['plant_id'];

		$queryAll = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId and t.status_id = 10 or t.status_id = 5 and t.status_id != 10";

        $configDate = $this->dateFormat();
		$countAll = mysqli_query($this->conn,$queryAll);

		$overCompAll = mysqli_num_rows($countAll);


        $query = "SELECT * from ohrm_ticket t where t.location_id = 3 AND t.plant_id = $plantId AND t.submitted_on < NOW() - INTERVAL 30 DAY AND t.status_id = 10 or t.status_id = 5 AND t.status_id != 11";

        $configDate = $this->dateFormat();
		$count=mysqli_query($this->conn, $query);

		$overCompGtr30 = mysqli_num_rows($count);


		 $query1 = "SELECT * from ohrm_ticket t where t.location_id = 3 AND t.plant_id = $plantId AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 30 DAY ) AND NOW() AND t.status_id = 10 or t.status_id = 5 AND t.status_id != 11";

		$count1 = mysqli_query($this->conn, $query1);

		$overCompBtwn30 = mysqli_num_rows($count1);	


		$query2 = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 15 DAY ) AND NOW() AND t.status_id = 10 or t.status_id = 5 and t.status_id != 11";

				$count2 = mysqli_query($this->conn, $query2);

				$overCompBtwn15 = mysqli_num_rows($count2);	 


		
		$query3 = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId AND t.submitted_on
					BETWEEN DATE_SUB( NOW() ,INTERVAL 7 DAY ) AND NOW() AND t.status_id = 10 or t.status_id = 5 and t.status_id != 11";

		$count3 = mysqli_query($this->conn, $query3);

		$overCompBtwn7 = mysqli_num_rows($count3);

		$query4 = "SELECT * FROM ohrm_ticket t WHERE t.location_id = 3 AND t.plant_id = $plantId AND t.submitted_on AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 24 HOUR ) AND NOW()
			AND t.status_id = 10 or t.status_id = 5 and t.status_id != 11";  

		$count4 = mysqli_query($this->conn, $query4);

		$overCompBtwn24Hrs = mysqli_num_rows($count4);


						$data['overCompAll'] = $overCompAll;
						$data['overCompGtr30'] = $overCompGtr30;
						$data['overCompBtwn30'] = $overCompBtwn30;
						$data['overCompBtwn15'] = $overCompBtwn15;
						$data['overCompBtwn7'] = $overCompBtwn7;
						$data['overCompBtwn24Hrs'] = $overCompBtwn24Hrs;

						$data1[] = $data;
					
						$data['overAllCompletedJobs']=$data1;
						$data['status']=1;
		
        return $data;
    }


   function overAllTotalJobs($userIdPass)
    {
       
        $data=array();

        $i=0;

        $userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$plantId = $empresult['plant_id'];

		$queryAll = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId and t.status_id != 11";

        $configDate = $this->dateFormat();
		$countAll = mysqli_query($this->conn, $queryAll);

		$overTotAll = mysqli_num_rows($countAll);

        $query = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId and t.status_id != 11
        			AND t.submitted_on < NOW() - INTERVAL 30 DAY";

        $configDate = $this->dateFormat();
		$count=mysqli_query($this->conn, $query);

		$overTotGtr30 = mysqli_num_rows($count);


		 $query1 = "SELECT * from ohrm_ticket t where t.location_id = 3 AND t.plant_id = $plantId AND t.status_id != 11 
		 			AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 30 DAY ) AND NOW()";

		$count1 = mysqli_query($this->conn, $query1); 

		$overTotBtwn30 = mysqli_num_rows($count1);	

		
		$query2 = "SELECT * from ohrm_ticket t where t.location_id = 3 AND t.plant_id = $plantId AND t.status_id != 11 
		 			AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 15 DAY ) AND NOW()";

				$count2 = mysqli_query($this->conn, $query2);

				$overTotBtwn15 = mysqli_num_rows($count2);


		
		$query3 = "SELECT * from ohrm_ticket t where t.location_id = 3 AND t.plant_id = $plantId AND t.status_id != 11 
		 			AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 7 DAY ) AND NOW()";

		$count3 = mysqli_query($this->conn, $query3);

		$overTotBtwn7 = mysqli_num_rows($count3);
		
		$query4 = "SELECT * FROM ohrm_ticket t WHERE t.location_id = 3 AND t.plant_id = $plantId AND t.status_id = 1 AND t.status_id != 11 AND t.submitted_on BETWEEN DATE_SUB(NOW(), INTERVAL 24 HOUR) AND NOW()";

		$count4 = mysqli_query($this->conn, $query4);

		$overTotBtwn24Hrs = mysqli_num_rows($count4);


						$data['overTotAll'] = $overTotAll;
						$data['overTotGtr30'] = $overTotGtr30;
						$data['overTotBtwn30'] = $overTotBtwn30;
						
						$data['overTotBtwn15'] = $overTotBtwn15;
						
						$data['overTotBtwn7'] = $overTotBtwn7;
						
						$data['overTotBtwn24Hrs'] = $overTotBtwn24Hrs;

						$data1[] = $data;
					
						$data['overAllTotalJobs']=$data1;
						$data['status']=1;
		
        return $data;
    }



    function overAllJobsAll($userIdPass)
    {
       
        $data=array();

        $i=0;

        $userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$plantId = $empresult['plant_id'];

		$queryNewAll = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = 1 and t.status_id = 1 and t.status_id != 11";


        $configDate = $this->dateFormat();
		$countNewAll=mysqli_query($this->conn, $queryNewAll);

		$totNewJobs = mysqli_num_rows($countNewAll);

       									$stsresult=$this->statusLists();

										for ($i=0; $i < sizeof($stsresult['stslist']) ; $i++) { 
							        	$stsList[] = $stsresult['stslist'][$i];
							        	//to convert Array into string the following implode method is used
							        	$stsLists = implode(',', $stsList);
							        }


		$queryPndngAll = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId and t.status_id IN ($stsLists)";

		$countPndngAll = mysqli_query($this->conn, $queryPndngAll);

		$totPndngJobs = mysqli_num_rows($countPndngAll);

		
		 $queryCompltdAll = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId and t.status_id = 10 or t.status_id = 5 and t.status_id != 10";

        $configDate = $this->dateFormat();
		$countCompltdAll = mysqli_query($this->conn,$queryCompltdAll);

		$totCompJobs = mysqli_num_rows($countCompltdAll);	


			
		$queryTotlAll = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId and t.status_id != 11";

        $configDate = $this->dateFormat();
		$countTotlAll = mysqli_query($this->conn, $queryTotlAll);

		$totlJobs = mysqli_num_rows($countTotlAll);


		$totlJobs1 = $totNewJobs+$totPndngJobs+$totCompJobs;
		/*echo  $totlJobs1;
		exit();*/
					$name = array("Total New Jobs","Total Open Jobs","Total Completed Jobs","Total Jobs");
					$count = array($totNewJobs,$totPndngJobs,$totCompJobs,$totlJobs1);

						for ($i=0; $i < 4; $i++) { 
							$data['name'] = $name[$i];
							$data['count'] = $count[$i];
							$data1[] = $data;
						}
						// $data['Total New Jobs'] = $totNewJobs;
						// $data['Total Pending Jobs'] = $totPndngJobs;
						
						// $data['Total Completed Jobs'] = $totCompJobs;
					
						// $data['Total Jobs'] = $totlJobs;
						
						
						
					
						$data['overAllJobsAll']=$data1;
						$data['status']=1;
		
        return $data;
    }

     function overAllJobsGrtr30($userIdPass)
    {
       
        $data=array();

        $i=0;

        $userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$plantId = $empresult['plant_id'];

		$queryNewGrtr30 = "SELECT * FROM ohrm_ticket t WHERE t.location_id = 3 AND t.plant_id = $plantId AND t.status_id = 1 AND t.status_id != 11 AND t.submitted_on < NOW() - INTERVAL 30 DAY";

        $configDate = $this->dateFormat();
		$countNewGrtr30 = mysqli_query($this->conn, $queryNewGrtr30);

		$totNewGrtr30Jobs = mysqli_num_rows($countNewGrtr30);

       									$stsresult=$this->statusLists();

										for ($i=0; $i < sizeof($stsresult['stslist']) ; $i++) { 
							        	$stsList[] = $stsresult['stslist'][$i];
							        	//to convert Array into string the following implode method is used
							        	$stsLists = implode(',', $stsList);
							        }


		$queryPendngGrtr30 = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId and t.status_id IN ($stsLists) AND t.submitted_on < NOW() - INTERVAL 30 DAY";


        $configDate = $this->dateFormat();
		$countPendgGrtr30 = mysqli_query($this->conn, $queryPendngGrtr30);

		$totPndgGtr30Jobs = mysqli_num_rows($countPendgGrtr30);

		
		 $queryCompltdGrtr30 = "SELECT * from ohrm_ticket t where t.location_id = 3 AND t.plant_id = $plantId AND t.submitted_on < NOW() - INTERVAL 30 DAY AND t.status_id = 10 or t.status_id = 5 AND t.status_id != 11";

        $configDate = $this->dateFormat();
		$countCompltdGrtr30 = mysqli_query($this->conn, $queryCompltdGrtr30);

		$totCompGtr30Jobs = mysqli_num_rows($countCompltdGrtr30);	


			
		$queryTotalGrtr30 = "SELECT * from ohrm_ticket t where t.location_id = 3 and
						 t.plant_id = $plantId and t.status_id != 11 AND t.submitted_on < NOW() - INTERVAL 30 DAY";

        $configDate = $this->dateFormat();

		$countTotlGrtr30 = mysqli_query($this->conn, $queryTotalGrtr30);

		$TotGtr30Jobs = mysqli_num_rows($countTotlGrtr30);


		$TotGtr30Jobs1 = $totNewGrtr30Jobs+$totPndgGtr30Jobs+$totCompGtr30Jobs;
		//echo  $TotGtr30Jobs1;
		//exit();
						$name = array("Total New Jobs","Total Open Jobs","Total Completed Jobs","Total Jobs");
					$count = array($totNewGrtr30Jobs,$totPndgGtr30Jobs,$totCompGtr30Jobs,$TotGtr30Jobs1);

						for ($i=0; $i < 4; $i++) { 
							$data['name'] = $name[$i];
							$data['count'] = $count[$i];
							$data1[] = $data;
						}


						/*$data['Total New Jobs'] = $totNewGrtr30Jobs;
						$data['Total Pending Jobs'] = $totPndgGtr30Jobs;
						
						$data['Total Completed Jobs'] = $totCompGtr30Jobs;
					
						$data['Total Jobs'] = $TotGtr30Jobs;*/
						
						
						//$data1[] = $data;
					
						$data['overAllJobsGrtr30']=$data1;
						$data['status']=1;
		
        return $data;
    }


     function overAllJobsWthn30($userIdPass)
    {
       
        $data=array();

        $i=0;

        $userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$plantId = $empresult['plant_id'];

		 $queryNewWthn30 = "SELECT * FROM ohrm_ticket t WHERE t.location_id = 3 AND t.plant_id = $plantId AND t.status_id = 1 AND t.status_id != 11 AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 30 DAY ) AND NOW()";

		$countNewWthn30 = mysqli_query($this->conn, $queryNewWthn30);

		$totNewWthn30Jobs = mysqli_num_rows($countNewWthn30);


       									$stsresult=$this->statusLists();

										for ($i=0; $i < sizeof($stsresult['stslist']) ; $i++) { 
							        	$stsList[] = $stsresult['stslist'][$i];
							        	//to convert Array into string the following implode method is used
							        	$stsLists = implode(',', $stsList);
							        }


		$queryPendngWthn30 = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId
							 AND t.status_id IN ($stsLists) AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 30 DAY ) AND NOW()";


		$countPendngWthn30 = mysqli_query($this->conn, $queryPendngWthn30);

		$totPndngWthn30Jobs = mysqli_num_rows($countPendngWthn30);	


		
		  $queryCompltdWthn30 = "SELECT * from ohrm_ticket t where t.location_id = 3 AND t.plant_id = $plantId
		  						 AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 30 DAY ) AND NOW() 
		  						 AND t.status_id = 10 or t.status_id = 5 AND t.status_id != 11";

		$countCompltdWthn30 = mysqli_query($this->conn, $queryCompltdWthn30);

		$totCompWthn30Jobs = mysqli_num_rows($countCompltdWthn30);	


		 $queryTotlWthn30 = "SELECT * from ohrm_ticket t where t.location_id = 3 AND
		 					 t.plant_id = $plantId AND t.status_id != 11
		 					AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 30 DAY ) AND NOW()";

		$countTotlWthn30 = mysqli_query($this->conn, $queryTotlWthn30); 

		$totWthn30Jobs = mysqli_num_rows($countTotlWthn30);	

		$totWthn30Jobs1 = $totNewWthn30Jobs+$totPndngWthn30Jobs+$totCompWthn30Jobs;

						$name = array("Total New Jobs","Total Open Jobs","Total Completed Jobs","Total Jobs");
					$count = array($totNewWthn30Jobs,$totPndngWthn30Jobs,$totCompWthn30Jobs,$totWthn30Jobs1);

						for ($i=0; $i < 4; $i++) { 
							$data['name'] = $name[$i];
							$data['count'] = $count[$i];
							$data1[] = $data;
						}


						/*$data['Total New Jobs'] = $totNewWthn30Jobs;
						$data['Total Pending Jobs'] = $totPndngWthn30Jobs;
						
						$data['Total Completed Jobs'] = $totCompWthn30Jobs;
					
						$data['Total Jobs'] = $totWthn30Jobs;
						*/
						
						//$data1[] = $data;
					
						$data['overAllJobsWthn30']=$data1;
						$data['status']=1;
		
        return $data;
    }

     function overAllJobsWthn15($userIdPass)
    {
       
        $data=array();

        $i=0;

        $userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$plantId = $empresult['plant_id'];

		 $queryNewWthn15 = "SELECT * FROM ohrm_ticket t WHERE t.location_id = 3 AND t.plant_id = $plantId AND t.status_id = 1 AND t.status_id != 11 AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 15 DAY ) AND NOW()";

		$countNewWthn15 = mysqli_query($this->conn, $queryNewWthn15);

		$totNewWthn15Jobs = mysqli_num_rows($countNewWthn15);


       									$stsresult=$this->statusLists();

										for ($i=0; $i < sizeof($stsresult['stslist']) ; $i++) { 
							        	$stsList[] = $stsresult['stslist'][$i];
							        	//to convert Array into string the following implode method is used
							        	$stsLists = implode(',', $stsList);
							        }


		$queryPendngWthn15 = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId
							 AND t.status_id IN ($stsLists) AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 15 DAY ) AND NOW()";


		$countPendngWthn15 = mysqli_query($this->conn, $queryPendngWthn15);

		$totPndngWthn15Jobs = mysqli_num_rows($countPendngWthn15);	


		
		  $queryCompltdWthn15 = "SELECT * from ohrm_ticket t where t.location_id = 3 AND t.plant_id = $plantId
		  						 AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 15 DAY ) AND NOW() 
		  						 AND t.status_id = 10 or t.status_id = 5 AND t.status_id != 11";

		$countCompltdWthn15 = mysqli_query($this->conn, $queryCompltdWthn15);

		$totCompWthn15Jobs = mysqli_num_rows($countCompltdWthn15);	


		 $queryTotlWthn15 = "SELECT * from ohrm_ticket t where t.location_id = 3 AND
		 					 t.plant_id = $plantId AND t.status_id != 11
		 					AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 15 DAY ) AND NOW()";

		$countTotlWthn15 = mysqli_query($this->conn, $queryTotlWthn15); 

		$totWthn15Jobs = mysqli_num_rows($countTotlWthn15);	

		$totWthn15Jobs1 = $totNewWthn15Jobs+$totPndngWthn15Jobs+$totCompWthn15Jobs;

						$name = array("Total New Jobs","Total Open Jobs","Total Completed Jobs","Total Jobs");
					$count = array($totNewWthn15Jobs,$totPndngWthn15Jobs,$totCompWthn15Jobs,$totWthn15Jobs1);

						for ($i=0; $i < 4; $i++) { 
							$data['name'] = $name[$i];
							$data['count'] = $count[$i];
							$data1[] = $data;
						}

						/*$data['Total New Jobs'] = $totNewWthn15Jobs;
						$data['Total Pending Jobs'] = $totPndngWthn15Jobs;
						
						$data['Total Completed Jobs'] = $totCompWthn15Jobs;
					
						$data['Total Jobs'] = $totWthn15Jobs;
						
						
						$data1[] = $data;*/
					
						$data['overAllJobsWthn15']=$data1;
						$data['status']=1;
		
        return $data;
    }


     function overAllJobsWthn7($userIdPass)
    {
       
        $data=array();

        $i=0;

        $userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$plantId = $empresult['plant_id'];

		 $queryNewWthn7 = "SELECT * FROM ohrm_ticket t WHERE t.location_id = 3 AND t.plant_id = $plantId AND t.status_id = 1 AND t.status_id != 11 AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 7 DAY ) AND NOW()";

		$countNewWthn7 = mysqli_query($this->conn, $queryNewWthn7);

		$totNewWthn7Jobs = mysqli_num_rows($countNewWthn7);


       									$stsresult=$this->statusLists();

										for ($i=0; $i < sizeof($stsresult['stslist']) ; $i++) { 
							        	$stsList[] = $stsresult['stslist'][$i];
							        	//to convert Array into string the following implode method is used
							        	$stsLists = implode(',', $stsList);
							        }


		$queryPendngWthn7 = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId
							 AND t.status_id IN ($stsLists) AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 7 DAY ) AND NOW()";


		$countPendngWthn7 = mysqli_query($this->conn, $queryPendngWthn7);

		$totPndngWthn7Jobs = mysqli_num_rows($countPendngWthn7);	


		
		  $queryCompltdWthn7 = "SELECT * from ohrm_ticket t where t.location_id = 3 AND t.plant_id = $plantId
		  						 AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 7 DAY ) AND NOW() 
		  						 AND t.status_id = 10 or t.status_id = 5 AND t.status_id != 11";

		$countCompltdWthn7 = mysqli_query($this->conn, $queryCompltdWthn7);

		$totCompWthn7Jobs = mysqli_num_rows($countCompltdWthn7);	


		 $queryTotlWthn7 = "SELECT * from ohrm_ticket t where t.location_id = 3 AND
		 					 t.plant_id = $plantId AND t.status_id != 11
		 					AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 7 DAY ) AND NOW()";

		$countTotlWthn7 = mysqli_query($this->conn, $queryTotlWthn7); 

		$totWthn7Jobs = mysqli_num_rows($countTotlWthn7);	

		$totWthn7Jobs1 = $totNewWthn7Jobs+$totPndngWthn7Jobs+$totCompWthn7Jobs;
						$name = array("Total New Jobs","Total Open Jobs","Total Completed Jobs","Total Jobs");
					$count = array($totNewWthn7Jobs,$totPndngWthn7Jobs,$totCompWthn7Jobs,$totWthn7Jobs1);

						for ($i=0; $i < 4; $i++) { 
							$data['name'] = $name[$i];
							$data['count'] = $count[$i];
							$data1[] = $data;
						}

						/*$data['Total New Jobs'] = $totNewWthn7Jobs;
						$data['Total Pending Jobs'] = $totPndngWthn7Jobs;
						
						$data['Total Completed Jobs'] = $totCompWthn7Jobs;
					
						$data['Total Jobs'] = $totWthn7Jobs;
						
						
						$data1[] = $data;*/
					
						$data['overAllJobsWthn7']=$data1;
						$data['status']=1;
		
        return $data;
    }


     function overAllJobsWthn24Hrs($userIdPass)
    {
       
        $data=array();

        $i=0;

        $userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$plantId = $empresult['plant_id'];

		 $queryNewWthn24 = "SELECT * FROM ohrm_ticket t WHERE t.location_id = 3 AND t.plant_id = $plantId AND t.status_id = 1 AND t.status_id != 11 AND t.submitted_on BETWEEN DATE_SUB(NOW(), INTERVAL 24 HOUR) AND NOW()";

		$countNewWthn24 = mysqli_query($this->conn, $queryNewWthn24);

		$totNewWthn24Jobs = mysqli_num_rows($countNewWthn24);


       									$stsresult=$this->statusLists();

										for ($i=0; $i < sizeof($stsresult['stslist']) ; $i++) { 
							        	$stsList[] = $stsresult['stslist'][$i];
							        	//to convert Array into string the following implode method is used
							        	$stsLists = implode(',', $stsList);
							        }


		$queryPendngWthn24 = "SELECT * from ohrm_ticket t where t.location_id = 3 and t.plant_id = $plantId
							 AND t.status_id IN ($stsLists) AND t.submitted_on BETWEEN DATE_SUB(NOW(), INTERVAL 24 HOUR) AND NOW()";


		$countPendngWthn24 = mysqli_query($this->conn, $queryPendngWthn24);

		$totPndngWthn24Jobs = mysqli_num_rows($countPendngWthn24);	


		
		  $queryCompltdWthn24 = "SELECT * from ohrm_ticket t where t.location_id = 3 AND t.plant_id = $plantId
		  						 AND t.submitted_on BETWEEN DATE_SUB(NOW(), INTERVAL 24 HOUR) AND NOW() 
		  						 AND t.status_id = 10 or t.status_id = 5 AND t.status_id != 11";

		$countCompltdWthn24 = mysqli_query($this->conn, $queryCompltdWthn24);

		$totCompWthn24Jobs = mysqli_num_rows($countCompltdWthn24);	


		 $queryTotlWthn24 = "SELECT * from ohrm_ticket t where t.location_id = 3 AND
		 					 t.plant_id = $plantId AND t.status_id != 11
		 					AND t.submitted_on BETWEEN DATE_SUB(NOW(), INTERVAL 24 HOUR) AND NOW()";

		$countTotlWthn24 = mysqli_query($this->conn, $queryTotlWthn24); 

		$totWthn24Jobs = mysqli_num_rows($countTotlWthn24);	


		$totWthn24Jobs1 = $totNewWthn24Jobs+$totPndngWthn24Jobs+$totCompWthn24Jobs;

						$name = array("Total New Jobs","Total Open Jobs","Total Completed Jobs","Total Jobs");
					$count = array($totNewWthn24Jobs,$totPndngWthn24Jobs,$totCompWthn24Jobs,$totWthn24Jobs1);

						for ($i=0; $i < 4; $i++) { 
							$data['name'] = $name[$i];
							$data['count'] = $count[$i];
							$data1[] = $data;
						}
						/*$data['Total New Jobs'] = $totNewWthn24Jobs;
						$data['Total Pending Jobs'] = $totPndngWthn24Jobs;
						
						$data['Total Completed Jobs'] = $totCompWthn24Jobs;
					
						$data['Total Jobs'] = $totWthn24Jobs;
						
						
						$data1[] = $data;*/
					
						$data['overAllJobsWthn24Hrs']=$data1;
						$data['status']=1;
		
        return $data;
    }


function engnrJobsSmryAll($userIdPass)
    {
       
        $data=array();

        $i=0;

        $userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$plantId = $empresult['plant_id'];

		 $empresult=$this->empList(11);
		 /*echo "<pre>";
		 print_r($empresult);
		 exit();*/
	        for ($i=0; $i < sizeof($empresult['emplist']) ; $i++) { 
	        	$empList = $empresult['emplist'][$i];
	        	$empresult1=$this->employeeDetails($empList);
	        	$departmentId = $empresult1['work_station'];
	        	/*echo $departmentId;
	        	exit();*/
	        	 //echo "<pre>";
		 //print_r($empList);
		 //exit();
	        	//to convert Array into string the following implode method is used
	        	// $empLists = implode(',', $empList);

	        	$queryEngJobs = "SELECT CONCAT(e.emp_firstname,' ',e.emp_lastname) as name,
(SELECT COUNT(*) as open FROM ohrm_ticket t 
        LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id 
        LEFT JOIN hs_hr_employee e ON e.emp_number = tal.accepted_by 
        WHERE tal.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND tal.accepted_by = $empList AND t.status_id = 3 ) as open,
(SELECT COUNT(*) as new FROM ohrm_ticket t
WHERE t.user_department_id = $departmentId AND t.status_id = 1 ) as new,

(SELECT COUNT(*) as open FROM ohrm_ticket t 
        LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id 
        LEFT JOIN hs_hr_employee e ON e.emp_number = tal.accepted_by 
        WHERE tal.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND tal.accepted_by = $empList AND t.status_id = 10 ) AS close 
        FROM ohrm_ticket t 
        LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id 
        LEFT JOIN hs_hr_employee e ON e.emp_number = tal.accepted_by 
        WHERE tal.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND tal.accepted_by = $empList GROUP BY tal.accepted_by";

        //echo $queryEngJobs;
        //exit();

        $countEngJobs = mysqli_query($this->conn, $queryEngJobs);
        //$engJobs = mysqli_num_rows($countEngJobs);
        $row=mysqli_fetch_assoc($countEngJobs);

					if($row['name']){
						$data['name']= $row['name'];
						$data['new']= $row['new'];
						$data['open']= $row['open'];
						$data['close']= $row['close'];
						$data['total'] = $row['open'] + $row['new'] + $row['close'];
						$data1[] = $data;
					}
						
	        }
	        $data['engnrJobsSmryAll']=$data1;
						$data['status']=1;




        return $data;
    }


function engnrJobsSmryGrtr30($userIdPass)
    {
       
        $data=array();

        $i=0;

        $userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$plantId = $empresult['plant_id'];

		 $empresult=$this->empList(11);
		 /*echo "<pre>";
		 print_r($empresult);
		 exit();*/
	        for ($i=0; $i < sizeof($empresult['emplist']) ; $i++) { 
	        	$empList = $empresult['emplist'][$i];

	        	$empresult1=$this->employeeDetails($empList);
	        	$departmentId = $empresult1['work_station'];
				
	        	//to convert Array into string the following implode method is used
	        	// $empLists = implode(',', $empList);

	        	$queryEngJobsGrtr30 = "SELECT CONCAT(e.emp_firstname,' ',e.emp_lastname) as name,
(SELECT COUNT(*) FROM ohrm_ticket t 
        LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id 
        LEFT JOIN hs_hr_employee e ON e.emp_number = tal.accepted_by 
        WHERE tal.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND tal.accepted_by = $empList AND t.status_id = 3 AND tal.submitted_on < NOW() - INTERVAL 30 DAY) as open,

        (SELECT COUNT(*) as new FROM ohrm_ticket t
WHERE t.user_department_id = $departmentId AND t.status_id = 1 ) as new,

(SELECT COUNT(*) FROM ohrm_ticket t 
        LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id 
        LEFT JOIN hs_hr_employee e ON e.emp_number = tal.accepted_by 
        WHERE tal.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND tal.accepted_by = $empList AND t.status_id = 10
AND tal.submitted_on < NOW() - INTERVAL 30 DAY ) as close 
        FROM ohrm_ticket t 
        LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id 
        LEFT JOIN hs_hr_employee e ON e.emp_number = tal.accepted_by 
        WHERE tal.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND tal.accepted_by = $empList GROUP BY tal.accepted_by";

       /*echo $queryEngJobsGrtr30;
        exit();
*/
        $countEngJobsGrtr30 = mysqli_query($this->conn, $queryEngJobsGrtr30);
        //$engJobs = mysqli_num_rows($countEngJobs);
        $row=mysqli_fetch_assoc($countEngJobsGrtr30);

					if($row['name']){
						$data['name']= $row['name'];
						$data['new']= $row['new'];
						$data['open']= $row['open'];
						$data['close']= $row['close'];
						$data['total'] = $row['open'] + $row['new'] + $row['close'];
						$data1[] = $data;
					}
						
	        }
	        $data['engnrJobsSmryGrtr30']=$data1;
						$data['status']=1;




        return $data;
    }


function engnrJobsSmryBtwn30($userIdPass)
    {
       
        $data=array();

        $i=0;

        $userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$plantId = $empresult['plant_id'];

		 $empresult=$this->empList(11);
		 /*echo "<pre>";
		 print_r($empresult);
		 exit();*/
	        for ($i=0; $i < sizeof($empresult['emplist']) ; $i++) { 
	        	$empList = $empresult['emplist'][$i];
	        	$empresult1=$this->employeeDetails($empList);
	        	$departmentId = $empresult1['work_station'];
	        	//to convert Array into string the following implode method is used
	        	// $empLists = implode(',', $empList);

	        	$queryEngJobsBtwn30 = "SELECT CONCAT(e.emp_firstname,' ',e.emp_lastname) as name,
										(SELECT COUNT(*) as open FROM ohrm_ticket t 
					       				 LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id 
					        			LEFT JOIN hs_hr_employee e ON e.emp_number = tal.accepted_by 
					        			WHERE tal.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND tal.accepted_by = $empList AND t.status_id = 3 AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 30 DAY ) AND NOW()) as open,
					        			(SELECT COUNT(*) as new FROM ohrm_ticket t
										WHERE t.user_department_id = $departmentId AND t.status_id = 1 ) as new,
										(SELECT COUNT(*) as open FROM ohrm_ticket t 
					        			LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id 
					        			LEFT JOIN hs_hr_employee e ON e.emp_number = tal.accepted_by 
					        			WHERE tal.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND tal.accepted_by = $empList AND t.status_id = 10
										AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 30 DAY ) AND NOW()) as close 
			        					FROM ohrm_ticket t 
			        					LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id 
			        					LEFT JOIN hs_hr_employee e ON e.emp_number = tal.accepted_by 
			        					WHERE tal.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND tal.accepted_by = $empList GROUP BY tal.accepted_by";

       /* echo $queryEngJobs;
        exit();*/

        $countEngJobsBtwn30 = mysqli_query($this->conn, $queryEngJobsBtwn30);
        //$engJobs = mysqli_num_rows($countEngJobs);
        $row=mysqli_fetch_assoc($countEngJobsBtwn30);

					if($row['name']){
						$data['name']= $row['name'];
						$data['new']= $row['new'];
						$data['open']= $row['open'];
						$data['close']= $row['close'];
						$data['total'] = $row['open'] + $row['new'] + $row['close'];
						$data1[] = $data;
					}
						
	        }
	        $data['engnrJobsSmryBtwn30']=$data1;
						$data['status']=1;




        return $data;
    }

    function engnrJobsSmryBtwn15($userIdPass)
    {
       
        $data=array();

        $i=0;

        $userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$plantId = $empresult['plant_id'];

		 $empresult=$this->empList(11);
		 /*echo "<pre>";
		 print_r($empresult);
		 exit();*/
	        for ($i=0; $i < sizeof($empresult['emplist']) ; $i++) { 
	        	$empList = $empresult['emplist'][$i];
	        	$empresult1=$this->employeeDetails($empList);
	        	$departmentId = $empresult1['work_station'];
	        	//to convert Array into string the following implode method is used
	        	// $empLists = implode(',', $empList);

	        	$queryEngJobsBtwn15 = "SELECT CONCAT(e.emp_firstname,' ',e.emp_lastname) as name,
										(SELECT COUNT(*) as open FROM ohrm_ticket t 
					       				 LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id 
					        			LEFT JOIN hs_hr_employee e ON e.emp_number = tal.accepted_by 
					        			WHERE tal.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND tal.accepted_by = $empList AND t.status_id = 3 AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 15 DAY ) AND NOW()) as open,
					        			(SELECT COUNT(*) as new FROM ohrm_ticket t
										WHERE t.user_department_id = $departmentId AND t.status_id = 1 ) as new,
										(SELECT COUNT(*) as open FROM ohrm_ticket t 
					        			LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id 
					        			LEFT JOIN hs_hr_employee e ON e.emp_number = tal.accepted_by 
					        			WHERE tal.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND tal.accepted_by = $empList AND t.status_id = 10
										AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 15 DAY ) AND NOW()) as close 
			        					FROM ohrm_ticket t 
			        					LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id 
			        					LEFT JOIN hs_hr_employee e ON e.emp_number = tal.accepted_by 
			        					WHERE tal.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND tal.accepted_by = $empList GROUP BY tal.accepted_by";

       /* echo $queryEngJobs;
        exit();*/

        $countEngJobsBtwn15 = mysqli_query($this->conn, $queryEngJobsBtwn15);
        //$engJobs = mysqli_num_rows($countEngJobs);
        $row=mysqli_fetch_assoc($countEngJobsBtwn15);

					if($row['name']){
						$data['name']= $row['name'];
						$data['new']= $row['new'];
						$data['open']= $row['open'];
						$data['close']= $row['close'];
						$data['total'] = $row['open'] + $row['new'] + $row['close'];
						$data1[] = $data;
					}
						
	        }
	        $data['engnrJobsSmryBtwn15']=$data1;
						$data['status']=1;




        return $data;
    }


     function engnrJobsSmryBtwn7($userIdPass)
    {
       
        $data=array();

        $i=0;

        $userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$plantId = $empresult['plant_id'];

		 $empresult=$this->empList(11);
		 /*echo "<pre>";
		 print_r($empresult);
		 exit();*/
	        for ($i=0; $i < sizeof($empresult['emplist']) ; $i++) { 
	        	$empList = $empresult['emplist'][$i];
	        	$empresult1=$this->employeeDetails($empList);
	        	$departmentId = $empresult1['work_station'];
	        	//to convert Array into string the following implode method is used
	        	// $empLists = implode(',', $empList);

	        	$queryEngJobsBtwn7 = "SELECT CONCAT(e.emp_firstname,' ',e.emp_lastname) as name,
										(SELECT COUNT(*) as open FROM ohrm_ticket t 
					       				 LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id 
					        			LEFT JOIN hs_hr_employee e ON e.emp_number = tal.accepted_by 
					        			WHERE tal.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND tal.accepted_by = $empList AND t.status_id = 3 AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 7 DAY ) AND NOW()) as open,
					        			(SELECT COUNT(*) as new FROM ohrm_ticket t
										WHERE t.user_department_id = $departmentId AND t.status_id = 1 ) as new,
										(SELECT COUNT(*) as open FROM ohrm_ticket t 
					        			LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id 
					        			LEFT JOIN hs_hr_employee e ON e.emp_number = tal.accepted_by 
					        			WHERE tal.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND tal.accepted_by = $empList AND t.status_id = 10
										AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 7 DAY ) AND NOW()) as close 
			        					FROM ohrm_ticket t 
			        					LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id 
			        					LEFT JOIN hs_hr_employee e ON e.emp_number = tal.accepted_by 
			        					WHERE tal.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND tal.accepted_by = $empList GROUP BY tal.accepted_by";

       /* echo $queryEngJobs;
        exit();*/

        $countEngJobsBtwn7 = mysqli_query($this->conn, $queryEngJobsBtwn7);
        //$engJobs = mysqli_num_rows($countEngJobs);
        $row=mysqli_fetch_assoc($countEngJobsBtwn7);

					if($row['name']){
						$data['name']= $row['name'];
						$data['new']= $row['new'];
						$data['open']= $row['open'];
						$data['close']= $row['close'];
						$data['total'] = $row['open'] + $row['new'] + $row['close'];
						$data1[] = $data;
					}
						
	        }
	        $data['engnrJobsSmryBtwn7']=$data1;
						$data['status']=1;




        return $data;
    }

    function engnrJobsSmryBtwn24Hrs($userIdPass)
    {
       
        $data=array();

        $i=0;

        $userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$plantId = $empresult['plant_id'];

		 $empresult=$this->empList(11);
		 /*echo "<pre>";
		 print_r($empresult);
		 exit();*/
	        for ($i=0; $i < sizeof($empresult['emplist']) ; $i++) { 
	        	$empList = $empresult['emplist'][$i];
	        	$empresult1=$this->employeeDetails($empList);
	        	$departmentId = $empresult1['work_station'];
	        	//to convert Array into string the following implode method is used
	        	// $empLists = implode(',', $empList);

	        	$queryEngJobsBtwn24Hrs = "SELECT CONCAT(e.emp_firstname,' ',e.emp_lastname) as name,
										(SELECT COUNT(*) as open FROM ohrm_ticket t 
					       				 LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id 
					        			LEFT JOIN hs_hr_employee e ON e.emp_number = tal.accepted_by 
					        			WHERE tal.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND tal.accepted_by = $empList AND t.status_id = 3 AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 24 HOUR ) AND NOW()) as open,
					        			(SELECT COUNT(*) as new FROM ohrm_ticket t
										WHERE t.user_department_id = $departmentId AND t.status_id = 1 ) as new,
										(SELECT COUNT(*) as open FROM ohrm_ticket t 
					        			LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id 
					        			LEFT JOIN hs_hr_employee e ON e.emp_number = tal.accepted_by 
					        			WHERE tal.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND tal.accepted_by = $empList AND t.status_id = 10
										AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 24 HOUR ) AND NOW()) as close 
			        					FROM ohrm_ticket t 
			        					LEFT JOIN ohrm_ticket_acknowledgement_action_log tal ON tal.ticket_id = t.id 
			        					LEFT JOIN hs_hr_employee e ON e.emp_number = tal.accepted_by 
			        					WHERE tal.id IN (SELECT MAX(id) FROM ohrm_ticket_acknowledgement_action_log GROUP BY ticket_id) AND tal.accepted_by = $empList GROUP BY tal.accepted_by";

       /* echo $queryEngJobs;
        exit();*/

        $countEngJobsBtwn24Hrs = mysqli_query($this->conn, $queryEngJobsBtwn24Hrs);
        //$engJobs = mysqli_num_rows($countEngJobs);
        $row=mysqli_fetch_assoc($countEngJobsBtwn24Hrs);

					if($row['name']){
						$data['name']= $row['name'];
						$data['new']= $row['new'];
						$data['open']= $row['open'];
						$data['close']= $row['close'];
						$data['total'] = $row['open'] + $row['new'] + $row['close'];
						$data1[] = $data;
					}
						
	        }
	        $data['engnrJobsSmryBtwn24Hrs']=$data1;
						$data['status']=1;




        return $data;
    }


    function agingreport($userIdPass)
    {


    		$data=array();

        $i=0;

        $userDetails = $this->getUserRoleByUserId($userIdPass);
		$empNumber = $userDetails['empNumber'];
		
		$empresult=$this->employeeDetails($empNumber);
		$department = $empresult['work_station'];
		$plantId = $empresult['plant_id'];

		$a = array(0,1,3,7,15,30);
		$b = array(1,3,7,15,30,60);
		for ($i=0; $i < 6; $i++) { 

			if($i==0)
			{

				$queryAgng0_1OpenJobs = "SELECT count(*) as open1 from ohrm_ticket t where t.location_id = 3 and t.plant_id = 1 AND t.submitted_on BETWEEN DATE(NOW() - INTERVAL ".$b[$i]." DAY) AND DATE(NOW() - INTERVAL ".$a[$i]." DAY) and t.status_id IN (2,3,4,6,7,8,9,12,13,16)";


		$countAgng0_1OpenJobs = mysqli_query($this->conn, $queryAgng0_1OpenJobs);

		$totaging0_1Jobs = mysqli_num_rows($countAgng0_1OpenJobs);	

		$queryAgng0_1NewJobs = "SELECT count(*) as new1
								  from ohrm_ticket t where t.location_id = 3 and t.plant_id = 1 and
								  t.status_id=1 AND t.submitted_on BETWEEN DATE_SUB( NOW() ,INTERVAL 24 HOUR ) AND NOW()";

		$countAgng0_1NewJobs = mysqli_query($this->conn, $queryAgng0_1NewJobs);

		$totAgngNew24Jobs = mysqli_num_rows($countAgng0_1NewJobs);

		$queryAgng0_1ReslvJobs = "SELECT count(*) as resolv1
								  from ohrm_ticket t where t.location_id = 3 and t.plant_id = 1 and
								  t.status_id=14 AND t.submitted_on BETWEEN DATE(NOW() - INTERVAL ".$b[$i]." DAY) AND DATE(NOW() - INTERVAL ".$a[$i]." DAY)";

		$countAgng0_1ReslvJobs = mysqli_query($this->conn, $queryAgng0_1ReslvJobs);

		$totAgngReslv24Jobs = mysqli_num_rows($countAgng0_1ReslvJobs);	

		$queryAgng0_1CompltdJobs = "SELECT count(*) as complete1
									from ohrm_ticket t where t.location_id = 3 and t.plant_id = 1 AND t.submitted_on BETWEEN DATE(NOW() - INTERVAL ".$b[$i]." DAY) AND DATE(NOW() - INTERVAL ".$a[$i]." DAY) AND t.status_id IN (5,10)";

		$countAgng0_1CompltdJobs = mysqli_query($this->conn, $queryAgng0_1CompltdJobs);

		$totAgngCompltd24Jobs = mysqli_num_rows($countAgng0_1CompltdJobs);	


						$row = mysqli_fetch_assoc($countAgng0_1OpenJobs);
						$row1 = mysqli_fetch_assoc($countAgng0_1ReslvJobs);
						$row2 = mysqli_fetch_assoc($countAgng0_1CompltdJobs);
						$row3 = mysqli_fetch_assoc($countAgng0_1NewJobs);
						if($i==0){
							$data['status']= $a[$i]."-".$b[$i]." day";
						}else{
							$data['status']= $a[$i]."-".$b[$i]." days";
						}

						$data['new']= $row3['new1'];
						$data['open']= $row['open1'];
						$data['resolved']= $row1['resolv1'];
						//$data['new']= $row3['new1'];
						$data['completed']= $row2['complete1'];
						$data1[] = $data;

			}
			else
			{

				$queryAgng0_1OpenJobs = "SELECT count(*) as open1 from ohrm_ticket t where t.location_id = 3 and t.plant_id = 1 AND t.submitted_on BETWEEN DATE(NOW() - INTERVAL ".$b[$i]." DAY) AND DATE(NOW() - INTERVAL ".$a[$i]." DAY) and t.status_id IN (2,3,4,6,7,8,9,12,13,16)";


		$countAgng0_1OpenJobs = mysqli_query($this->conn, $queryAgng0_1OpenJobs);

		$totaging0_1Jobs = mysqli_num_rows($countAgng0_1OpenJobs);	

		$queryAgng0_1NewJobs = "SELECT count(*) as new1
								  from ohrm_ticket t where t.location_id = 3 and t.plant_id = 1 and
								  t.status_id=1 AND t.submitted_on BETWEEN DATE(NOW() - INTERVAL ".$b[$i]." DAY) AND DATE(NOW() - INTERVAL ".$a[$i]." DAY)";

		$countAgng0_1NewJobs = mysqli_query($this->conn, $queryAgng0_1NewJobs);

		$totAgngNew24Jobs = mysqli_num_rows($countAgng0_1NewJobs);

		$queryAgng0_1ReslvJobs = "SELECT count(*) as resolv1
								  from ohrm_ticket t where t.location_id = 3 and t.plant_id = 1 and
								  t.status_id=14 AND t.submitted_on BETWEEN DATE(NOW() - INTERVAL ".$b[$i]." DAY) AND DATE(NOW() - INTERVAL ".$a[$i]." DAY)";

		$countAgng0_1ReslvJobs = mysqli_query($this->conn, $queryAgng0_1ReslvJobs);

		$totAgngReslv24Jobs = mysqli_num_rows($countAgng0_1ReslvJobs);	

		$queryAgng0_1CompltdJobs = "SELECT count(*) as complete1
									from ohrm_ticket t where t.location_id = 3 and t.plant_id = 1 AND t.submitted_on BETWEEN DATE(NOW() - INTERVAL ".$b[$i]." DAY) AND DATE(NOW() - INTERVAL ".$a[$i]." DAY) AND t.status_id IN (5,10)";

		$countAgng0_1CompltdJobs = mysqli_query($this->conn, $queryAgng0_1CompltdJobs);

		$totAgngCompltd24Jobs = mysqli_num_rows($countAgng0_1CompltdJobs);	


						$row = mysqli_fetch_assoc($countAgng0_1OpenJobs);
						$row1 = mysqli_fetch_assoc($countAgng0_1ReslvJobs);
						$row2 = mysqli_fetch_assoc($countAgng0_1CompltdJobs);
						$row3 = mysqli_fetch_assoc($countAgng0_1NewJobs);
						if($i==0){
							$data['status']= $a[$i]."-".$b[$i]." day";
						}else{
							$data['status']= $a[$i]."-".$b[$i]." days";
						}

						$data['new']= $row3['new1'];
						$data['open']= $row['open1'];
						$data['resolved']= $row1['resolv1'];
						//$data['new']= $row3['new1'];
						$data['completed']= $row2['complete1'];
						$data1[] = $data;

			}
			

		
		}

		$queryAgng0_1OpenJobs = "SELECT count(*) as open1 from ohrm_ticket t where t.location_id = 3 and t.plant_id = 1 AND t.submitted_on < NOW() - INTERVAL 60 DAY and t.status_id IN (2,3,4,6,7,8,9,12,13,16)";


		$countAgng0_1OpenJobs = mysqli_query($this->conn, $queryAgng0_1OpenJobs);

		$totaging0_1Jobs = mysqli_num_rows($countAgng0_1OpenJobs);

		$queryAgng0_1NewJobs = "SELECT count(*) as new1
								  from ohrm_ticket t where t.location_id = 3 and t.plant_id = 1 and
								  t.status_id=1 AND t.submitted_on < NOW() - INTERVAL 60 DAY";

		$countAgng0_1NewJobs = mysqli_query($this->conn, $queryAgng0_1NewJobs);

		$totAgngNew24Jobs = mysqli_num_rows($countAgng0_1NewJobs);	

		$queryAgng0_1ReslvJobs = "SELECT count(*) as resolv1
								  from ohrm_ticket t where t.location_id = 3 and t.plant_id = 1 and
								  t.status_id=14 AND t.submitted_on < NOW() - INTERVAL 60 DAY";

		$countAgng0_1ReslvJobs = mysqli_query($this->conn, $queryAgng0_1ReslvJobs);

		$totAgngReslv24Jobs = mysqli_num_rows($countAgng0_1ReslvJobs);	

		$queryAgng0_1CompltdJobs = "SELECT count(*) as complete1
									from ohrm_ticket t where t.location_id = 3 and t.plant_id = 1 AND t.submitted_on < NOW() - INTERVAL 60 DAY AND t.status_id IN (5,10)";

		$countAgng0_1CompltdJobs = mysqli_query($this->conn, $queryAgng0_1CompltdJobs);

		$totAgngCompltd24Jobs = mysqli_num_rows($countAgng0_1CompltdJobs);	


		$row = mysqli_fetch_assoc($countAgng0_1OpenJobs);
		$row1 = mysqli_fetch_assoc($countAgng0_1ReslvJobs);
		$row2 = mysqli_fetch_assoc($countAgng0_1CompltdJobs);
		$row3 = mysqli_fetch_assoc($countAgng0_1NewJobs);
		$data['status']= ">60 days";
		$data['new']= $row3['new1'];
		$data['open']= $row['open1'];
		$data['resolved']= $row1['resolv1'];
		
		$data['completed']= $row2['complete1'];
		$data1[] = $data;
						
						$data['agingreport']=$data1;
						$data['status']=1;

    	return $data;
    }

    function prevMainReport($userIdPass)
    {
    	$data= array();
    	for ($i=1; $i <= 12 ; $i++) {
    		$month = date("F", mktime(0, 0, 0, $i, 10));

    		// Planned hours
			$query="SELECT TIME_FORMAT(SEC_TO_TIME(SUM(TIME_TO_SEC(timediff(end_time,start_time)))), '%H:%i') as plannedHrs FROM ohrm_maintenance_schedule where MONTH(date) = ".$i." AND YEAR(date) = YEAR(now())";
			$count=mysqli_query($this->conn, $query);			
			$row=mysqli_fetch_assoc($count);

			//Actual hours
			$query1 = "SELECT DISTINCT(TIME_FORMAT(SEC_TO_TIME(b.total), '%H:%i')) AS actualHrs FROM
				        (SELECT t.id,(TIME_TO_SEC(timediff(IF(
				            timediff(
				                MIN(log.submitted_on),MAX(log.submitted_on)
				            ) ,MAX(log.submitted_on),now()),MIN(log.submitted_on)))) AS duration
				        FROM ohrm_ticket t
				        LEFT JOIN ohrm_ticket_acknowledgement_action_log log ON log.ticket_id = t.id
				        WHERE is_PreventiveMaintenance = 1 AND MONTH(t.submitted_on) = ".$i." AND YEAR(t.submitted_on) = YEAR(now()) GROUP BY log.ticket_id) AS act_preventive
				        CROSS JOIN
				        (
				            SELECT SUM(duration) total FROM 
				            (SELECT t.id,(TIME_TO_SEC(timediff(IF(
				            timediff(
				                MIN(log.submitted_on),MAX(log.submitted_on)
				            ) ,MAX(log.submitted_on),now()),MIN(log.submitted_on)))) AS duration
				        FROM ohrm_ticket t
				        LEFT JOIN ohrm_ticket_acknowledgement_action_log log ON log.ticket_id = t.id
				        WHERE is_PreventiveMaintenance = 1 AND MONTH(t.submitted_on) = ".$i." AND YEAR(t.submitted_on) = YEAR(now()) GROUP BY log.ticket_id) AS act_preventive
				        ) b";
			$count1=mysqli_query($this->conn, $query1);			
			$row1=mysqli_fetch_assoc($count1);

			// No. of machines planned
			$query2="SELECT count(*) as plannedMachines FROM (SELECT equipment_id FROM `ohrm_maintenance_schedule` WHERE MONTH(date) = ".$i." AND YEAR(date) = YEAR(now()) GROUP BY equipment_id) AS planned_machines";
			$count2=mysqli_query($this->conn, $query2);			
			$row2=mysqli_fetch_assoc($count2);

			// No. of machines actual
			$query3="SELECT COUNT(*) as actualmachines FROM (SELECT equipment_id,COUNT(*) FROM `ohrm_ticket` WHERE is_PreventiveMaintenance = 1 and  MONTH(submitted_on) = ".$i." AND YEAR(submitted_on) = YEAR(now()) GROUP BY equipment_id) AS actal_machines";
			$count3=mysqli_query($this->conn, $query3);			
			$row3=mysqli_fetch_assoc($count3);

				$data['Month']= substr($month,0,3);
				$data['Planned Hours']=$row['plannedHrs'] ? $row['plannedHrs'] : "00:00";
				$data['Actual Hours']=$row1['actualHrs'] ? $row1['actualHrs'] : "00:00";	
				$data['No. of machines planned']=$row2['plannedMachines'] ? $row2['plannedMachines'] : "0";
				$data['No. of machines actual']=$row3['actualmachines'] ? $row3['actualmachines'] : "0";
				$data1[] = $data;					
		}
		$data['prevMainReport']=$data1;
		$data['status']=1;
		return $data;
		// }
    }
}
?>
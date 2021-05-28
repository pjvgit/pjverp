<?php
define("EXE_PATH", "C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe");
define("WKHTMLTOPDF_PATH","C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe");
// response code 
define("HTTP_OK", 200);
define("HTTP_NO_CONTENT", 204);
define("HTTP_BAD_REQUEST", 400);
define("HTTP_UNAUTHORIZE", 401);
define("HTTP_FORBIDDEN", 403);

define("SUCCESS", 200);
define("INVALID_INPUT", 400);
define("UNAUTHORIZE_CODE", 401);
define("ERROR",150);

//Email Settings
define("FROM_EMAIL", "test@poderjudicialvirtual.com");
define("FROM_EMAIL_TITLE", "Legalcase");
define("DO_NOT_REPLAY_FROM_EMAIL_TITLE", "No-Reply");
define("DO_NOT_REPLAY_FROM_EMAIL", "no-reply@legalcase.com");

define("SUPPORT_EMAIL", "test@poderjudicialvirtual.com");
define("REGARDS", "LegalCase");

/****************************************************/
/*********************ADMIN PANEL  ******************/
/****************************************************/
define("TITLE","Legalcase");
 
define("NO_RECORD_FOUND", "No record found.");
define("DEFAULT_IMG","/images/default/no-img.jpg");

define("USER_PER_PAGE_LIMIT","10");
define("DEFAULT_PER_PAGE","10");

//Error Messages
define("ERROR_LOGIN_MESSAGE", "Incorrect email or password.");
define("ERROR_INACTIVE_MESSAGE", "You are temporary blocked. please contact to admin.");
define("ERROR_INVALID_TOKEN", "Invalid token. Please try again.");
define("ERROR_NETWORK_ERROR", "A Network Error occurred. Please try again.");
define("ERROR_USER_NOTEXIST", "User does not exist.");
define("ERROR_EMAIL_NOTFOUND", "Email not found.");

define("ERROR_SAME_EMAIL", "New email is the same as the old one");
define("ERROR_INCORRECT_PASSWORD", "Current password is incorrect.");


//Success Messages
define("SUCCESS_PASSWORD_CHANGE", 'Password has been changed successfully.');
define("SUCCESS_EMAIL_SENT", 'If you have a valid account, instructions to reset your password have been emailed to you.');
define("SUCCESS_SAVE_PROFILE", 'Profile has been updated successfully.');

//User
define("SENT_LINK","We sent you an activation link. Check your email and click on the link to verify.");
define("INACTIVE_ACCOUNT","You need to confirm your account. We have sent you an activation code, please check your email.");

define("EMAIL_VERIFIED","Your e-mail is verified. You can now login.");
define("EMAIL_ALREADY_VERIFIED","Your e-mail is already verified. You can now login.");
define("EMAIL_NOT_IDENTIFIED","Sorry your email cannot be identified.");

define("USER_CREATED","User Created Successfully.");
define("USER_DELETED","User Deleted Successfully.");
define("USER_UPDATED","User Updated Successfully.");
define("NO_USER_LISTING","User not found!");
define("USER_NOT_EXIST","User does not exist.");
define("USER_FOUND","User found successfully!");


if(@$_SERVER['SERVER_NAME']=='localhost'){
    define("BASE_URL", "http://localhost/legalcase7_git/");
    define("BASE_LOGO_URL", "http://localhost/legalcase7_git/");
    //Local folder path
    define("USER_IMAGE_FOLDER_PATH", "local/users/");
}elseif(@$_SERVER['SERVER_NAME']=='bne.poderjudicialvirtual.com'){
    define("BASE_URL", "http://bne.poderjudicialvirtual.com/");
    define("BASE_LOGO_URL", "http://bne.poderjudicialvirtual.com/");
    define("USER_IMAGE_FOLDER_PATH", "users/");
}else{
    define("BASE_URL", "http://bna.poderjudicialvirtual.com/");
    define("BASE_LOGO_URL", "http://bna.poderjudicialvirtual.com/");
    define("USER_IMAGE_FOLDER_PATH", "users/");

}

/* * get dynamically timezone data* */
$timezones = [];

foreach (timezone_identifiers_list() as $timezone) {
    $datetime = new \DateTime('now', new DateTimeZone($timezone));
    $timezones[] = [
        'sort' => str_replace(':', '', $datetime->format('P')),
        'offset' => $datetime->format('P'),
        'name' => str_replace('_', ' ', implode(', ', explode('/', $timezone))),
        'timezone' => $timezone,
    ];
}

usort($timezones, function($a, $b) {
    return $a['sort'] - $b['sort'] ?: strcmp($a['name'], $b['name']);
});

$timezoneData = [];
foreach ($timezones as $key => $timezone) {
    $timezoneData['(UTC ' . $timezone['offset'] . ') - ' . $timezone['timezone']] = $timezone['timezone'];
}
define('TIME_ZONE_DATA', serialize($timezoneData));


$yseTitle=array("Accountant","Associate Attorney","Attorney","Billing Specialist","Bookkeeper","Business Manager","Counsel","Data Entry Clerk","Deputy Director","Finance Manager","IT Manager","Legal Intake Specialist","Legal Secretary","Office Manage","Paralegal","Partner","Receptionist","Staff");
define('USER_TITLE', serialize($yseTitle));

$paymentMethod=array("Credit Card","Cash","Check","Paypal","Other");
define('PAYMENT_METHOD', serialize($paymentMethod));

$deactivateReasons=array(
    1=> "This person is no longer with my law firm.",
    2=> "This person used ".config('app.name')." less than expected.",
    3=> "I can't justify monthly cost for this person.",
    4=> "This person was just testing". config('app.name').".",
    5=> "Other");
define('REASON_TITLE', serialize($deactivateReasons));

//user type
// 1:Attorney 2: Paralegal 3:Staff 4: None 

$userType=array("1"=>"Attorney","2"=>"Paralegal","3"=>"Staff");
define('USER_TYPE', serialize($userType));

define('LOADER', BASE_URL.'public/images/ajax_arrows.gif');

define('ADDRESS','9201 Spectrum Center Blvd STE 100, San Diego, CA 92123');
define('CELL','00000000');

define('GOOGLE_CAPTCHA_SECRATE_KEY','6LfC0JQaAAAAABP1teNxor8FJ4CDTcNsvQgzTPEl');
define('GOOGLE_CAPTCHA_SITE_KEY','6LfC0JQaAAAAAGfDzY23bY9WHG1pKx43B5ZJraMX');

/****************************************************/
/*********************ADMIN PANEL  ******************/
/****************************************************/

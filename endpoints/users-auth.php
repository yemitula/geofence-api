<?php

$app->post('/auth/user/login', function() use ($app) {
    // require_once 'passwordHash.php';
    $r = json_decode($app->request->getBody());
    verifyRequiredParams(array('email', 'password'),$r);
    $response = array();
    $db = new DbHandler();
    $password = $db->purify($r->password);
    $email = $db->purify($r->email);
    $user = $db->getOneRecord("SELECT * FROM user WHERE user_email='$email'");
    if ($user) {
        // check if password is correct
        //if(passwordHash::check_password($user['user_password'],$password)){
        if($user['user_password'] == $password){
            // create JSON token
            $jh = new JWTHandler();
            $user['user_password'] = '';
            if($token = $jh->createUserToken($user)) {
                $response['status'] = "success";
                $response['message'] = 'Logged in successfully. Taking you in...';
                $response['user'] = $user;
                $response['user']['token'] = $token;
                echoResponse(200, $response);
                // log admin action
                $actorob = json_decode(json_encode($user));
            } else {
                $response['status'] = "error";
                $response['message'] = 'Login failed! Could not complete authentication';
                echoResponse(401, $response);
            }
        } else {
            $response['status'] = "error";
            $response['message'] = 'Login failed! Incorrect Email or Password';
            echoResponse(401, $response);
        }
    } else {
            $response['status'] = "error";
            $response['message'] = "Sorry, we didn't find anybody matching your email!";
            echoResponse(401, $response);
    }
});

$app->post('/requestPassword', function() use ($app) {
    $response = array();
    $r = json_decode($app->request->getBody());
    $db = new DbHandler();
    $SHORTNAME = SHORTNAME;
    $response['email_sent'] = $email = $db->purify($r->email);

    $user = $db->getOneRecord("SELECT * FROM user WHERE user_email='$email'");
    if($user) {
        // //found user, send password
            $swiftmailer = new mySwiftMailer();
            $SHORTNAME = SHORTNAME;
            $subject = "Login Details Requested on $SHORTNAME";
            $body = "<p>Dear {$user['user_name']},</p>
    <p>You requested for your Password on $SHORTNAME. Your request has been completed.</p>
    <p>Your Password is <strong>{$user['user_password']}</strong></p>
    <p>Thank you for using $SHORTNAME.</p>
    <p><br><strong>$SHORTNAME App</strong></p>";
            $swiftmailer->sendmail(FROM_EMAIL, SHORTNAME, [$user['user_email']], $subject, $body);
            //return response
            $response['status'] = "success";
            $response["message"] = "Password Sent successfully! Please check your email.";
            echoResponse(200, $response);
    } else {
        $response['status'] = "error";
        $response["message"] = "Oops! The email you supplied is NOT associated with any $SHORTNAME account!";
        echoResponse(201, $response);
    }
});

$app->get('/userResetPassword', function() use ($app) {
    $response = array();

    $db = new DbHandler();
    $SHORTNAME = SHORTNAME;
    $response['email_sent'] = $email = $db->purify($app->request->get('email'));
    // var_dump("$email");die;

    $user = $db->getOneRecord("SELECT * FROM user WHERE user_email='$email'");

    if($user) {
        //found user, generate new password
        $pg = new PasswordGenerator();
        $new_pass = $pg->randomPassword();

        //update the new password in db
        $update_user = $db->updateInTable(
            "user", /*table*/
            [ 'user_password'=> $new_pass], /*columns*/
            [ 'user_id'=>$user['user_id'] ] /*where clause*/
        );

        if($update_user > 0) {
            //send new password to user
            $swiftmailer = new mySwiftMailer();
            $SHORTNAME = SHORTNAME; $subject = "Login Details RESET on $SHORTNAME";
            $body = "<p>Dear {$user['user_fullname']},</p>
    <p>You requested a Password Reset on $SHORTNAME. Your request has been completed.</p>
    <p>Your new Password is <strong>$new_pass</strong></p>
    <p>Thank you for using $SHORTNAME.</p>
    <p><br><strong>$SHORTNAME App</strong></p>";
            $swiftmailer->sendmail(FROM_EMAIL, SHORTNAME, [$user['user_email']], $subject, $body);

            //return response
            $response['status'] = "success";
            $response["message"] = "Password Reset successfully! Please check your email to retrieve the new password.";
            echoResponse(200, $response);
        } else {
            $response['status'] = "error";
            $response["message"] = "ERROR: Something went wrong while trying to reset your password. Please try again or contact Administrator!";
            echoResponse(201, $response);
        }
    } else {
        $response['status'] = "error";
        $response["message"] = "ERROR: The email you supplied is NOT associated with any $SHORTNAME account!";
        echoResponse(201, $response);
    }
});

$app->post('/changePassword', function() use ($app) {
    // require_once 'passwordHash.php';
    $r = json_decode($app->request->getBody());
    verifyRequiredParams(array('user_id', 'current', 'new'),$r->password);
    $response = array();
    $db = new DbHandler();
    // $ss = new SessionHandlr();
    // $session = $ss->getSession('call2fix_user');
    // $call2fix_user = $session['call2fix_user'];

    $user_id = $db->purify($r->password->user_id);
    $current = $db->purify($r->password->current);
    $new = $db->purify($r->password->new);

        // id matches logged in user
        // is current password correct?
        $user_password = $db->getOneRecord("SELECT user_id from user WHERE user_id='$user_id' AND user_password = '$current' ");

        if($user_password) {
            // password correct, update user
            $update_user = $db->updateInTable(
                "user", /*table*/
                [ 'user_password'=> $new ], /*columns*/
                [ 'user_id'=>$user_id ] /*where clause*/
            );
            if($update_user >= 0) {
                // password changed
                $response['status'] = "success";
                $response['message'] = 'Password changed successfully';
            } else {
                // something went wrong
                $response['status'] = "error";
                $response['message'] = 'Something went wrong while trying to udpate password!';
            }
        } else {
            // password incorrect
            $response['status'] = "error";
            $response['message'] = 'Current password supplied is incorrect!';
        }
    
    echoResponse(200, $response);
});

$app->delete('/auth/user/logout', function() {
    // log user action
    // $lg = new Logger();
    // $lg->logAction("call2fix_user", $session['call2fix_user']['user_fullname'] . " logged out");

    // var_dump("I AM HERE"); die;
            // log admin action
            // $lg = new Logger();
            // $lg->logAction(" Logged out successfully");
    
    $response['status'] = "success";
    echoResponse(200, $response);
});

// $app->get('/logout', function() {
//     $ss = new SessionHandlr();

//     $session = $ss->getSession('call2fix_user');
//     var_dump("I AM HERE"); die;

//     // log user action
//     // $lg = new Logger();
//     // $lg->logAction("call2fix_user", $session['call2fix_user']['user_fullname'] . " logged out");
    
//     // log admin action
//     $lg = new Logger();
//     $lg->logAction(" Logged Out");

//     $session = $ss->destroySession('call2fix_user');
//     $response['status'] = "success";
//     echoResponse(200, $response);
// });

// $app->get('/getUserSession', function() {
//     $ss = new SessionHandlr();
//     $session = $ss->getSession('call2fix_user');
//     echoResponse(200, $session);
// });
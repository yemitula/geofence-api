<?php
/**
 * Database configuration
 */

//  local
// define('DB_USERNAME', 'geofence_app');
// define('DB_PASSWORD', 'GEO$ql@1920');
// define('DB_HOST', 'localhost');
// define('DB_NAME', 'geofence_app');

// on heroku
define('DB_USERNAME', 'b0dd12a4df6bf7');
define('DB_PASSWORD', '1e799de1');
define('DB_HOST', 'us-cdbr-east-02.cleardb.com');
define('DB_NAME', 'heroku_a30f39b1170b714');

/* Site URL Config */
define('SITE_URL', 'https://www.alphalex.ng');

/* Mobile URL */
define('APP_URL', 'https://play.google.com/store');

/* Brand ID */
define('SHORTNAME', 'ALPhalex');
define('LONGNAME', 'ALPhalex Business and Legal Tool');

/*Site Email*/
define('FROM_EMAIL', 'app@alphalex.com.ng');

/*Admin Email*/
define('ADMIN_EMAIL', 'yemitula@gmail.com');

/* NORMAL LOCALHOST SMTP */
/*Server SMTP Access*/
define('SMTP_SERVER', 'localhost');
define('SMTP_EMAIL','app@alphalex.com.ng');
define('SMTP_PWD','yemi123');
define('SMTP_PORT', '25');
define('SMTP_ENCRYPTION', null); // use null if not an ssl server

// /* GMAIL SMTP RELAY */
// /*Server SMTP Access*/
// define('SMTP_SERVER', 'smtp.gmail.com');
// define('SMTP_EMAIL','yemitula@gmail.com');
// define('SMTP_PWD','ggmail8855');
// define('SMTP_PORT', '465');
// define('SMTP_ENCRYPTION', 'ssl'); // use null if not an ssl server

/*Swiftmailer Auth Type - MAIL/SMTP*/
define('MAILER_TYPE','MAIL');


/*Hash Secret*/
define('FHS', 'rTxNwwoPaq14smONPKdl');

/*JWT Secret*/
define('JWT_KEY', 'alpha!MjMHJQR*ae2020@lt.com.ng');

/*Login Token Lifetime*/
define('LOGIN_TOKEN_LIFETIME', '+24 hour');

/* EBULKSMS Parameters */
define('EBULK_USERNAME', 'yemgab@yahoo.com');
define('EBULK_APIKEY', 'a83a9fab2671a6c3159b835d4f2c11d71321f7d8');
define('EBULK_SENDER', SHORTNAME);

/* DOCS Save Path */
// define('DOCS_PATH', '/Applications/AMPPS/www/alphalex/docs'); //localhost on mac
define('DOCS_PATH', '/home/alphalex/public_html/app/docs'); //cpanel on shared

/*Public URLS Allowed - No Auth*/
// define('PUBLIC_ROUTES', ['\/userLogin', '\/userSignup']);

/* SMS Parameters */
/*define('SMS_CMD', 'sendquickmsg');
define('SMS_OWNEREMAIL', 'yemgab@yahoo.com');
define('SMS_SUBACCT', 'TRENOVA');
define('SMS_SUBACCTPWD', 'trenova1720');
define('SMS_SENDER', 'TRENOVA');
*/


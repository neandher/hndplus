<?php

define('BASE_PATH', dirname(realpath(__FILE__)) . '/');

//define('BASE_URL', 'http://localhost:8080/sistemas/scripthinode/');

define('BASE_URL', 'http://localhost/hndplus/');

define('TEMP_PATH', BASE_PATH . 'Temp/');

define('LOG_PATH', BASE_PATH . 'Log/');

define('HELPER_PATH', BASE_PATH . 'Helpers/');

define('HND_USER', '00817068');

define('HND_PASS', '@123#HND$');

//define('HND_USER', '00878893');

//define('HND_PASS', 'andrehnd@2015');

/*define('DB_HOST_MYSQL','localhost:3307');
define('DB_USER_MYSQL','root');
define('DB_PASS_MYSQL','');
define('DB_NAME_MYSQL','hnd_plus');*/

define('DB_HOST_MYSQL','localhost');
define('DB_USER_MYSQL','root');
define('DB_PASS_MYSQL','root');
define('DB_NAME_MYSQL','hnd_plus');

// Configuração de Email

define('SMTPHOST', 'smtp.office365.com');
define('SMTPSECURE', 'tls');
define('SMTPAUTH', true);
define('SMTPUSER', 'email.automatico@faesa.br');
define('SMTPPASS', 'sfe@SVCfaesa');
define('FROM', 'email.automatico@faesa.br');
define('FROMNAME', 'Produtos HND');
define('PORTA', 587);
<?php

return [
    'server' => env('LDAP_SERVER'),
    'ou' => env('LDAP_OU'),
    'password_bypass' => env('LDAP_PASSWORD_BYPASS', false),
    'username' => env('LDAP_USERNAME'),
    'password' => env('LDAP_PASSWORD')
];

<?php

$awsConfig = array(
  // Bootstrap the configuration file with AWS specific features
  'includes' => array('_aws'),
  'services' => array(
    // All AWS clients extend from 'default_settings'. Here we are
    // overriding 'default_settings' with our default credentials and
    // providing a default region setting.
    'default_settings' => array(
      'params' => array(
	      'key'    => getenv('bigprimesawskey'),
	    	'secret' => getenv('bigprimesawssecret'),
        'region' => 'eu-west-1'
      )
    )
  )
);

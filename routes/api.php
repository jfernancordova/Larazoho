<?php

use Dingo\Api\Routing\Router;
use App\Helpers\ApiResponse;

/** @var Router $api */
$api = app(Router::class);

$api->version('v1', function (Router $api) {
	
	$api->post('zoho/{module?}', function(string $module = 'Contacts'){
		$zohoManager = new \App\Services\ZohoCRM\ZohoManager($module);
		return $zohoManager->getRecords();
	});
	
	$api->post('zohoSync/{module?}', function(string $module = 'Contacts'){
		$zohoManager = new \App\Services\ZohoCRM\ZohoSync($module);
		$zohoManager->sync(1,300,300);
	})->where(['module' => 'Contacts|Potentials|Accounts|Leads']);
	
	$api->resource('contacts', 'App\\Api\\V1\\Controllers\\ContactsController',
		['except' => ['create', 'edit']]);
	
	$api->resource('accounts', 'App\\Api\\V1\\Controllers\\AccountsController',
		['except' => ['create', 'edit']]);
	
	$api->resource('potentials', 'App\\Api\\V1\\Controllers\\PotentialsController',
		['except' => ['create', 'edit']]);
	
	$api->resource('leads', 'App\\Api\\V1\\Controllers\\LeadsController',
		['except' => ['create', 'edit']]);
	
    $api->group(['prefix' => 'auth'], function(Router $api) {
        $api->post('signup', 'App\\Api\\V1\\Controllers\\SignUpController@signUp');
        $api->post('login', 'App\\Api\\V1\\Controllers\\LoginController@login');

        $api->post('recovery', 'App\\Api\\V1\\Controllers\\ForgotPasswordController@sendResetEmail');
        $api->post('reset', 'App\\Api\\V1\\Controllers\\ResetPasswordController@resetPassword');

        $api->post('logout', 'App\\Api\\V1\\Controllers\\LogoutController@logout');
        $api->post('refresh', 'App\\Api\\V1\\Controllers\\RefreshController@refresh');
        $api->get('profile', 'App\\Api\\V1\\Controllers\\UserController@profile');
    });

    $api->group(['middleware' => 'jwt.auth'], function(Router $api) {
        $api->get('protected', function() {
            return ApiResponse::response(200,'Ok',
                ['message' => 'Access to protected resources granted! You are seeing this text as you provided the token correctly.']);
        });

        $api->get('refresh', [
            'middleware' => 'jwt.refresh',
            function() {
                return ApiResponse::response(200, 'Ok',
                    ['message' => 'By accessing this endpoint, you can refresh your access token at each request. 
                        Check out this response headers!']);
            }
        ]);
        
    });
	
	$api->get('hello', function() {
		return ApiResponse::response(200, 'Ok',
			['message' => 'This is a simple example of item returned by your APIs. Everyone can see it.']);
	});
});

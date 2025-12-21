<?php 

return [
	'public_key' => env('LIQPAY_PUBLIC_KEY'),
	'private_key' => env('LIQPAY_PRIVATE_KEY'),
	'callback' => env('LIQPAY_CALLBACK'),
	'result' => env('LIQPAY_RESULTS'),
	'client_url' => rtrim(
		env('CLIENT_URL', env('FRONT_URL', env('APP_URL', 'http://localhost:3000'))),
		'/'
	),
];

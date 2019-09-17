<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;


$router->get('/', function() {
	return redirect('salaries');
});

$router->get('salaries', function() {
	$client = new Client();
	$response = $client->request('GET', 'http://www.guiatrabalhista.com.br/guia/salario_minimo.htm');

	$body = (string) $response->getBody()->getContents();
	$crawler = new Crawler($body);

	$rows = $crawler->filter('table')->filter('tr')->each(function (Crawler $node) {
        return $node->html();
    });
	foreach ($rows as $key => $row) {
		$rowContent = (new Crawler($row))->filter('td')->text();
		echo trim(strip_tags($rowContent));
	}
	//dd($rows->count());
	return;

	$data = [
		[
			'vigencia' => '01.01.2018',
			'valor_mensal' => 'R$ 954,00'
		],
		[
			'vigencia' => '01.01.2018',
			'valor_mensal' => 'R$ 954,00'
		],
		[
			'vigencia' => '01.01.2018',
			'valor_mensal' => 'R$ 954,00'
		],
	];
	return response()->json($data);
});

$router->get('version', function () use ($router) {
    return $router->app->version();
});

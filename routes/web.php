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

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;


$router->get('/', function() {
	return redirect('salaries');
});

$router->get('salaries', function(Request $request) {
	function slug( $string ) {
		if (is_string($string)) {
			$string = strtolower(trim(utf8_decode($string)));

			$before = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿRr';
			$after  = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';           
			$string = strtr($string, utf8_decode($before), $after);

			$replace = array(
				'/[^a-z0-9.-]/'	=> '_',
				'/-+/'			=> '_',
				'/\.+/'			=> '_',
		    	'/\-{2,}/'		=> ''
			);
			$string = preg_replace(array_keys($replace), array_values($replace), $string);
		}
		return $string;
	}

	$format   = $request->get('format') ?? 'arr';
	$data     = [];
	$dataName = [];

	$client   = new Client();
	$response = $client->request('GET', 'http://www.guiatrabalhista.com.br/guia/salario_minimo.htm');
	$bodyCra  = (string) $response->getBody()->getContents();
	$crawler  = new Crawler($body);

	$rows     = $crawler->filter('table')->first()->filter('tr')->each(function (Crawler $node) {
        return $node->html();
    });

	/**
	 * Varre as linhas encontradas
	 */
	foreach ($rows as $rowIndex => $row) {
		$td = (new Crawler($row))->filter('td')->each(function (Crawler $node) {
	        return $node->html();
	    });

	    // Varre as colunas encontradas
	    foreach ($td as $tdIndex=>$tdContent) {

	    	// Salva o cabeçalho como o nome dos indices
	    	// Será executado apenas uma vez
	    	if($rowIndex == 0) {
	    		$dataName[$tdIndex] = slug(trim(strip_tags($tdContent)));
	    		continue;
	    	}

	    	// Gera o array de dados nomeando com o index salvo do cabeçalho da tabela
	    	// Não será executado na primeira vez
    		if( isset($dataName[$tdIndex]) )
	    		$data[$rowIndex][$tdIndex][$dataName[$tdIndex]] = trim(strip_tags($tdContent));
	    }
	}

	if($format == 'json')
		return response()->json($data);
	else
		return $data;
});

$router->get('version', function () use ($router) {
    return $router->app->version();
});

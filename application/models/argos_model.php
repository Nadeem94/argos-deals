<?php

class argos_model extends CI_Model 
{	

	function __construct()
	{
		parent::__construct();
	}
	
	// This function retrieves product data from HUKD/Amazon and sends it to the controller.
	function getDeals()
	{
		// My unique identifiers that are required for amazon requests.
		$AWSAccessKeyId = 'SADFDSFAASDFFDSAFDSSADF';
		$AWSSecretKey = 'xTVZsAD2LAFdGBDse0yNfsadasdf/sdfafsda';
		$AssociatesID = 'fdsafdsafsad';
		
		// Arguments for Amazon request.
		$args = array("Operation"=>"ItemSearch","SearchIndex"=>"All","ResponseGroup"=>"Offers","MerchantId"=>"All",
		"Keywords"=>"item");
		
		$temperatures = array();
		$prices = array();
		$titles = array();	
		$descriptions = array();
		$images = array();
		$deal_links = array();
		$product_links = array();		
		$amazonurls = array();	
		$result = array();
		
		// Url that will be used to retrieve data from HUKD.
		$url = 'http://api.hotukdeals.com/rest_api/v2/?key=1d4db359b006475c3765c82652ac9b5d&merchant=argos';
		$xml = simplexml_load_file($url);
		
		// Loop over xml data from HUKD and store in arrays.
		foreach($xml->deals->children() as $item) 
		{
			
			$titlestring = (string)$item->title;
			$imagestring = (string)$item->deal_image;	
			$temperatures[] = (string)$item->temperature;
			$descriptions[] = (string)$item->description;
			$deal_links[] = (string)$item->deal_link;
			$images[] = $imagestring;
			
			// Hacky way round to get url for Argos website.
			$product_links[] = 'http://www.hotukdeals.com/visit?m=5&q=' . substr($imagestring, 44);
			
			$prices[] = (float)$item->price;
			$titles[] = $titlestring;
			
			// If satatements which removes characters that are not required from the HUKD title, for the amazon request.
			if (strpos($titlestring, "Argos") != false) {
				$titlestring = substr($titlestring, 0, strpos($titlestring, "Argos"));
			}
			if (strpos($titlestring, "£") != false) {
				$titlestring = substr($titlestring, 0, strpos($titlestring, "£"));
			}			
			if (strpos($titlestring, ",") != false) {
				$titlestring = substr($titlestring, 0, strpos($titlestring, ","));
			}			
			if (strpos($titlestring, "(") != false) {
				$titlestring = substr($titlestring, 0, strpos($titlestring, "("));
			}	
			
			$args['Keywords'] = $titlestring;
			$requesturl = $this->aws_signed_request('co.uk',$args,$AWSAccessKeyId,$AWSSecretKey,$AssociatesID);
			$amazonurls[] = $requesturl;
		}
		
		// Sort based on temperature, for hottest deals.
		array_multisort($temperatures, SORT_DESC, $prices, $titles, $descriptions, $images, $deal_links, $product_links, $amazonurls);
		
		// Get only top ten hottest deals.
		$top_ten = count($temperatures);
		for ($top_ten; $top_ten>10; $top_ten--)
		{
			array_pop($temperatures);
			array_pop($prices);
			array_pop($titles);
			array_pop($amazonurls);
			
			array_pop($descriptions);
			array_pop($images);
			array_pop($deal_links);
			array_pop($product_links);
		}
		
		$amazon_prices = array();
		$amazon_links= array();
		$price_difference = array();
		
		// Loop over amazon url requests and store data from them.
		foreach ($amazonurls as $url) 
		{			
			 $curl_handler = curl_init();
			 curl_setopt($curl_handler, CURLOPT_URL,$url);
			 curl_setopt($curl_handler, CURLOPT_CONNECTTIMEOUT, 2);
			 curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, 1);
			 $query = curl_exec($curl_handler);
			 curl_close($curl_handler);
			 
			 $xml = simplexml_load_string($query);
			 
			 // Check if there is a product matching the title string from HUKD	 
			 if( isset($xml->Items->Item[0]) ) 
			 {
				$price = (string)$xml->Items->Item[0]->OfferSummary->LowestNewPrice->FormattedPrice;
				$price = ltrim($price, '£');
				$amazon_prices [] = (float)$price;
				$amazon_links[] = (string)$xml->Items->Item[0]->Offers->MoreOffersUrl;
			 }
			 else 
			 {
				$amazon_prices [] = 0.00;
				$amazon_links[] = "#";
			 }		 
		}
				
		// Compare prices with HUKD/Amazon and find difference.
		foreach($amazon_prices as $key => &$val)
		{
			if(isset($prices[$key]))
			{
				$price_difference[] = $val - $prices[$key];
			}
		}
		
		// Sort arrays based on better price difference between Argos and Amazon.
		array_multisort($price_difference, SORT_DESC, $temperatures, $prices, $amazon_prices, $amazon_links, 
			$titles, $descriptions, $images, $deal_links, $product_links, $amazonurls);

		// Loop over each array and form a multidimensional array, in order to convert to JSON in the controller.
		foreach ($price_difference as $id => $key) 
		{
			$result[] = array
			(
				'priceDiff' => $price_difference[$id],
				'title'    => $titles[$id],
				'price' => $prices[$id],
				'amazonPrice' => $amazon_prices[$id],
				'temperature'  => $temperatures[$id],
				'desc' => $descriptions[$id],
				'img' => $images[$id],
				'dealLink' => $deal_links[$id],
				'amazonLink' => $amazon_links[$id],
				'productLink' => $product_links[$id]

			);
		}
			
		return $result;
	}
	
	/* This function was downloaded from: http://www.ulrichmierendorff.com/software/aws_hmac_signer.html
		It is a free to use php function that calculates the signature that is required for the Amazon request. The function
		returns a full formed amazon request with my unique identifiers and arguments. */
	function aws_signed_request($region, $params, $public_key, $private_key, $associate_tag=NULL, $version='2011-08-01')
	{
		/*
		Copyright (c) 2009-2012 Ulrich Mierendorff

		Permission is hereby granted, free of charge, to any person obtaining a
		copy of this software and associated documentation files (the "Software"),
		to deal in the Software without restriction, including without limitation
		the rights to use, copy, modify, merge, publish, distribute, sublicense,
		and/or sell copies of the Software, and to permit persons to whom the
		Software is furnished to do so, subject to the following conditions:

		The above copyright notice and this permission notice shall be included in
		all copies or substantial portions of the Software.

		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
		IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
		FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
		THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
		LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
		FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
		DEALINGS IN THE SOFTWARE.
		*/
		
		/*
		Parameters:
			$region - the Amazon(r) region (ca,com,co.uk,de,fr,co.jp)
			$params - an array of parameters, eg. array("Operation"=>"ItemLookup",
							"ItemId"=>"B000X9FLKM", "ResponseGroup"=>"Small")
			$public_key - your "Access Key ID"
			$private_key - your "Secret Access Key"
			$version (optional)
		*/
		
		// some paramters
		$method = 'GET';
		$host = 'webservices.amazon.'.$region;
		$uri = '/onca/xml';
		
		// additional parameters
		$params['Service'] = 'AWSECommerceService';
		$params['AWSAccessKeyId'] = $public_key;
		// GMT timestamp
		$params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
		// API version
		$params['Version'] = $version;
		if ($associate_tag !== NULL) {
			$params['AssociateTag'] = $associate_tag;
		}
		
		// sort the parameters
		ksort($params);
		
		// create the canonicalized query
		$canonicalized_query = array();
		foreach ($params as $param=>$value)
		{
			$param = str_replace('%7E', '~', rawurlencode($param));
			$value = str_replace('%7E', '~', rawurlencode($value));
			$canonicalized_query[] = $param.'='.$value;
		}
		$canonicalized_query = implode('&', $canonicalized_query);
		
		// create the string to sign
		$string_to_sign = $method."\n".$host."\n".$uri."\n".$canonicalized_query;
		
		// calculate HMAC with SHA256 and base64-encoding
		$signature = base64_encode(hash_hmac('sha256', $string_to_sign, $private_key, TRUE));
		
		// encode the signature for the request
		$signature = str_replace('%7E', '~', rawurlencode($signature));
		
		// create request
		$request = 'http://'.$host.$uri.'?'.$canonicalized_query.'&Signature='.$signature;
		
		return $request;
	}
}
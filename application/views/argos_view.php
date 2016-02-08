<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <!-- Bootstrap Stylesheet -->
  <link rel="stylesheet" 
    href="https://cdn.rawgit.com/twbs/bootstrap/v4-dev/dist/css/bootstrap.css">
  <title> Argos Deals </title>
</head>
<body style="padding:20px">

  <div class="container">
    <div class="jumbotron jumbotron-fluid">
	  <center>
	    <h1 class="display-4">Argos Deals</h1><br>
        <p class="lead">Choose from 10 of the hottest Argos deals,
		  <br>ordered from 1-10, by the better price difference between Amazon.
		</p>
		<a class="btn btn-primary" id="refreshBtn" role="button">Refresh Deals</a>
	  </center>
    </div>	
	<center>		
	  <div id="loadingContent"> 
	    <h2><span class="label label-default">Loading Deals, Please Wait . . .</span></h2>
	  </div>	  
	  <div id="pageContent" style="display:none">	  
		<select class="c-select" id="dealSelect">
		</select><br><br>			
		<nav>
		  <ul class="pagination">
			<li class="page-item">
			  <a id="prevBtn" class="page-link" href="#" aria-label="Previous">
				<span aria-hidden="true">&laquo;</span>
				<span class="sr-only">Previous</span>
		      </a>
			</li>
			<li id="0" class="page-item"><a id="p1" class="page-link" href="#">1</a></li>
			<li id="1" class="page-item"><a id="p2" class="page-link" href="#">2</a></li>
			<li id="2" class="page-item"><a id="p3" class="page-link" href="#">3</a></li>
			<li id="3" class="page-item"><a id="p4" class="page-link" href="#">4</a></li>
			<li id="4" class="page-item"><a id="p5" class="page-link" href="#">5</a></li>
			<li id="5" class="page-item"><a id="p6" class="page-link" href="#">6</a></li>
			<li id="6" class="page-item"><a id="p7" class="page-link" href="#">7</a></li>
			<li id="7" class="page-item"><a id="p8" class="page-link" href="#">8</a></li>
		    <li id="8" class="page-item"><a id="p9" class="page-link" href="#">9</a></li>
		    <li id="9" class="page-item"><a id="p10" class="page-link" href="#">10</a></li>
		    <li class="page-item">
		      <a id="nextBtn" class="page-link" href="#" aria-label="Next">
			    <span aria-hidden="true">&raquo;</span>
			    <span class="sr-only">Next</span>
		      </a>
		    </li>
		  </ul>
		</nav>	
	    <div id="dealContent"></div>	
	  </div>
	</center>	
  </div>
  
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
  <script type="text/javascript">
    $(document).ready(function () {	
		// Arrays to store retrieved JSON data.
		var pricediffs = [];
		var prices = [];
		var amazonPrices = [];
		var amazonLinks = [];	
		var titles = [];
		var temperatures = [];
		var descs = [];
		var imgs = [];
		var dealLinks = [];
		var productLinks = [];
		
		// Ajax function for JSON API
		$.ajax({
				url: "../../index.php/argos_controller/argosDeals",
				method: 'GET',
				dataType: 'JSON'		
			}).done(function (data) {			
				var html = '';
				var count = 1;			
				// Function to loop over JSON data that was retrieved.
				$.each(data, function() {				
					$.each(this, function(key, value) {
						if (key == 'priceDiff')
						{
							pricediffs.push(value);
						}							
						if (key == 'title')
						{
							titles.push(value);
							html += '<option selected>'+count+ ') '+value+ '</option>';
						}
						if (key == 'temperature')
						{
							temperatures.push(value);
						}
						if (key == 'price')
						{
							prices.push(value);						
						}
						if (key == 'desc')
						{
							descs.push(value);						
						}
						if (key == 'img')
						{
							imgs.push(value);						
						}
						if (key == 'dealLink')
						{
							dealLinks.push(value);						
						}
						if (key == 'productLink')
						{
							productLinks.push(value);						
						}
						if (key == 'amazonPrice')
						{
							amazonPrices.push(value);						
						}
						if (key == 'amazonLink')
						{
							amazonLinks.push(value);						
						}										
					});
					count++;
				});			
				count = 1;
				$('#dealSelect').html(html);
				$('#dealSelect>option:eq(0)').prop('selected', true);
				$( "#dealSelect" ).trigger( "change" );
				$( '#0' ).addClass( "active" );
				$( "#loadingContent").hide();
				$( "#pageContent" ).show();					
			}).fail(function (data) {
				alert("The request has failed.");
				$( "#loadingContent").html('<h2><span class="label label-danger">Failed to load data.</span></h2>');
				console.log(data);				
			});
			
		// Function to refresh the data on the page
		$(document).on("click", "#refreshBtn", function() {
			location.reload(true);
		});
		
		// Function to listen to the drop down change and display corresponding deal dynamically.
		$(document).on("change", "#dealSelect", function() {
			var titleSelected = $(this).val();
			titleSelected = titleSelected.substr(titleSelected.indexOf(")") + 2);
			var id = $.inArray(titleSelected, titles);
			var selectIndex = $(this).prop('selectedIndex');
			
			var amazonItemPrice = "Lowest Price (New Only): £" + amazonPrices[id]; 
			var priceDifference = "Price Difference: £" + pricediffs[id];
			
			if (amazonItemPrice == "Lowest Price (New Only): £0")
			{
				amazonItemPrice = "Lowest Price (New Only): N/A";
				priceDifference = "Price Difference: N/A";
			}
			
			// Dynamically display product details.
			var html = '<div class="card">'+
			'<div class="card-block">'+
			'<h4 class="card-title"><span class="label label-primary">'+ titles[id] +'</span></h4></div>'+
			'<img class="card-img-top" src="'+ imgs[id] +'" alt="Deal Image"/>'+
			'<div class="card-block">'+
			'<p class="card-text">'+ descs[id] +'</p></div>'+
			'<ul class="list-group list-group-flush"><li class="list-group-item">'+
			'<h3><span class="label label-warning">HUKD Temperature: '+ temperatures[id] +
			'°C</span></h3></li>'+
			'<li class="list-group-item">'+
			'<img src="https://qudini-production.s3.amazonaws.com/uploads/customer/logo/5/argos.png"'+
			'style="width:200px;height:100px"/>'+
			'<h4><span class="label label-default">Price: £'+ prices[id] +'</span></h4></li>'+
			'<li class="list-group-item">'+
			'<img src="http://www.tacwise.com/wp-content/uploads/2014/01/Amazon-logo-FEATURE-400x200.jpg"'+
			'style="width:200px;height:75px"/>'+
			'<h4><span class="label label-default">'+ amazonItemPrice +'</span></h4></li>'+
			'<li class="list-group-item"><h4><span class="label label-default">'+ priceDifference +'</span></h4>'+
			'</li></ul><div class="card-block">'+
			'<a href="'+ dealLinks[id] +'" target="_blank" class="btn btn-primary card-link">HUKD Link</a>'+
			'<a href="'+ productLinks[id] +'" target="_blank" class="btn btn-primary card-link">Argos Link</a>';
			
			// Only display amazon link button if there is a valid amazon product url.
			if ( (amazonItemPrice != "Lowest Price (New Only): N/A") && (amazonLinks[id] != "0") )
			{
				html+= '<a href="'+ amazonLinks[id] +'" target="_blank" class="btn btn-primary card-link">Amazon Link</a>';
			}
			
			html+= '</div></div>';
			
			$('#dealContent').html(html);
			$('.page-item').removeClass("active");
			$( '#'+selectIndex ).addClass( "active" );			
		});
		
		// Function for the previous button within the pagination.
		$(document).on("click", "#prevBtn", function() {
			var prevIndex = $("#dealSelect").prop('selectedIndex') - 1;
			$('.page-item').removeClass("active");
			$( '#'+prevIndex ).addClass( "active" );			 
			$('#dealSelect option:selected').prev().attr('selected', 'selected');		
			$( "#dealSelect" ).trigger( "change" );
		});

		// Function for the next button within the pagination.		
		$(document).on("click", "#nextBtn", function() {
			var nextIndex = $("#dealSelect").prop('selectedIndex') + 1;
			$('.page-item').removeClass("active");
			$( '#'+nextIndex ).addClass( "active" );			
			$('#dealSelect option:selected').next().attr('selected', 'selected');
			$( "#dealSelect" ).trigger( "change" );
		});
		
		// Function to display corresponding deal when pagination item is clicked.
		$(document).on("click", "#p1, #p2, #p3, #p4, #p5, #p6, #p7, #p8, #p9, #p10", function() {
			var page = $(this).text() - 1;
			$('.page-item').removeClass("active");
			$( '#'+page ).addClass( "active" );
			$('#dealSelect>option:eq('+page+')').prop('selected', true);
			$( "#dealSelect" ).trigger( "change" );
		});	
	});
  </script>
</body>
</html>
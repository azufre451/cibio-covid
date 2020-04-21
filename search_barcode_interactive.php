<!DOCTYPE html> 

<html xmlns="http://www.w3.org/1999/xhtml" lang="it">
	<head>
		<script type="text/javascript" src="TEMPLATES/js/jquery.js"> </script>
		<style>
			#barcode_result .row_neg { background-color: #eb837a; }
			#barcode_result .row_pos { background-color: #a2f285; }
			#barcode_result .row_rep_pcr { background-color: #e0be58; }
			#barcode_result .row_rep_ext { background-color: #d3b0ff; }
			/* #barcode_result .row_rep_tamp { background-color: #eeeeee; } */
			#barcode_result .control_barcode, #barcode_result .no_result { background-color: #bbb; };
			label {
				font-weight: bold;
			}
			#query_container {
				font-size: 5em;
				text-align: center;
			}
			#barcode_query {
				margin-bottom: 2em;
			}
			#spinner {
				width: 30em;
				display: block;
				margin: 0 auto;
			}
		</style>
	</head>
	<body>
		<textarea id = 'search_barcode' placeholder = 'Scan or type your barcode here' autofocus=""></textarea>
		<div id = "query_container">
			<div id = "barcode_query"><label>Valore inserito:</label> <span class="value"></span></div>
			<div id = "barcode_result"></div>
		</div>
		<img id = "spinner" src = "TEMPLATES/img/spinner.gif" />
<script>
var debounce = null,
		$spinner = $('#spinner'),
		$query_container = $('#query_container');
$spinner.hide();
$('#search_barcode').on('input', function (){
	var barcode_input = this,
		  search_barcode = barcode_input.value.trim();
	clearTimeout(debounce);
	debounce = setTimeout( function() {
			$query_container.hide();
			$spinner.show();
			$.ajax({
			  method: "GET",
			  url: "search.php",
			  data: { json: true, barcode: search_barcode.replace(/^(\d{8})01$/, "$1") }
			})
			.done(function(msg) {
				var render_result = function () {
					var len_pcrs = msg.PCRs.length,
						  $result  = $('#barcode_result');
					$spinner.hide();
					$query_container.show();
					$('#barcode_query .value').text(search_barcode);
					if (len_pcrs > 0) {
						var last_pcr = msg.PCRs[len_pcrs - 1];
						console.log(last_pcr);
						if (last_pcr.isControl === "0") {
							$result.html('<label>Esito tampone:</label> <span class="' + last_pcr.htmlClass + '">' + last_pcr.esito_pcr + '</span>');
						} else {
							$result.html('<span class="control_barcode">Inserito pozzetto di controllo!</span>');
						}
					} else {
						$result.html('<span class="no_result">BARCODE NON TROVATO</span>');
					}
					barcode_input.value = '';
				};
				setTimeout(render_result, 500);
			});
   }, 500);
}).on('blur', function () {
	var that = this;
	setTimeout(function () { that.focus(); }, 100);
});
</script>
	</body>
</html>

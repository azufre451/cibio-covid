function notiEC(msg) { 
						var len_pcrs = msg.PCRs.length,
							  $result  = $('#barcode_result'),
							  $jqContainer = jQuery('#query_container');
						//$jQ.hide();
						$jqContainer.show();
						
						if (len_pcrs > 0) {
							var last_pcr = msg.PCRs[len_pcrs - 1];
							console.log(last_pcr);
							if (last_pcr.isControl === "0") {
								if (last_pcr.esito_pcr === 'POSITIVO' || last_pcr.esito_pcr === 'NEGATIVO')
									jQuery('#track_'+last_pcr.esito_pcr+' textarea').append(last_pcr.barcode + '\r\n');
								$result.html('<label>Esito tampone:</label> <span class="' + last_pcr.htmlClass + '">' + last_pcr.esito_pcr + '</span>');
							} else { 
								$result.html('<span class="control_barcode">Inserito pozzetto di controllo!</span>');
							}
						} else {
							$result.html('<span class="no_result">BARCODE NON TROVATO</span>');
						} 
						
						//alert('AAA');
						jQuery('#search_barcode').val('');
						jQuery('#search_barcode').select();
						JsBarcode("#barcode", last_pcr.barcode);

						

					
				}

jQuery(function()
		{
			jQuery('#search_barcode').select();


			$('.trackerDiv textarea').on('focus', function (){
				this.select();
			});
	
			$('#search_barcode').on('keyup', function (e)
			{
					
				var pitapa = (window.event) ? e.keyCode : e.which;
				if(pitapa == 13)
				{
				
					var barcode_input = this;
					var search_barcode = barcode_input.value.trim();
					
						jQuery.ajax({
							beforeSend: function(){$('#barcode_query #barcode').text(search_barcode);},
							success: notiEC,
							async:true,
							type: 'POST',
							url:'search.php?partialmatch=true',
							data: { json: true, barcode: search_barcode}
					
							});
								
				} 
			});	
		}
);

	
	
	
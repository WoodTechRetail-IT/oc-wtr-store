//  Quantity per Option / Количество для опций
//  Support: support@liveopencart.com / Поддержка: help@liveopencart.ru

if ( typeof($qpo_script_loaded) == 'undefined' ) { // defence fron double loading of the QPO script (by some bugged themes)

	$qpo_script_loaded = true;

	$(document).ready(function(){
		$('[data-type="qpo-minus"], [data-type="qpo-plus"]').off('click');
		$('[data-type="qpo-minus"], [data-type="qpo-plus"]').click(function(e){
			e.preventDefault();
			e.stopPropagation();
			var $this = $(this);
			if ( $this.attr('data-field') ) {
				var $quantity_input = $('input[name="'+$this.attr('data-field')+'"]');
				var quantity = ( parseInt($quantity_input.val()) || 0 );
				if ( $this.attr('data-type') == 'qpo-minus' ) {
					if ( quantity > 0 ) {
						quantity--;
					}
				} else { // plus
					quantity++;
				}
				$quantity_input.val(quantity);
				$quantity_input.change();
			}
		});
	});
	
}
	
function qpo_resetQuantities(reset_quantities, p_product_option_id){
	
	var input_name = 'quantity_per_option[';
	if ( p_product_option_id ) {
		input_name+= p_product_option_id+']';
	}
	
	if ( reset_quantities ) {
		$('input[name^="'+input_name+'"]').each(function(){
			var $qpo_input = $(this);
			if ( $(this).is(':visible') || p_product_option_id ) { // only if the input is visible or the function is called by the specific option
				if ( reset_quantities && parseInt(reset_quantities) ) {
					var default_value = ''; 
					if ( reset_quantities == 1 ) { // to zeroes
						default_value = 0;
					} else if ( reset_quantities == 2 ) { // to default quantities
						default_value = $qpo_input.attr('data-default-value') || 0;
					}
					$qpo_input.val(default_value);
					$qpo_input.change();
				}
			}
		});
	}
}

function qpo_getPOVIdByName(name){ // quantity_per_option[..][X]
	return name.split('][')[1].replace(']','');
}
function qpo_getPOIdByName(name){ // quantity_per_option[..][X]
	return name.split('][')[0].replace('quantity_per_option[','');
}
	

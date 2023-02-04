<script type="text/javascript"><!--
var product_options_images = <?php echo json_encode($product_options_images); ?>;
var images_product_options_values = <?php echo json_encode($images_product_options_values); ?>;
$(document).ready(function() {

  $(".option input[type='checkbox'],.option input[type='radio'],.option select").change(function() {

    if ($(this).is('select') || ($(this).is('input') && $(this).is(':checked'))) {
    
      // all checked options
      var checked = $('.option input:checked, .option select option:selected');
      // array with checked ids
      var checked_ids = [];
      // fill array
      checked.each(function(i) {
        checked_ids[i] = $(this).val();
      });
      //console.log(checked_ids);          
      
      var product_option_value_id = $(this).val();

      if (product_options_images[product_option_value_id] !== undefined ) {
      
        $.each(product_options_images[product_option_value_id], function(index, product_options_images_image){

          //check if all options for this image checked 
          var all_options_checked = true;

          $.each(images_product_options_values[product_options_images_image['image']], function(index2, product_option_value_id){
            if (checked_ids.indexOf(product_option_value_id) == -1) {
              all_options_checked = false;
            }
          });

          if (all_options_checked) {
            var image = product_options_images_image['image_thumb'];
            var image_popup = product_options_images_image['image_popup'];
            $('#image').attr('src', image);
            $('.image > a').attr('href', image_popup);
          }
        });
      }    
    }
  });    
});
//--></script>
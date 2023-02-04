<div class="ocf-container ocf-theme-<?php echo $theme; ?> ocf-mobile-<?php echo $index; ?> ocf-mobile-<?php echo $mobile_placement; ?> ocf-<?php echo $layout; ?> ocf-<?php echo $position; ?>" id="ocf-module-<?php echo $index; ?>">
<?php if ($index < 2) { ?>
<link rel="stylesheet" href="<?php echo $stylesheet; ?>" />
<?php } ?>

  <div class="ocf-content">
    <div class="ocf-header">
      <i class="ocf-icon ocf-icon-16 ocf-brand ocf-sliders"></i>      
      
      <?php echo $heading_title; ?>
      
      <?php if ($layout == 'horizontal' && ($search_button || $selecteds)) { ?>
      <div class="ocf-header-btn ocf-desktop">
        <?php if ($selecteds) { ?>
        <button type="button" onclick="location = '<?php echo $link; ?>';" class="ocf-btn ocf-btn-link"><?php echo $button_cancel_all; ?></button>
        <?php } else { ?>
        <button type="button" data-ocf-discard="*" class="ocf-btn ocf-btn-link ocf-disabled" disabled="disabled"><?php echo $button_cancel_all; ?></button>
        <?php } ?>         
        <?php if ($search_button) { ?>
        <button type="button" class="ocf-btn ocf-btn-link ocf-disabled" data-ocf="button" data-loading-text="<?php echo $text_loading; ?>" disabled="disabled"><?php echo $button_select; ?></button>
        <?php } ?>        
      </div>   
      <?php } ?>  
      
      <button type="button" data-ocf="mobile" class="ocf-btn ocf-btn-link ocf-mobile ocf-close-mobile" aria-label="Close filter"><i class="ocf-icon ocf-icon-16 ocf-times"></i></button>
    </div>
          
    <div class="ocf-body">        
      <?php include 'filter_list.tpl'; ?>
    </div>      
       
    <?php if ($search_button || $selecteds) { ?>
    <?php if ($layout == 'vertical') { ?>
    <?php $class = 'ocf-footer'; ?>
    <?php } else { ?>
    <?php $class = 'ocf-footer ocf-mobile'; ?>
    <?php } ?>
    <div class="<?php echo $class; ?>">
      <div class="ocf-between">
        <?php if ($selecteds) { ?>
        <button type="button" onclick="location = '<?php echo $link; ?>';" class="ocf-btn ocf-btn-link"><?php echo $button_cancel_all; ?></button>
        <?php } else { ?>
        <button type="button" data-ocf-discard="*" class="ocf-btn ocf-btn-link ocf-disabled" disabled="disabled"><?php echo $button_cancel_all; ?></button>
        <?php } ?>        
        <?php if ($search_button) { ?>
        <button type="button" class="ocf-btn ocf-disabled ocf-btn-block ocf-search-btn-static" data-ocf="button" data-loading-text="<?php echo $text_loading; ?>" disabled="disabled"><?php echo $button_select; ?></button>
        <?php } ?>        
      </div>
    </div>            
    <?php } ?>
  </div><!-- /.ocf-content -->
  
  <div class="ocf-is-mobile"></div>
  
  <?php if (($mobile_button_position == 'fixed' || $mobile_button_position == 'both') && $index < 2) { ?>
  <div class="ocf-btn-mobile-fixed ocf-mobile">
    <button type="button" class="ocf-btn ocf-btn-default" data-ocf="mobile" aria-label="<?php echo $button_ocfilter_mobile; ?>">
      <span class="ocf-btn-name"><?php echo $button_ocfilter_mobile; ?></span>
      <i class="ocf-icon ocf-icon-16 ocf-brand ocf-sliders"></i>
    </button>
  </div>  
  <?php } ?> 
  
  <div class="ocf-hidden">
    <button class="ocf-btn ocf-search-btn-popover" data-ocf="button" data-loading-text="<?php echo $text_loading; ?>"><?php echo $button_select; ?></button>
  </div>
  
<script>
+(function(global) {

var startOCFilter = function() {
  var loadScript = function(url, callback) {
    $.ajax({ url: url, dataType: 'script', success: callback, async: true });
  };
  
  var init = function() {
    $('#ocf-module-<?php echo $index; ?>').ocfilter({
      index: '<?php echo $index; ?>',
      
      paramsIndex: '<?php echo $url_index; ?>',
      
      urlHost: '<?php echo $url_host; ?>',
      urlParams: '<?php echo $url_params; ?>',    
      
      params: '<?php echo $filter_params; ?>',
      
      sepFilt: '<?php echo $sep_filt; ?>',
      sepFsrc: '<?php echo $sep_fsrc; ?>',
      sepVals: '<?php echo $sep_vals; ?>',
      sepSdot: '<?php echo $sep_sdot; ?>',
      sepSneg: '<?php echo $sep_sneg; ?>',
      sepSran: '<?php echo $sep_sran; ?>',

      position: '<?php echo $position; ?>',
      layout: '<?php echo $layout; ?>',
      numeralLocale: '<?php echo $numeral_locale; ?>',
      searchButton: <?php echo (int)$search_button; ?>,
      showCounter: <?php echo (int)$show_counter; ?>,
      sliderInput: <?php echo (int)$slider_input; ?>,
      sliderPips: <?php echo (int)$slider_pips; ?>,
      priceLogarithmic: <?php echo (int)$price_logarithmic; ?>,
      lazyLoadFilters: <?php echo (int)$hidden_filters_lazy_load; ?>,
      lazyLoadValues: <?php echo (int)$hidden_values_lazy_load; ?>,
      
      mobileMaxWidth: <?php echo (int)$mobile_max_width; ?>,
      mobileRememberState: <?php echo (int)$mobile_remember_state; ?>,
      
      textLoad: '<?php echo addslashes($text_loading_ocf); ?>',
      textSelect: '<?php echo addslashes($button_select); ?>'
    });
  };
  
  <?php if ($index < 2) { ?>
  loadScript('<?php echo $javascript; ?>', init);
  <?php } ?> 
};

var ready = function(fn) {
  if (global.readyState != 'loading') {
    fn();
  } else {
    global.addEventListener('DOMContentLoaded', fn);
  }
};

ready(function() { // DOM loaded
  if ('undefined' == typeof jQuery) {
    console.error('OCFilter required jQuery');
    
    return;
  }
  
  $(startOCFilter); // jQuery loaded
});

})(document);
</script>
</div><!-- /.ocf-container -->
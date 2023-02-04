<div class="ocf-value-list">
  <?php if ($slider_input) { ?>
  <div class="ocf-input-group ocf-slider-input-group">
    <?php if ($filter['prefix']) { ?>
    <span class="ocf-input-group-addon"><?php echo $filter['prefix']; ?></span>
    <?php } ?>
    <input type="number" name="ocf[<?php echo $filter['id']; ?>][min]" value="<?php echo $filter['min_request']; ?>" class="ocf-form-control" id="ocf-input-min-<?php echo $filter['id']; ?>" autocomplete="off" aria-label="<?php echo $filter['name']; ?>" <?php echo (!$filter['slider_enabled'] ? 'disabled="disabled"' : ''); ?> />
    <?php if ($filter['type'] == 'slide_dual') { ?>
    <span class="ocf-input-group-addon">-</span>
    <input type="number" name="ocf[<?php echo $filter['id']; ?>][max]" value="<?php echo $filter['max_request']; ?>" class="ocf-form-control" id="ocf-input-max-<?php echo $filter['id']; ?>" autocomplete="off" aria-label="<?php echo $filter['name']; ?>" <?php echo (!$filter['slider_enabled'] ? 'disabled="disabled"' : ''); ?> />
    <?php } ?>
    <?php if ($filter['suffix']) { ?>
    <span class="ocf-input-group-addon"><?php echo $filter['suffix']; ?></span>
    <?php } ?>
  </div>
  <?php } ?>
  <div class="ocf-value-slider">
    <div id="ocf-s-<?php echo $filter['id']; ?>" class="ocf-value-scale"
      data-filter-key="<?php echo $filter['filter_key']; ?>"
      data-min="<?php echo $filter['min']; ?>"
      data-max="<?php echo $filter['max']; ?>"
      data-range="<?php echo ($filter['type'] == 'slide_dual' ? 'true' : 'false'); ?>"
      data-min-start="<?php echo $filter['min_request']; ?>"
      <?php if ($filter['type'] == 'slide_dual') { ?>
      data-max-start="<?php echo $filter['max_request']; ?>"
      <?php } ?>
      
      <?php if ($slider_input) { ?>
      data-input-min="#ocf-input-min-<?php echo $filter['id']; ?>"
      <?php if ($filter['type'] == 'slide_dual') { ?>
      data-input-max="#ocf-input-max-<?php echo $filter['id']; ?>"
      <?php } ?>
      <?php } ?>
      
      data-text-min="#ocf-text-min-<?php echo $filter['id']; ?>"
      <?php if ($filter['type'] == 'slide_dual') { ?>
      data-text-max="#ocf-text-max-<?php echo $filter['id']; ?>"
      <?php } ?>
      
      data-prefix="<?php echo $filter['prefix']; ?>"
      data-suffix="<?php echo $filter['suffix']; ?>"
      
      <?php if (!$filter['slider_enabled']) { ?>
      disabled="disabled"
      <?php } ?>
    ></div>
  </div>
  <?php if (!$search_button) { ?>
  <div class="ocf-mobile ocf-btn-search-slider ocf-text-right">
    <button type="button" class="ocf-btn ocf-disabled ocf-search-btn-static" data-ocf="button" data-loading-text="<?php echo $text_loading; ?>" disabled="disabled"><?php echo $button_select; ?></button>
  </div>
  <?php } ?>   
</div>
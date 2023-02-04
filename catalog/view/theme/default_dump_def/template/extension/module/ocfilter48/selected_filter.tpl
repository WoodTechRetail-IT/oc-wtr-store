<div class="ocf-selected-card ocf-desktop">
  <div class="ocf-selected-header"><?php echo $text_choosed; ?></div>
  <?php foreach ($selecteds as $filter) { ?>
  <div class="ocf-selected-filter">
    <span class="ocf-selected-filter-name"><?php echo $filter['name']; ?>:</span>
    
    <?php foreach ($filter['values'] as $value) { ?>
    <button type="button" onclick="location = '<?php echo $value['href']; ?>';" class="ocf-selected-discard" title="<?php echo $value['name']; ?>">
      <span class="ocf-selected-value-name"><?php echo $value['name']; ?></span> 
      <i class="ocf-icon ocf-times-circle"></i>
    </button>
    <?php } ?>
  </div>
  <?php } ?>
  
  <div class="ocf-between">
    <button type="button" class="ocf-btn ocf-btn-link ocf-text-danger" onclick="location = '<?php echo $link; ?>';"><?php echo $button_cancel_all; ?></button>
    <button type="button" class="ocf-btn ocf-btn-link" data-ocf="specify"><?php echo $button_specify; ?></button>
  </div>
</div>
<?php if ((!empty($has_loaded_values) && !$filter['dropdown'] && $layout == 'vertical') || (empty($has_loaded_values) && ($filter['dropdown'] || $layout == 'horizontal'))) { ?>
<?php $class_scroll = 'ocf-scroll-y'; ?>
<?php } else { ?>
<?php $class_scroll = false; ?>
<?php } ?>

<?php if ($filter['columns'] > 1) { ?>
<?php $class_value_list_body = 'ocf-value-list-body ocf-auto-column ocf-column-' . $filter['columns']; ?>
<?php } else { ?>
<?php $class_value_list_body = 'ocf-value-list-body'; ?>
<?php } ?>

<div class="ocf-value-list">
  <?php if ($class_scroll) { ?>
  <div class="<?php echo $class_scroll; ?>">  
  <?php } ?>   
    <div class="<?php echo $class_value_list_body; ?>">
      <?php foreach ($filter['values'] as $value) { ?>
      <?php include 'value_item.tpl'; ?>
      <?php } ?>
    </div>

    <?php if ($filter['button_show_more_values']) { ?>
    <div class="ocf-collapse ocf-collapse-value" id="ocf-hidden-values-<?php echo $filter['id']; ?>" data-ocf-load="values" data-filter-key="<?php echo $filter['filter_key']; ?>"> 
      <?php if ($filter['hidden_values']) { ?>
      <div class="ocf-value-list">
        <div class="ocf-scroll-y">
          <div class="<?php echo $class_value_list_body; ?>">
            <?php foreach ($filter['hidden_values'] as $value) { ?>
            <?php include 'value_item.tpl'; ?>
            <?php } ?>      
          </div>        
        </div>
      </div>      
      <?php } ?>
    </div>
    <?php } ?>
  <?php if ($class_scroll) { ?>
  </div>
  <?php } ?>      
  
  <?php if ($filter['button_show_more_values']) { ?>
  <button type="button" class="ocf-btn ocf-btn-link ocf-btn-show-values" data-ocf="collapse" data-target="#ocf-hidden-values-<?php echo $filter['id']; ?>" data-loading-text="<?php echo $text_loading; ?>" aria-expanded="false">
    <span class="ocf-hide-expand-1"><?php echo $filter['button_show_more_values']; ?> <i class="ocf-icon ocf-angle ocf-angle-down"></i></span>
    <span class="ocf-hide-expand-0"><?php echo $button_hide; ?> <i class="ocf-icon ocf-angle ocf-angle-up"></i></span>
  </button>  
  <?php } ?>
</div>
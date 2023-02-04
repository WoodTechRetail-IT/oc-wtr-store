<div class="ocf-filter-list ocf-clearfix">
  <?php if (empty($has_loaded_filters)) { ?>
  <?php if ($seo_pages) { ?>
  <?php include 'page_link_list.tpl'; ?>
  <?php } ?>

  <?php if ($layout == 'vertical' && $selecteds) { ?>
  <?php include 'selected_filter.tpl'; ?>
  <?php } ?>  
  <?php } ?>
  
  <?php if ($layout == 'vertical' && !empty($has_loaded_filters)) { ?>
  <div class="ocf-hidden-filters-divider">
    <span data-ocf="collapse" data-target="#ocf-hidden-filters-<?php echo $index; ?>"><?php echo $button_hide; ?> <i class="ocf-icon ocf-angle ocf-angle-up"></i></span>
  </div>
  <?php } ?>

  <?php foreach ($filters as $filter) { ?>
  <?php include 'filter_item.tpl'; ?>
  <?php } ?>

  <?php if ($button_show_more_filters) { ?>
  <?php if ($layout == 'horizontal') { ?>
  <div class="ocf-btn-block ocf-text-center">
    <button type="button" class="ocf-btn ocf-btn-link ocf-btn-show-filters" data-ocf="collapse" data-target="#ocf-hidden-filters-<?php echo $index; ?>" data-loading-text="<?php echo $text_loading; ?>" aria-expanded="false"> 
      <span class="ocf-hide-expand-1"><?php echo $button_show_more_filters; ?> <i class="ocf-icon ocf-angle ocf-angle-down"></i></span>
      <span class="ocf-hide-expand-0"><?php echo $button_hide; ?> <i class="ocf-icon ocf-angle ocf-angle-up"></i></span>  
    </button>   
  </div>
  <?php } ?>
  <div class="ocf-collapse ocf-collapse-filter" id="ocf-hidden-filters-<?php echo $index; ?>" data-ocf-load="filters"> 
    <div class="ocf-filter-list">
      <?php foreach ($hidden_filters as $filter) { ?>
      <?php include 'filter_item.tpl'; ?>
      <?php } ?>  
    </div>    
  </div>
  <?php if ($layout == 'vertical') { ?>
  <button type="button" class="ocf-btn ocf-btn-block ocf-btn-show-filters" data-ocf="collapse" data-target="#ocf-hidden-filters-<?php echo $index; ?>" data-loading-text="<?php echo $text_loading; ?>" aria-expanded="false"> 
    <span class="ocf-hide-expand-1"><?php echo $button_show_more_filters; ?> <i class="ocf-icon ocf-angle ocf-angle-down"></i></span>
    <span class="ocf-hide-expand-0"><?php echo $button_hide; ?> <i class="ocf-icon ocf-angle ocf-angle-up"></i></span>  
  </button>
  <?php } ?>
  <?php } ?>
</div>
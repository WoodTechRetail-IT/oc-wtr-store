<?php if ($layout == 'horizontal') { ?>
<?php $filter['dropdown'] = true; ?>
<?php } ?>

<?php $class = 'ocf-filter'; ?>

<?php if ($filter['dropdown']) { ?>
<?php $class .= ' ocf-dropdown'; ?>
<?php } ?>

<?php if ($filter['type'] == 'slide' || $filter['type'] == 'slide_dual') { ?>
<?php $class .= ' ocf-slider'; ?>

<?php if ($slider_input) { ?>
<?php $class .= ' ocf-has-input'; ?>
<?php } ?>
<?php } ?>

<?php if ($filter['selected']) { ?>
<?php $class .= ' ocf-active'; ?>
<?php } ?>

<div class="<?php echo $class; ?>" id="ocf-filter-<?php echo $filter['id']; ?>">
  <div class="ocf-filter-body">
    <div class="ocf-filter-header" data-ocf="expand">  
      <i class="ocf-mobile ocf-icon ocf-arrow-long ocf-arrow-left"></i> 
      
      <?php if ($filter['type'] == 'slide' || $filter['type'] == 'slide_dual') { ?>
      <span class="ocf-active-label">
        <?php echo $filter['prefix']; ?>
        <span id="ocf-text-min-<?php echo $filter['id']; ?>"><?php echo $filter['min_request']; ?></span>
        <?php if ($filter['type'] == 'slide_dual') { ?>
        - <span id="ocf-text-max-<?php echo $filter['id']; ?>"><?php echo $filter['max_request']; ?></span>
        <?php } ?>
        <?php echo $filter['suffix']; ?>
      </span>    
      <?php } else { ?>
      <span class="ocf-active-label"><?php echo $filter['text_selected']; ?></span>    
      <?php } ?>                      
      
      <span class="ocf-filter-name"><?php echo $filter['name']; ?></span>     
      
      <span class="ocf-filter-header-append">
        <?php if ($filter['description']) { ?>
        <span class="ocf-desktop ocf-filter-description" data-ocf="popover" data-content="<?php echo $filter['description']; ?>">
          <i class="ocf-icon ocf-icon-16 ocf-help-circle"></i>
        </span>
        <?php } ?>     
        
        <span class="ocf-filter-discard ocf-icon ocf-icon-16 ocf-minus-circle" data-ocf-discard="<?php echo $filter['filter_key']; ?>"></span> 
        
        <i class="ocf-mobile ocf-icon ocf-angle ocf-angle-right"></i>        
        <?php if ($filter['dropdown']) { ?> 
        <i class="ocf-desktop ocf-icon ocf-angle ocf-angle-down"></i>
        <?php } ?>   
      </span>
    </div><!-- /.ocf-filter-header -->
    
    <?php if ($filter['type'] == 'slide' || $filter['type'] == 'slide_dual') { ?>
    
    <?php include 'filter_slider_item.tpl'; ?>
    
    <?php } else { ?>
    
    <?php include 'value_list.tpl'; ?>
    
    <?php } ?>
  </div>    
</div>
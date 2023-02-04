<?php $class = 'ocf-value ocf-' . $filter['type']; ?>
<?php if ($value['selected']) { ?>
<?php $class .= ' ocf-selected'; ?>
<?php } else if (!$value['count']) { ?>
<?php $class .= ' ocf-disabled'; ?>
<?php } ?>

<button type="button" id="ocf-v-<?php echo $value['id']; ?>-<?php echo $index; ?>" class="<?php echo $class; ?>" data-filter-key="<?php echo $filter['filter_key']; ?>" data-value-id="<?php echo $value['value_id']; ?>">
  <?php if (!$value['color'] && !$value['image']) { ?>
  <span class="ocf-value-input ocf-value-input-<?php echo $filter['type']; ?>"></span>
  <?php } else if ($value['color']) { ?>
  <span class="ocf-value-color" style="background-color: #<?php echo $value['color']; ?>;"></span>
  <?php } else if ($value['image']) { ?>
  <span class="ocf-value-image" style="background-image: url(<?php echo $value['image']; ?>);"></span>
  <?php } ?>
    
  <span class="ocf-value-name"><?php echo $value['name']; ?></span>
  <?php if ($show_counter) { ?>
  <span class="ocf-value-append">   
    <span class="ocf-value-count"><?php echo $value['count']; ?></span>
  </span>
  <?php } ?>
</button>
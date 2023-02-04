<div class="ocf-module-page">
  <div class="ocf-module-page-header"><?php echo $page_module_link_title; ?></div>
  <ul class="ocf-module-page-list ocf-scroll-y">
    <?php foreach ($seo_pages as $page) { ?>
    <li>
      <?php if ($page['selected']) { ?>
      <a href="<?php echo $page['href']; ?>" class="ocf-page-selected ocf-between"><?php echo $page['name']; ?> <i class="ocf-icon ocf-icon-16 ocf-times-circle"></i></a>
      <?php } else { ?>
      <a href="<?php echo $page['href']; ?>"><?php echo $page['name']; ?></a>
      <?php } ?>
    </li>
    <?php } ?>
  </ul> 
</div>
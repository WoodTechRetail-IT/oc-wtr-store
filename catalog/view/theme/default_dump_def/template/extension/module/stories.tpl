<?php if (isset($stories['stories_block'])) { ?>

<h3><?php echo $heading_title; ?></h3>
<div class="row">
  <div class="col-sm-12">
    <div id="stories" class="storiesWrapper"></div>
  </div>
</div>


<script>
  var currentSkin = getCurrentSkin();
  var stories = new Zuck('stories', {
    backNative: true,
    previousTap: true,
    skin: currentSkin['name'],
    autoFullScreen: currentSkin['params']['autoFullScreen'],
    avatars: currentSkin['params']['avatars'],
    paginationArrows: currentSkin['params']['paginationArrows'],
    list: currentSkin['params']['list'],
    cubeEffect: currentSkin['params']['cubeEffect'],
    localStorage: true,
    stories: [
      <?php foreach($stories['stories_block'] as $block => $story) { ?>
        Zuck.buildTimelineItem(
                "<?php echo $block; ?>",
                "<?php echo (isset($story['stories']['images'][0]) ? '/image/' . $story['stories']['images'][0] : ''); ?>",
                "<?php echo $story['name']; ?>",
                "#",
                timestamp(),
                [
                  <?php if (isset($story['stories']['images']) && is_array($story['stories']['images'])) { ?>
                    <?php foreach ($story['stories']['images'] as $key => $story_img) { ?>
                      [
                              "<?php echo $block; ?>-<?php echo $key; ?>",
                        "photo",
                        3,
                        "<?php echo '/image/' . $story_img; ?>",
                        "<?php echo '/image/' . $story_img; ?>",
                        '<?php echo (isset($story['stories']['links'][$key]) ? $story['stories']['links'][$key] : ''); ?>',
                        false,
                        false,
                        timestamp()
                      ],
                  <?php } ?>
                  <?php } ?>
                ]
        ),
      <?php } ?>
    ]

  });
</script>




<?php } ?>

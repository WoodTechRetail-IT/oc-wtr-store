<div id="jetcache-informer" class="sc-jetcache-bottom-heading">

	<div class="sc-flex-container">
		<div class="sc-flex-block sc-jetcache-heading">

			<div class="sc-flex-block">
				<div class="sc-flex-container">
					<div class="sc-flex-container-left">
						<div>
						 <img src="<?php echo $icon; ?>">
						</div>
				    </div>
					<div>
						<div>
							&nbsp;
							<?php echo $entry_jetcache; ?>
						</div>
				    </div>
				</div>
	   		</div>

		</div>
        <?php if ($cache_remove_status) { ?>
		<div class="sc-flex-block">
		    <a href="#" id="httpsfix_cache_remove" onclick="
				$.ajax({
					url: '<?php echo $jetcache_url_cache_remove; ?>',
					type: 'POST',
					data: 'filename=<?php echo $filename; ?>',
					dataType: 'html',
					beforeSend: function() {
		               $('.div_cache_remove').show().html('<?php echo $text_jetcache_loading; ?>');
					},
					success: function(content) {
						if (content) {
							$('.div_cache_remove').show().html('<span style=\'color:#fff\'>'+content+'<\/span>');
							setTimeout('jetcache_div_hide()', 2000);
						}
					},
					error: function(content) {
						$('.div_cache_remove').show().html('<span style=\'color:red\'><?php echo $text_jetcache_cache_remove_fail; ?><\/span>');
					}
				}); return false;" class="jetcache-button-buy" style=""><?php echo $text_jetcache_url_cache_remove; ?></a>
				<div class="div_cache_remove"></div>
		</div>
        <?php } ?>

		<div class="sc-flex-block" id="jet_queries">
		<?php if ($queries != $queries_cache && $round_queries_queries_cache > 0) { ?>
			<?php echo $entry_jetcache_db; ?>
			<?php echo ' ('; ?>
			<?php echo $text_jetcache_queries; ?>
			<?php echo ') <br>Jet:&nbsp;&nbsp;x'; ?>
			<?php echo $round_queries_queries_cache; ?>
			<?php } else { ?>
			 &nbsp;<br>&nbsp;
			<?php } ?>
		</div>
        <?php if ($queries > 0 && $queries_cache > 0) { ?>
  		<div class="sc-flex-block">
			<div class="sc-flex-container">
				<div class="sc-flex-container-left">
					<div>
					 <?php echo $entry_jetcache_queries; ?>&nbsp;
					</div>
					<?php if ($queries != $queries_cache) { ?>
					<div>
					   <?php echo $entry_jetcache_queries_cache; ?>&nbsp;
					</div>
					<?php } ?>
			    </div>
				<div>
					<div>
					  <?php echo $round_queries; ?>
					</div>
					<?php if ($queries != $queries_cache) { ?>
					<div>
					  <?php echo $round_queries_cache; ?>
					</div>
					<?php } ?>
			    </div>
			</div>
   		</div>
   		<?php } ?>
<!--
<?php if ($queries_count_cache != '') { ?>
  		<div class="sc-flex-block">
			<div class="sc-flex-container">
				<div class="sc-flex-container-left">
					<div>
					 <?php echo $entry_queries_count_cache; ?>&nbsp;
					</div>
			    </div>
				<div>
					<div>
					  <?php echo $round_queries_count_cache; ?>
					</div>
			    </div>
			</div>
   		</div>
<?php } ?>
-->

<?php if ($count_cont_cached > 0) { ?>
  		<div class="sc-flex-block">
			<div class="sc-flex-container">
				<div class="sc-flex-container-left">
					<div>
					 <?php echo $entry_count_cont_cached; ?>&nbsp;
					</div>
			    </div>
				<div>
					<div>
					  <?php echo $count_cont_cached; ?>
					</div>
			    </div>
			</div>
   		</div>
<?php } ?>


<?php if ($count_model_cached != 0) { ?>
  		<div class="sc-flex-block">
			<div class="sc-flex-container">
				<div class="sc-flex-container-left">
					<div>
					 <?php echo $entry_count_model_cached; ?>&nbsp;
					</div>
			    </div>
				<div>
					<div>
					  <?php echo $count_model_cached; ?>
					</div>
			    </div>
			</div>
   		</div>
<?php } ?>

<?php if ($count_query_cached > 0 && $queries > 0 && $queries_cache > 0) { ?>
  		<div class="sc-flex-block">
			<div class="sc-flex-container">
				<div class="sc-flex-container-left">
					<div>
					 <?php echo $entry_count_query_cached; ?>&nbsp;
					</div>
			    </div>
				<div>
					<div>
					  <?php echo $count_query_cached; ?>
					</div>
			    </div>
			</div>
   		</div>
<?php } ?>


<?php if ($queries_time_cache != '' && $jetcache_opencart_core != '') { ?>
		<div class="sc-flex-block">
			<div class="sc-flex-container">
				<div class="sc-flex-container-left">
					<div>
					 <?php echo $entry_queries_time_cache; ?>&nbsp;
					</div>
			    </div>
				<div>
					<div>
					  &nbsp;<?php echo $round_queries_time_cache; ?> <?php echo $entry_jetcache_sec; ?>
					</div>
			    </div>
			</div>
   		</div>
<?php } ?>

<?php if ($jetcache_opencart_core != '') { ?>
	  <div class="sc-flex-block">
			<div class="sc-flex-container">
				<div class="sc-flex-container-left">
					<div>
					 <?php echo $entry_jetcache_opencart_core; ?>&nbsp;
					</div>
			    </div>
				<div>
					<div>
					  <?php echo $round_jetcache_opencart_core; ?> <?php echo $entry_jetcache_sec; ?>
					</div>
			    </div>
			</div>
	   </div>
<?php } ?>

		<div class="sc-flex-block">
			<?php if ($load != $cache && $rate > 1) { ?>
				<?php echo $entry_jetcache_pages.'<br>Jet:&nbsp;&nbsp;x'. $rate;  ?>
			<?php } else { ?>
			 &nbsp;<br>&nbsp;
			<?php } ?>
  		</div>

		<div class="sc-flex-block">
			<div class="sc-flex-container">
				<div class="sc-flex-container-left">
					<div>
					 <?php echo $entry_jetcache_withoutcache; ?>&nbsp;
					</div>
					<?php if ($load != $cache && $load != $cache_all) { ?>
					<div>
					   <?php echo $entry_jetcache_cache; ?>&nbsp;
					</div>
					<?php } ?>
			    </div>
				<div>
					<div>
					  <?php echo $round_load; ?> <?php echo $entry_jetcache_sec; ?>
					</div>
					<?php if ($load != $cache && $load != $cache_all) { ?>
					<div id="round_cache">
					  <?php echo $round_cache; ?> <?php echo $entry_jetcache_sec; ?>
					</div>
					<div id="round_cache_all">
					  <?php echo $round_cache_all; ?> <?php echo $entry_jetcache_sec; ?>
					</div>
                    <script>
                    $('#round_cache, #round_cache_all').click(function(){
                    	$('#round_cache').toggle();
                    	$('#round_cache_all').toggle();
                    });
                    </script>

					<?php } ?>
			    </div>
			</div>
  		</div>

	</div>

</div>

<script>
function jetcache_div_hide() {
	$('.div_cache_remove').hide();
}
</script>
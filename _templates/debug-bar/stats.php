<div class="headings">
	<h2 for="dbrr_rewrite_rules_array"><span><?php _e('Total Rewrite Rules', $__DATA['object']->_domain); ?></span><?php echo $rewrite_rules; ?></h2>

<?php if (isset($filters['list']) && is_array($filters['list']) && count($filters['list']) > 0 && 
 			isset($filters['count']) && $filters['count'] > 0) {   ?>
		<h2 for="dbrr_rewrites_filters"><span><?php _e('Total Rewrite Rules Filters', $__DATA['object']->_domain); ?></span><?php echo $filters['count']; ?></h2>
<?php } ?>

		<h2><span>&nbsp;</span><a href="#" class="flush_rewrite_rules_trigger">Flush Rewrite Rules<em class="spinner"></em></a></h2>

		<div class="clear"></div>
</div>
<div class="clear"></div>
<script>
RRAT.ajax  = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>
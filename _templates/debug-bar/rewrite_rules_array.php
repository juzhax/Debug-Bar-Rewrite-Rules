
<div class="dbrr_header">
	<h3 id="dbrr_rewrite_rules_array"><?php _e('Rewrite Rules Array', $__DATA['object']->_domain); ?></h3>
</div>

<div class="clear"></div> 
<?php if (count($rewrite_rules) > 0) { ?>
	
	<div class="filter_holder">
	
		<div class="filter_url">
			<input type="text" class="mono pattern_domain" readonly="readonly" style="text-align:right;" value="<?php echo rtrim(get_option('home')).'/'; ?>"  />
			<input type="text" class="mono pattern_search" style="text-align:left;" tabindex="600"/>
		</div>
		
		<div class="filter_rule">
			<input type="text" tabindex="601" class="mono matches_field" style="text-align:center;" placeholder="Filter Rewrite Rules List" />
		</div>
		<div class="clear"></div>
	</div>	
<?php } ?>

<div id="____debug"></div>

<div class="inside" id="rewrite_rules_array_holder">
	<?php include dirname(__FILE__)."/rewrite_rules_array-table.php"; ?>
</div>

<script>
	// domain - used for calculation of input width and repalcements.	
	RRAT.domain    = '<?php echo rtrim(get_option('home')).'/'; ?>';
	// List of table rows
	RRAT.el 	   =  jQuery('#rewrite_rules_array_table tbody tr');
	// Matches Col Tittle
	RRAT.__matches_col_title 	   = '<?php _e('Matches', $this->_domain); ?>';
	
	// rewrites checking settings. 
	RRAT.validator = '<?php echo DBRR_URL.'validator.php'; ?>';

	
	RRAT.use_verbose_page_rules = <?php echo intval($GLOBALS['wp_rewrite']->use_verbose_page_rules); ?>;
	
</script>
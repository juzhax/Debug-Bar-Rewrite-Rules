<?php if ( !is_admin() && $wp->did_permalink == true) {?>
<div class="clear"></div> 	
<div class="dbrr_wrapper" id="dbrr_rewite_rules_array">
	
	<div class="dbrr_header">
		<h3><?php _e('Current Matched Rule and Query', $__DATA['object']->_domain); ?></h3>
		<div class="inside">
			<table cellspacing="0">
				<thead>
				 	<th width="20%"><?php _e('Request',  	$__DATA['object']->_domain)?></th>
				 	<th width="20%"><?php _e('Rule',  	    $__DATA['object']->_domain)?></th>
				  	<th width="20%"><?php _e('Match', 		$__DATA['object']->_domain); ?></th>
				  	<th width="20%"><?php _e('Query String',$__DATA['object']->_domain); ?></th>
					<th width="20%"><?php _e('Query Vars',  $__DATA['object']->_domain); ?></th>
				</thead>	
			</table>
		</div>		
	</div>
 
	<div class="inside">
		<table>
			<tr>
				<td width="20%"><?php echo $wp->request; ?></td>
				<td width="20%"><?php echo $wp->matched_rule; ?></td>
				<td width="20%"><?php echo $wp->matched_query; ?></td>
				<td width="20%"><?php echo $wp->query_string; ?></td>
				<td width="20%">
					<?php echo (ini_get('xdebug.overload_var_dump') != 1) ? '<pre>' : ''; ?>
					<?php echo var_dump($wp->query_vars); ?>
					<?php echo (ini_get('xdebug.overload_var_dump') != 1) ? '</pre>' : ''; ?>
					
				</td>
			</tr>
		</table>
	</div>
</div>	

 

<?php } ?>
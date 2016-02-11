<table cellspacing="0" class="dbrrtbl">
	<thead>
 		<tr>
			<th width="10%"><?php _e('Hook',  $this->_domain)?></th>
			<th width="10%"><?php _e('Priority', $this->_domain); ?></th>
			<th width="20%"><?php _e('Callback Type', $this->_domain); ?></th>
			<th width="20%"><?php _e('Callback', $this->_domain); ?></th>
	 	</tr>
	</thead>
	<tbody>
<?php if ( is_array($filters['list']) && count($filters['list']) > 0 ) { ?>
	<?php 
		foreach($filters['list'] as $hook => $filters) {  
			ksort($filters);
			foreach( $filters as $priority => $filters ){
				foreach( $filters as $filter){
					?>
		<tr class="<?php echo @++$i%2?'alt':'';?>">
		 	<td width="10%"><?php echo $hook; ?></td>
			<td width="10%"><?php echo $priority; ?></td>
			<td width="20%"><?php echo $filter[0]; ?></td>
			<td width="20%"><?php echo $filter[1]; ?></td>
		</tr>
					<?php 
				}
			}
		} 
	?>	
<?php } ?>
	</tbody>	
</table>
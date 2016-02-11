<table cellspacing="0" class="dbrrtbl" id="rewrite_rules_array_table">
	<thead>
		<tr>
			<th class="col-data" style="width:50%;"><?php _e('Rule',  $this->_domain)?></th>
			<th class="col-data" style="width:50%;"><?php _e('Match', $this->_domain); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php if ( is_array($rewrite_rules) && count($rewrite_rules) > 0 ) { ?>
			<?php foreach($rewrite_rules as $rewrite_rule => $rewrite_query) { ?>
				<tr class="<?php echo @++$i%2?'alt':'';?>" id="<?php echo $i; ?>">
					<td><?php echo $rewrite_rule; ?></td>
					<td><?php echo $rewrite_query; ?></td>
				</tr>
			<?php } ?>
		<?php } else { ?>
			<tr><td colspan="2"><?php _e('Permalinks not aviable', $this->_domain); ?></td></tr>
		<?php } ?>		
	</tbody>	
</table>
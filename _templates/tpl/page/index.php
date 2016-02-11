<form method="post" id="dbrrwp_admin" action="<?php $_SERVER['REQUEST_URI']; ?>" >
	
 
	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div id="side-info-column" class="inner-sidebar">
			<?php do_meta_boxes(get_current_screen()->id, 'side', 'default'); ?>
		</div> 


		<div id="post-body" class="has-sidebar"> 
		 	<div id="post-body-content">
				<?php do_meta_boxes(get_current_screen()->id, 'normal', 'default'); ?>
			</div>
		</div> 
	</div> 
	
</form>
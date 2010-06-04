//$Id$
Drupal.grid_debug = function (context) {
	// add an extra identifying class to body
	omega('body').addClass('has-grid');
	if(Drupal.settings.grid_overlay_state) {
		// grid is on by default
		omega('body').addClass('show-grid');
		var debug_next_state = "Off";
	}
	else {
		// grid is off by default
		var debug_next_state = "On";
	}
	// add toggle button/section
	omega('#page').after('<div id="omega-debug"><a href="#">Turn Debug <strong>'+debug_next_state+'</strong></a><div id="omega-debug-info"></div></div>');
	// toggle info between clicks
	omega('#omega-debug a').click(function(){
		if(debug_next_state == "Off") {
			omega('#omega-debug a').html('Turn Debug <strong>On</strong>');
			omega('body').removeClass('show-grid');
			debug_next_state = "On";
			console.log('Overlay Turned Off...');
			return false;
		}
		else {
			omega('#omega-debug a').html('Turn Debug <strong>Off</strong>');
			omega('body').addClass('show-grid');
			debug_next_state = "Off";
			console.log('Overlay Turned On...');
			return false;
		}	
	});
	
	// show info on hovering region
	omega('.has-grid .grid-1, .has-grid .grid-2, .has-grid .grid-3, .has-grid .grid-4, .has-grid .grid-5, .has-grid .grid-6, .has-grid .grid-7, .has-grid .grid-8, .has-grid .grid-9, .has-grid .grid-10, .has-grid .grid-11, .has-grid .grid-12, .has-grid .grid-13, .has-grid .grid-14, .has-grid .grid-15, .has-grid .grid-16, .has-grid .grid-17, .has-grid .grid-18, .has-grid .grid-19, .has-grid .grid-20, .has-grid .grid-21, .has-grid .grid-22, .has-grid .grid-23, .has-grid .grid-24')
	.hover(function(){
		if(omega(this).parents('body').hasClass('show-grid')) {
			var grid_classes = '';
			var i = 1;
			var grid_size = 'undefined';
			for(i = 1; i <= 24; i++) {
				var grid_size_test = 'grid-'+i; 
				if(omega(this).hasClass(grid_size_test)) {
					var grid_size = grid_size_test;
				}
			}
			
			var container_parent = omega(this).parents('div[class*=container]');
			if(container_parent.hasClass('container-12')) {
				var container_size = 'container-12';
			}
			if(container_parent.hasClass('container-16')) {
				var container_size = 'container-16';
			}
			if(container_parent.hasClass('container-24')) {
				var container_size = 'container-24';
			}
			omega('#omega-debug-info').html('<hr /><div><strong>Container class: '+container_size+'</div><div><strong>Grid class: </strong>'+grid_size+'</div>');
			//omega('#omega-debug-info').html(container_info);
		}
	}, function(){
		if(omega(this).parents('body').hasClass('show-grid')) {
			omega('#omega-debug-info').html('');
		}
	});
	
};

omega(document).ready(function(){
	var debug = new Drupal.grid_debug();
});
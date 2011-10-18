/*********************************************************
 *	This plugin takes text and makes it editable on click
 *********************************************************/
 (function( $ ){
		var methods = {
			init : function( options ) {
				// need to encapsulate these in each item
				var settings = {
					url:                  "/wp-admin/admin-ajax.php", // string: POST URL to send adjusted amount
					action:               "guthrie_update_profile_field_value", // string: POST URL to send adjusted amount
					input_width:          "134", // integer: value for width
					element_id:           "element_id", // string: the id of the element to insert the returned value in
					field_id_name:	      "id", // string: the id of the element to insert the returned value in
					field_value_name:     "value", // string: parameter name for the adjustment value
					original_field_value: "original_value" // string: parameter name for the adjustment value
				};
				if ( options ) {
					$.extend( settings, options );
				}

				return this.each(function() {
					var $this = $(this), data = $this.data("settings");

					 // If the plugin hasn't been initialized yet
					 if ( ! data ) {
							$this.data("settings",settings);
							$this.bind('click.fieldEvents', methods.setup_control);
							$this.addClass("edit-in-place");

							var field_value = $this.html();
							$this.html('<span class="editable">' + field_value + '</span>');
					}
				});
			},

			/******************************************************
			 ** Destroy us, everything
			 ******************************************************/
			destroy : function () {
				var $this = $(this);
				while($this.data("settings") == null){
					$this = $this.parent();
				}
				$this.unbind(".fieldEvents");
				$this.removeData();
			},

			/******************************************************
			 ** Set's up the control when it is needed
			 ******************************************************/
			setup_control : function(){
				var $this = $(this);
				while($this.data("settings") == null){
					$this = $this.parent();
				}
				$this.unbind('click.fieldEvents');

				
				var settings = $this.data("settings");
				//var original_field_value = $this.html();
				var original_field_value = $this.find(".editable").html();
				var html = '<input type="text" class="field-input editing" value="' + original_field_value + '" />' +
				           '<div class="debug"></div>' +
				           '<div class="log"></div>';
				$this.html(html);
				$this.css("z-index",9999);

				var field_input = $this.find(".field-input");
				field_input.width(settings.input_width);

				field_input.bind('blur.fieldEvents', methods.blur_handler);
				field_input.focus();
				$this.data("field-input", field_input);
				$this.data("original-field-value", original_field_value);
			},
			
			/******************************************************
			 ** Save our changes and break us down
			 ******************************************************/
			save_changes : function(event){
				var $this = $(event.target);
				while($this.data("settings") == null){
					$this = $this.parent();
				}


				var field_input = $this.data("field-input");
				var field_value = field_input.val();
				var original_field_value = $this.data("original-field-value");
				var id = $this.attr("id");
				var field_id = id.substring(id.lastIndexOf("_") + 1);

				field_input.unbind('blur.fieldEvents');

				if( field_value !== original_field_value){
					methods.submit_to_server($this, field_id, field_value, original_field_value);
				}
				methods.breakdown_control(event);
			},

			/******************************************************
			 ** Handles blur events within the control, 
			 ** decides if we need to break down or not
			 ******************************************************/
			blur_handler : function (event){
				methods.save_changes(event);
			},

			/******************************************************
			 ** Destroy most of us, 
			 ** return to only displaying the time
			 ******************************************************/
			breakdown_control : function (event){
				var $this = $(event.target);
				while($this.data("settings") == null){
					$this = $this.parent();
				}

				
				$this.unbind('blur.fieldEvents');

				var field_input = $this.data("field-input");
				var field_value = field_input.val();
				$this.html('<span class="editable">' + field_value + '</span>');

				$this.data("field-input", null);
				$this.data("original-field-value", null);

				$this.css("z-index",1);
				event.stopPropagation();
				$this.bind('click.fieldEvents', methods.setup_control);

			},

			/******************************************************
			 ** Submits our data to the server
			 ******************************************************/
			submit_to_server: function(element, field_id, new_value, original_value) {
				var $this = $(element);
				var settings = $this.data("settings");
				var data = '';
				var url = settings.url;
				var element_id = $this.attr("id"); 

				data += settings.element_id + '=' + encodeURIComponent(element_id)
					+ '&action=' + encodeURIComponent(settings.action)
					+ '&' + settings.original_field_value + '=' + encodeURIComponent(original_value)
					+ '&' + settings.field_value_name + '=' + encodeURIComponent(new_value)
					+ '&' + settings.field_id_name + '=' + encodeURIComponent(field_id);

				$.ajax({
					url: url,
					type: "POST",
					data: data,
					dataType: "json",
					complete: function( response ) {
						//alert( response.responseText );
					}
				});
			},
 
			/******************************************************
			 ** Log panel, always appends to previous content
			 ******************************************************/
			log : function(slider, message) {
				var log = slider.find(".log");
				log.prepend(message + "<br />");
			},
	
			/******************************************************
			 ** Debug panel, always overwrites previous content
			 ******************************************************/
			debug : function(slider, message) {
				var debug = slider.find(".debug");
				debug.html(message);
			}
		};

			
		/******************************************************
		 ** Where it all starts, calls the correct method
		 ******************************************************/
		$.fn.guthrieEditInPlace = function( method ) {
			if ( methods[method] ) {
				return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
			} else if ( typeof method == 'object' || ! method ) {
				return methods.init.apply( this, arguments );
			} else {
				$.error( 'Method ' +	method + ' does not exist on jQuery.clocklogEditInPlace' );
			}
		};
})( jQuery );

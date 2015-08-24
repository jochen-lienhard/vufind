;(function ( $, window, document, undefined ) {

	var autoheighter = "autoheighter";
	defaults = {};

	// The actual plugin constructor
	function Autoheighter( element, options ) {

		autoheighter = this;
		autoheighter.el = element;
		$autoheightContainer = $(autoheighter.el);
		autoheighter.options = $.extend( {}, defaults, options) ;
		autoheighter._defaults = defaults;
		autoheighter.maxHeight = 0;
		autoheighter.heightGridValue = 75;
		autoheighter.offsetValue = 8;
		autoheighter.init();

	}

	Autoheighter.prototype = {

		init: function() {
			autoheighter._adjustAllHeightSizes();
		},

		_adjustAllHeightSizes: function() {
			$autoheightContainer.find(autoheighter.options.elemSelector).each(function() {
				var originalElemHeight = $(this).outerHeight();
				var gridFactor = Math.ceil(originalElemHeight / autoheighter.heightGridValue);
				if(gridFactor > 3) {
					gridFactor = 3;
				}
				//var newElemHeight = (gridFactor * autoheighter.heightGridValue) + (gridFactor - 1) * autoheighter.offsetValue;
				//var elemOffset = (gridFactor - 1) * autoheighter.offsetValue;
				//var newElemHeight = (gridFactor * autoheighter.heightGridValue) + elemOffset;
				$(this).addClass('autoheight-grid-'+gridFactor);
			});
		},

	};

	$.fn[autoheighter] = function ( options ) {
		return this.each(function () {
			if (!$.data(this, "plugin_" + autoheighter)) {
				$.data(this, "plugin_" + autoheighter,
					new Autoheighter( this, options ));
			}
		});
	};

})( jQuery, window, document );
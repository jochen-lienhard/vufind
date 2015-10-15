// General settings and constants
var GLOBALS = {
	'OS': window.navigator.platform,
	'IS_MAC': window.navigator.platform.toUpperCase().indexOf('MAC')!==-1,
	'IS_WINDOWS': window.navigator.platform.toUpperCase().indexOf('WIN')!==-1,
	'IS_LINUX': window.navigator.platform.toUpperCase().indexOf('LINUX')!==-1,
	'TOUCH_DEVICE': !!(Modernizr.touch || window.navigator.userAgent.indexOf("Windows Phone") > 0),
	'CLICK_EVENT': 'click', // default click event. Will be replaced by touch event when on touch screens
	'IS_RETINA': window.devicePixelRatio > 1 || (window.matchMedia && window.matchMedia("(-webkit-min-device-pixel-ratio: 1.5),(-moz-min-device-pixel-ratio: 1.5),(min-device-pixel-ratio: 1.5)").matches),
	'SVG_SUPPORT': Modernizr.svg
}

$(function() {

	// globals
	if(GLOBALS.TOUCH_DEVICE) {
		GLOBALS.CLICK_EVENT = 'touchstart';
	}

	var urlParams = [], hash;
	var params = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
	var hashTag = window.location.hash.split('?')[0];
	console.log(hashTag);
	for(var i = 0; i < params.length; i++)
	{
		param = params[i].split('=');
		if(param.length > 1) {
			urlParams.push(param[0]);
			urlParams[param[0]] = decodeURIComponent(param[1]);
		}
	}

	if(hashTag) {
		setTimeout(function() {
			window.scrollTo(0, 0);
		}, 1);
	}

	$('.accordion').each(function() {
		$(this).find('.accordion-item').each(function(index) {
			$(this).attr('data-index', index);
		});
	});


	$(".accordion").accordion({
		header: '.accordion-head',
		heightStyle: 'content',
		active: false,
		navigation: false,
		collapsible: true
	});

	$('.accordion').each(function() {
		if(hashTag) {
			var accordionIndex = $(hashTag).data('index');
			$(this).accordion('option', 'active', accordionIndex);
		} else {
			if($(this).data('init-active') !== false) {
				$(this).accordion("option", "active", 0);
			}
		}
	});

	$(".tabs" ).tabs();

	if(hashTag) {
		var hashTagOffset = $(hashTag).offset().top;
		//console.log(hashTagOffset);
		//$(hashTag).hide();
		$('html, body').animate({
			scrollTop: hashTagOffset
		}, 1000);
		//$(hashTag).show();
	}



	$('#mainNavigation').navbar({
		clickListener: GLOBALS.CLICK_EVENT,
		mainLevelContainer: '.level-0',
		mainLevelATag: '.level-0 > li > a',
		openStateClass: 'open-state',
		subLevelContainer: '.level-1'

	});

	$('.ui-trigger').on(GLOBALS.CLICK_EVENT, function(e) {
		e.preventDefault();
		var action = $(this).data('action');
		var target = $(this).data('target');
		var handlerClass = $(this).data('handler-class');

		if(action === 'close') {
			console.log(target);
			console.log(handlerClass);
			$(target).removeClass(handlerClass);
		}

	})

	$('#metaNavigation').metaNavigation({
		linkSelector: 'a',
		metaLayer: '#metaNavigationPanel',
		metaContentContainer: '.meta-content',
		controlSelector: '#pageHead',
		openClassSelector: 'open-meta',
		clickListener: GLOBALS.CLICK_EVENT
	});


	//var alphaPager = [];
	//$('.data-table').find('.alpha').each(function() {
	//	alphaPager.push($(this).text());
	//});
	//
	//alphaPager = $.unique(alphaPager);
	//$.each(alphaPager, function(index, value) {
	//	$('.table-controls ul').append('<li>' + value + '</li>');
	//})


	$('.data-table').DataTable({
		"pagingType": "simple",
		"pageLength": 25,
		"order": [[ 0, "asc" ]],
		"language": {
			"lengthMenu": "_MENU_ Zeilen pro Seite",
			"info": "Seite _PAGE_ von _PAGES_",
			"sSearch": "Suchen",
			"paginate": {
				"next": "Nächste",
				"previous": "Vorherige"
			}
		}
		//"columnDefs": [
		//	{ "visible": false, "targets": 0 }
		//]
	});


	$('.autoheight-grid-container').autoheighter({
		'elemSelector': '.autoheight-grid-element'
	});



	$('#toPageTop a').on(GLOBALS.CLICK_EVENT, function(e) {
		e.preventDefault();
		var scrolltarget = $(this).attr('href');
		$('html,body').animate({
			scrollTop: $(scrolltarget).offset().top
		}, 1000);
	});




	var lastScrollTop = 0;
	$(window).scroll(function(e){
		var st = $(this).scrollTop();
		var offset = $(window).height();
		if(st < offset) {
			$('#toPageTop').removeClass('show');
		} else {
			$('#toPageTop').addClass('show');
		}
		lastScrollTop = st;
	});


	$('.news-teaser-item.gallery-teaser').each(function() {
		var imageSource = $(this).css('background-image').replace(/url\((['"])?(.*?)\1\)/gi, '$2').split(',')[0];
		var image = new Image();
		image.src = imageSource;
		var boxContainer = $(this);
		$(image).load(function() {
			$(boxContainer).css('height', image.height);
		});
	});


	lightbox.option({
		'fadeDuration': 200,
		'positionFromTop': 600,
		'resizeDuration': 200,
		'showImageNumberLabel': false,
		'wrapAround': true
	})


	$(".image-slider").royalSlider({
		// options go here
		// as an example, enable keyboard arrows nav
		keyboardNavEnabled: true,
		autoScaleSlider: true,
		//autoScaleSliderWidth: 100,
		imageScaleMode: 'fill',
		imageScalePadding: 0,
		controlNavigation: 'bullets',
		arrowsNav: true,
		slidesSpacing: 0,
		loop: true,
		transitionType: 'move',
		transitionSpeed: 1200,
		globalCaption: true,
		usePreloader: true,
		numImagesToPreload: 2,
		autoPlay: {
			enabled: true,
			pauseOnHover: true,
			delay: 8000
		}
	});


	// minisearch input prompt
	$('.input-prompt').on('focusin', function () {
		$(this).attr('placeholder', '');
	});

	$('.input-prompt').on('focusout', function () {
		$(this).attr('placeholder', $(this).data().placeholder);
	});

	if($('.input-flush input').val().length > 0) {
		$('.input-flush .flush-button').removeClass('hidden');
	}

	$('.input-flush input').on('input', function () {
		if($(this).val()) {
			$(this).parent().find('.flush-button').removeClass('hidden');
		} else {
			$(this).parent().find('.flush-button').addClass('hidden');
		}
	});

	$('.input-flush .flush-button').on(GLOBALS.CLICK_EVENT, function() {
		$(this).addClass('hidden');
		$(this).parent().find('input').val('').focus();
	})

	// hightlight given search strings from url param hl
	if(urlParams['hl']) {
		$.each(urlParams['hl'].split('+'), function(i, phrase) {
			$('#contentWrapper').highlight(phrase);
		})
	}


	$('.content-slider .royalSlider').royalSlider({
		autoHeight: true,
		arrowsNav: true,
		arrowsNavAutoHide: false,
		fadeinLoadedSlide: false,
		controlNavigationSpacing: 0,
		controlNavigation: true,
		imageScaleMode: 'none',
		imageAlignCenter:false,
		loop: false,
		loopRewind: true,
		numImagesToPreload: 6,
		keyboardNavEnabled: true,
		usePreloader: false,
		slidesSpacing: 40,
	});

	$('.content-slider li').on(GLOBALS.CLICK_EVENT, function() {
		var slider = $(this).closest('.content-slider').find('.royalSlider').data('royalSlider');
		var slideId = $(this).data().slideId;
		slider.goTo(slideId);
		console.log();
	});

	var contentSlider = $('.content-slider .contentSlider').data('royalSlider');
	if(contentSlider) {
		contentSlider.ev.on('rsAfterSlideChange', function(event) {
			var sliderElem = event.target.slider;
			var sliderNav = $(sliderElem).parent().find('.content-slider-nav');
			var contentSliderContainer = $(sliderElem).parent();
			var activeSlide = event.target.currSlideId;
			$(contentSliderContainer).attr('data-active-slide', activeSlide);
			$(sliderNav).find('li').each(function (index) {
				if(index === activeSlide) {
					$(this).addClass('active');
				} else {
					$(this).removeClass('active');
				}
			});
		})
	}


	$('.bibs-by-schlagwort .schlagwort-title').on(GLOBALS.CLICK_EVENT, function() {
		$(this).parent().toggleClass('active');
	})

	// add several event listeners for behaviour in frontend
	// add click listener outside mainnavigation
	$(document).on(GLOBALS.CLICK_EVENT, function(e) {
		// click outside mainnavigation, trigger event
		if(!$(e.target).closest($('#mainNavigation > ul.level-0')).length) {
			$.event.custom.create('clickOutsideMainnavigation');
		}
		if(!$(e.target).closest($('#metaNavigation')).length && !$(e.target).closest($('#metaNavigationPanel > .container')).length) {
			$.event.custom.create('clickOutsideMetanavigation');
		}
	})


	// collapse search field on page catalog
	$('#searchSlot.collapsed').on(GLOBALS.CLICK_EVENT, function(e) {
		if($(this).hasClass('collapsed')) {
			e.preventDefault();
			$(this).removeClass('collapsed');
		}
	})


})

;(function ( $, window, document, undefined ) {
	$.event.custom = {
		create: function(eventName) {
			$.event.trigger({
				type: eventName
			});
		}
	}
})( jQuery, window, document );
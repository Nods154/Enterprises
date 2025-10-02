/*
 * portfolio_detail.js
 * ---
 * @author takaaki.koyama
 * ---
 * ---------------------------------------------
 * (c) baqemono.inc. all rights reserved.
 * ---------------------------------------------
 */
(function(){

	function init(target){
		var $target = $(target);
		$target.data('PhotoSlider', true);

		$target.find('#images').addClass('slider');

		var $items = $target.find('#images li'),
			numImages = $items.length,
			current = 0,
			$itemContainer = $target.find('#images ul')
			itemWidth = 100/numImages;

		if(numImages == 1) return;

		$itemContainer.width(numImages*100+'%');
		$items.width(itemWidth+'%').each(function(i){
			$(this).css('left', (i/numImages*100)+'%')
		});
		$target.find('#images').append('<div class="cover prev"></div><div class="cover next"></div>');

		$target.append(createControl(numImages));

		var $controlButtons = $target.find('#images-control a');
		$controlButtons.on('click.photo-slider', controlClickHandler);
		
		$target.find('.cover').on({
			'click.photo-slider' : coverClickHandler,
			'mouseover.photo-slider touchstart.photo-slider' : coverMouseOverHandler,
			'mouseout.photo-slider touchend.photo-slider' : coverMouseOutHandler
		});

		setCurrent();

		function setCurrent(){
			$controlButtons.removeClass('current');
			$controlButtons.eq(current).addClass('current');
		}


		function slide(to){
			$itemContainer.css(Modernizr.prefixedCSS('transform'),'translate('+(-to*itemWidth)+'%, 0)');
			current = to;
			setCurrent();
			if(current == 0 || current == numImages-1){
				$itemContainer.addClass('edge');
			}
		}

		function coverMouseOverHandler(){
			var isPrev  = $(this).hasClass('prev');
			if(isPrev && current == 0) return;
			if(!isPrev && current == numImages-1) return;

			$itemContainer.removeClass('edge');
			$itemContainer.addClass(isPrev? 'hover-prev' : 'hover-next')
		}

		function coverMouseOutHandler(){
			$itemContainer.removeClass('hover-prev hover-next');
		}

		function coverClickHandler(e){
			e.preventDefault();
			e.stopPropagation();

			var isPrev  = $(this).hasClass('prev');
			if(isPrev && current == 0) return;
			if(!isPrev && current == numImages-1) return;
			var to = (numImages+current+( isPrev? -1 : 1) )%numImages;
			slide(to);
		}

		function controlClickHandler(e){
			e.preventDefault();
			e.stopPropagation();
			if($(this).hasClass('current')) return;

			var index = $controlButtons.index($(this));
			slide(index);
		}

	}

	function createControl(numImages){
		var html = ['<ul id="images-control">']
		for (var i = 0; i < numImages; i++) {
			html[html.length] = '<li><a href="#"></a></li>'
		};
		html[html.length] = '</ul>';
		return html.join('');
	}


	$(function(){
		$('#news-detail #photo').each(function(){
			if(!$(this).data('PhotoSlider')){
				init(this);
			}
		});
	});
})(jQuery);
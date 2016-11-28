/**
 * jQuery slider plugin
 *
 * 	Depends:
 *	   - jQuery 1.4.2+
 * 	author:
 *     - lanqy 2016-1-21
 */
(function($) {

    $.extend($.fn, {
        slider: function(options) {
            return this.each(function() {
                var slider = $.data(this, "slider");
                if (!slider) {
                    slider = new $.slider(options, this);
                    $.data(this, "slider", slider);
                }
            });
        }
    });

    $.slider = function(options, el) {
        if (arguments.length) {
            this._init(options, el);
        }
    }

    $.slider.prototype = {

        options: {
            containerEl: $(".index-banner-scroll"),
            silderContainerEl: $("#index-banner ul"),
            itemEl: $("#index-banner ul li"),
            btnEl:$('.control span'),
            btnContainerEl:$('.control'),
            prev: $(".left"),
            next: $(".right"),
            index: 0,
            resizeable:true,
            showButton: true,
            timer: ''
        },

        _init: function(options,el) { // init
            this.options = $.extend(true, {}, this.options, options)
            this.element = $(el);
            this._bindEvents();
            this._initBtnPos();
            this._initContainerMarginLeft();
            this._slider(this.options.index);
        },

        _bindEvents: function() { // bind event
            var self = this;
            this.options.containerEl.hover(function() {
                clearInterval(self.options.timer);
            }, function() {
                self._setInterVal();
            }).trigger("mouseleave");

            if(this.options.resizeable){ // if resize
                $(window).resize(function() {
                  self._resize();
                });
            }

            this.options.next.click(function(){
              self._next();
            });

            this.options.prev.click(function(){
              self._prev();
            });

            this.options.next.hover(function(){
              clearInterval(self.options.timer);
            },function(){
              self._setInterVal();
            });

            this.options.prev.hover(function(){
              clearInterval(self.options.timer);
            },function(){
              self._setInterVal();
            });

            this.options.btnEl.hover(function(){
                self._slider($(this).index());
            });
        },

        _setInterVal: function(){
          var self = this;
            this.options.timer = setInterval(function() {
              self._scroll();
            }, 5000);
        },

        _scroll: function(){
            var index = this._getCurrentIndex();
              this._slider(index);
              this._setCurrentIndex((index+1));
              if (this._getCurrentIndex() == this._getSliderSize()) {
                  this._setCurrentIndex(0);
              }
        },

        _prev: function() { // prevsion
            var index = this._getCurrentIndex();
            var len = this._getSliderSize() - 1;
            index === 0 ?  index = len : index = index - 1;
            this._setCurrentIndex(index);
            this._slider(index);
        },

        _next: function() { // next
          var index = this._getCurrentIndex();
          var len = this._getSliderSize() - 1;
          index === len ?  index = 0 : index = index + 1;
          this._setCurrentIndex(index);
          this._slider(index);
        },

        _setCurrentIndex: function(index) {
            this.options.index = index;
        },

        _setIndex: function(index){
          this._setCurrentIndex(index);
          this._slider(this._getCurrentIndex());
        },

        _slider: function(index) { // do slider when ready
            var self = this;
            var nowLeft = -index * this._getContainerWidth();
            this.options.silderContainerEl.stop(true, false).animate({
                "left": nowLeft + "px"
            }, 300);
            this.options.btnEl.removeClass("active").eq(index).addClass("active");
            this.options.btnEl.stop(true, false).animate({
                "opacity": "1"
            }, 300).eq(index).stop(true, false).animate({
                "opacity": "1"
            }, 300);

            this._setCurrentIndex(index++);
        },

        _getSliderSize: function() {
            return this.options.itemEl.length;
        },

        _getWinWidth: function() { // get window width
            return $(window).width();
        },

        _getContainerWidth: function() { // get container width
            return this.options.containerEl.width()
        },

        _initBtnPos: function(){
          var a = this._getWinWidth(), c = this._getBtnOutwidth();
          this._btnPosition(a,c);
        },

        _initContainerMarginLeft: function(){
          var a = this._getWinWidth();
          var b =  this._getContainerWidth();
          this._containerPos(a,b);
        },

        _getCurrentIndex: function() { // get current index
            return this.options.index;
        },

        _getBtnOutwidth: function() {
            return this.options.btnContainerEl.outerWidth();
        },

        _resize: function() {
            var a = this._getWinWidth(), b = this._getContainerWidth(), c = this._getBtnOutwidth();
            this._containerPos(a,b);
            this._btnPosition(a,c);
        },

        _containerPos: function(a,b){
          this.options.containerEl.css("margin-left", (a - b) / 2 + "px");
        },

        _btnPosition: function(a,c){
            this.options.btnContainerEl.css("left", (a - c) / 2 + "px");
        }
    }

    $.extend($.fn, {
        setIndex: function(cb) {
            $(this).data("slider") && $(this).data("slider")._setIndex(index)
        }
    });

})(jQuery);
; (function ($) {
    $.extend({
        'foucs': function (con) {
            var $container = $('#index_b_hero')
                , $imgs = $container.find('li.hero')
            , $leftBtn = $container.find('a.prev')
            , $rightBtn = $container.find('a.next')
            , config = {
                interval: con && con.interval || 3500,
                animateTime: con && con.animateTime || 500,
                direction: con && (con.direction === 'right'),
                _imgLen: $imgs.length
            }
            , i = 0
            , getNextIndex = function (y) { return i + y >= config._imgLen ? i + y - config._imgLen : i + y; }
            , getPrevIndex = function (y) { return i - y < 0 ? config._imgLen + i - y : i - y; }
            , silde = function (d) {
                $imgs.eq((d ? getPrevIndex(2) : getNextIndex(2))).css('left', (d ? '-3840px' : '3840px'))
                $imgs.animate({
                    'left': (d ? '+' : '-') + '=1920px'
                }, config.animateTime);
                i = d ? getPrevIndex(1) : getNextIndex(1);
            }
            , s = setInterval(function () { silde(config.direction); }, config.interval);
            $imgs.eq(i).css('left',0).end().eq(i + 1).css('left', '1920px').end().eq(i - 1).css('left', '-1920px');
            $container.find('.hero-wrap').add($leftBtn).add($rightBtn).hover(function () { clearInterval(s); }, function () { s = setInterval(function () { silde(config.direction); }, config.interval); });
            $leftBtn.click(function () {
                if ($(':animated').length === 0) {
                    silde(false);
                }
            });
            $rightBtn.click(function () {
                if ($(':animated').length === 0) {
                    silde(true);
                }
            });
        }
    });
}(jQuery));
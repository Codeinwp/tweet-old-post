var timenow="";
(function() {

  (function($) {
    $.countdown = function(el, options) {
      var getDateData,
        _this = this;
      this.el = el;
      this.$el = $(el);
      this.$el.data("countdown", this);
      this.init = function() {
        _this.options = $.extend({}, $.countdown.defaultOptions, options);
        if (_this.options.refresh) {
          _this.interval = setInterval(function() {
            return _this.render();
          }, _this.options.refresh);
        }
        _this.render();
        return _this;
      };

      getDateData = function(endDate) {
        var dateData, diff;
        jQuery.ajax({
          type: "POST",
          url: cwp_top_ajaxload.ajaxurl,
          data: {
            action: "gettime_action"
          },
          success: function(response) {
            timenow = response;
          },
          error: function(MLHttpRequest, textStatus, errorThrown) {
            console.log("There was an error: "+errorThrown);
          }
        });

        //var timenow = new Date(<?php echo time(); ?>*1000)
        //var timenow = new Date( d1.getUTCFullYear(), d1.getUTCMonth(), d1.getUTCDate(), d1.getUTCHours(), d1.getUTCMinutes(), d1.getUTCSeconds() );
        //endDate = Date.parse($.isPlainObject(_this.options.date) ? _this.options.date : new Date(_this.options.date));
        endDate = _this.options.date;

        if (timenow == '')
          timenow = _this.options.date-20;

        diff = endDate - timenow;
        //diff = Math.floor(diff);
        if (diff <= 0) {
          diff = 0;
          if (_this.interval) {
            _this.stop();
          }
          _this.options.onEnd.apply(_this);
        }
        dateData = {
          years: 0,
          days: 0,
          hours: 0,
          min: 0,
          sec: 0,
          millisec: 0
        };
        if (diff >= (365.25 * 86400)) {
          dateData.years = Math.floor(diff / (365.25 * 86400));
          diff -= dateData.years * 365.25 * 86400;
        }
        if (diff >= 86400) {
          dateData.days = Math.floor(diff / 86400);
          diff -= dateData.days * 86400;
        }
        if (diff >= 3600) {
          dateData.hours = Math.floor(diff / 3600);
          diff -= dateData.hours * 3600;
        }
        if (diff >= 60) {
          dateData.min = Math.floor(diff / 60);
          diff -= dateData.min * 60;
        }
        dateData.sec = diff;
        return dateData;
      };
      this.leadingZeros = function(num, length) {
        if (length == null) {
          length = 2;
        }
        num = String(num);
        while (num.length < length) {
          num = "0" + num;
        }
        return num;
      };
      this.update = function(newDate) {
        _this.options.date = newDate;
        return _this;
      };
      this.render = function() {
        _this.options.render.apply(_this, [getDateData(_this.options.date)]);
        return _this;
      };
      this.stop = function() {
        if (_this.interval) {
          clearInterval(_this.interval);
        }
        _this.interval = null;
        jQuery(".cwp_top_container .nextTweet").html('Your post was just sent to social networks servers, wait 15s for a confirmation below. Refresh the page to see when the next one will be posted.');

        return _this;
        //return _this;
      };
      this.start = function(refresh) {
        if (refresh == null) {
          refresh = _this.options.refresh || $.countdown.defaultOptions.refresh;
        }
        if (_this.interval) {
          clearInterval(_this.interval);
        }
        _this.render();
        _this.options.refresh = refresh;
        _this.interval = setInterval(function() {
          return _this.render();
        }, _this.options.refresh);
        return _this;
      };
      return this.init();
    };
    $.countdown.defaultOptions = {
      date: "June 7, 2087 15:03:25",
      refresh: 2000,
      onEnd: $.noop,
      render: function(date) {
        if (date.days!=0 || date.hours!=0 || date.min!=0 || date.sec!=0)
        return $(this.el).html("" +  date.days + " days, " + (this.leadingZeros(date.hours)) + " hours, " + (this.leadingZeros(date.min)) + " min and " + (this.leadingZeros(date.sec)) + " sec");
      }
    };
    $.fn.countdown = function(options) {
      return $.each(this, function(i, el) {
        var $el;
        $el = $(el);
        if (!$el.data('countdown')) {
          return $el.data('countdown', new $.countdown(el, options));
        }
      });
    };
    return void 0;
  })(jQuery);

}).call(this);


;(function(factory) {
  'use strict';

  if (typeof define === 'function' && define.amd) {
    define(['jquery'], factory);
  } else {
    factory(jQuery);
  }
})(function($){
  'use strict';

  var PRECISION   = 100; // 0.1 seconds, used to update the DOM
  var instances   = [],
      matchers    = [];
  // Miliseconds
  matchers.push(/^[0-9]*$/.source);
  // Month/Day/Year [hours:minutes:seconds]
  matchers.push(/([0-9]{1,2}\/){2}[0-9]{4}( [0-9]{1,2}(:[0-9]{2}){2})?/
      .source);
  // Year/Day/Month [hours:minutes:seconds] and
  // Year-Day-Month [hours:minutes:seconds]
  matchers.push(/[0-9]{4}([\/\-][0-9]{1,2}){2}( [0-9]{1,2}(:[0-9]{2}){2})?/
      .source);
  // Cast the matchers to a regular expression object
  matchers = new RegExp(matchers.join('|'));
  // Parse a Date formatted has String to a native object
  function parseDateString(dateString) {
    // Pass through when a native object is sent
    if(dateString instanceof Date) {
      return dateString;
    }
    // Caste string to date object
    if(String(dateString).match(matchers)) {
      // If looks like a milisecond value cast to number before
      // final casting (Thanks to @msigley)
      if(String(dateString).match(/^[0-9]*$/)) {
        dateString = Number(dateString);
      }
      // Replace dashes to slashes
      if(String(dateString).match(/\-/)) {
        dateString = String(dateString).replace(/\-/g, '/');
      }
      return new Date(dateString);
    } else {
      throw new Error('Couldn\'t cast `' + dateString +
      '` to a date object.');
    }
  }
  // Map to convert from a directive to offset object property
  var DIRECTIVE_KEY_MAP = {
    'Y': 'years',
    'm': 'months',
    'w': 'weeks',
    'd': 'days',
    'D': 'totalDays',
    'H': 'hours',
    'M': 'minutes',
    'S': 'seconds'
  };
  // Returns an escaped regexp from the string
  function escapedRegExp(str) {
    var sanitize = str.toString().replace(/([.?*+^$[\]\\(){}|-])/g, '\\$1');
    return new RegExp(sanitize);
  }
  // Time string formatter
  function strftime(offsetObject) {
    return function(format) {
      var directives = format.match(/%(-|!)?[A-Z]{1}(:[^;]+;)?/gi);
      if(directives) {
        for(var i = 0, len = directives.length; i < len; ++i) {
          var directive   = directives[i]
                  .match(/%(-|!)?([a-zA-Z]{1})(:[^;]+;)?/),
              regexp    = escapedRegExp(directive[0]),
              modifier  = directive[1] || '',
              plural    = directive[3] || '',
              value     = null;
          // Get the key
          directive = directive[2];
          // Swap shot-versions directives
          if(DIRECTIVE_KEY_MAP.hasOwnProperty(directive)) {
            value = DIRECTIVE_KEY_MAP[directive];
            value = Number(offsetObject[value]);
          }
          if(value !== null) {
            // Pluralize
            if(modifier === '!') {
              value = pluralize(plural, value);
            }
            // Add zero-padding
            if(modifier === '') {
              if(value < 10) {
                value = '0' + value.toString();
              }
            }
            // Replace the directive
            format = format.replace(regexp, value.toString());
          }
        }
      }
      format = format.replace(/%%/, '%');
      return format;
    };
  }
  // Pluralize
  function pluralize(format, count) {
    var plural = 's', singular = '';
    if(format) {
      format = format.replace(/(:|;|\s)/gi, '').split(/\,/);
      if(format.length === 1) {
        plural = format[0];
      } else {
        singular = format[0];
        plural = format[1];
      }
    }
    if(Math.abs(count) === 1) {
      return singular;
    } else {
      return plural;
    }
  }
  // The Final Countdown
  var Countdown = function(el, finalDate, callback) {
    this.el       = el;
    this.$el      = $(el);
    this.interval = null;
    this.offset   = {};
    // Register this instance
    this.instanceNumber = instances.length;
    instances.push(this);
    // Save the reference
    this.$el.data('countdown-instance', this.instanceNumber);
    // Register the callbacks when supplied
    if(callback) {
      this.$el.on('update.countdown', callback);
      this.$el.on('stoped.countdown', callback);
      this.$el.on('finish.countdown', callback);
    }
    // Set the final date and start
    this.setFinalDate(finalDate);
    this.start();
  };
  $.extend(Countdown.prototype, {
    start: function() {
      if(this.interval !== null) {
        clearInterval(this.interval);
      }
      var self = this;
      this.update();
      this.interval = setInterval(function() {
        self.update.call(self);
      }, PRECISION);
    },
    stop: function() {
      clearInterval(this.interval);
      this.interval = null;
      this.dispatchEvent('stoped');
    },
    toggle: function() {
      if (this.interval) {
        this.stop();
      } else {
        this.start();
      }
    },
    pause: function() {
      this.stop();
    },
    resume: function() {
      this.start();
    },
    remove: function() {
      this.stop.call(this);
      instances[this.instanceNumber] = null;
      // Reset the countdown instance under data attr (Thanks to @assiotis)
      delete this.$el.data().countdownInstance;
    },
    setFinalDate: function(value) {
      this.finalDate = parseDateString(value); // Cast the given date
    },
    update: function() {
      // Stop if dom is not in the html (Thanks to @dleavitt)
      if(this.$el.closest('html').length === 0) {
        this.remove();
        return;
      }
      // Calculate the remaining time
      this.totalSecsLeft = this.finalDate.getTime() -
      new Date().getTime(); // In miliseconds
      this.totalSecsLeft = Math.ceil(this.totalSecsLeft / 1000);
      this.totalSecsLeft = this.totalSecsLeft < 0 ? 0 : this.totalSecsLeft;
      // Calculate the offsets
      this.offset = {
        seconds   : this.totalSecsLeft % 60,
        minutes   : Math.floor(this.totalSecsLeft / 60) % 60,
        hours     : Math.floor(this.totalSecsLeft / 60 / 60) % 24,
        days      : Math.floor(this.totalSecsLeft / 60 / 60 / 24) % 7,
        totalDays : Math.floor(this.totalSecsLeft / 60 / 60 / 24),
        weeks     : Math.floor(this.totalSecsLeft / 60 / 60 / 24 / 7),
        months    : Math.floor(this.totalSecsLeft / 60 / 60 / 24 / 30),
        years     : Math.floor(this.totalSecsLeft / 60 / 60 / 24 / 365)
      };
      // Dispatch an event
      if(this.totalSecsLeft === 0) {
        this.stop();
        this.dispatchEvent('finish');
      } else {
        this.dispatchEvent('update');
      }
    },
    dispatchEvent: function(eventName) {
      var event = $.Event(eventName + '.countdown');
      event.finalDate     = this.finalDate;
      event.offset        = $.extend({}, this.offset);
      event.strftime      = strftime(this.offset);
      this.$el.trigger(event);
    }
  });
  // Register the jQuery selector actions
  $.fn.countdownPlugin = function() {
    var argumentsArray = Array.prototype.slice.call(arguments, 0);
    return this.each(function() {
      // If no data was set, jQuery.data returns undefined
      var instanceNumber = $(this).data('countdown-instance');
      // Verify if we already have a countdown for this node ...
      // Fix issue #22 (Thanks to @romanbsd)
      if (instanceNumber !== undefined) {
        var instance = instances[instanceNumber],
            method = argumentsArray[0];
        // If method exists in the prototype execute
        if(Countdown.prototype.hasOwnProperty(method)) {
          instance[method].apply(instance, argumentsArray.slice(1));
          // If method look like a date try to set a new final date
        } else if(String(method).match(/^[$A-Z_][0-9A-Z_$]*$/i) === null) {
          instance.setFinalDate.call(instance, method);
          // Allow plugin to restart after finished
          // Fix issue #38 (thanks to @yaoazhen)
          instance.start();
        } else {
          $.error('Method %s does not exist on jQuery.countdown'
              .replace(/\%s/gi, method));
        }
      } else {
        // ... if not we create an instance
        new Countdown(this, argumentsArray[0], argumentsArray[1]);
      }
    });
  };
});
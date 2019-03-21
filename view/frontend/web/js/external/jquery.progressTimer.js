(function ($) {
    $.fn.progressTimer = function (options, action) {
		var settings = $.extend({}, $.fn.progressTimer.defaults, options);
		
		action = action || "start";
		
		if(action == "start")
		{
			this.each(function () {
				$(this).empty();
				var barContainer = $("<div>").addClass("progress active progress-striped");
				var bar = $("<div>").addClass("progress-bar").addClass(settings.baseStyle)
					.attr("role", "progressbar")
					.attr("aria-valuenow", settings.timeLimit)
					.attr("aria-valuemin", "0")
					.attr("aria-valuemax", settings.timeLimit);

				bar.appendTo(barContainer);
				barContainer.appendTo($(this));

				var start = new Date();
				var limit = settings.timeLimit * 1000;
				var interval = window.setInterval(function () {
					var elapsed = new Date() - start;
					var procent = 100 - ((elapsed / limit) * 100);

					bar.width( procent + "%");
					bar.attr("aria-valuenow", settings.timeLimit - (elapsed / 1000));

					if (elapsed >= limit) {
						window.clearInterval(interval);

						bar.removeClass(settings.baseStyle)
						   .removeClass(settings.warningStyle)
						   .addClass(settings.completeStyle);

						settings.onFinish.call(this);
					}
					else if ( limit - elapsed <= (settings.completeThreshold * 1000) )
						bar.removeClass(settings.baseStyle)
						   .removeClass(settings.warningStyle)
						   .addClass(settings.completeStyle);
					else if (limit - elapsed <= (settings.warningThreshold * 1000))
						bar.removeClass(settings.baseStyle)
						   .removeClass(settings.completeStyle)
						   .addClass(settings.warningStyle);

				}, 250);
				
				$(this).data('progress', interval);

			});
		}
		
		if(action == "stop")
		{
			this.each(function () {
				var interval = $(this).data('progress');
				window.clearInterval(interval);
				$(this).empty();
			});
		}

        return this;
    };

    $.fn.progressTimer.defaults = {
        timeLimit: 60,  //total number of seconds
        warningThreshold: 10,  //seconds remaining triggering switch to warning color
		completeThreshold: 5,  //seconds remaining triggering switch to warning color
        onFinish: function () {},  //invoked once the timer expires
		baseStyle: '',  //bootstrap progress bar style at the beginning of the timer
        warningStyle: 'progress-bar-success',  //bootstrap progress bar style in the warning phase
        completeStyle: 'progress-bar-danger'  //bootstrap progress bar style at completion of timer
    };
}(jQuery));



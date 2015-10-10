(function($) {
    var settings, $notify_button, $notify_content, $notify_counter, $openTimeOut;
    var methods = {
        getNumberInCounter : function(){
            return parseInt($notify_counter.text());
        },
        updateCounter : function(number){
            var label_count=methods.getNumberInCounter();
            if(number <= 0 && $notify_button.hasClass('active')){
                $notify_button.removeClass('active');
            }
            if(label_count!=number){
                $notify_counter.text(number);
            }
        },
        checkNew :function(){

            if(typeof settings.notifyUrlUpdate === 'undefined' || settings.notifyUrlUpdate === null){
                console.error('variable "settings.notifyUrlUpdate" need be installed');
                return this;
            }

            var update_notify=function(){
                var notify_button = $notify_button;
                var label=$notify_counter;
                var label_count=parseInt(label.text());
                $.ajax({
                    type: "POST",
                    dataType:'json',
                    url:settings.notifyUrlUpdate,
                    data:{'count':label_count?label_count:0},
                    global: false,
                    success: function (data, textStatus) {
                        if(data){
                            if(label_count!=data.count||data.update){
                                label.text(data.count);
                                if(data.count!=0||data.update){
                                    notify_button.addClass('active');
                                    label.effect('bounce',{ times:5}, 1500);
                                    $notify_content.find('.popover-content').html(data.content);
                                }else if(data.count==0){
                                    methods.updateCounter(0);
                                }
                                methods.auto_height(true);
                            }
                        }
                    }
                });

            };

            setInterval(update_notify,settings.notifyTimeUpdate);

            return this;

        },
        show : function(){
            //var button = elem || $(this) || $notify_button;
            $notify_content.filter(".popover").fadeToggle(200, function(){
                if($notify_content.is(":visible")){
                    $openTimeOut = setTimeout(function(content){
                        var ids = [];
                        $notify_content.find('.notify:not(.read)').each(function(){
                            ids.push($(this).data('id'));
                        });
                        if(settings.notifyUrlRead && ids.length > 0){
                            $.post(settings.notifyUrlRead,{'ids':ids},function(data){
                                if(data){
                                    $.each(data,function(index, value){
                                        if(parseInt(value, 10) == 1){
                                            $notify_content.find('[data-id="'+index+'"]').addClass('read');
                                            methods.updateCounter(methods.getNumberInCounter() - 1);
                                        }
                                    });
                                }
                            }, 'json');
                        }
                    },1000);
                }else{
                    clearTimeout($openTimeOut);
                }
            });
            methods.auto_width();
            methods.auto_height();
            methods.auto_position();
            return this;
        },
        auto_height : function(reset){
            var $reset = reset || false;
            var content=$notify_content.find('.popover-content');
            if($reset){
                content.css({height:'auto'});
                $notify_content.data('default-height',false);
            }
            var content_height = content.children().first().outerHeight();
            var min_height = (content.find(settings.selector_notify_message).first() || content.find('.no_notify')).outerHeight();
            var height_notify=$notify_content.data('default-height');
            if(!height_notify){
                height_notify = $notify_content.outerHeight();
                $notify_content.data('default-height',height_notify);
            }
            height_notify+=$notify_content.offset().top;

            var body_height=$('body').height();
            if(height_notify>body_height){
                content.css({
                    height:content_height-(height_notify-body_height)
                });
            }else if(height_notify<body_height){
                content.css({
                    height:content_height
                });
            }
            if(min_height&&content.outerHeight()<min_height){
                var messages_count = content.children().children().length;
                content.css({
                    height:min_height * (messages_count<=settings.min_count_show?messages_count:settings.min_count_show)
                });
            }
        },
        auto_position : function(){
            var button_pos = $notify_button.position();
            var rePositionLeft = -4;
            var newPosition = (($notify_content.outerWidth()/2)-($notify_button.width()/2)) - rePositionLeft;
            $notify_content.css({
                top: button_pos.top + $notify_button.outerHeight() - 9,
                left: '-'+newPosition+'px'
            });
            var differenceWidth = (($notify_content.outerWidth()+$notify_content.offset().left)-$('body').outerWidth());
            $notify_content.css({
                left: '-'+(newPosition+(differenceWidth>0?differenceWidth:0))+'px'
            });
            $notify_content.find('.arrow').css({
                'left':($notify_content.outerWidth()/2+(differenceWidth>0?Math.ceil(differenceWidth):0)) - rePositionLeft + 'px'
            });
        },
        auto_width : function(){
            if($notify_content.outerWidth() > $('body').outerWidth()){
                $notify_content.css({
                    'min-width': $('body').outerWidth() + 'px'
                });
            }
        },
        init : function( options ) {
            if(this.data('notifyCenter')){
                return this;
            }

            settings = $.extend( {
                'selector_notify_button'  : '.notify_button',
                'selector_notify_content' : '.notify_content',
                'selector_notify_counter' : '.notify_button .count',
                'selector_notify_message' : '.notify',
                'notifyTimeUpdate'        : (15*1000), //one minutes
                'notifyUrlUpdate'         : null,
                'notifyUrlRead'           : null,
                'min_count_show'          : 3
            }, options);

            $notify_button = $(settings.selector_notify_button);
            $notify_content = $(settings.selector_notify_content);
            $notify_counter = $(settings.selector_notify_counter);

            $notify_button.click(function (e) {
                e.preventDefault();
                methods.show();
            });

            $(document).bind('click', function(e) {
                var $clicked = $(e.target);
                if (!$clicked.closest($notify_button).length&&!$clicked.closest($notify_content).length){
                    $notify_content.filter(".popover").fadeOut(200);
                }
            });

            $(window).resize(function(){
                methods.auto_width();
                methods.auto_height();
                methods.auto_position();
            });

            privateMethods.saveProperties();
            return this;
        }
    };
    var privateMethods = {
        saveProperties: function () {
            $(this).data('notifyCenter', settings);
        },
        loadProperties: function () {
            if ($(this).data('notifyCenter')) {
                settings = $(this).data('notifyCenter');
                $notify_button = $(settings.selector_notify_button);
                $notify_content = $(settings.selector_notify_content);
                $notify_counter = $(settings.selector_notify_counter);
            }
        }
    };

    $.fn.notifyCenter = function(method) {
        privateMethods.loadProperties();
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' not found in jQuery.notifyCenter ' );
        }
    };
})(jQuery);
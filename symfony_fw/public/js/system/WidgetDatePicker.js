/* global helper, mdc */

var widgetDatePicker = new WidgetDatePicker();

function WidgetDatePicker() {
    // Vars
    var self = this;

    var language;
    var currentYear;
    var currentMonth;
    var currentDay;
    
    var yearMin;
    var yearMax;

    var monthDays;
    
    var monthLabels;
    var dayLabels;
    
    var monthLength;
    var weekCurrentDay;
    var weekDayShift;
    var dayFirstPosition;
    
    var result;
    
    var inputFillTag;
    
    var currentInput;
    
    // Properties
    self.setLanguage = function(value) {
        language = value;
    };
    
    self.setCurrentYear = function(value) {
        currentYear = value;
    };
    
    self.setCurrentMonth = function(value) {
        currentMonth = value - 1;
    };
    
    self.setCurrentDay = function(value) {
        currentDay = value;
    };
    
    self.setInputFill = function(value) {
        if ($(value).is("input") === true)
            inputFillTag = value;
        else
            inputFillTag = $(value).find("input");
    };

    // Functions public
    self.init = function() {
        language = "";
        currentYear = -1;
        currentMonth = -1;
        currentDay = -1;

        yearMin = 1900;
        yearMax = -1;

        monthDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        monthLabels = {
            'en': new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"),
            'jp': new Array("一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"),
            'it': new Array("Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre")
        };
        dayLabels = {
            'en': new Array("S", "M", "T", "W", "T", "F", "S"),
            'jp': new Array("日", "月", "火", "水", "木", "金", "土"),
            'it': new Array("D", "L", "M", "M", "G", "V", "S")
        };

        monthLength = 0;
        weekCurrentDay = 0;
        weekDayShift = 0;
        dayFirstPosition = 0;

        result = "";

        inputFillTag = "";
        
        currentInput = null;
    };
    
    self.create = function() {
        var date = new Date();
        
        if (currentYear === -1)
            currentYear = date.getFullYear();
        
        if (currentMonth === -1)
            currentMonth = date.getMonth();
        
        if (currentDay === -1)
            currentDay = date.getDate();
        
        calculateMonthLength();
        calculateWeekDayShift();
        calculateDayPosition();
        
        var content = "<div class=\"widget_datePicker_back\"></div>\n\
            <div class=\"mdc-elevation--z8 widget_datePicker unselect\">";
            
            content += createHeaderHtml(true);
            content += createListYearsHtml();
            
            content += "<div class=\"calendar\">";
                content += createMonthHtml();
                content += createWeekHtml();
                content += createDayHtml();
                content += createButtonHtml();
            content += "</div>";
            
        content += "</div>";
        
        if ($(".widget_datePicker").length === 0)
            $("body").append(content);
        else {
            $(".widget_datePicker_back").remove();
            $(".widget_datePicker").remove();
            
            $("body").append(content);
            
            $(".widget_datePicker_back").show();
            $(".widget_datePicker").show();
        }
        
        $.each($(".widget_datePicker").find(".mdc-select"), function(key, value) {
            mdc.select.MDCSelect.attachTo(value);
        });
        
        $.each($(".widget_datePicker").find(".mdc-button"), function(key, value) {
            mdc.ripple.MDCRipple.attachTo(value);
        });
        
        $.each($(".widget_datePicker").find(".mdc-fab"), function(key, value) {
            mdc.ripple.MDCRipple.attachTo(value);
        });
        
        self.action();
    };
    
    self.action = function() {
        $(inputFillTag).off("click").on("click", "", function(event) {
            $(".widget_datePicker_back").show();
            $(".widget_datePicker").show();
            
            currentInput = $(event.target);
        });
        
        $(".widget_datePicker").find(".header p").off("click").on("click", "", function() {
            $(".widget_datePicker").find(".calendar").hide();
            $(".widget_datePicker").find(".listYears").show();
            
            var container = $(".widget_datePicker").find(".listYears");
            var target = $(".widget_datePicker").find(".listYears .mdc-list-item--activated");
            
            container.animate({
                scrollTop: target.offset().top - container.offset().top + container.scrollTop()
            }, "slow");
        });
        
        $(".widget_datePicker").find(".listYears li").off("click").on("click", "", function() {
            currentYear = parseInt($.trim($(this).text()));
            
            self.create();
        });
        
        $(".widget_datePicker").find(".calendar .month .material-icons").not(".mdc-fab").off("click").on("click", "", function() {
            if ($(this).parent().prop("class") === "left")
                currentMonth -= 1;
            else
                currentMonth += 1;

            if (currentMonth === -1) {
                currentYear -= 1;
                currentMonth = 11;
            }
            else if (currentMonth === monthLabels[language].length) {
                currentYear += 1;

                currentMonth = 0;
            }
            
            if (currentYear < yearMin) {
                currentYear = yearMin;
                currentMonth = 0;
            }
            else if (currentYear > yearMax) {
                currentYear = yearMax;
                currentMonth = 11;
            }
            
            self.create();
        });
        
        $(".widget_datePicker").find(".day li span").off("mouseover").on("mouseover", "", function() {
            if ($.trim($(this).text()) !== "")
                $(this).addClass("mdc-theme--secondary-bg mdc-theme--on-secondary");
        });
        
        $(".widget_datePicker").find(".day li span").off("mouseout").on("mouseout", "", function() {
            if ($.trim($(this).text()) !== "")
                $(this).removeClass("mdc-theme--secondary-bg mdc-theme--on-secondary");
        });
        
        $(".widget_datePicker").find(".day li span").off("click").on("click", "", function() {
            var text = $.trim($(this).text());
            
            if (text !== "") {
                $(this).parents(".day").find("li span").removeClass("mdc-theme--primary-bg mdc-theme--on-primary");
                $(this).addClass("mdc-theme--primary-bg mdc-theme--on-primary");
                
                currentDay = parseInt(text);
                
                var html = createHeaderHtml(false);
                
                $(".widget_datePicker").find(".header .text").html(html);
            }
        });
        
        $(".widget_datePicker").find(".button .button_today").off("click").on("click", "", function() {
            currentYear = -1;
            currentMonth = -1;
            currentDay = -1;
            
            self.create();
        });
        
        $(".widget_datePicker").find(".button .button_clear").off("click").on("click", "", function() {
            fillInput(false);
        });
        
        $(".widget_datePicker").find(".button .button_confirm").off("click").on("click", "", function() {
            fillInput(true);
        });
        
        $(".widget_datePicker").find(".header > .mdc-fab").off("click").on("click", "", function() {
            $(currentInput).focus();

            $(".widget_datePicker_back").hide();
            $(".widget_datePicker").hide();
        });
    };

    // Functions private
    function calculateMonthLength() {
        monthLength = monthDays[currentMonth];
        
        if (currentMonth === 1) {
            if ((currentYear % 4 === 0 && currentYear % 100 !== 0) || currentYear % 400 === 0)
                monthLength = 29;
        }
    }
    
    function calculateWeekDayShift() {
        var value = 0;
        
        if (language === "it")
            value = 1;
        
        weekDayShift = (value || 0) % 7;
        
        weekCurrentDay = dayLabels[language][(new Date(currentYear, currentMonth, currentDay).getDay() + weekDayShift + 7) % 7];
    }
    
    function calculateDayPosition() {
        dayFirstPosition = new Date(currentYear, currentMonth, 1).getDay();
        
        if (weekDayShift > dayFirstPosition)
            weekDayShift -= 7;
    }
    
    function createHeaderHtml(type) {
        var html = "";
        
        if (type === true) {
            html = "<div class=\"mdc-theme--primary-bg mdc-theme--on-primary header\">\n\
                <p>" + currentYear + "</p>\n\
                <div class=\"mdc-typography--headline6 text\">" + weekCurrentDay + ", " + monthLabels[language][currentMonth] + " " + currentDay + "</div>\n\
                <button class=\"mdc-fab mdc-fab--mini cp_payment_delete\" type=\"button\" aria-label=\"label\"><span class=\"mdc-fab__icon material-icons\">close</span></button>\n\
            </div>";
        }
        else if (type === false)
            html = weekCurrentDay + ", " + monthLabels[language][currentMonth] + " " + currentDay;

        return html;
    }
    
    function createListYearsHtml() {
        yearMin = 1900;
        yearMax = new Date().getFullYear();
        
        var html = "<div class=\"listYears\"><div class=\"mdc-list mdc-list--two-line mdc-list--avatar-list\">\n\
            <ul>";
                var count = 0;
                
                for (var a = yearMin; a <= yearMax; a ++) {
                    var selected = "mdc-list-item--activated";

                    if (a === currentYear)
                        html += "<li class=\"mdc-list-item " + selected + "\" role=\"option\" value=\"" + a + "\" tabindex=\"" + count + "\">" + a + "</li>";
                    else
                        html += "<li class=\"mdc-list-item\" role=\"option\" value=\"" + a + "\" tabindex=\"" + count + "\">" + a + "</li>";

                    count ++;
                }
            html += "</ul>\n\
        </div></div>";
        
        return html;
    }

    function createMonthHtml() {
        var html = "<div class=\"month\">\n\
            <div class=\"left\"><i class=\"material-icons mdc-ripple-surface\">keyboard_arrow_left</i></div>\n\
            <div class=\"mdc-typography--body2 label\">" + monthLabels[language][currentMonth] + "</div>\n\
            <div class=\"right\"><i class=\"material-icons mdc-ripple-surface\">keyboard_arrow_right</i></div>\n\
        </div>";
        
        return html;
    }

    function createWeekHtml() {
        var html = "<div class=\"mdc-typography--body2 week\"><ul>";
        
        for (var a = 0; a <= 6; a ++) {
            html += "<li>" + dayLabels[language][(a + weekDayShift + 7) % 7] + "</li>";
        }
        
        html += "</ul></div>";

        return html;
    }
    
    function createDayHtml() {
        var html = "<div class=\"mdc-typography--body2 day\">";
        
        var day = 1;
        
        for (var a = 0; a < 9; a ++) {
            html += "<ul>";
            
            for (var b = 0; b <= 6; b ++) {
                if (day === currentDay) {
                    if (a > 0 || b + weekDayShift >= dayFirstPosition)
                        html += "<li><span class=\"mdc-theme--primary-bg mdc-theme--on-primary\">";
                    else
                        html += "</span><li>";
                }
                else
                    html += "<li><span>";
                
                if (day <= monthLength && (a > 0 || b + weekDayShift >= dayFirstPosition)) {
                    html += day;
                    
                    day ++;
                }
                else
                    html += "&nbsp;";
                
                html += "</span></li>";
            }
            
            html += "</ul>";
            
            if (day > monthLength)
                break;
        }
        
        html += "</div>";
        
        return html;
    }
    
    function createButtonHtml() {
        return html = "<div class=\"button\">\n\
            <button class=\"mdc-button mdc-button--dense mdc-button--raised button_today\" type=\"button\">" + window.textWidgetDatePicker.label_1 + "</button>\n\
            <button class=\"mdc-button mdc-button--dense mdc-button--raised button_clear\" type=\"button\">" + window.textWidgetDatePicker.label_2 + "</button>\n\
            <button class=\"mdc-button mdc-button--dense mdc-button--raised button_confirm\" type=\"button\">" + window.textWidgetDatePicker.label_3 + "</button>\n\
        </div>";
    }
    
    function fillInput(type) {
        var currentMontTmp = currentMonth + 1;
        
        result = currentYear + "-" + helper.padZero(currentMontTmp) + "-" + currentDay;
        
        if (language === "it")
            result = currentDay + "-" + helper.padZero(currentMontTmp) + "-" + currentYear;
        
        if (type === true)
            $(currentInput).val(result);
        else
            $(currentInput).val("");
        
        if ($(currentInput).parent().find(".mdc-text-field__label").length > 0) {
            if (type === true) {
                $(currentInput).parent().addClass("mdc-text-field--focused");
                $(currentInput).parent().find(".mdc-text-field__label").addClass("mdc-text-field__label--float-above");
                $(currentInput).parent().find(".mdc-line-ripple").addClass("mdc-line-ripple--active");
            }
            else {
                $(currentInput).parent().removeClass("mdc-text-field--focused");
                $(currentInput).parent().find(".mdc-text-field__label").removeClass("mdc-text-field__label--float-above");
                $(currentInput).parent().find(".mdc-line-ripple").removeClass("mdc-line-ripple--active");
            }
        }
        else
            $(currentInput).focus();
        
        $(".widget_datePicker_back").hide();
        $(".widget_datePicker").hide();
    }
}
"use strict";

/* global helper, mdc */

class WidgetDatePicker {
    // Properties
    set setCurrentYear(value) {
        this.currentYear = value;
    }
    
    set setCurrentMonth(value) {
        this.currentMonth = value - 1;
    }
    
    set setCurrentDay(value) {
        this.currentDay = value;
    }
    
    set setInputFill(value) {
        if ($(value).is("input") === true)
            this.inputFillTag = value;
        else
            this.inputFillTag = $(value).find("input");
    }
    
    // Functions public
    constructor(lang) {
        this.language = lang;
        
        this.date = new Date();
        
        this.currentYear = this.date.getFullYear();
        this.currentMonth = this.date.getMonth();
        this.currentDay = this.date.getDate();
        
        this.yearMin = 1900;
        this.yearMax = -1;
        
        this.monthDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        this.monthLabels = {
            'en': ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
            'jp': ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
            'it': ["Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"]
        };
        
        this.dayLabels = {
            'en': ["S", "M", "T", "W", "T", "F", "S"],
            'jp': ["日", "月", "火", "水", "木", "金", "土"],
            'it': ["D", "L", "M", "M", "G", "V", "S"]
        };
        
        this.monthLength = 0;
        this.weekCurrentDay = 0;
        this.weekDayShift = 0;
        this.dayFirstPosition = 0;
        
        this.result = "";
        
        this.inputFillTag = "";
        
        this.currentInput = null;
    }
    
    create = () => {
        this._calculateMonthLength();
        this._calculateWeekDayShift();
        this._calculateDayPosition();
        
        let content = `<div class="widget_datePicker unselect">
            ${this._createHeaderHtml(false)}
            ${this._createListYearsHtml()}
            <div class="calendar">
                ${this._createMonthHtml()}
                ${this._createWeekHtml()}
                ${this._createDayHtml()}
                ${this._createButtonHtml()}
            </div>
        </div>`;
        
        if ($(".widget_datePicker").length === 0)
            $("body").append(content);
        else {
            $(".widget_datePicker").remove();
            
            $("body").append(content);
        }
        
        $.each($(".widget_datePicker").find(".mdc-select"), (key, value) => {
            mdc.select.MDCSelect.attachTo(value);
        });
        
        $.each($(".widget_datePicker").find(".mdc-button"), (key, value) => {
            mdc.ripple.MDCRipple.attachTo(value);
        });
        
        $.each($(".widget_datePicker").find(".mdc-fab"), (key, value) => {
            mdc.ripple.MDCRipple.attachTo(value);
        });
        
        this._action();
    }
    
    // Functions private
    _action = () => {
        $(this.inputFillTag).on("click", "", (event) => {
            $(".loader_back").show();

            $(".widget_datePicker").show();
            
            this.currentInput = $(event.target);
        });
        
        $(".widget_datePicker").find(".header p").on("click", "", (event) => {
            $(".widget_datePicker").find(".calendar").hide();
            $(".widget_datePicker").find(".listYears").show();
            
            let container = $(".widget_datePicker").find(".listYears");
            let target = $(".widget_datePicker").find(".listYears .mdc-list-item--activated");
            
            container.animate({
                scrollTop: target.offset().top - container.offset().top + container.scrollTop()
            }, "slow");
        });
        
        $(".widget_datePicker").find(".listYears li").on("click", "", (event) => {
            this.currentYear = parseInt($.trim($(event.target).text()));
            
            this.create();
            
            $(".widget_datePicker").show();
        });
        
        $(".widget_datePicker").find(".calendar .month .material-icons").not(".mdc-fab").on("click", "", (event) => {
            if ($(event.target).parent().prop("class") === "left")
                this.currentMonth -= 1;
            else
                this.currentMonth += 1;

            if (this.currentMonth === -1) {
                this.currentYear -= 1;
                this.currentMonth = 11;
            }
            else if (this.currentMonth === this.monthLabels[this.language].length) {
                this.currentYear += 1;

                this.currentMonth = 0;
            }
            
            if (this.currentYear < this.yearMin) {
                this.currentYear = this.yearMin;
                this.currentMonth = 0;
            }
            else if (this.currentYear > this.yearMax) {
                this.currentYear = this.yearMax;
                this.currentMonth = 11;
            }
            
            this.create();
            
            $(".widget_datePicker").show();
        });
        
        $(".widget_datePicker").find(".day li span").on("mouseover", "", (event) => {
            if ($.trim($(event.target).text()) !== "")
                $(event.target).addClass("button_warning");
        });
        
        $(".widget_datePicker").find(".day li span").on("mouseout", "", (event) => {
            if ($.trim($(event.target).text()) !== "")
                $(event.target).removeClass("button_warning");
        });
        
        $(".widget_datePicker").find(".day li span").on("click", "", (event) => {
            let text = $.trim($(event.target).text());
            
            if (text !== "") {
                $(event.target).parents(".day").find("li span").removeClass("mdc-theme--primary-bg mdc-theme--on-primary");
                $(event.target).addClass("mdc-theme--primary-bg mdc-theme--on-primary");
                
                this.currentDay = parseInt(text);
                
                this._calculateWeekDayShift();
                
                let html = this._createHeaderHtml(true);
                
                $(".widget_datePicker").find(".header .text").html(html);
            }
        });
        
        $(".widget_datePicker").find(".button .button_today").on("click", "", (event) => {
            this.currentYear = this.date.getFullYear();
            this.currentMonth = this.date.getMonth();
            this.currentDay = this.date.getDate();
            
            this.create();
            
            $(".widget_datePicker").show();
        });
        
        $(".widget_datePicker").find(".button .button_clear").on("click", "", (event) => {
            this._fillInput(false);
        });
        
        $(".widget_datePicker").find(".button .button_confirm").on("click", "", (event) => {
            this._fillInput(true);
        });
        
        $(".widget_datePicker").find(".header > .mdc-fab").on("click", "", (event) => {
            $(this.currentInput).focus();

            $(".loader_back").hide();

            $(".widget_datePicker").hide();
        });
    }
    
    _calculateMonthLength = () => {
        this.monthLength = this.monthDays[this.currentMonth];
        
        if (this.currentMonth === 1) {
            if ((this.currentYear % 4 === 0 && this.currentYear % 100 !== 0) || this.currentYear % 400 === 0)
                this.monthLength = 29;
        }
    }
    
    _calculateWeekDayShift = () => {
        let value = 0;
        
        if (this.language === "it")
            value = 1;
        
        this.weekDayShift = (value || 0) % 7;
        
        this.weekCurrentDay = this.dayLabels[this.language][(new Date(this.currentYear, this.currentMonth, this.currentDay).getDay() + this.weekDayShift + 7) % 7];
    }
    
    _calculateDayPosition = () => {
        this.dayFirstPosition = new Date(this.currentYear, this.currentMonth, 1).getDay();
        
        if (this.weekDayShift > this.dayFirstPosition)
            this.weekDayShift -= 7;
    }
    
    _createHeaderHtml = (onlyText) => {
        let html = "";
        
        if (onlyText === false) {
            html = `<div class="mdc-theme--primary-bg mdc-theme--on-primary header">
                <p>${this.currentYear}</p>
                <div class="mdc-typography--headline6 text">${this.weekCurrentDay}, ${this.monthLabels[this.language][this.currentMonth]} ${this.currentDay}</div>
                <button class="mdc-fab mdc-fab--mini cp_payment_delete icon_warning" type="button" aria-label="label"><span class="mdc-fab__icon material-icons">close</span></button>
            </div>`;
        }
        else
            html = `${this.weekCurrentDay}, ${this.monthLabels[this.language][this.currentMonth]} ${this.currentDay}`;

        return html;
    }
    
    _createListYearsHtml = () => {
        this.yearMin = 1900;
        this.yearMax = new Date().getFullYear();
        
        let html = "<div class=\"listYears\">\
            <div class=\"mdc-list mdc-list--two-line mdc-list--avatar-list\">\
                <ul>";
                    let count = 0;

                    for (let a = this.yearMin; a <= this.yearMax; a ++) {
                        let selected = "mdc-list-item--activated";

                        if (this.currentYear === a)
                            html += `<li class="mdc-list-item ${selected}" role="option" value="${a}" tabindex="${count}">${a}</li>`;
                        else
                            html += `<li class="mdc-list-item" role="option" value="${a}" tabindex="${count}">${a}</li>`;

                        count ++;
                    }
                html += "</ul>\
            </div>\
        </div>";
        
        return html;
    }

    _createMonthHtml = () => {
        let html = `<div class="month">
            <div class="left"><i class="material-icons mdc-ripple-surface">keyboard_arrow_left</i></div>
            <div class="mdc-typography--body2 label">${this.monthLabels[this.language][this.currentMonth]}</div>
            <div class="right"><i class="material-icons mdc-ripple-surface">keyboard_arrow_right</i></div>
        </div>`;
        
        return html;
    }

    _createWeekHtml = () => {
        let html = "<div class=\"mdc-typography--body2 week\"><ul>";
        
        for (let a = 0; a <= 6; a ++) {
            html += `<li>${this.dayLabels[this.language][(a + this.weekDayShift + 7) % 7]}</li>`;
        }
        
        html += "</ul></div>";

        return html;
    }
    
    _createDayHtml = () => {
        let html = "<div class=\"mdc-typography--body2 day\">";
        
        let day = 1;
        
        for (let a = 0; a < 9; a ++) {
            html += "<ul>";
            
            for (let b = 0; b <= 6; b ++) {
                if (day === this.currentDay) {
                    if (a > 0 || b + this.weekDayShift >= this.dayFirstPosition)
                        html += "<li><span class=\"mdc-theme--primary-bg mdc-theme--on-primary\">";
                    else
                        html += "</span><li>";
                }
                else
                    html += "<li><span>";
                
                if (day <= this.monthLength && (a > 0 || b + this.weekDayShift >= this.dayFirstPosition)) {
                    html += day;
                    
                    day ++;
                }
                else
                    html += "&nbsp;";
                
                html += "</span></li>";
            }
            
            html += "</ul>";
            
            if (day > this.monthLength)
                break;
        }
        
        html += "</div>";
        
        return html;
    }
    
    _createButtonHtml = () => {
        let html = `<div class="button">
            <button class="mdc-button mdc-button--dense mdc-button--raised button_today" type="button">${window.textWidgetDatePicker.label_1}</button>
            <button class="mdc-button mdc-button--dense mdc-button--raised button_clear" type="button">${window.textWidgetDatePicker.label_2}</button>
            <button class="mdc-button mdc-button--dense mdc-button--raised button_confirm" type="button">${window.textWidgetDatePicker.label_3}</button>
        </div>`;
        
        return html;
    }
    
    _fillInput = (type) => {
        let currentMontTmp = this.currentMonth + 1;
        
        this.result = this.currentYear + "-" + helper.padZero(currentMontTmp) + "-" + this.currentDay;
        
        if (this.language === "it")
            this.result = this.currentDay + "-" + helper.padZero(currentMontTmp) + "-" + this.currentYear;
        
        if (type === true)
            $(this.currentInput).val(this.result);
        else
            $(this.currentInput).val("");
        
        if ($(this.currentInput).parent().find(".mdc-text-field__label").length > 0) {
            if (type === true) {
                $(this.currentInput).parent().addClass("mdc-text-field--focused");
                $(this.currentInput).parent().find(".mdc-text-field__label").addClass("mdc-text-field__label--float-above");
                $(this.currentInput).parent().find(".mdc-line-ripple").addClass("mdc-line-ripple--active");
            }
            else {
                $(this.currentInput).parent().removeClass("mdc-text-field--focused");
                $(this.currentInput).parent().find(".mdc-text-field__label").removeClass("mdc-text-field__label--float-above");
                $(this.currentInput).parent().find(".mdc-line-ripple").removeClass("mdc-line-ripple--active");
            }
        }
        else
            $(this.currentInput).focus();

        $(".loader_back").hide();

        $(".widget_datePicker").hide();
    }
}
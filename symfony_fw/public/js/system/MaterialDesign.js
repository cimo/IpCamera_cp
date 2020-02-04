"use strict";

/* global helper, mdc */

const materialDesign = new MaterialDesign();

function MaterialDesign() {
    // Vars
    const self = this;
    
    let dialogMdc;
    
    let snackbarMdc;
    
    let mdcTextFields;
    
    // Properties
    self.getDialogMdc = function() {
        return dialogMdc;
    };
    
    self.getSnackbarMdc = function() {
        return snackbarMdc;
    };
    
    // Functions public
    self.init = function() {
        dialogMdc = null;
        
        snackbarMdc = null;
        
        mdcTextFields = [];
        
        //window.mdc.autoInit();
    };
    
    self.button = function() {
        $.each($(".mdc-button"), function(key, value) {
            new mdc.ripple.MDCRipple.attachTo(value);
        });
    };
    
    self.fabButton = function() {
        $.each($(".mdc-fab"), function(key, value) {
            new mdc.ripple.MDCRipple.attachTo(value);
            
            /*$(value).on("click", "", function(event) {
                $(this).addClass("mdc-fab--exited");
            });*/
        });
    };
    
    self.iconButton = function() {
        $.each($(".mdc-icon-toggle"), function(key, value) {
           new mdc.iconToggle.MDCIconToggle.attachTo(value);
        });
    };
    
    self.chip = function() {
        $.each($(".mdc-chip"), function(key, value) {
           new mdc.ripple.MDCRipple.attachTo(value);
        });
    };
    
    self.dialog = function() {
        if ($(".mdc-dialog").length > 0) {
            dialogMdc = new mdc.dialog.MDCDialog.attachTo($(".mdc-dialog")[0]);

            /*$(".show_dialog").on("click", "", function(event) {
                dialogMdc.lastFocusedTarget = event.target;
                dialogMdc.show();
            });

            dialogMdc.listen("MDCDialog:accept", function() {
                console.log("Dialog - Accepted");
            });

            dialogMdc.listen("MDCDialog:cancel", function() {
                console.log("Dialog - Canceled");
            });*/
        }
    };
    
    self.drawer = function() {
        if ($(".mdc-drawer--temporary").length > 0) {
            let drawerMdc = new mdc.drawer.MDCTemporaryDrawer.attachTo($(".mdc-drawer--temporary")[0]);

            $(".menu_root_mobile").on("click", "", function(event) {
                drawerMdc.open = true;
            });
        }
    };
    
    self.checkbox = function() {
        $.each($(".mdc-checkbox"), function(key, value) {
            new mdc.checkbox.MDCCheckbox.attachTo(value);
        });
    };
    
    self.radioButton = function() {
        $.each($(".mdc-radio"), function(key, value) {
            new mdc.radio.MDCRadio.attachTo(value);
        });
    };
    
    self.select = function() {
        $.each($(".mdc-select"), function(key, value) {
            let selectMdc = new mdc.select.MDCSelect.attachTo(value);

            /*$(value).on("change", "", function() {
                console.log("Select - Item with index " + selectMdc.selectedIndex + " and value " + selectMdc.value);
            });*/
        });
    };
    
    self.slider = function() {
        $.each($(".mdc-slider"), function(key, value) {
            let sliderMdc = new mdc.slider.MDCSlider.attachTo(value);

            /*$(value).on("MDCSlider:change", "", function() {
                console.log("Slider - Value: " + sliderMdc.value);
            });*/
        });
    };
    
    self.textField = function() {
        mdcTextFields = [];
        
        $.each($(".mdc-text-field"), function(key, value) {
            mdcTextFields.push(new mdc.textField.MDCTextField.attachTo(value));
            mdcTextFields[key].layout();
            
            if ($(value).find(".mdc-text-field__input").attr('placeholder') === "******") {
                let color = getComputedStyle(document.documentElement).getPropertyValue("--mdc-theme-on-primary");
                
                $(value).find(".mdc-floating-label").addClass("mdc-floating-label--float-above");
                $(value).find(".mdc-floating-label").css("background-color", color);
                $(value).find(".mdc-notched-outline").addClass("mdc-notched-outline--notched");
                $(value).find(".mdc-notched-outline__path").attr("d", "M78.995,1h472.805a4,4 0 0 1 4,4v35.6a4,4 0 0 1 -4,4h-546.6a4,4 0 0 1 -4,-4v-35.6a4,4 0 0 1 4,-4h5.8");
                
                $(value).find(".mdc-text-field__input[placeholder='******']").on("blur", "", function(event) {
                    $(value).find(".mdc-floating-label").addClass("mdc-floating-label--float-above");
                    $(value).find(".mdc-floating-label").css("background-color", color);
                    $(value).find(".mdc-notched-outline").addClass("mdc-notched-outline--notched");
                    $(value).find(".mdc-notched-outline__path").attr("d", "M78.995,1h472.805a4,4 0 0 1 4,4v35.6a4,4 0 0 1 -4,4h-546.6a4,4 0 0 1 -4,-4v-35.6a4,4 0 0 1 4,-4h5.8");
                    $(value).removeClass("mdc-text-field--invalid");
                });
            }
        });
        
        /*$.each($(".mdc-text-field"), function(key, value) {
            $(value).find(".mdc-text-field__input");
            $(value).parent().find(".mdc-text-field-helper-text");
        });*/
    };
    
    self.linearProgress = function(tag, start, end, buffer) {
        if ($(tag).length > 0) {
            let linearProgressMdc = new mdc.linearProgress.MDCLinearProgress.attachTo($(tag)[0]);
            
            let progress = 0;
            
            if (start !== undefined && end !== undefined && end !== 0) {
                progress = start / end;
                
                linearProgressMdc.progress = progress;
            }
            else
                linearProgressMdc.progress = 0;
            
            if (buffer !== undefined && end !== 0)
                linearProgressMdc.buffer = buffer;
        }
    };
    
    self.list = function() {
        $.each($(".mdc-list-item"), function(key, value) {
            new mdc.ripple.MDCRipple.attachTo(value);
        });
    };
    
    self.menu = function() {
        $.each($(".mdc-menu"), function(key, value) {
            let menuMdc = new mdc.menu.MDCMenu.attachTo(value);
            
            menuMdc.quickOpen = false;
            menuMdc.setAnchorCorner(1 | 4 | 8); //BOTTOM: 1, CENTER: 2, RIGHT: 4, FLIP_RTL: 8
            
            $(value).prev().on("click", "", function(event) {
                menuMdc.open = !menuMdc.open;
            });
            
            /*$(value).on("MDCMenu:selected", "", function(event) {
                console.log("Menu - Item with index " + event.detail.index + " and value " + event.detail.item.innerText);
            });*/
        });
    };
    
    self.snackbar = function() {
        $.each($(".mdc-snackbar"), function(key, value) {
            snackbarMdc = new mdc.snackbar.MDCSnackbar.attachTo(value);
        });
        
        /*$(".show_snackbar").on("click", "", function(event) {
            let snackbarDataObj = {
                message: "Text",
                actionText: "Close",
                actionHandler: function() {}
            };

            snackbarMdc.show(snackbarDataObj);
        });*/
    };
    
    self.tabBar = function() {
        if ($(".mdc-tab-bar").length > 0) {
            $.each($(".mdc-tab-bar").not(".mdc-tab-bar-scroller__scroll-frame__tabs"), function(key, value) {
                let tabBarMdc = new mdc.tabs.MDCTabBar.attachTo(value);

                mdcTabBarCustom("tabBar", tabBarMdc);
            });
        }
        
        if ($(".mdc-tab-bar-scroller").length > 0) {
            $.each($(".mdc-tab-bar-scroller"), function(key, value) {
                let tabBarScrollerMdc = new mdc.tabs.MDCTabBarScroller.attachTo(value);

                mdcTabBarCustom("tabBarScroller", tabBarScrollerMdc);
            });
        }
    };
    
    self.refresh = function() {
        self.button();
        self.fabButton();
        self.iconButton();
        self.chip();
        self.dialog();
        self.drawer();
        self.checkbox();
        self.radioButton();
        self.select();
        self.slider();
        self.textField();
        self.list();
        self.menu();
        self.snackbar();
        self.tabBar();
    };
    
    self.fix = function() {
        mdcTopAppBarCustom();
        mdcDrawerCustom();
        mdcTextFieldHelperTextClear();
    };
    
    // Functions private
    function mdcTabBarCustom(type, mdc) {
        let parameters = helper.urlParameters(window.session.languageTextCode);

        $(".mdc-tab-bar").find(".mdc-tab").removeClass("mdc-tab--active");

        let isActive = false;

        $.each($(".mdc-tab-bar").find(".mdc-tab"), function(key, value) {
            if ($(value).prop("href").indexOf(parameters[2]) !== -1) {
                $(value).addClass("mdc-tab--active");

                if (type === "tabBar")
                    mdc.activeTabIndex = key;
                else if (type === "tabBarScroller") {
                    let element = $(value).parent().find(".mdc-tab-bar__indicator");

                    helper.mutationObserver(['attributes'], element[0], function() {
                        if (isActive === true)
                            return false;

                        let transformSplit = element.css("transform").split(",");

                        element.css("transform", transformSplit[0] + ", " + transformSplit[1] + ", " + transformSplit[2] + ", " + transformSplit[3] + ", " + $(value).position().left + ", " + transformSplit[5]);

                        isActive = true;
                    });
                }

                return false;
            }
        });
    }
    
    function mdcTopAppBarCustom() {
        if ($(".mdc-top-app-bar").length > 0) {
            let scrollLimit = 30;

            if (helper.checkWidthType() === "desktop") {
                $(".mdc-top-app-bar").addClass("mdc-top-app-bar--prominent");

                if ($(document).scrollTop() > scrollLimit) {
                    $(".mdc-top-app-bar__row").addClass("mdc-top-app-bar_shrink");
                    
                    $(".logo_main_big").hide();
                }

                $(window).scroll(function() {
                    if (helper.checkWidthType() === "desktop") {
                        if ($(document).scrollTop() > scrollLimit) {
                            $(".mdc-top-app-bar__row").addClass("mdc-top-app-bar_shrink");
                            
                            $(".logo_main_big").hide();
                        }
                        else {
                            $(".mdc-top-app-bar__row").removeClass("mdc-top-app-bar_shrink");
                            
                            $(".logo_main_big").show();
                        }
                    }
                });
            }
            else {
                $(".mdc-top-app-bar").removeClass("mdc-top-app-bar--prominent");
                $(".mdc-top-app-bar__row").removeClass("mdc-top-app-bar_shrink");
            }
        }
    }
    
    function mdcDrawerCustom() {
        if (helper.checkWidthType() === "desktop") {
            $("body").removeClass("mdc-drawer-scroll-lock");
            $(".mdc-drawer").removeClass("mdc-drawer--open");
        }
        
        let parameters = helper.urlParameters(window.session.languageTextCode);
        
        $(".mdc-drawer").find(".mdc-list-item").removeClass("mdc-list-item--activated");
        
        $.each($(".mdc-drawer"), function(key, value) {
            $.each($(value).find(".mdc-list-item"), function(keySub, valueSub) {
                if (window.location.href.indexOf("control_panel") !== -1) {
                    if (parameters[2] === undefined) {
                        if (keySub === 1) {
                            $(valueSub).addClass("mdc-list-item--activated");

                            return false;
                        }
                    }
                    else {
                        if ($(valueSub).prop("href").indexOf(parameters[2]) !== -1) {
                            $(valueSub).addClass("mdc-list-item--activated");

                            return false;
                        }
                    }
                }
                else {
                    if ((parameters[0] === "" || parameters[1] === "2") && keySub === 0) {
                        $(valueSub).addClass("mdc-list-item--activated");

                        return false;
                    }
                    else if ($(valueSub).prop("href").indexOf(parameters[1]) !== -1 && parseInt(parameters[1]) > 5) {
                        $(valueSub).addClass("mdc-list-item--activated");
                        
                        $(valueSub).parentsUntil($(".menu_root_container"), ".children_container").show();

                        return false;
                    }
                }
            });
        });
    }
    
    function mdcTextFieldHelperTextClear() {
        $(".mdc-text-field__input").on("blur", "", function(event) {
            $(event.target).parents(".form_row").find(".mdc-text-field-helper-text").text("");
        });
    }
}
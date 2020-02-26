"use strict";

/* global helper, mdc */

class MaterialDesign {
    // Properties
    get getDialogMdc() {
        return this.dialogMdc;
    }
    
    get getSnackbarMdc() {
        return this.snackbarMdc;
    }
    
    // Functions public
    constructor() {
        this.dialogMdc = null;
    
        this.snackbarMdc = null;

        this.mdcTextFields = [];
        
        //window.mdc.autoInit();
    }
    
    button = () => {
        $.each($(".mdc-button"), (key, value) => {
            new mdc.ripple.MDCRipple.attachTo(value);
        });
    }
    
    fabButton = () => {
        $.each($(".mdc-fab"), (key, value) => {
            new mdc.ripple.MDCRipple.attachTo(value);
            
            /*$(value).on("click", "", (event) => {
                $(event.target).addClass("mdc-fab--exited");
            });*/
        });
    }
    
    iconButton = () => {
        $.each($(".mdc-icon-toggle"), (key, value) => {
           new mdc.iconToggle.MDCIconToggle.attachTo(value);
        });
    }
    
    chip = () => {
        $.each($(".mdc-chip"), (key, value) => {
           new mdc.ripple.MDCRipple.attachTo(value);
        });
    }
    
    dialog = () => {
        if ($(".mdc-dialog").length > 0) {
            this.dialogMdc = new mdc.dialog.MDCDialog.attachTo($(".mdc-dialog")[0]);
            
            /*$(".show_dialog").on("click", "", (event) => {
                this.dialogMdc.lastFocusedTarget = event.target;
                this.dialogMdc.show();
            });
            
            this.dialogMdc.listen("MDCDialog:accept", () => {
                console.log("Dialog - Accepted");
            });
            
            this.dialogMdc.listen("MDCDialog:cancel", () => {
                console.log("Dialog - Canceled");
            });*/
        }
    }
    
    drawer = () => {
        if ($(".mdc-drawer--temporary").length > 0) {
            let drawerMdc = new mdc.drawer.MDCTemporaryDrawer.attachTo($(".mdc-drawer--temporary")[0]);

            $(".menu_root_mobile").on("click", "", (event) => {
                drawerMdc.open = true;
            });
        }
    }
    
    checkbox = () => {
        $.each($(".mdc-checkbox"), (key, value) => {
            new mdc.checkbox.MDCCheckbox.attachTo(value);
        });
    }
    
    radioButton = () => {
        $.each($(".mdc-radio"), (key, value) => {
            new mdc.radio.MDCRadio.attachTo(value);
        });
    }
    
    select = () => {
        $.each($(".mdc-select"), (key, value) => {
            let selectMdc = new mdc.select.MDCSelect.attachTo(value);

            /*$(value).on("change", "", (event) => {
                console.log(`Select - Item with index ${selectMdc.selectedIndex} and value ${selectMdc.value}`);
            });*/
        });
    }
    
    slider = () => {
        $.each($(".mdc-slider"), (key, value) => {
            let sliderMdc = new mdc.slider.MDCSlider.attachTo(value);

            /*$(value).on("MDCSlider:change", "", (event) => {
                console.log(`Slider - Value: ${sliderMdc.value}`);
            });*/
        });
    }
    
    textField = () => {
        this.mdcTextFields = [];
        
        $.each($(".mdc-text-field"), (key, value) => {
            this.mdcTextFields.push(new mdc.textField.MDCTextField.attachTo(value));
            this.mdcTextFields[key].layout();
            
            if ($(value).find(".mdc-text-field__input").attr('placeholder') === "******") {
                $(value).find(".mdc-floating-label").addClass("mdc-floating-label--float-above mdc-floating-label_password");
                
                $(value).find(".mdc-text-field__input[placeholder='******']").on("blur", "", (event) => {
                    $(value).find(".mdc-floating-label").addClass("mdc-floating-label--float-above mdc-floating-label_password");
                    $(value).removeClass("mdc-text-field--invalid");
                });
            }
        });
        
        /*$.each($(".mdc-text-field"), (key, value) => {
            $(value).find(".mdc-text-field__input");
            $(value).parent().find(".mdc-text-field-helper-text");
        });*/
    }
    
    linearProgress = (tag, start, end, buffer) => {
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
    }
    
    list = () => {
        $.each($(".mdc-list-item"), (key, value) => {
            new mdc.ripple.MDCRipple.attachTo(value);
        });
    };
    
    menu = () => {
        $.each($(".mdc-menu"), (key, value) => {
            let menuMdc = new mdc.menu.MDCMenu.attachTo(value);
            
            menuMdc.quickOpen = false;
            menuMdc.setAnchorCorner(1 | 4 | 8); //BOTTOM: 1, CENTER: 2, RIGHT: 4, FLIP_RTL: 8
            
            $(value).prev().on("click", "", (event) => {
                menuMdc.open = !menuMdc.open;
            });
            
            /*$(value).on("MDCMenu:selected", "", (event) => {
                console.log("Menu - Item with index " + event.detail.index + " and value " + event.detail.item.innerText);
            });*/
        });
    }
    
    snackbar = () => {
        $.each($(".mdc-snackbar"), (key, value) => {
            this.snackbarMdc = new mdc.snackbar.MDCSnackbar.attachTo(value);
        });
        
        /*$(".show_snackbar").on("click", "", (event) => {
            let snackbarDataObj = {
                message: "Text",
                actionText: "Close",
                actionHandler: () => {}
            };

            this.snackbarMdc.show(snackbarDataObj);
        });*/
    }
    
    tabBar = () => {
        if ($(".mdc-tab-bar").length > 0) {
            $.each($(".mdc-tab-bar").not(".mdc-tab-bar-scroller__scroll-frame__tabs"), (key, value) => {
                let tabBarMdc = new mdc.tabs.MDCTabBar.attachTo(value);

                this.mdcTabBarCustom("tabBar", tabBarMdc);
            });
        }
        
        if ($(".mdc-tab-bar-scroller").length > 0) {
            $.each($(".mdc-tab-bar-scroller"), (key, value) => {
                let tabBarScrollerMdc = new mdc.tabs.MDCTabBarScroller.attachTo(value);

                this.mdcTabBarCustom("tabBarScroller", tabBarScrollerMdc);
            });
        }
    }
    
    refresh = () => {
        this.button();
        this.fabButton();
        this.iconButton();
        this.chip();
        this.dialog();
        this.drawer();
        this.checkbox();
        this.radioButton();
        this.select();
        this.slider();
        this.textField();
        this.list();
        this.menu();
        this.snackbar();
        this.tabBar();
    }
    
    fix = () => {
        this.mdcTopAppBarCustom();
        this.mdcDrawerCustom();
        this.mdcTextFieldHelperTextClear();
    }
    
    // Functions private
    mdcTabBarCustom = (type, mdc) => {
        let parameters = helper.urlParameters(window.session.languageTextCode);

        $(".mdc-tab-bar").find(".mdc-tab").removeClass("mdc-tab--active");

        let isActive = false;

        $.each($(".mdc-tab-bar").find(".mdc-tab"), (key, value) => {
            if ($(value).prop("href").indexOf(parameters[2]) !== -1) {
                $(value).addClass("mdc-tab--active");

                if (type === "tabBar")
                    mdc.activeTabIndex = key;
                else if (type === "tabBarScroller") {
                    let element = $(value).parent().find(".mdc-tab-bar__indicator");

                    helper.mutationObserver(['attributes'], element[0], () => {
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
    
    mdcTopAppBarCustom = () => {
        if ($(".mdc-top-app-bar").length > 0) {
            let scrollLimit = 30;

            if (helper.checkWidthType() === "desktop") {
                $(".mdc-top-app-bar").addClass("mdc-top-app-bar--prominent");

                if ($(document).scrollTop() > scrollLimit) {
                    $(".mdc-top-app-bar__row").addClass("mdc-top-app-bar_shrink");
                    
                    $(".logo_main_big").hide();
                }

                $(window).scroll(() => {
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
    
    mdcDrawerCustom = () => {
        if (helper.checkWidthType() === "desktop") {
            $("body").removeClass("mdc-drawer-scroll-lock");
            $(".mdc-drawer").removeClass("mdc-drawer--open");
        }
        
        let parameters = helper.urlParameters(window.session.languageTextCode);
        
        $(".mdc-drawer").find(".mdc-list-item").removeClass("mdc-list-item--activated");
        
        $.each($(".mdc-drawer"), (key, value) => {
            $.each($(value).find(".mdc-list-item"), (keySub, valueSub) => {
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
    
    mdcTextFieldHelperTextClear = () => {
        $(".mdc-text-field__input").on("blur", "", (event) => {
            $(event.target).parents(".form_row").find(".mdc-text-field-helper-text").text("");
        });
    }
}
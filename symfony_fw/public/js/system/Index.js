"use strict";

/* global */

let ajax = null;
let authentication = null;
let captcha = null;
let chaato = null;
let flashBag = null;
let helper = null;
let language = null;
let loader = null;
let materialDesign = null;
let menuUser = null;
let pageComment = null;
let popupEasy = null;
let recoverPassword = null;
let registration = null;
let search = null;
let uploadChunk = null;
let widgetDatePicker = null;
let widgetSearch = null;
let wysiwyg = null;

$(document).ready(() => {
    ajax = new Ajax();
    authentication = new Authentication();
    captcha = new Captcha();
    chaato = new Chaato();
    flashBag = new FlashBag();
    helper = new Helper();
    language = new Language();
    loader = new Loader();
    materialDesign = new MaterialDesign();
    menuUser = new MenuUser();
    pageComment = new PageComment();
    popupEasy = new PopupEasy();
    recoverPassword = new RecoverPassword();
    registration = new Registration();
    search = new Search();
    uploadChunk = new UploadChunk();
    widgetDatePicker = new WidgetDatePicker();
    widgetSearch = new WidgetSearch();
    wysiwyg = new Wysiwyg();
    
    helper.checkMobile(true);
    helper.linkPreventDefault();
    helper.accordion("button");
    helper.menuRoot();
    helper.uploadFakeClick();
    helper.blockMultiTab(true);
    helper.bodyProgress();
    
    materialDesign.button();
    materialDesign.fabButton();
    materialDesign.iconButton();
    materialDesign.chip();
    materialDesign.dialog();
    materialDesign.drawer();
    materialDesign.checkbox();
    materialDesign.radioButton();
    materialDesign.select();
    materialDesign.slider();
    materialDesign.textField();
    materialDesign.linearProgress(".linear_progress_b", 0.5, 1, 0.75);
    materialDesign.linearProgress(".linear_progress_c", 0.5, 1);
    materialDesign.list();
    materialDesign.menu();
    materialDesign.snackbar();
    materialDesign.tabBar();
    materialDesign.fix();
    
    flashBag.setElement = materialDesign.getSnackbarMdc;
    flashBag.sessionActivity();
    
    authentication.action();
    captcha.action();
    language.action();
    menuUser.action();
    pageComment.action();
    recoverPassword.action();
    registration.action();
    search.action();
    
    widgetDatePicker.setLanguage = "en";
    //widgetDatePicker.setCurrentYear = 1984;
    //widgetDatePicker.setCurrentMonth = 4;
    //widgetDatePicker.setCurrentDay = 11;
    widgetDatePicker.setInputFill = ".widget_datePicker_input";
    widgetDatePicker.create();
    
    widgetSearch.create();
    widgetSearch.changeView();
    
    $(window).on("resize", "", (event) => {
        materialDesign.refresh();
        materialDesign.fix();
        
        widgetSearch.changeView();
    });
    
    $(window).on("orientationchange", "", (event) => {
        materialDesign.refresh();
        materialDesign.fix();
        
        widgetSearch.changeView();
    });
});
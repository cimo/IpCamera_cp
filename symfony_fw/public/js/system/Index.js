"use strict";

/* global */

let helper = null;
let ajax = null;
let authentication = null;
let captcha = null;
let flashBag = null;
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
    helper = new Helper();
    ajax = new Ajax();
    authentication = new Authentication();
    captcha = new Captcha();
    flashBag = new FlashBag();
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
    widgetDatePicker = new WidgetDatePicker(window.setting.language);
    widgetSearch = new WidgetSearch();
    wysiwyg = new Wysiwyg();
    
    helper.checkMobile();
    helper.linkPreventDefault();
    helper.accordion("button");
    helper.menuRoot();
    helper.uploadFakeClick();
    helper.blockMultiTab();
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
    
    authentication.action();
    
    captcha.action();
    
    flashBag.setElement = materialDesign.getSnackbarMdc;
    flashBag.sessionActivity();
    
    language.action();
    
    menuUser.action();
    
    pageComment.action();
    
    popupEasy.setElement = materialDesign.getDialogMdc;
    
    recoverPassword.action();
    
    registration.action();
    
    search.action();
    
    widgetSearch.action();
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
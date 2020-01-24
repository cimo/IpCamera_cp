/* global ajax, authentication, captcha, chaato, flashBag, helper, language, loader, materialDesign, menuUser, pageComment, popupEasy, recoverPassword, registration, search,
    uploadChunk, widgetDatePicker, widgetSearch, wysiwyg */

$(document).ready(function() {
    ajax.init();
    authentication.init();
    captcha.init();
    chaato.init();
    flashBag.init();
    helper.init();
    language.init();
    loader.init();
    materialDesign.init();
    menuUser.init();
    pageComment.init();
    popupEasy.init();
    recoverPassword.init();
    registration.init();
    search.init();
    uploadChunk.init();
    widgetDatePicker.init();
    widgetSearch.init();
    wysiwyg.init();
    
    helper.checkMobile(true);
    helper.linkPreventDefault();
    helper.accordion("button");
    helper.menuRoot();
    helper.uploadFakeClick();
    helper.blockMultiTab(true);
    helper.bodyProgress();
    
    authentication.action();
    captcha.action();
    language.action();
    menuUser.action();
    pageComment.action();
    recoverPassword.action();
    registration.action();
    search.action();
    
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
    
    widgetSearch.create();
    widgetSearch.changeView();
    
    widgetDatePicker.setLanguage("en");
    //widgetDatePicker.setCurrentYear(1984);
    //widgetDatePicker.setCurrentMonth(4);
    //widgetDatePicker.setCurrentDay(11);
    widgetDatePicker.setInputFill(".widget_datePicker_input");
    widgetDatePicker.create();
    
    flashBag.setElement(materialDesign.getSnackbarMdc());
    flashBag.sessionActivity();
    
    $(window).resize(function() {
        materialDesign.refresh();
        materialDesign.fix();
        
        widgetSearch.changeView();
    });
});
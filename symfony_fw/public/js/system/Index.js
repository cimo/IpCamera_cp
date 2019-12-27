/* global helper, materialDesign, widgetSearch, widgetDatePicker, flashBag, search, captcha, language, authentication, registration, recoverPassword, pageComment, menuUser */

helper.init();
helper.bodyProgress();

$(document).ready(function() {
    helper.checkMobile(true);
    helper.linkPreventDefault();
    helper.accordion("button");
    helper.menuRoot();
    helper.uploadFakeClick();
    helper.blockMultiTab(true);
    
    // Material design
    materialDesign.init();
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
    
    // Widget
    widgetSearch.init();
    widgetSearch.create();
    widgetSearch.changeView();
    
    widgetDatePicker.init();
    widgetDatePicker.setLanguage("en");
    //widgetDatePicker.setCurrentYear(1984);
    //widgetDatePicker.setCurrentMonth(4);
    //widgetDatePicker.setCurrentDay(11);
    widgetDatePicker.setInputFill(".widget_datePicker_input");
    widgetDatePicker.create();
    
    flashBag.init();
    flashBag.setElement(materialDesign.getSnackbarMdc());
    flashBag.sessionActivity();
    
    search.init();
    
    captcha.init();
    
    language.init();
    
    authentication.init();
    registration.init();
    recoverPassword.init();
    
    pageComment.init();
    
    menuUser.init();
    
    $(window).resize(function() {
        materialDesign.refresh();
        materialDesign.fix();
        
        widgetSearch.changeView();
    });
});
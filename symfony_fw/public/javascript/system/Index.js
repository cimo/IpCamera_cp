/* global utility, materialDesign, widgetSearch, widgetDatePicker, flashBag, search, captcha, language, authentication, registration, recoverPassword, pageComment, menuUser */

utility.init();
utility.bodyProgress();

$(document).ready(function() {
    utility.checkMobile(true);
    utility.linkPreventDefault();
    utility.accordion("button");
    utility.menuRoot();
    utility.uploadFakeClick();
    
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
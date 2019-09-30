function deferred(method) {
    if (window.$ !== undefined)
        method();
    else
        setTimeout(function() {deferred(method);}, 50);
}

deferred(function() {
    $(document).ready(function() {
    });
});
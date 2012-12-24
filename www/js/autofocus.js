$(document).ready(function() {
    fotavia.focusFirstElement();
});

fotavia = {
    // focus on the first empty element of any form
    focusFirstElement: function() {
        for (var i=0; i < document.forms.length; i++) {
            var elems = document.forms[i].elements;
            for (var j=0; j < elems.length; j++) {
                if (elems[j].nodeName.toLowerCase() == 'input') {
                    if (elems[j].value == '') {
                        elems[j].focus();
                        return;
                    }
                } else if (elems[j].nodeName.toLowerCase() == 'textarea') {
                    if (elems[j].innerHTML == '') {
                        elems[j].focus();
                    }
                }
            }
        }
    }
}

/**
 * Newbb Javascript Validation functions
 *
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         GNU GPL 2 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * @module             newbb
 * @since           4.3
 * @author          irmtfan
 */

/**
 * Function for validation of xoops forms: prevent user select nothing or disable some options
 * @param elName : elements name
 * @param elType : element type eg: select, checkbox
 * @param prevent: prevent user select nothing: true or false
 * @param disablecat: disable categories in forum select box: true or false
 * @param elMsg: the message
 */


function validate(elName, elType, prevent, disablecat, elMsg) {
    var i = 0;
    var el = document.getElementsByName(elName);
    var is_valid = true;
    switch (elType) {
        case 'checkbox':
            var hasChecked = false;
            if (el.length) {
                for (i = 0; i < el.length; i++) {
                    if (el[i].checked === true) {
                        hasChecked = true;
                        break;
                    }
                }
            } else {
                if (el.checked === true) {
                    hasChecked = true;
                }
            }
            if (!hasChecked) {
                if (el.length) {
                    if (prevent) {
                        el[0].checked = true;
                    }
                    el[0].focus();
                } else {
                    if (prevent) {
                        el.checked = true;
                    }
                    el.focus();
                }
                is_valid = false;
            }
            break;
        case 'select':
            el = el[0];
            if (disablecat) {
                for (i = 0; i < el.options.length; i++) {
                    if (el.options[i].value < 0) {
                        el.options[i].disabled = true;
                        el.options[i].value = '';
                    }
                }
            }

            if (el.value === '') {
                is_valid = false;
                if (prevent) {
                    for (i = 0; i < el.options.length; i++) {
                        if (el.options[i].value !== '') {
                            el.value = el.options[i].value;
                            break; // loop exit
                        }
                    }
                }
            }
            break;
    }
    return is_valid;
}

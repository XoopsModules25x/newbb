// START irmtfan - improve: add alt, title, id and innerHTML - recognize a IMG tag for src - innerHTML for non img TAGs
function ToggleBlockCategory(block, icon, src_expand, src_collapse, alt_expand, alt_collapse) {
    var Img_tag = 'IMG';
    if (document.getElementById) {
        if (document.getElementById(block).style.display === 'block') {
            document.getElementById(block).style.display = 'none';
            if (icon.nodeName === Img_tag) {
                icon.src = src_collapse;
            }
            icon.alt = alt_collapse;
            icon.id = findBaseName(src_collapse);
            SaveCollapsed(block, true);
        }
        else {
            document.getElementById(block).style.display = 'block';
            if (icon.nodeName === Img_tag) {
                icon.src = src_expand;
            }
            icon.alt = alt_expand;
            icon.id = findBaseName(src_expand);
            SaveCollapsed(block, false);
        }
    }
    else if (document.all) {
        if (document.all[block].style.display === 'block') {
            document.all[block].style.display = 'none';
            if (icon.nodeName === Img_tag) {
                icon.src = src_collapse;
            }
            icon.alt = alt_collapse;
            icon.id = findBaseName(src_collapse);
            SaveCollapsed(block, true);
        }
        else {
            document.all[block].style.display = 'block';
            if (icon.nodeName === Img_tag) {
                icon.src = src_expand;
            }
            icon.alt = alt_expand;
            icon.id = findBaseName(src_expand);
            SaveCollapsed(block, false);
        }
    }
    icon.title = icon.alt;
    if (icon.nodeName !== Img_tag) {
        icon.innerHTML = icon.alt; // to support IE7&8 use innerHTML istead of textContent
    }
}
// source: http://stackoverflow.com/questions/1991608/find-base-name-in-url-in-javascript
function findBaseName(url) {
    var fileName = url.substring(url.lastIndexOf('/') + 1);
    var dot = fileName.lastIndexOf('.');
    return dot == -1 ? fileName : fileName.substring(0, dot);
}
// END irmtfan - improve: add alt, title and innerHTML - recognize a IMG tag for src

function SaveCollapsed(objid, addcollapsed) {
    var collapsed = GetCookie(toggle_cookie);
    var tmp = "";

    if (collapsed !== null) {
        collapsed = collapsed.split(",");

        for (i in collapsed) {
            if (collapsed[i] !== objid && collapsed[i] !== "") {
                tmp = tmp + collapsed[i];
                tmp = tmp + ",";
            }
        }
    }

    if (addcollapsed) {
        tmp = tmp + objid;
    }

    expires = new Date();
    expires.setTime(expires.getTime() + (1000 * 86400 * 365));
    SetCookie(toggle_cookie, tmp, expires);
}

function SetCookie(name, value, expires) {
    if (!expires) {
        expires = new Date();
    }
    document.cookie = name + "=" + escape(value) + "; expires=" + expires.toGMTString() + "; path=/";
}

/**
 * @return {string}
 */
function GetCookie(name) {
    cookie_name = name + "=";
    cookie_length = document.cookie.length;
    cookie_begin = 0;
    while (cookie_begin < cookie_length) {
        value_begin = cookie_begin + cookie_name.length;
        if (document.cookie.substring(cookie_begin, value_begin) === cookie_name) {
            var value_end = document.cookie.indexOf(";", value_begin);
            if (value_end === -1) {
                value_end = cookie_length;
            }
            return unescape(document.cookie.substring(value_begin, value_end));
        }
        cookie_begin = document.cookie.indexOf(" ", cookie_begin) + 1;
        if (cookie_begin === 0) {
            break;
        }
    }
    return null;
}

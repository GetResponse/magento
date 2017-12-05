
var even = false;

function GetresponseMapping(customs, custom_values, select_custom) {

    var used_customs = [];

    if (customs.length === 0) {
        return;
    }

    var custom = '';
    if (typeof select_custom !== 'undefined') {
        custom = select_custom;
    } else {
        custom = customs.pop();
    }

    used_customs.push(custom);

    var tr = document.createElement('tr');

    if (even) {
        tr.classList.add('even');
    }

    var td1 = document.createElement('td');
    var td2 = document.createElement('td');
    var td3 = document.createElement('td');

    var select = document.createElement('select');
    select.setAttribute('name', 'custom_field['+custom.id_custom+']');

    custom_values.map(function(element) {
        var option = document.createElement('option');
        option.setAttribute('value', element);
        option.text = element;
        select.appendChild(option);
    });

    select.value = custom.custom_field;

    var input = document.createElement('input');
    input.setAttribute('name', 'gr_custom_field[' + custom.id_custom+']');
    input.setAttribute('class', 'input-text');
    input.setAttribute('type', 'text');
    input.setAttribute('value', custom.custom_value);

    td1.appendChild(select);
    td2.appendChild(input);

    var link = document.createElement('a');
    link.setAttribute('href', 'javascript:void(0)');
    link.setAttribute('class', 'delete');
    link.setAttribute('data-custom-id', custom.id_custom);
    link.text = 'Delete';

    link.addEventListener('click', function() {

        var used_id = this.getAttribute('data-custom-id');

        if (parseInt(used_id) === 0) {
            return;
        }

        var removed_element = used_customs.find(function(el) {
            if (used_id === el.id_custom) {
                return el;
            }
        });

        customs.push(removed_element);

        this.parentNode.parentNode.remove(this.parentNode);
    });

    td3.appendChild(link);

    tr.appendChild(td1);
    tr.appendChild(td2);
    tr.appendChild(td3);

    jQuery('.customs-table > tbody').append(tr);

    even = !even;
};
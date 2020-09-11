document.getElementById('addBtn').addEventListener('click', newtrow);
var tbody = document.getElementById("tbody");
function newtrow() {
    var trow = document.createElement('tr');
    var tdata1 = document.createElement('td');
    var tdata2 = document.createElement('td');
    var tdata3 = document.createElement('td');
    var itemName = document.createElement('input');
    var itemQty = document.createElement('input');
    var itemPrice = document.createElement('input');

    itemName.type = "text";
    itemName.placeholder = "Item name";

    itemQty.type = "text";
    itemQty.placeholder = "Qty";

    itemPrice.type = "text";
    itemPrice.placeholder = "Price";

    tdata1.appendChild(itemName);
    tdata2.appendChild(itemQty);
    tdata3.appendChild(itemPrice);
    trow.appendChild(tdata1);
    trow.appendChild(tdata2);
    trow.appendChild(tdata3);
    tbody.appendChild(trow);

}
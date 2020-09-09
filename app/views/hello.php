
    <h1>Hello {{name}}</h1>
    <h2>Welcome to {{framework}}</h2>
    <h4>{{version}}<h4>

    <button id="addBtn">Add Item</button>
    <table border="1">
    <thead>
        <tr>
            <td>Item Name</td>
            <td>Qty</td>
            <td>Price</td>
        </tr>
    </thead>
    <tbody id="tbody">
        <tr>
            <td>
                <input type="text" placeholder="Item name"/>
            </td>
            <td>
                <input type="text" placeholder="Qty"/>
            </td>
            <td>
                <input type="text" placeholder="Price"/>
            </td>
        </tr>
    </tbody>
    </table>

    {sample}
    <h5>{title}</h5>
    <p>{body}</p>
    {/sample}

    {if 'ad'=='ad'}
        <h1>Welcome, Admin!</h1>
    {endif}

    <script>
    document.getElementById('addBtn').addEventListener('click',newtrow);
    var tbody=document.getElementById("tbody");
    function newtrow(){
        var trow=document.createElement('tr');
        var tdata1=document.createElement('td');
        var tdata2=document.createElement('td');
        var tdata3=document.createElement('td');
        var itemName=document.createElement('input');
        var itemQty=document.createElement('input');
        var itemPrice=document.createElement('input');

        itemName.type="text";
        itemName.placeholder="Item name";

        itemQty.type="text";
        itemQty.placeholder="Qty";

        itemPrice.type="text";
        itemPrice.placeholder="Price";

        tdata1.appendChild(itemName);
        tdata2.appendChild(itemQty);
        tdata3.appendChild(itemPrice);
        trow.appendChild(tdata1);
        trow.appendChild(tdata2);
        trow.appendChild(tdata3);
        tbody.appendChild(trow);

    }
    </script>

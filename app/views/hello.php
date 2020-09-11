
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
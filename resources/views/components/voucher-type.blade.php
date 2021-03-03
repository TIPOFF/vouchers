<tr>
    <td>Voucher Type: {{ $sellable->name }}</td>
    <td>Quantity: {{ $cartItem->getQuantity() }}</td>
    <x-tipoff-money label="Each" :amount="$cartItem->getAmountEach()->getOriginalAmount()"/>
    <x-tipoff-money label="Discount" :amount="$cartItem->getAmountEach()->getDiscounts()"/>
    <x-tipoff-money label="Subtotal" :amount="$cartItem->getAmountTotal()->getDiscountedAmount()"/>
</tr>

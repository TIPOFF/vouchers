<div {{ $attributes }}>
    <div>{{ $deduction->name }}</div>
    <div>Code {{ $deduction->getCode() }}</div>
    <div><x-tipoff-money label="Voucher" :amount="$deduction->getAmount()"/></div>
</div>

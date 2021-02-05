<?php

return [

    'model_class' => [
        'user' => \App\Models\User::class,
        'customer' => \App\Models\Customer::class,
        'location' => \App\Models\Location::class,
    ],

    'nova_class' => [
        'user' => \App\Nova\User::class,
        'order' => \App\Nova\Order::class,
        'customer' => \App\Nova\Customer::class,
        'location' => \App\Nova\Location::class,
    ],

];

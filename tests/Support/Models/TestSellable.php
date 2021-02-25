<?php

declare(strict_types=1);


namespace Tipoff\Vouchers\Tests\Support\Models;

use Tipoff\Support\Contracts\Sellable\Booking;
use Tipoff\Support\Models\BaseModel;
use Tipoff\Support\Models\TestModelStub;

class TestSellable extends BaseModel implements Booking
{
    use TestModelStub;

    public function getMorphClass(): string
    {
        return get_class($this);
    }

    public function getDescription(): string
    {
        return 'Test Sellable';
    }

    public function getParticipants(): int
    {
        return 4;
    }
}

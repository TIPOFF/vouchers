<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Tests\Unit\Notifications;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Notifications\PartialRedemptionVoucherCreated;
use Tipoff\Vouchers\Tests\TestCase;

class PartialRedemptionVoucherCreatedTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function creates_mail()
    {
        /** @var Voucher $voucher */
        $voucher = Voucher::factory()->amount(1000)->create();

        $notification = new PartialRedemptionVoucherCreated($voucher);

        $mailData = $notification->toMail($voucher->getUser())->toArray();

        $this->assertEquals("Partial Redemption", $mailData['subject'] ?? '');
        $this->assertEquals("Your voucher was partially redeemed.", $mailData['introLines'][0] ?? '');
        $this->assertStringContainsString($voucher->decoratedAmount(), $mailData['introLines'][1] ?? '');
        $this->assertStringContainsString($voucher->code, $mailData['introLines'][2] ?? '');
    }
}

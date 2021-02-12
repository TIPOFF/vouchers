<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Tipoff\Vouchers\Models\Voucher;

class PartialRedemptionVoucherCreated extends Notification
{
    use Queueable;

    public Voucher $voucher;

    public function __construct(Voucher $voucher)
    {
        $this->voucher = $voucher;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Partial Redemption')
            ->line('Your voucher was partially redeemed.')
            ->line('New amount: ' . $this->voucher->decoratedAmount())
            ->line('New voucher code: ' . $this->voucher->code);
    }
}

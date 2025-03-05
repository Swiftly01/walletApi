<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessNotification extends Notification
{
    use Queueable;

    private $name;
    private $amount;
    private $invoice;
    


    /**
     * Create a new notification instance.
     */
    public function __construct($name, $amount, $invoice, )
    {
        $this->name = $name;
        $this->amount = $amount;
        $this->invoice = $invoice;
    
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->greeting('Dear ' .$this->name . ', ')
                    ->line('Your payment of ' . '#'. Number_format($this->amount) .' is successful')
                    ->action('Check Wallet', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}

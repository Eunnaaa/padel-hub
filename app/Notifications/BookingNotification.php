<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public ?string $url = null,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (! empty($notifiable->email) && in_array(config('mail.default'), ['smtp', 'mailgun', 'resend', 'ses', 'postmark', 'array'])) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('PadelHub - '.$this->title)
            ->greeting('Halo, '.($notifiable->name ?? 'Pemain').'!')
            ->line($this->body);

        if ($this->url) {
            $mail->action('Lihat Detail', $this->url);
        }

        return $mail->line('Terima kasih telah berolahraga bersama PadelHub!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
        ];
    }
}

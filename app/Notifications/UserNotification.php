<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class UserNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $title, $body;
    
     /**
      * Create a new notification instance.
      */
   public function __construct($title, $body)
    {
        $this->title = $title;
        $this->body  = $body;
    }


    /** 
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [FcmChannel::class, 'database'];
    }

    /** 
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    public function toFcm($notifiable)
    {
        return (new FcmMessage(notification: new FcmNotification(
            title: $this->title,
            body : $this->body,
        )))
        ->custom([
            'android' => [
                'notification' => [
                    'color' => '#0A0A0A',
                ],
                'fcm_options' => [
                    'analytics_label' => 'eventy',
                ],
            ],
            'apns' => [
                'fcm_options' => [
                    'analytics_label' => 'eventy',
                ],
            ],
        ]);
    }


    /** 
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body'  => $this->body,
        ];
    }
}
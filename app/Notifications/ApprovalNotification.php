<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApprovalNotification extends Notification
{
    use Queueable;

    public $approval;

    /**
     * Create a new notification instance.
     */
    public function __construct($approval)
    {
        $this->approval = $approval;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // return ['mail'];
        return ['database'];
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

    public function toDatabase($notifiable)
    {
        switch ($this->approval->module) {

            case 'member_create':
                $message = "New member created and waiting for approval";
                $notificationType = "member_create";
                break;

            case 'member_edit':
                $message = "A member is edited and waiting for approval";
                $notificationType = "member_edit";
                break;

            case 'offer':
                $actionLabel = match($this->approval->action_type) {
                    'create' => 'created',
                    'update' => 'edited',
                    'delete' => 'deleted',
                    default  => 'updated',
                };
                $payload   = is_array($this->approval->request_payload)
                    ? $this->approval->request_payload
                    : json_decode($this->approval->request_payload, true);
                $offerName = $payload['new']['name']   // update payload
                    ?? $payload['name']                 // create / delete payload
                    ?? 'an offer';
                $message          = "Offer \"{$offerName}\" has been {$actionLabel} and is waiting for approval";
                $notificationType = "offer_{$this->approval->action_type}";
                break;
        }

        return [
            'title' => 'Approval Request',
            'message' => $message,
            'notification_type' => $notificationType
        ];
    }
}

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
        $title = 'Approval Request';

        switch ($this->approval->module) {

            case 'member_create':
                $message          = "New member created and waiting for approval";
                $notificationType = "member_create";
                break;

            case 'member_edit':
                $message          = "A member is edited and waiting for approval";
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
                $offerName        = $payload['new']['name'] ?? $payload['name'] ?? 'an offer';
                $message          = "Offer \"{$offerName}\" has been {$actionLabel} and is waiting for approval";
                $notificationType = "offer_{$this->approval->action_type}";
                break;

            case 'stock_adjustment':
                $payload      = is_array($this->approval->request_payload)
                    ? $this->approval->request_payload
                    : json_decode($this->approval->request_payload, true);
                $itemName     = $payload['item_name'] ?? 'Unknown Item';
                $qty          = $payload['quantity']  ?? 0;
                $movementType = $payload['movement_type'] ?? 'purchase';
                $direction    = $payload['direction'] ?? 'in';
                $isPending    = $this->approval->status === 'pending';

                if ($movementType === 'purchase') {
                    if ($isPending) {
                        $title            = 'Stock Addition Request';
                        $message          = "Stock addition request for \"{$itemName}\" ({$qty} BTL) is waiting for approval.";
                        $notificationType = 'stock_add_pending';
                    } else {
                        $title            = 'Stock Added';
                        $message          = "{$qty} bottle(s) of \"{$itemName}\" added to godown.";
                        $notificationType = 'stock_added';
                    }
                } else {
                    $systemQty   = $payload['system_qty']   ?? 0;
                    $physicalQty = $payload['physical_qty'] ?? 0;
                    $diff        = ($direction === 'in' ? '+' : '−') . $qty;
                    if ($isPending) {
                        $title            = 'Stock Adjustment Request';
                        $message          = "Physical count adjustment for \"{$itemName}\" ({$systemQty} → {$physicalQty} BTL, {$diff} BTL) is waiting for approval.";
                        $notificationType = 'stock_adjust_pending';
                    } else {
                        $title            = 'Stock Adjusted';
                        $message          = "\"{$itemName}\" godown stock adjusted: {$systemQty} → {$physicalQty} BTL ({$diff} BTL).";
                        $notificationType = 'stock_adjusted';
                    }
                }
                break;

            case 'liquor_serving_create':
            case 'liquor_serving_update':
            case 'liquor_serving_delete':
                $payload   = is_array($this->approval->request_payload)
                    ? $this->approval->request_payload
                    : json_decode($this->approval->request_payload, true);
                $itemName  = $payload['item_name'] ?? $payload['name'] ?? 'Unknown';
                $actionMap = [
                    'liquor_serving_create' => 'add',
                    'liquor_serving_update' => 'update',
                    'liquor_serving_delete' => 'delete',
                ];
                $actionLabel      = $actionMap[$this->approval->module] ?? 'change';
                $message          = "Liquor menu \"{$itemName}\" {$actionLabel} request is waiting for approval.";
                $notificationType = 'liquor_serving_' . $actionLabel;
                $title            = 'Liquor Menu Approval';
                break;

            case 'bar_stock_transfer':
                $payload   = is_array($this->approval->request_payload)
                    ? $this->approval->request_payload
                    : json_decode($this->approval->request_payload, true);
                $itemName  = $payload['item_name'] ?? 'Unknown Item';
                $bottles   = $payload['bottles']   ?? 0;
                $isPending = $this->approval->status === 'pending';

                if ($isPending) {
                    $title            = 'Bar Transfer Request';
                    $message          = "Transfer request for \"{$itemName}\" ({$bottles} BTL) from godown to bar is waiting for approval.";
                    $notificationType = 'bar_transfer_pending';
                } else {
                    $title            = 'Bar Stock Transferred';
                    $message          = "{$bottles} bottle(s) of \"{$itemName}\" transferred from godown to bar.";
                    $notificationType = 'bar_transfer_done';
                }
                break;

            default:
                $message          = "A new action is waiting for approval";
                $notificationType = "general";
        }

        return [
            'title'             => $title,
            'message'           => $message,
            'notification_type' => $notificationType,
        ];
    }
}

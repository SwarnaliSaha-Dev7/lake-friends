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

            case 'plan_renewal':
                $payload    = is_array($this->approval->request_payload)
                    ? $this->approval->request_payload
                    : json_decode($this->approval->request_payload, true);
                $memberId   = $payload['member_id'] ?? null;
                $memberName = $memberId ? (\App\Models\Member::find($memberId)?->name ?? 'Unknown') : 'Unknown';
                $title      = 'Plan Renewal Approval';
                $message    = "Membership plan renewal for \"{$memberName}\" is waiting for your approval.";
                $notificationType = "plan_renewal";
                break;

            case 'member_create':
                $payload    = is_array($this->approval->request_payload)
                    ? $this->approval->request_payload
                    : json_decode($this->approval->request_payload, true);
                $memberName = $payload['name'] ?? $payload['swim_name'] ?? 'a new member';
                $title      = 'New Member Approval';
                $message    = "New member \"{$memberName}\" has been added and is waiting for your approval.";
                $notificationType = "member_create";
                break;

            case 'member_edit':
                $payload    = is_array($this->approval->request_payload)
                    ? $this->approval->request_payload
                    : json_decode($this->approval->request_payload, true);
                $memberName = $payload['name'] ?? $payload['swim_name'] ?? null;
                if (!$memberName) {
                    $memberName = \App\Models\Member::find($this->approval->entity_id)?->name ?? 'a member';
                }
                $title   = 'Member Edit Approval';
                $message = "Member \"{$memberName}\" details have been edited and are waiting for your approval.";
                $notificationType = "member_edit";
                break;

            case 'member_delete':
                $memberName = \App\Models\Member::find($this->approval->entity_id)?->name ?? 'a member';
                $title      = 'Member Deletion Approval';
                $message    = "Deletion request for member \"{$memberName}\" is waiting for your approval.";
                $notificationType = "member_delete";
                break;
            case 'food_price_update':
                $payload    = is_array($this->approval->request_payload)
                    ? $this->approval->request_payload
                    : json_decode($this->approval->request_payload, true);
                $itemName         = $payload['item_name'] ?? 'a food item';
                $oldPrice         = $payload['old_price'] ?? 0;
                $newPrice         = $payload['new_price'] ?? 0;
                $title            = 'Food Price Update Approval';
                $message          = "Price update request for \"{$itemName}\" (₹{$oldPrice} → ₹{$newPrice}) is waiting for your approval.";
                $notificationType = 'food_item_create'; // routes to food item approval list
                break;

            case 'food_item_create':
                $payload    = is_array($this->approval->request_payload)
                    ? $this->approval->request_payload
                    : json_decode($this->approval->request_payload, true);
                $itemName         = $payload['item_name'] ?? 'a food item';
                $title            = 'Food Item Add Approval';
                $message          = "New food item \"{$itemName}\" has been added and is waiting for your approval.";
                $notificationType = 'food_item_create';
                break;

            case 'food_item_update':
                $payload    = is_array($this->approval->request_payload)
                    ? $this->approval->request_payload
                    : json_decode($this->approval->request_payload, true);
                $itemName         = $payload['old_name'] ?? $payload['item_name'] ?? 'a food item';
                $title            = 'Food Item Edit Approval';
                $message          = "Food item \"{$itemName}\" has been edited and is waiting for your approval.";
                $notificationType = 'food_item_update';
                break;

            case 'food_item_delete':
                $payload    = is_array($this->approval->request_payload)
                    ? $this->approval->request_payload
                    : json_decode($this->approval->request_payload, true);
                $itemName         = $payload['item_name'] ?? 'a food item';
                $title            = 'Food Item Delete Approval';
                $message          = "Deletion request for food item \"{$itemName}\" is waiting for your approval.";
                $notificationType = 'food_item_delete';
                break;

            case 'locker_purchase':
                $memberName = \App\Models\Member::find($this->approval->entity_id)?->name ?? 'A member';
                $title      = 'Member locker purchase Approval';
                $message = "A member \"{$memberName}\" has purchased locker and waiting for approval";
                $notificationType = "locker_purchase";
                break;
            case 'add_on_purchase':
                $memberName = \App\Models\Member::find($this->approval->entity_id)?->name ?? 'A member';
                $title      = 'Member Add-on purchase Approval';
                $message = "A member \"{$memberName}\" has purchased Add-ons and waiting for approval";
                $notificationType = "add_on_purchase";
                break;

            // case 'user':
            //     $message = "New user registration waiting for approval";
            //     break;

            // case 'transaction':
            //     $message = "Transaction waiting for approval";
            //     break;
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

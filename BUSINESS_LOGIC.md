# Lake Friends — Business Logic Documentation

> A comprehensive guide to all business rules, workflows, and logic in the system. Intended for developers joining the project.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Number Generation System](#2-number-generation-system)
3. [Order Session (Running Bill / Tab)](#3-order-session-running-bill--tab)
4. [Restaurant Orders (Direct Order)](#4-restaurant-orders-direct-order)
5. [Bar Orders](#5-bar-orders)
6. [Cancelled Bills (Reorder)](#6-cancelled-bills-reorder)
7. [Offers & Promotions](#7-offers--promotions)
8. [Member Management](#8-member-management)
9. [Approval Workflow (Maker-Checker)](#9-approval-workflow-maker-checker)
10. [Liquor Serving (Spirit Menu)](#10-liquor-serving-spirit-menu)
11. [Stock Management](#11-stock-management)
12. [Wallet & Payments](#12-wallet--payments)
13. [Fine System](#13-fine-system)
14. [Locker Management](#14-locker-management)
15. [GST Calculation](#15-gst-calculation)
16. [Role-Based Access](#16-role-based-access)
17. [Key Models & Relationships](#17-key-models--relationships)
18. [Quick Reference: Common Scenarios](#18-quick-reference-common-scenarios)

---

## 1. Project Overview

**Lake Friends** is a club management system that handles two types of members:

| Member Type | Description |
|---|---|
| **Club Member** | Adult member, includes spouse details |
| **Swimming Member** | Swimming program member (child/adult), includes guardian details and health info |

Every member has:
- A **Physical Card** (used to punch in and open their profile)
- A **Digital Wallet** (all payments go through this wallet)
- A **Membership Plan** (with an expiry date that requires renewal)

---

## 2. Number Generation System

**File:** `app/helpers.php`

All system-generated numbers follow this format:

```
LF / [Financial Year] / [4-digit sequence]
Example: LF/25-26/0001
```

### Financial Year Rule
- **April to March** = one financial year (Indian system)
- April 2025 → March 2026 = `25-26`
- The 4-digit sequence **resets to 0001 at the start of each FY**

### Four Types of Numbers

| Function | Where Used | Example |
|---|---|---|
| `generateSessionNo()` | When creating an OrderSession | LF/25-26/0001 |
| `generateOrderNo()` | When creating a RestaurantOrder or BarOrder | LF/25-26/0002 |
| `generateMrNo()` | At bill generation time | LF/25-26/0003 |
| `generateBillNo()` | At bill generation time | LF/25-26/0004 |

> **Important:** `mr_no` and `bill_no` are NOT assigned when an order is created. They are assigned only when the **bill is generated**.

---

## 3. Order Session (Running Bill / Tab)

**Controller:** `OrderSessionController`
**Model:** `OrderSession`

### Concept
A member can place multiple orders during one sitting. All orders are grouped under a session and billed together at the end.

```
Card Punch → Create Session → Add Orders (one or many) → Generate Bill → Billed
```

### Status Flow

```
[open] ──────────────────────────────────────► [billed]
  │                                          (wallet deducted once for all orders)
  └──► [cancelled]
       (no wallet deduction if cancelled while open)
       (full wallet refund if cancelled after billing)
```

### Session Creation Rules

1. **A member can only have one active session per day.** If an open session already exists for today, it is returned — a new one is not created.
2. `session_no` is assigned at creation time.
3. `created_by` records which operator opened the session.

### Order Addition Rules

Two checks are performed when adding an order to a session:

**① Wallet Check:**
```
Current wallet balance >= (all existing pending orders total) + (new order amount)
```
If insufficient → error: "Insufficient wallet balance"

**② Stock Check (Liquor items only):**
```
Bar stock >= required quantity
Beer  → checked in bottles
Spirit → checked in ml  (quantity × volume_ml)
```

> **Important:** When an order is added, **bar stock is deducted immediately**, but the wallet is NOT yet charged.

### Bill Generation Rules

When "Generate Bill" is triggered:
1. All `pending` orders are totalled.
2. The entire amount is deducted from the wallet in **one single wallet transaction**.
3. All orders → status: `paid`
4. Session → status: `billed`
5. `mr_no` and `bill_no` are generated at this point using the session's original `created_at` date.

### Cancellation Rules

| Scenario | Stock | Wallet |
|---|---|---|
| Cancel while session is open | Stock is restored | No wallet change |
| Cancel & Refund (after billing) | Stock is NOT restored (items consumed) | Full refund issued |

### Financial Calculation

```
taxable_amount  = sum(item.quantity × item.unit_price) for all items
discount_amount = sum of offer discounts applied
gst_amount      = taxable_amount × 10%  (fixed rate)
net_amount      = taxable_amount - discount_amount + gst_amount
```

---

## 4. Restaurant Orders (Direct Order)

**Controller:** `RestaurantOrderController`

### Difference from Session Orders

| Aspect | Session Order | Direct Order |
|---|---|---|
| Payment | Deducted once at bill generation | Deducted immediately on creation |
| MR / Bill No | Assigned at bill generation | Assigned immediately on creation |
| Grouping | Multiple orders under one session | Each order is standalone |

### Status Flow

```
pending → paid (immediate wallet deduction)
       ↘ delivered
       ↘ cancelled (wallet refund + stock restore)
```

### Cancellation Rule
- Orders with status `delivered` cannot be cancelled.
- Cancelling an order triggers: wallet refund + bar stock restoration.

---

## 5. Bar Orders

**Controller:** `BarOrderController`

Same flow as Restaurant Orders, but exclusively for **liquor items** — no food, no sessions.

### Offer Support

Three offer types can be applied to bar orders:

**① B1G1 (Buy X Get Y Free)**
```
Example: Buy 2 Get 1
Total quantity = 3 (2 paid + 1 free)
Charged for 2 bottles only
Display: "2 BTL + 1 Free = 3 BTL"

If quantity = 1 (offer not taken advantage of) → displays just "1 BTL"
```

**② Percentage Discount**
```
price = unit_price × (1 - discount_value / 100) × quantity
```

**③ Flat Discount**
```
price = (unit_price × quantity) - discount_value
```

### Beer vs Spirit Units

| Item Type | Unit | Stock Tracked In | Display |
|---|---|---|---|
| Beer | `btl` | Bottles | "3 BTL" |
| Spirit (Whiskey etc.) | `ml` | Millilitres | "60ml × 3" |

### Statistics

- **Top Selling Liquor**: The item with the highest total revenue (non-cancelled)
- **Total Selling**: Sum of all non-cancelled order amounts

---

## 6. Cancelled Bills (Reorder)

**Controller:** `CancelledBillController`

### Use Case
A session was cancelled, but the manager wants to re-create that order retroactively.

### Flow

```
Cancelled Bills List → Edit Order → Add Items → Place Order
→ Session status becomes: open
→ Go to Current Order page to generate the bill
```

### Reorder Logic (Step by Step)

1. Find the cancelled session within the current financial year.
2. Create a new order under that session.
3. **Backdate** the order's `created_at` to the original session date.
4. Generate `order_no` using the original session date.
5. Deduct bar stock using the original date (stock ledger entry is backdated).
6. Change session status from `cancelled` → `open`.
7. The user then generates the bill from the Current Order page.

> **Why backdate?** MR/Bill numbers must be sequential within their financial year date range. Backdating ensures the numbers are consistent with the original transaction date.

---

## 7. Offers & Promotions

**Controller:** `OfferManageController`
**Models:** `Offer`, `OfferItem`, `OfferType`

### Offer Types

| Type Slug | Name | How It Works |
|---|---|---|
| `b1g1` | Buy X Get Y | Free items added, total reduced |
| `percentage` | X% OFF | price × (1 - X/100) |
| `flat` | Rs. X OFF | price - X |

### Business Rules

1. **An item cannot be in two active offers at the same time.**
   - Items already in an active offer are shown as disabled when creating a new offer.
   - Items from expired offers become available again for new offers.

2. **Offer Expiry:**
   - When `end_at` has passed, the offer is displayed as "Expired" (display-level logic, no DB status change).
   - Expired offer items can be added to new offers.

3. **Admin vs Operator:**
   - Admin creates → directly `active`
   - Operator creates → `pending` approval

4. **Edit / Delete:**
   - Cannot edit or delete an offer that has a pending approval request.
   - If an **update/delete is rejected** → offer reverts to `active` (unchanged).
   - If a **create is rejected** → offer status becomes `rejected`.
   - If approved → offer becomes `active`.

### Offer Scope (applies_to)

| Value | Where Displayed |
|---|---|
| `food` | Food items only |
| `liquor` | Liquor items only |
| `both` | Both food and liquor |

---

## 8. Member Management

**Controllers:** `ClubMemberController`, `SwimmingMemberController`

### Member Creation Flow (Operator)

```
1. Basic info (name, email, phone, address, image)
2. Membership form details (type-specific fields)
3. Plan selection
4. Payment entry
5. Card assignment
6. Wallet created with balance: 0
7. Submitted for approval
```

### Member Status Flow

```
[pending] ──► (approved) ──► [active]
           └► (rejected) ──► [rejected]
                              card unassigned, purchase cancelled
```

### Membership Plan Types

| Plan Type | Expiry |
|---|---|
| Lifetime | Never expires |
| Duration-based | Expires after N months |

### Plan Renewal Flow

```
Renewal request submitted → ActionApproval created (pending)
→ Approved: purchase history status → "active", new expiry date set
→ Rejected: purchase history status → "cancelled"
```

### Member Card System

- Each member is assigned one **physical card**.
- Punching the card opens the member's profile modal.
- Card statuses: `active` or `inactive`
- When a member is rejected or deleted, the card is unassigned and becomes available for reuse.

---

## 9. Approval Workflow (Maker-Checker)

**Controller:** `ActionApprovalController`
**Model:** `ActionApproval`

### Two-Tier Approval Model

```
Maker (Operator) → Submits request → Checker (Admin / Another Operator) → Approve / Reject
```

### All Modules and Their Approval Logic

| Module | Action | On Approve | On Reject |
|---|---|---|---|
| `offer` | create | status → active | status → rejected |
| `offer` | update | New values applied | status → active (reverted) |
| `offer` | delete | Offer deleted | status → active (reverted) |
| `member_create` | create | status → active | status → rejected, card unassigned |
| `member_edit` | update | New values applied | Old values remain |
| `member_delete` | delete | Member deleted, card/locker released | Nothing changes |
| `plan_renewal` | create | Purchase → active | Purchase → cancelled |
| `food_item_create` | create | is_active → 1 | Item deleted |
| `food_item_update` | update | New values applied | Nothing changes |
| `food_item_delete` | delete | Item deleted | Nothing changes |
| `liquor_item_create` | create | is_active → 1 | Item deleted |
| `food_price_update` | update | New price record activated | Nothing changes |
| `bar_stock_transfer` | create | Godown → Bar transfer executed | Nothing changes |
| `stock_adjustment` | create | Stock ledger entry created | Nothing changes |
| `locker_purchase` | create | Locker → allocated | Locker → available, wallet refund |
| `add_on_purchase` | create | Add-on activated | Add-on deleted, wallet refund |
| `liquor_serving_create` | create | is_active → 1 | Serving deleted |
| `liquor_serving_update` | update | New values applied | Nothing changes |
| `liquor_serving_delete` | delete | Serving deleted | Nothing changes |

### Notifications
When an operator submits a request, all admins and other operators receive a notification.

### Admin Auto-Approval
When an admin creates or modifies something, an `ActionApproval` record is still created but immediately marked as `approved` — no waiting required.

---

## 10. Liquor Serving (Spirit Menu)

**Controller:** `LiquorServingController`
**Model:** `LiquorServing`

### Purpose
Spirits (whiskey, vodka, rum, etc.) are served in predefined portion sizes. Each size is a separate menu entry.

```
Example:
  Johnnie Walker Black → 30ml → Rs. 500
  Johnnie Walker Black → 60ml → Rs. 900
```

### Available Volumes
- 30 ml (Single Peg)
- 60 ml (Double Peg)

### Duplicate Prevention
The same food item + same volume_ml combination cannot be created twice.

### Auto-Generated Name
```
name = food_item.name + " " + volume_ml + "ml"
Example: "Johnnie Walker Black 60ml"
```

### Approval
- Admin creates → immediately active
- Operator creates → pending approval (same maker-checker flow)

---

## 11. Stock Management

### Stock Locations

| Location | Description |
|---|---|
| **Godown** | Main warehouse. All stock arrives here first. |
| **Bar** | The bar counter. Stock is served from here. |

### Stock Transfer (Godown → Bar)

```
Transfer request created → Approval →
On approve: Godown stock decreases + Bar stock increases + StockLedger entries created
```

### Stock Deduction on Order Creation

Beer:
```
deduct_qty = order_item.quantity  (in bottles)
```

Spirit:
```
deduct_qty = order_item.quantity × volume_ml  (total ml deducted)
```

### Stock Restoration on Order Cancellation

When an order is cancelled:
- A `StockLedger` entry is created with `direction: "in"` and `movement_type: "adjustment"`
- `FoodItemCurrentStock.quantity` is increased accordingly

### StockLedger Fields Reference

| Field | Value | Meaning |
|---|---|---|
| `direction` | `in` | Stock is increasing |
| `direction` | `out` | Stock is decreasing |
| `movement_type` | `sale` | Result of an order |
| `movement_type` | `adjustment` | Manual correction or cancellation |
| `movement_type` | `transfer` | Godown → Bar transfer |
| `reference_type` | `order` | Linked to a specific order |

---

## 12. Wallet & Payments

### Wallet System

Every member has exactly one `Wallet`:
- `current_balance` — running balance
- All orders, memberships, lockers, and add-ons are paid through the wallet

### Transaction Flow

**Debit (money deducted):**
```
Order payment / Bill / Membership / Locker / Add-on
→ WalletTransaction created (direction: "debit")
→ Wallet.current_balance decreases
```

**Credit (money returned):**
```
Order cancel / Session cancel & refund / Approval rejection
→ WalletTransaction created (direction: "credit")
→ Wallet.current_balance increases
```

### Transaction Types (txn_type)

| Type | When Created |
|---|---|
| `Food and Liquor Order` | Restaurant/Session order payment |
| `Bar Order` | Bar order payment |
| `refund` | Any cancellation refund |
| `Membership Fee` | Plan purchase or renewal |
| `Locker Fee` | Locker allocation payment |
| `Add-on Fee` | Add-on purchase |

### PaymentHistory

Records the details of membership-related payments:
- MR No, Bill No
- GST amount
- Bank information
- Payment mode
- Status: `success`, `pending`, `refunded`, `failed`

---

## 13. Fine System

**Model:** `MemberFine`

### How Fines Work

Fine rules are configured in the `FineRule` model (admin-managed). Each rule defines a reason and an amount.

### Fine Statuses

| Status | Meaning |
|---|---|
| `pending` | Not yet paid |
| `paid` | Cleared |

### Fines at Membership Renewal

If a member has pending fines, they are included in the renewal fee:

```
total_fee = membership_fee + pending_fine_amount + gst
```

---

## 14. Locker Management

**Model:** `LockerAllocation`

### Locker Status Flow

```
[available] ──► (purchased) ──► [allocated]
            └► (rejected / returned) ──► [available]
```

### Refund Logic on Approval Rejection

| Member Type | Refund Method |
|---|---|
| Club Member | Amount credited back to wallet |
| Swimming Member | `PaymentHistory.status` → "refunded" (no wallet transaction) |

---

## 15. GST Calculation

- Food/liquor orders use a **fixed 10% GST rate** (hardcoded).
- Membership fees use a configurable rate from the `GstRate` table.

```
gst_amount = taxable_amount × (gst_percentage / 100)
net_amount = taxable_amount - discount + gst_amount
```

---

## 16. Role-Based Access

| Role | Capabilities |
|---|---|
| **Admin** | Full access; self-approves all actions |
| **Operator** | Can create and modify; sensitive actions require admin/checker approval |

### Key Differences

- Admin-created records are immediately active.
- Operator-created records start as `pending` and require a checker to approve.
- An operator cannot approve their own requests.

---

## 17. Key Models & Relationships

```
Member
  ├── Wallet (1:1)
  ├── Card → via MemberCardMapping (1:1)
  ├── MembershipFormDetail (1:1)
  ├── MembershipPurchaseHistory (1:many)
  ├── MemberFine (1:many)
  └── MembershipType (belongs to)

OrderSession
  ├── Member (belongs to)
  ├── RestaurantOrder (1:many)
  │     └── RestaurantOrderItem (1:many)
  │           └── FoodItem (belongs to)
  └── WalletTransaction (belongs to, created at billing)

Offer
  ├── OfferType (belongs to)
  └── OfferItem (1:many)
        └── FoodItem (belongs to)

FoodItemCurrentStock
  ├── FoodItem (belongs to)
  ├── Location (belongs to)
  └── StockWarehouse (belongs to)

StockLedger
  └── FoodItem (belongs to)
      (movement audit trail — never deleted)

ActionApproval
  ├── maker_user: User (belongs to)
  ├── checker_user: User (belongs to)
  └── entity: polymorphic → Offer / Member / FoodItem / etc.

Wallet
  ├── Member (belongs to)
  └── WalletTransaction (1:many)
```

---

## 18. Quick Reference: Common Scenarios

### Scenario 1: Member Arrives and Places a Food/Liquor Order

```
1. Staff punches member's card → member info modal opens
2. Click "Create Order" → OrderSession is created (or existing open session returned)
3. Select food/liquor items → stock check + wallet check performed
4. Click "Place Order" → order saved as pending, bar stock deducted
5. Member orders again → another order added to the same session
6. Click "Generate Bill" → wallet deducted once for all pending orders
```

### Scenario 2: Bar Order (Immediate Payment)

```
1. Select member
2. Select liquor items and quantities
3. Offers are automatically applied if applicable
4. Click "Place Order" → stock deducted + wallet charged immediately
```

### Scenario 3: Operator Creates an Offer

```
1. Click "Add Offer" → select items, discount type, and date range
2. Submit → ActionApproval record created with status "pending"
3. Admin receives a notification
4. Admin approves → Offer becomes "active"
5. Admin rejects → Offer remains "rejected"
```

### Scenario 4: Reorder from a Cancelled Bill

```
1. Go to Cancelled Bills list → click "Edit Order"
2. Add items in the popup
3. Click "Place Order" → old session reopened, order backdated to original session date
4. Go to Current Order page → generate bill
```

### Scenario 5: Cancel & Refund a Billed Session

```
1. Open Current Order → select the session
2. Click "Cancel & Refund" (visible only on billed sessions)
3. Full wallet refund is issued
4. Bar stock is NOT restored (items are considered consumed)
```

### Scenario 6: Member Renewal with Pending Fine

```
1. Open member profile → click Renew
2. System fetches pending fines automatically
3. Total = membership fee + fine amount + GST
4. Submit → pending approval
5. On approval → wallet deducted, new expiry date set, fines cleared
```

---

*Last updated: March 2026*

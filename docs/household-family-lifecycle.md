# Household and Family Lifecycle

This document defines the canonical behavior of household/family data in the system.

## Core Entities

- `users` (account-bearing residents)
- `households` (one row per head)
- `family_members` (non-account member records linked to a head)

## Canonical State Rules

### Head resident

- `users.head_of_family = 'yes'`
- `users.head_of_family_id = null`
- has a corresponding `households.head_id = users.id`
- must not carry `household_connection_type` or `connection_note`

### Linked resident member

- `users.head_of_family = 'no'`
- `users.head_of_family_id = <head user id>`
- `users.household_id = <head household id>`
- `users.family_link_status = 'linked'`

### Non-account family member

- stored in `family_members`
- must have:
  - `head_user_id`
  - `household_id`
- may optionally reference `linked_user_id` (for account linkage to existing resident)

## Allowed Transitions

- `link` (unlinked resident -> linked to head)
- `reassign` (linked resident -> different head)
- `unlink` (linked resident -> unlinked)
- every transition requires:
  - `transfer_reason_code`
  - `transfer_reason_details` when reason is `other`

## Recovery and Safety

- `family_members` uses soft-delete
- restore window: 7 days
- restore after 7 days is blocked

## Auto-Sync Behavior

When a head profile address/purok/resident type changes from admin:

- linked `users` under that head are cascaded to the new values
- linked `family_members` under that head are cascaded to the new values

## Transfer Audit Trail

Structured transfer history is stored in `household_head_transfer_logs`:

- resident
- old head
- new head
- actor
- action (`link`, `reassign`, `unlink`)
- reason code + details
- timestamp

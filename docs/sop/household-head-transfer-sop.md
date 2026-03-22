# SOP: Household Head Transfer (Admin)

Use this standard operating procedure when changing a resident's head-of-family linkage.

## Preconditions

- Actor must be Admin/Super Admin.
- Target resident must be an approved resident account.
- New head (if linking/reassigning) must be:
  - approved
  - not suspended
  - marked as head (`head_of_family = yes`)
  - not linked under another head

## Procedure

1. Open **Admin -> Residents -> Resident Profile**.
2. In **Family Linking** section:
   - choose target head (for link/reassign), or use unlink action.
3. Complete **Head Transfer Reason** modal:
   - select reason code
   - add details when reason is `other`
4. Submit action.

## Required Recording

Every action records:

- action type (`link`, `reassign`, `unlink`)
- resident id
- old head id (if any)
- new head id (if any)
- actor id
- reason code/details
- timestamp

## Validation Failures and Handling

- Same head selected -> reject action.
- Inactive/suspended head -> reject action.
- Target resident has dependent family records and cannot be nested -> reject action.
- Missing reason -> reject action.

## Post-Action Verification

- Resident profile shows updated link status and linked head.
- Transfer appears in **Head Transfer History** panel.
- Audit logs include before/after snapshot.

## Rollback Guidance

- If wrong reassignment happened:
  - perform new reassign action back to correct head
  - use reason code `correction_error`
  - include short details of corrective action


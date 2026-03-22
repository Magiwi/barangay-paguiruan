# Phase A UAT Checklist: Household and Family Workflows

Use this checklist after deploying the latest household/family changes to staging or production-like environment.

## Environment Prep

- [ ] Admin account available.
- [ ] At least one approved resident marked as head of family.
- [ ] At least one approved resident linked to a head.
- [ ] At least one non-account family member record under a head.

## Test Matrix

### 1) Link Family Requires Reason

- [ ] Open admin resident profile -> Family Linking.
- [ ] Attempt to link a resident to a head without a reason.
- [ ] Expected: action blocked with validation error.
- [ ] Attempt same action with valid reason.
- [ ] Expected: link succeeds and resident status becomes linked.

### 2) Invalid Head Target is Blocked

- [ ] Prepare a suspended or inactive resident marked as head.
- [ ] Attempt to link a member to this head.
- [ ] Expected: action blocked with clear error.

### 3) Unlink Family Requires Reason

- [ ] From admin resident profile, unlink a currently linked resident.
- [ ] Expected: requires reason and then succeeds.
- [ ] Expected DB state:
  - `head_of_family_id` = null
  - `family_link_status` = `unlinked`

### 4) Family Member Soft Delete and Restore (Within 7 Days)

- [ ] As head resident, remove a family member in profile family tab.
- [ ] Expected: member removed from active list.
- [ ] Expected: member appears in "Recently Removed (Restorable)".
- [ ] Click Restore.
- [ ] Expected: member reappears in active list.

### 5) Restore Window Expiry

- [ ] Use test data older than 7 days since removal.
- [ ] Attempt to restore.
- [ ] Expected: restore blocked with expiry message.

### 6) Head Address Cascade Sync

- [ ] As admin, update head resident address/purok/resident_type.
- [ ] Expected: linked resident users update to the same household address fields.
- [ ] Expected: non-account `family_members` records update similarly.

### 7) Family List Pagination

- [ ] Ensure a head has more than 12 family members.
- [ ] Open profile family tab.
- [ ] Expected: pagination controls visible.
- [ ] Expected: page navigation keeps data consistent.

## Sign-off

- [ ] QA passed all required checks.
- [ ] Any failed check has ticket reference and owner.
- [ ] Deployment approved by product owner/admin lead.

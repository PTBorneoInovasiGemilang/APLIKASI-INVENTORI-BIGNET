# BIGNET DATABASE BLUEPRINT

## PURPOSE
Memetakan database aktual terhadap business flow.

## KNOWN TABLES FROM PROJECT MEMORY

- users
- settings
- item_categories
- units
- warehouses
- locations
- master_items
- master_item_attachments
- stocks
- serial_numbers
- serial_movements
- stock_ledgers
- incoming
- incoming_items
- outgoing
- outgoing_items
- transfers
- transfer_items
- transfer_item_serials
- submissions
- submission_items
- loans
- loan_items
- loan_item_serials
- returns
- return_items
- installations
- installation_items
- stock_adjustments
- stock_adjustment_items
- audit_logs
- attachments
- schema_migrations

## VERIFICATION RULE

Daftar di atas berasal dari project memory dan harus diverifikasi terhadap schema aktual.

Untuk setiap entity dokumentasikan:
- purpose;
- primary key;
- foreign keys;
- timestamps;
- status;
- unique constraints;
- business relation;
- audit relation;
- timeline/movement relation.

## INVENTORY INTEGRITY

Non-serialized:
`Beginning + Incoming - Outgoing ± Transfer/Adjustment = Ending`

Serialized:
- unique serial;
- one coherent active location/custody state;
- movement event per state/location change;
- no contradictory concurrent states.

## TRANSACTION RULE

Multi-step inventory operation harus atomic.

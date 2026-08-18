# BIGNET BUSINESS FLOW

## PURPOSE

Dokumen ini menjelaskan alur bisnis kanonik. Jangan mengubah alur bisnis berdasarkan struktur source yang kebetulan sudah ada.

## CORE PRINCIPLE

Setiap barang harus dapat ditelusuri:
`Item/Serial → Location → Custodian → Transaction → Movement → Timeline → Audit → Evidence`

## FLOW 01 — INCOMING
`Source/Vendor → Receive → Validate → Inventory Increase → Movement → Audit → Timeline`

## FLOW 02 — REQUEST
`Technician → Draft → Submit → Approval → Processing → Outgoing/Fulfillment → Assignment`

## FLOW 03 — OUTGOING
`Authorized Issue → Validate Availability → Issue → Inventory Decrease → Custody/Assignment → Movement → Audit → Timeline`

## FLOW 04 — TRANSFER
`Initiate → Validate Source → Dispatch → Receive Destination → Movement → Audit → Timeline`

## FLOW 05 — BORROWING
`Available → Borrow/Assign → Custody → Return`

## FLOW 06 — RETURN
`Custody → Return → Inspection → Condition Decision → Inventory/Lifecycle Update → Audit → Timeline`

## FLOW 07 — INSTALLATION
`Available/Assigned → Assignment → Installation → Installed Location → Evidence → Timeline`

## FLOW 08 — DAMAGE
`Report → Evidence → Review → Damage State → Repair/Retire/Replace`

## FLOW 09 — LOST
`Report → Evidence → Last Known Custody/Location → Investigation → Resolution → Audit`

## FLOW 10 — STOCK OPNAME
`Count → Compare → Variance → Review → Approved Adjustment → Audit`

## FLOW 11 — ADJUSTMENT
Adjustment adalah transaksi koreksi yang dapat diaudit.
Adjustment bukan pengganti Stock Opname.

## TRACEABILITY RULES
- Event yang benar-benar terjadi harus tercatat.
- Timestamp relevan wajib.
- Actor relevan wajib.
- Source/destination wajib bila movement.
- Serial wajib dilacak untuk serialized asset.
- Evidence wajib bila workflow memerlukannya.

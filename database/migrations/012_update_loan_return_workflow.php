<?php
class UpdateLoanReturnWorkflow {
 public function up(PDO $pdo) {
  $has=function($t,$c)use($pdo){$q=$pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?");$q->execute([$t,$c]);return (bool)$q->fetchColumn();};
  $fk=function($t,$c)use($pdo){$q=$pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME=? AND CONSTRAINT_NAME=?");$q->execute([$t,$c]);return (bool)$q->fetchColumn();};
  if(!$has('loans','approved_by'))$pdo->exec("ALTER TABLE loans ADD approved_by INT NULL AFTER processed_by");
  if(!$has('loans','approved_at'))$pdo->exec("ALTER TABLE loans ADD approved_at DATETIME NULL AFTER approved_by");
  if(!$has('loans','approval_notes'))$pdo->exec("ALTER TABLE loans ADD approval_notes TEXT NULL AFTER approved_at");
  if(!$has('loans','handed_over_by'))$pdo->exec("ALTER TABLE loans ADD handed_over_by INT NULL AFTER approval_notes");
  if(!$has('loans','handed_over_at'))$pdo->exec("ALTER TABLE loans ADD handed_over_at DATETIME NULL AFTER handed_over_by");
  if(!$fk('loans','fk_loans_approved_by'))$pdo->exec("ALTER TABLE loans ADD CONSTRAINT fk_loans_approved_by FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE RESTRICT");
  if(!$fk('loans','fk_loans_handed_over_by'))$pdo->exec("ALTER TABLE loans ADD CONSTRAINT fk_loans_handed_over_by FOREIGN KEY (handed_over_by) REFERENCES users(id) ON DELETE RESTRICT");
  $pdo->exec("ALTER TABLE loans MODIFY status VARCHAR(50) NOT NULL DEFAULT 'PENDING_APPROVAL'");
  $pdo->exec("ALTER TABLE loan_item_serials MODIFY status ENUM('PENDING','BORROWED','RETURNED') NOT NULL DEFAULT 'PENDING'");
  $pdo->exec("ALTER TABLE return_items MODIFY master_item_id INT NULL");
  if(!$has('return_items','loan_manual_item_id'))$pdo->exec("ALTER TABLE return_items ADD loan_manual_item_id INT NULL AFTER loan_item_id");
  if(!$fk('return_items','fk_return_items_manual'))$pdo->exec("ALTER TABLE return_items ADD CONSTRAINT fk_return_items_manual FOREIGN KEY (loan_manual_item_id) REFERENCES loan_manual_items(id) ON DELETE RESTRICT");
 }
}

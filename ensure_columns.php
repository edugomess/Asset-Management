<?php
/**
 * Utility function to safely add a column to a table if it doesn't already exist.
 * This avoids repetitive DDL overhead and is compatible with older MySQL versions.
 */
function ensure_column($conn, $table, $column, $definition) {
    // Check if column already exists
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($res && $res->num_rows === 0) {
        // Column doesn't exist, add it
        $sql = "ALTER TABLE `$table` ADD COLUMN `$column` $definition";
        if (!$conn->query($sql)) {
            error_log("Failed to add column $column to $table: " . $conn->error);
            return false;
        }
        return true;
    }
    return true;
}

/**
 * Utility function to safely create a table if it doesn't exist.
 */
function ensure_table($conn, $table, $definition) {
    $sql = "CREATE TABLE IF NOT EXISTS `$table` ($definition)";
    if (!$conn->query($sql)) {
        error_log("Failed to ensure table $table: " . $conn->error);
        return false;
    }
    return true;
}
?>

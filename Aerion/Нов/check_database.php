<?php
require_once 'config.php';

echo "<h2>Database Structure Check</h2>";

// Check tables
$tables = ['users', 'flights', 'bookings', 'check_ins'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW CREATE TABLE $table");
    if ($result) {
        echo "<h3>Table $table exists</h3>";
        $row = $result->fetch_assoc();
        echo "<pre>" . $row['Create Table'] . "</pre>";
        
        // Count records
        $count = $conn->query("SELECT COUNT(*) as count FROM $table")->fetch_assoc()['count'];
        echo "Records in table: $count<br><br>";
        
        // Show sample data
        $data = $conn->query("SELECT * FROM $table LIMIT 1");
        if ($data->num_rows > 0) {
            echo "Sample record:<br>";
            echo "<pre>";
            print_r($data->fetch_assoc());
            echo "</pre>";
        }
    } else {
        echo "<h3>Table $table does not exist</h3>";
    }
    echo "<hr>";
}

// Check foreign key relationships
echo "<h2>Foreign Key Relationships</h2>";
$relationships = [
    'bookings' => ['users' => 'user_id', 'flights' => 'flight_id'],
    'check_ins' => ['bookings' => 'booking_id']
];

foreach ($relationships as $table => $refs) {
    foreach ($refs as $ref_table => $column) {
        $sql = "SELECT COUNT(*) as count FROM $table t 
                LEFT JOIN $ref_table r ON t.$column = r.id 
                WHERE r.id IS NULL";
        $orphans = $conn->query($sql)->fetch_assoc()['count'];
        echo "Orphaned records in $table referencing $ref_table: $orphans<br>";
    }
}
?> 
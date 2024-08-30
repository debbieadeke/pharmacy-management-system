<?php
require "db_connection.php";

if($con) {
    if (isset($_GET["action"])) {
        switch ($_GET["action"]) {
            case "delete":
                $id = intval($_GET["id"]); // Ensure ID is an integer
                $stmt = $con->prepare("DELETE FROM medicines WHERE ID = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    showMedicinesStock("0");
                }
                $stmt->close();
                break;

            case "edit":
                showMedicinesStock($_GET["id"]);
                break;

            case "update":
                updateMedicineStock(
                    $_GET["id"], $_GET["batch_id"], ucwords($_GET["expiry_date"]),
                    floatval($_GET["quantity"]), floatval($_GET["mrp"]), floatval($_GET["rate"])
                );
                break;

            case "cancel":
                showMedicinesStock("0");
                break;

            case "search":
                searchMedicineStock(strtoupper($_GET["text"]), $_GET["tag"]);
                break;
        }
    }
}

function showMedicinesStock($id) {
    global $con;
    $stmt = $con->prepare("SELECT * FROM medicines INNER JOIN medicines_stock ON medicines.NAME = medicines_stock.NAME");
    $stmt->execute();
    $result = $stmt->get_result();
    $seq_no = 0;
    while($row = $result->fetch_assoc()) {
        $seq_no++;
        if($row['BATCH_ID'] == $id) {
            showEditOptionsRow($seq_no, $row);
        } else {
            showMedicineStockRow($seq_no, $row);
        }
    }
    $stmt->close();
}

function showMedicineStockRow($seq_no, $row) {
    echo "<tr>
        <td>{$seq_no}</td>
        <td>{$row['NAME']}</td>
        <td>{$row['PACKING']}</td>
        <td>{$row['GENERIC_NAME']}</td>
        <td>{$row['BATCH_ID']}</td>
        <td>{$row['EXPIRY_DATE']}</td>
        <td>{$row['SUPPLIER_NAME']}</td>
        <td>{$row['QUANTITY']}</td>
        <td>{$row['MRP']}</td>
        <td>{$row['RATE']}</td>
        <td><button href='' class='btn btn-info btn-sm' onclick='editMedicineStock(\"{$row['BATCH_ID']}\");'>
            <i class='fa fa-pencil'></i>
        </button></td>
    </tr>";
}

function showEditOptionsRow($seq_no, $row) {
    echo "<tr>
        <td>{$seq_no}</td>
        <td>{$row['NAME']}</td>
        <td>{$row['PACKING']}</td>
        <td>{$row['GENERIC_NAME']}</td>
        <td><input type='text' class='form-control' value='{$row['BATCH_ID']}' placeholder='Batch ID' id='batch_id'></td>
        <td><input type='text' class='form-control' value='{$row['EXPIRY_DATE']}' placeholder='Expiry' id='expiry_date'></td>
        <td>{$row['SUPPLIER_NAME']}</td>
        <td><input type='number' class='form-control' value='{$row['QUANTITY']}' placeholder='Quantity' id='quantity'></td>
        <td><input type='number' class='form-control' value='{$row['MRP']}' placeholder='MRP' id='mrp'></td>
        <td><input type='number' class='form-control' value='{$row['RATE']}' placeholder='Rate' id='rate'></td>
        <td><button class='btn btn-success btn-sm' onclick='updateMedicineStock(\"{$row['ID']}\");'><i class='fa fa-edit'></i></button>
            <button class='btn btn-danger btn-sm' onclick='cancel();'><i class='fa fa-close'></i></button></td>
    </tr>";
}

function updateMedicineStock($id, $batch_id, $expiry_date, $quantity, $mrp, $rate) {
    global $con;
    $stmt = $con->prepare("UPDATE medicines_stock SET BATCH_ID = ?, EXPIRY_DATE = ?, QUANTITY = ?, MRP = ?, RATE = ? WHERE ID = ?");
    $stmt->bind_param("ssdddi", $batch_id, $expiry_date, $quantity, $mrp, $rate, $id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        showMedicinesStock("0"); // Refresh or redirect to show the updated list
    }
    $stmt->close();
}

function searchMedicineStock($text, $column) {
  global $con;
  $seq_no = 0;

  if ($column == 'EXPIRY_DATE') {
      // Current date in mm/yy format
      $currentDate = date('m/y'); // Format the current date as mm/yy
      
      $stmt = $con->prepare("SELECT * FROM medicines 
                             INNER JOIN medicines_stock ON medicines.NAME = medicines_stock.NAME 
                             WHERE STR_TO_DATE(CONCAT('01/', medicines_stock.EXPIRY_DATE), '%d/%m/%y') < STR_TO_DATE(CONCAT('01/', ?), '%d/%m/%y')");
      $stmt->bind_param("s", $currentDate);
  } else if ($column == 'QUANTITY') {
      $stmt = $con->prepare("SELECT * FROM medicines 
                             INNER JOIN medicines_stock ON medicines.NAME = medicines_stock.NAME 
                             WHERE medicines_stock.QUANTITY = 0");
  } else {
      $text = "%$text%";
      $stmt = $con->prepare("SELECT * FROM medicines 
                             INNER JOIN medicines_stock ON medicines.NAME = medicines_stock.NAME 
                             WHERE UPPER(medicines.$column) LIKE ?");
      $stmt->bind_param("s", $text);
  }

  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
      $seq_no++;
      showMedicineStockRow($seq_no, $row);
  }
  $stmt->close();
}


// Additional JavaScript for handling client-side operations
echo "<script>
function editMedicineStock(batchId) {
    // Assume AJAX call to backend for editing by batch ID
}

function updateMedicineStock(id) {
    // AJAX call to send updated data to the backend
}

function cancel() {
    // Refresh or redirect to a default view
}
</script>";
?>

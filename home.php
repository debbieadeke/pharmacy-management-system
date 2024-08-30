<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Dashboard - Home</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
		<script src="bootstrap/js/jquery.min.js"></script>
		<script src="bootstrap/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="images/icon.svg" type="image/x-icon">
    <link rel="stylesheet" href="css/sidenav.css">
    <link rel="stylesheet" href="css/home.css">
    <script src="js/restrict.js"></script>
  </head>
  <body>
    <?php include "sections/sidenav.html"; ?>
    <div class="container-fluid">
      <div class="container">
        <!-- header section -->
        <?php
          require "php/header.php";
          createHeader('home', 'Dashboard', 'Home');
        ?>
        <!-- header section end -->

        <!-- form content -->
        <div class="row">
          <div class="row col col-xs-8 col-sm-8 col-md-8 col-lg-8">
          <?php

require 'php/db_connection.php'; // Ensure you have included your database connection

/**
 * Counts items in a database table with an optional condition.
 *
 * @param mysqli $con Database connection object
 * @param string $table Database table name
 * @param string $condition Optional SQL condition
 * @return int Count of items
 */
function countItems($con, $table, $condition = "") {
    $query = "SELECT COUNT(*) AS total FROM $table";
    if (!empty($condition)) {
        $query .= " WHERE $condition";
    }
    
    $result = mysqli_query($con, $query);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['total'];
    }
    return 0; // Return 0 if the query fails or no rows are found
}

/**
 * Creates a dashboard section with a count of items.
 *
 * @param mysqli $con Database connection object
 * @param string $location URL to link to
 * @param string $title Title of the section
 * @param string $table Database table name
 * @param string $condition Optional SQL condition
 */
function createSection1($con, $location, $title, $table, $condition = "") {
    $count = countItems($con, $table, $condition);

    echo '
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4" style="padding: 10px">
          <div class="dashboard-stats" onclick="location.href=\''.$location.'\'">
            <a class="text-dark text-decoration-none" href="'.$location.'">
              <span class="h4">'.$count.'</span>
              <div class="small font-weight-bold">'.$title.'</div>
            </a>
          </div>
        </div>
    ';
}

// Usage of createSection1 for different dashboard sections
createSection1($con, 'manage_customer.php', 'Total Customers', 'customers');
createSection1($con, 'manage_supplier.php', 'Total Suppliers', 'suppliers');
createSection1($con, 'manage_medicine.php', 'Total Medicine', 'medicines');
createSection1($con, 'manage_medicine_stock.php?out_of_stock', 'Out of Stock Medicines', 'medicines_stock', 'QUANTITY = 0');
createSection1($con, 'manage_medicine_stock.php?expired', 'Expired Medicines', 'medicines_stock', "STR_TO_DATE(CONCAT('01/', EXPIRY_DATE), '%d/%m/%y') < CURDATE()");
createSection1($con, 'manage_invoice.php', 'Total Invoices', 'invoices');
?>



          </div>

          <div class="col col-xs-4 col-sm-4 col-md-4 col-lg-4" style="padding: 7px 0; margin-left: 15px;">
            <div class="todays-report">
              <div class="h5">Todays Report</div>
              <table class="table table-bordered table-striped table-hover">
                <tbody>
                  <?php
                    require 'php/db_connection.php';
                    if($con) {
                      $date = date('Y-m-d');
                  ?>
                  <tr>
                    <?php
                      $total = 0;
                      $query = "SELECT NET_TOTAL FROM invoices WHERE INVOICE_DATE = '$date'";
                      $result = mysqli_query($con, $query);

                      while($row = mysqli_fetch_array($result))
                        $total = $total + $row['NET_TOTAL'];
                    ?>
                    <th>Total Sales</th>
                    <th class="text-success">Kshs. <?php echo $total; ?></th>
                  </tr>
                  <tr>
                    <?php
                      //echo $date;
                      $total = 0;
                      $query = "SELECT TOTAL_AMOUNT FROM purchases WHERE PURCHASE_DATE = '$date'";
                      $result = mysqli_query($con, $query);
                      while($row = mysqli_fetch_array($result))
                        $total = $total + $row['TOTAL_AMOUNT'];
                    }
                    ?>
                    <th>Total Purchase</th>
                    <th class="text-danger">Kshs. <?php echo $total; ?></th>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        </div>

        <hr style="border-top: 2px solid #AC3E31;">

        <div class="row">

          <?php
            function createSection2($icon, $location, $title) {
              echo '
                <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3" style="padding: 10px;">
              		<div class="dashboard-stats" style="padding: 30px 15px;" onclick="location.href=\''.$location.'\'">
              			<div class="text-center">
                      <span class="h1"><i class="fa fa-'.$icon.' p-2"></i></span>
              				<div class="h5">'.$title.'</div>
              			</div>
              		</div>
                </div>
              ';
            }
            createSection2('address-card', 'new_invoice.php', 'Create New Invoice');
            createSection2('user', 'add_customer.php', 'Add New Customer');
            createSection2('capsules', 'add_medicine.php', 'Add New Medicine');
            createSection2('group', 'add_supplier.php', 'Add New Supplier');
            createSection2('bag-shopping', 'add_purchase.php', 'Add New Purchase');
            createSection2('book', 'sales_report.php', 'Sales Report');
            createSection2('book', 'purchase_report.php', 'Purchase Report');
          ?>

        </div>
        <!-- form content end -->

        <hr style="border-top: 2px solid #AC3E31;">

      </div>
    </div>
  </body>
</html>

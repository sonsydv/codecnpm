<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử sách đã mượn</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #4CAF50;
            text-align: center;
            margin-bottom: 30px;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .btn-outline-primary {
            background-color: #4CAF50;
            color: white;
        }
        .btn-outline-primary:hover {
            background-color: #45a049;
            color: white;
        }
        .btn-outline-danger {
            background-color: #e74a3b;
            color: white;
        }
        .btn-outline-danger:hover {
            background-color: #d93c2c;
            color: white;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f1f1f1;
        }
        .table th {
            background-color: #4CAF50;
            color: white;
        }
        .table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .search-bar {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <?php 
        
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "library";
        
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>STT</th>
                <th>Tên sách</th>
                <th>Tác giả</th>
                <th>Image</th>
                <th>Ngày mượn</th>       
                <th>Ngày trả</th>
                <th>Trạng thái</th>               
            </tr>
        </thead>
        <tbody>
        <?php
        if (isset($data) && mysqli_num_rows($data) >= 0) {
            $i = 0;
            while ($row = mysqli_fetch_assoc($data)) {
                ?>
                <tr>
                    <td><?php echo (++$i) ?></td>
                    <td><?php echo $row['title'] ?></td>
                     <td><?php echo $row['author'] ?></td>
                     <td> <img src=<?php echo $row['image'] ?> width="150px" height="200px" alt="ảnh">
                    <td><?php echo $row['borrow_date'] ?></td>
                    <td><?php echo $row['return_date'] ?></td>
                    <td><?php 
                      if($row['returned']==0){
                        echo 'Đang mượn';
                    }else{
                        echo 'Đã trả';
                    }
                    ?></td>                
                </tr>
                
                <?php
            }
        }
        ?>
        </tbody>
    </table>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</form>
</body>
</html>

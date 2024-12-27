<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra cứu sách</title>
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
    <?php 
        if (isset($_POST['btnSearch']) && isset($_POST['search'])&& !empty($_POST['search'])) {
            $key = $_POST['search'];
            
            
            $con = mysqli_connect("localhost", "root", "", "library");
        
         
            $query = "SELECT * FROM `books` WHERE title  like '%$key%' or author  like '%$key%'";
        
           $data =mysqli_query($con, $query);
        }
    ?>

  <form method="post" action="">
<div class="container">
    <h2> Tra cứu sách </h2>

    <div class="search-bar">
      
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Tra cứu sách..." name="search" id="searchInput" value=<?php if( isset($key)&& !empty($key)) echo $key;?>>
                <button class="btn btn-outline-success" type="submit" name="btnSearch">
                    Tìm kiếm
                </button>
            </div>
       
    </div>

 
    <table class="table table-striped">
        <thead>
            <tr>
                <th>STT</th>
                <th>Tên sách</th>
                <th>Tác giả</th>
                <th>Image</th>
                <th>Tình trạng</th>               
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
                    
                </td>
                    <td><?php 
                    if($row['available']==0){
                        echo 'Hết sách';
                    }else{
                        echo 'Còn sách';
                    }
                   
                    ?>
                    </td>                                                           
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

<?php
require_once 'connect/connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $description = $_POST['description'];

    // Xử lý upload file
    $target_dir = "image"; // Thư mục lưu ảnh
    $imageName = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $imageName;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Đổi tên file để tránh trùng lặp
    $uniqueFilename = uniqid() . "." . $imageFileType;
    $target_file = $target_dir . $uniqueFilename;
    $imagePath = $target_dir . $uniqueFilename;

    // Kiểm tra file có phải là ảnh không
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        $message = "<div class='error'>File không phải là ảnh.</div>";
        $uploadOk = 0;
    }

    //Kiểm tra file đã tồn tại chưa
     if (file_exists($target_file)) {
        $message = "<div class='error'>Xin lỗi, file đã tồn tại.</div>";
        $uploadOk = 0;
    }

    //Kiểm tra kích thước file
    if ($_FILES["image"]["size"] > 5 * 1024 * 1024) { // 5MB
        $message = "<div class='error'>Xin lỗi, file quá lớn (tối đa 5MB).</div>";
        $uploadOk = 0;
    }

    // Chỉ cho phép một số định dạng file
    $allowedTypes = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowedTypes)) {
        $message = "<div class='error'>Xin lỗi, chỉ cho phép file JPG, JPEG, PNG & GIF.</div>";
        $uploadOk = 0;
    }


    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Lưu thông tin sách vào CSDL
            $sql = "INSERT INTO books (title, author, image, description, available ) VALUES (?, ?, ?, ?, 1 )";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $title, $author, $imagePath, $description);

            if ($stmt->execute()) {
                $message = "<div class='success'>Thêm sách thành công</div>";
            } else {
                $message = "<div class='error'>Lỗi: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            $message = "<div class='error'>Lỗi khi upload file.</div>";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Thêm Sách</title>
    <link rel="stylesheet" href="css/add_sach.css">
</head>

<body>
    <h1>Thêm Sách</h1>
    <?php echo $message; ?>
    <form method="POST" enctype="multipart/form-data">
        <label for="title">Tiêu đề:</label>
        <input type="text" name="title" id="title" required><br><br>

        <label for="author">Tác giả:</label>
        <input type="text" name="author" id="author"><br><br>

        <label for="image">Chọn ảnh:</label>
        <input type="file" name="image" id="image" accept="image/*" required><br><br>

        <label for="description">Mô tả:</label>
        <textarea name="description" id="description"></textarea><br><br>

        <input type="submit" value="Thêm">
    </form>
    <a href="quan_ly_sach.php">Trở lại trang quản lý</a>
</body>

</html>
<?php
require_once 'connect/connect.php';

$message = "";
$book = null; // Khởi tạo $book là null

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT * FROM books WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        die("Không tìm thấy sách với ID này.");
    }

    $book = $result->fetch_assoc(); // Lấy thông tin sách trước khi xử lý form
    $stmt->close();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $title = $_POST['title'];
        $author = $_POST['author'];
        $description = $_POST['description'];
        $imagePath = $book['image']; // Gán giá trị mặc định là ảnh cũ

        if (!empty($_FILES["image"]["name"])) {
            $target_dir = "image/";
            $imageName = basename($_FILES["image"]["name"]);
            $imageFileType = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
            $uniqueFilename = uniqid() . "." . $imageFileType;
            $target_file = $target_dir . $uniqueFilename;
            $imagePath = $target_file;

            $uploadOk = 1;

            $check = @getimagesize($_FILES["image"]["tmp_name"]);
            if ($check === false) {
                $message = "<div class='error'>File không phải là ảnh.</div>";
                $uploadOk = 0;
            }

            if ($_FILES["image"]["size"] > 5 * 1024 * 1024) {
                $message = "<div class='error'>Xin lỗi, file quá lớn (tối đa 5MB).</div>";
                $uploadOk = 0;
            }

            $allowedTypes = ["jpg", "jpeg", "png", "gif"];
            if (!in_array($imageFileType, $allowedTypes)) {
                $message = "<div class='error'>Xin lỗi, chỉ cho phép file JPG, JPEG, PNG & GIF.</div>";
                $uploadOk = 0;
            }

            if ($uploadOk == 1) {
                if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $message = "<div class='error'>Lỗi khi upload file.</div>";
                    $imagePath = $book['image']; // Giữ lại ảnh cũ nếu upload thất bại
                }
            } else {
                $imagePath = $book['image']; // Giữ lại ảnh cũ nếu upload không thành công do lỗi định dạng, kích thước...
            }
        }

        $sql = "UPDATE books SET title = ?, author = ?, image = ?, description = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $title, $author, $imagePath, $description, $id);

        if ($stmt->execute()) {
            $message = "<div class='success'>Cập nhật thành công</div>";
        } else {
            $message = "<div class='error'>Lỗi: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }


} else {
    die("Không có ID sách.");
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sửa Sách</title>
    <link rel="stylesheet" href="css/edit_sach.css">
</head>
<body>
    <div class="container">
        <h1>Sửa Sách</h1>
        <?php echo $message; ?>
        <form method="POST" enctype="multipart/form-data">
            <label for="title">Tiêu đề:</label>
            <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($book['title']); ?>" required><br><br>

            <label for="author">Tác giả:</label>
            <input type="text" name="author" id="author" value="<?php echo htmlspecialchars($book['author']); ?>"><br><br>

            <label for="image">Chọn ảnh mới (hoặc giữ nguyên):</label>
            <input type="file" name="image" id="image" accept="image/*"><br><br>

            <label for="description">Mô tả:</label>
            <textarea name="description" id="description"><?php echo htmlspecialchars($book['description']); ?></textarea><br><br>

            <input type="submit" value="Cập nhật">
        </form>
        <a href="quan_ly_sach.php">Trở lại trang quản lý</a>
    </div>
</body>
</html>
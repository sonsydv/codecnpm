<?php
require_once 'connect/connect.php';

$search_term = isset($_GET['search']) ? strtolower($_GET['search']) : '';
$error_message = null;
$books = [];

try {
    $sql = !empty($search_term) ?
        "SELECT id, title, author, image, description FROM books WHERE LOWER(title) LIKE '%$search_term%' OR LOWER(author) LIKE '%$search_term%'" :
        "SELECT id, title, author, image, description FROM books";

    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception($conn->error);
    }

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }
} catch (Exception $e) {
    $error_message = "Lỗi: " . $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookId = $_POST['book_id'];

    // Kiểm tra xem sách có còn hay không
    $sql = "SELECT available FROM books WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['available'] == 1) {
        // Cập nhật trạng thái sách thành đã mượn
        $sql = "UPDATE books SET available = 0 WHERE id = ?";
        // ... thực thi câu lệnh

        // Thêm thông tin vào bảng borrowed_books (nếu cần)
        // ...

        echo "<script>alert('Mượn sách thành công!');</script>";
    } else {
        echo "<script>alert('Sách đã được mượn hết!');</script>";
    }
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thư viện sách</title>
    <link rel="stylesheet" href="css/1trang_chu.css">
</head>

<body>
    <nav>
        <ul>
            <li><a href="trang_chu.php">Trang chủ</a></li>
            <li><a href="quan_ly_sach.php">Quản trị Sách</a></li>
            <li><a href="muonsach.php">Mượn sách</a></li>
            <li><a href="tra_sach.php">Trả sách</a></li>
            <li><a href="user/index.php">Tài Khoản</a></li>
            <li><a href="lichsumuon.php">Lịch sử</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Thư viện sách</h1>

        <div class="search-box">
            <form method="GET">
                <input type="text" name="search" placeholder="Tìm kiếm sách..."
                    value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit">Tìm kiếm</button>
            </form>
        </div>

        <?php if ($error_message): ?>
            <div class="error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="book-list">
            <?php if (count($books) > 0): ?>
                <?php foreach ($books as $book): ?>
                    <div class="book">
                        <img class="book img" src="<?php echo htmlspecialchars($book['image']); ?>"  alt="<?php echo htmlspecialchars($book['title']); ?>" onerror="this.onerror=null;this.src='images/placeholder.png';">
                        <h3>
                            <?php echo htmlspecialchars($book['title']); ?>
                        </h3>
                        <p>
                            <?php echo htmlspecialchars($book['description']); ?>
                        </p>
                        <a href="#" onclick="confirmBorrow(<?php echo $book['id']; ?>)" class="borrow-button">Mượn</a>

<form id="borrowForm" method="post" action="muonsach.php" style="display: none;">
    <input type="hidden" name="book_id" value="">
</form>

<script>
function confirmBorrow(bookId) {
    if (confirm("Bạn có muốn mượn sách này không?")) {
        document.getElementById("borrowForm").book_id.value = bookId;
        document.getElementById("borrowForm").submit();
    }
}
</script>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Không tìm thấy sách nào.</p>
            <?php endif; ?>
        </div>
    </div>
    
</body>

</html>
<?php
// Kết nối đến cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "library";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Xử lý thêm thông tin chả sách
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_record'])) {
    $user_id = $_POST['user_id'];
    $book_id = $_POST['book_id'];
    $borrow_date = date('Y-m-d H:i:s');
    $due_date = date('Y-m-d H:i:s', strtotime('+7 days')); // Hạn trả sách sau 7 ngày
    // Kiểm tra số lượng sách khả dụng
    $sql_check = "SELECT available, title FROM books WHERE id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $book_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $book = $result_check->fetch_assoc();

    if ($book['available'] > 0) {
        // Thêm thông tin vào bảng borrowed_books
        $sql = "INSERT INTO borrowed_books (user_id, book_id, borrow_date, due_date) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $user_id, $book_id, $borrow_date, $due_date);

        if ($stmt->execute()) {
            // Giảm số lượng sách khả dụng
            $sql_update = "UPDATE books SET available = available - 1 WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $book_id);
            $stmt_update->execute();

            $message = "<p style='color:green;'>trả sách thành công!</p>";
        } else {
            $message = "<p style='color:red;'>Lỗi: " . $stmt->error . "</p>";
        }
    } else {
        $message = "<p style='color:red;'>Sách này hiện không còn khả dụng.</p>";
    }
}

// Xử lý xóa thông tin mượn sách
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_record'])) {
    $record_id = $_POST['record_id'];

    // Lấy thông tin sách từ bảng borrowed_books
    $sql_get_book = "SELECT book_id FROM borrowed_books WHERE id = ?";
    $stmt_get_book = $conn->prepare($sql_get_book);
    $stmt_get_book->bind_param("i", $record_id);
    $stmt_get_book->execute();
    $result_get_book = $stmt_get_book->get_result();
    $borrow = $result_get_book->fetch_assoc();

    // Xóa bản ghi mượn sách
    $sql = "DELETE FROM borrowed_books WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $record_id);

    if ($stmt->execute()) {
        // Tăng số lượng sách khả dụng
        $sql_update = "UPDATE books SET available = available + 1 WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $borrow['book_id']);
        $stmt_update->execute();

        $message = "<p style='color:green;'>Xóa thông tin trả sách thành công!</p>";
    } else {
        $message = "<p style='color:red;'>Lỗi: " . $stmt->error . "</p>";
    }
}

// Lấy danh sách sách
$sql_books = "SELECT * FROM books ORDER BY title ASC";
$result_books = $conn->query($sql_books);

// Lấy danh sách sách đã mượn
$sql_borrowed = "
    SELECT bb.id, bb.user_id, bb.borrow_date, bb.due_date, b.title, b.author
    FROM borrowed_books bb
    JOIN books b ON bb.book_id = b.id
    ORDER BY bb.borrow_date DESC";
$result_borrowed = $conn->query($sql_borrowed);

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý trả Sách</title>
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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        form {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        label {
            display: block;
            margin-bottom: 8px;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        nav {
  background-color: #007bff;
  padding: 10px;
  margin-bottom: 20px;
}

nav ul {
  list-style: none;
  margin: 0;
  padding: 0;
  text-align: center;
}

nav li {
  display: inline;
  margin: 0 10px;
}

nav a {
  color: white;
  text-decoration: none;
  padding: 5px 10px;
  border-radius: 5px;
}

nav a:hover {
  background-color: #0056b3;
}
    </style>
</head>
<body>
    <h2>Quản Lý trả Sách</h2>
    <?php echo $message; ?>

    <!-- Form thêm thông tin mượn sách -->
    <form method="POST">
        <label for="user_id">Mã Người Dùng:</label>
        <input type="text" id="user_id" name="user_id" required>

        <label for="book_id">Chọn Sách:</label>
        <select id="book_id" name="book_id" required>
            <?php while ($row = $result_books->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>">
                    <?php echo htmlspecialchars($row['title'] . " - " . $row['author'] . " (Còn lại: " . $row['available'] . ")"); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit" name="add_record">trả Sách</button>
    </form>

    <!-- Bảng danh sách sách đã mượn -->
    <h2>Danh Sách Sách Đã Mượn</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Mã Người Dùng</th>
                <th>Tên Sách</th>
                <th>Tác Giả</th>
                <th>Ngày Mượn</th>
                <th>Hạn Trả</th>
                <th>Hành Động</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result_borrowed->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['user_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                    <td><?php echo $row['borrow_date']; ?></td>
                    <td><?php echo $row['due_date']; ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="record_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="delete_record" style="background-color:#dc3545;">Xóa</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>

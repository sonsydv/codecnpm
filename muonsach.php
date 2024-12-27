<?php
// Kết nối đến cơ sở dữ liệu
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'library');

// Đặt múi giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_record'])) {
        $user_id = $_POST['user_id'];
        $book_id = $_POST['book_id'];
        $borrow_date = date('Y-m-d H:i:s');
        $due_date = date('Y-m-d H:i:s', strtotime('+7 days'));

        $sql = "INSERT INTO borrowed_books (user_id, book_id, borrow_date, due_date) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $user_id, $book_id, $borrow_date, $due_date);

        if ($stmt->execute()) {
            $message = "<p style='color:green;'>Mượn sách thành công!</p>";
        } else {
            $message = "<p style='color:red;'>Lỗi: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } elseif (isset($_POST['delete_record'])) {
        $id = $_POST['record_id'];
        $sql = "DELETE FROM borrowed_books WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "<p style='color:green;'>Xóa thông tin thành công!</p>";
        } else {
            $message = "<p style='color:red;'>Lỗi: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}


// Hiển thị danh sách sách có sẵn
$sql_books = "SELECT id, title FROM books WHERE available > 0";
$result_books = $conn->query($sql_books);

// Hiển thị danh sách người dùng
$sql_users = "SELECT id, CONCAT(firstName, ' ', lastName) AS full_name FROM users";
$result_users = $conn->query($sql_users);

// Hiển thị danh sách mượn sách
$sql_borrow = "
    SELECT bb.id, u.username, b.title, bb.borrow_date, bb.due_date, bb.return_date
    FROM borrowed_books bb
    JOIN users u ON bb.user_id = u.id
    JOIN books b ON bb.book_id = b.id
    ORDER BY bb.borrow_date DESC
";
$result_borrow = $conn->query($sql_borrow);

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Mượn Sách</title>
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
    <h2>Quản Lý Mượn Sách</h2>

    <?php echo $message; ?>

    <form method="POST">
        <input type="hidden" name="add_record" value="1">
        <label for="user_id">Người Mượn:</label>
        <select id="user_id" name="user_id" required>
            <option value="">-- Chọn Người Mượn --</option>
            <?php if ($result_users->num_rows > 0): ?>
                <?php while ($row = $result_users->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>">
                        <?php echo htmlspecialchars($row['full_name']); ?>
                    </option>
                <?php endwhile; ?>
            <?php else: ?>
                <option value="">Không có người dùng nào</option>
            <?php endif; ?>
        </select>

        <label for="book_id">Sách:</label>
        <select id="book_id" name="book_id" required>
            <option value="">-- Chọn Sách --</option>
            <?php if ($result_books->num_rows > 0): ?>
                <?php while ($row = $result_books->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </option>
                <?php endwhile; ?>
            <?php else: ?>
                <option value="">Không có sách nào</option>
            <?php endif; ?>
        </select>

        <button type="submit">Lưu Thông Tin</button>
    </form>

    <h2>Danh Sách Mượn Sách</h2>
    <table>
        <thead>
            <tr>
                <th>Người Mượn</th>
                <th>Tên Sách</th>
                <th>Ngày Mượn</th>
                <th>Hạn Trả</th>
                <th>Ngày Trả</th>
                <th>Hành Động</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_borrow->num_rows > 0): ?>
                <?php while ($row = $result_borrow->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['borrow_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['return_date'] ?? 'Chưa trả'); ?></td>
                        <td>
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="record_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="delete_record" value="1">
                                <button type="submit" style="background-color:#dc3545;color:white;border:none;padding:5px 10px;border-radius:5px;">
                                    Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">Chưa có dữ liệu.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

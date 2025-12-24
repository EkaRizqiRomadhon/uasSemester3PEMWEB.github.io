<?php
// admin_messages.php - Halaman untuk melihat pesan kontak
session_start();
require_once 'db.php';

// Cek login admin (sesuaikan dengan sistem auth Anda)
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header('Location: login.php');
//     exit;
// }

$conn = getDBConnection();

// Handle update status
if (isset($_POST['update_status'])) {
    $message_id = intval($_POST['message_id']);
    $new_status = $_POST['status'];
    
    $update_stmt = $conn->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_status, $message_id);
    $update_stmt->execute();
    $update_stmt->close();
}

// Handle delete
if (isset($_POST['delete_message'])) {
    $message_id = intval($_POST['message_id']);
    
    $delete_stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
    $delete_stmt->bind_param("i", $message_id);
    $delete_stmt->execute();
    $delete_stmt->close();
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT * FROM contact_messages WHERE 1=1";

if ($filter !== 'all') {
    $query .= " AND status = '" . $conn->real_escape_string($filter) . "'";
}

if (!empty($search)) {
    $search_term = $conn->real_escape_string($search);
    $query .= " AND (name LIKE '%$search_term%' OR email LIKE '%$search_term%' OR subject LIKE '%$search_term%' OR message LIKE '%$search_term%')";
}

$query .= " ORDER BY created_at DESC";

$result = $conn->query($query);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'unread' THEN 1 ELSE 0 END) as unread,
    SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read,
    SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied
    FROM contact_messages";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Kontak - Admin ARVEN PARFUME</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid #667eea;
        }

        .stat-card.unread {
            border-left-color: #f39c12;
        }

        .stat-card.read {
            border-left-color: #3498db;
        }

        .stat-card.replied {
            border-left-color: #27ae60;
        }

        .stat-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-group label {
            font-weight: 600;
            color: #666;
        }

        select, input[type="text"] {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s;
        }

        select:focus, input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }

        .messages-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-unread {
            background: #fff3cd;
            color: #856404;
        }

        .status-read {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-replied {
            background: #d4edda;
            color: #155724;
        }

        .message-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #666;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .action-buttons button {
            padding: 5px 10px;
            font-size: 12px;
        }

        .btn-read {
            background: #3498db;
            color: white;
        }

        .btn-replied {
            background: #27ae60;
            color: white;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-content h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .modal-field {
            margin-bottom: 15px;
        }

        .modal-field label {
            display: block;
            font-weight: 600;
            color: #666;
            margin-bottom: 5px;
        }

        .modal-field p {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            border-left: 3px solid #667eea;
        }

        .close-modal {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #999;
        }

        .close-modal:hover {
            color: #333;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state svg {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📬 Pesan Kontak</h1>
        <p>Kelola pesan dari formulir kontak ARVEN PARFUME</p>
    </div>

    <div class="container">
        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <h3>Total Pesan</h3>
                <div class="number"><?= $stats['total'] ?></div>
            </div>
            <div class="stat-card unread">
                <h3>Belum Dibaca</h3>
                <div class="number"><?= $stats['unread'] ?></div>
            </div>
            <div class="stat-card read">
                <h3>Sudah Dibaca</h3>
                <div class="number"><?= $stats['read'] ?></div>
            </div>
            <div class="stat-card replied">
                <h3>Sudah Dibalas</h3>
                <div class="number"><?= $stats['replied'] ?></div>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" class="filters">
            <div class="filter-group">
                <label>Status:</label>
                <select name="filter" onchange="this.form.submit()">
                    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Semua</option>
                    <option value="unread" <?= $filter === 'unread' ? 'selected' : '' ?>>Belum Dibaca</option>
                    <option value="read" <?= $filter === 'read' ? 'selected' : '' ?>>Sudah Dibaca</option>
                    <option value="replied" <?= $filter === 'replied' ? 'selected' : '' ?>>Sudah Dibalas</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label>Cari:</label>
                <input type="text" name="search" placeholder="Nama, email, subjek..." value="<?= htmlspecialchars($search) ?>">
            </div>
            
            <button type="submit" class="btn btn-primary">🔍 Filter</button>
        </form>

        <!-- Messages Table -->
        <div class="messages-table">
            <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Subjek</th>
                        <th>Pesan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['subject']) ?></td>
                        <td class="message-preview"><?= htmlspecialchars($row['message']) ?></td>
                        <td>
                            <span class="status-badge status-<?= $row['status'] ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-primary" onclick="viewMessage(<?= htmlspecialchars(json_encode($row)) ?>)">
                                    👁️
                                </button>
                                
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="message_id" value="<?= $row['id'] ?>">
                                    <?php if ($row['status'] === 'unread'): ?>
                                        <input type="hidden" name="status" value="read">
                                        <button type="submit" name="update_status" class="btn btn-read">✓</button>
                                    <?php elseif ($row['status'] === 'read'): ?>
                                        <input type="hidden" name="status" value="replied">
                                        <button type="submit" name="update_status" class="btn btn-replied">✉️</button>
                                    <?php endif; ?>
                                </form>
                                
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus pesan ini?')">
                                    <input type="hidden" name="message_id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="delete_message" class="btn btn-delete">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <svg fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                </svg>
                <h3>Tidak ada pesan</h3>
                <p>Belum ada pesan yang masuk dari formulir kontak.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <h2>Detail Pesan</h2>
            <div id="modalBody"></div>
        </div>
    </div>

    <script>
        function viewMessage(data) {
            const modal = document.getElementById('messageModal');
            const modalBody = document.getElementById('modalBody');
            
            modalBody.innerHTML = `
                <div class="modal-field">
                    <label>Dari:</label>
                    <p><strong>${data.name}</strong> (${data.email})</p>
                </div>
                <div class="modal-field">
                    <label>Subjek:</label>
                    <p>${data.subject}</p>
                </div>
                <div class="modal-field">
                    <label>Pesan:</label>
                    <p style="white-space: pre-wrap;">${data.message}</p>
                </div>
                <div class="modal-field">
                    <label>Tanggal:</label>
                    <p>${new Date(data.created_at).toLocaleString('id-ID')}</p>
                </div>
                <div class="modal-field">
                    <label>IP Address:</label>
                    <p>${data.ip_address || 'N/A'}</p>
                </div>
            `;
            
            modal.classList.add('active');
        }

        function closeModal() {
            document.getElementById('messageModal').classList.remove('active');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('messageModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>
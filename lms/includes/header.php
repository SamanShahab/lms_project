<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'LMS'; ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Learning Management System</h1>
            <nav>
                <?php if (isset($_SESSION['user'])): ?>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></span>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="../auth/logout.php">Logout</a>
                <?php else: ?>
                    <a href="../auth/login.php">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container">
<?php




include 'admin/db.php'; // Include your database connection file

// Check if the ID parameter is present in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid destination ID.");
}

// Sanitize the ID
$id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch destination details
$destination_query = mysqli_query($conn, "SELECT * FROM destination WHERE id = '$id'");
if (!$destination_query || mysqli_num_rows($destination_query) == 0) {
    die("Destination not found.");
}

$destination = mysqli_fetch_assoc($destination_query);

// Fetch related packages
$packages_query = mysqli_query($conn, "SELECT p.id, p.title FROM destination_package dp JOIN yatra_package p ON dp.package_id = p.id WHERE dp.main_id = '$id'");
$packages = mysqli_fetch_all($packages_query, MYSQLI_ASSOC);

// Fetch related themes
$themes_query = mysqli_query($conn, "
    SELECT t.id, t.title 
    FROM destination_theme dt 
    JOIN theme_package t ON dt.theme_id = t.id 
    WHERE dt.main_id = '$id'
");
$themes = mysqli_fetch_all($themes_query, MYSQLI_ASSOC);
?>

<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?= htmlspecialchars($destination['title'], ENT_QUOTES, 'UTF-8'); ?> | Holiday Guru Travel</title>
    <meta name="description" content="<?= htmlspecialchars($destination['meta_tags'], ENT_QUOTES, 'UTF-8'); ?>">
    <?php include 'include/header.php'; ?>
</head>
<body>
    <div class="breadcumb-wrapper" data-bg-src="assets/img/destination/<?= htmlspecialchars($destination['thumb_img'], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="container">
            <div class="breadcumb-content">
                <h1 class="breadcumb-title"><?= htmlspecialchars($destination['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <ul class="breadcumb-menu">
                    <li><a href="/">Home</a></li>
                    <li><a href="destination_table.php">Destinations</a></li>
                    <li><?= htmlspecialchars($destination['title'], ENT_QUOTES, 'UTF-8'); ?></li>
                </ul>
            </div>
        </div>
    </div>
    
    <section class="space">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="destination-details">
                        <h2 class="destination-title"><?= htmlspecialchars($destination['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <div class="destination-img">
                            <img src="assets/img/destination/<?= htmlspecialchars($destination['thumb_img'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($destination['title'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <p><?= htmlspecialchars($destination['meta_tags'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="related-packages">
                        <h3>Related Packages</h3>
                        <?php if (count($packages) > 0) { ?>
                            <?php foreach ($packages as $package) { ?>
                                <div class="related-package-item">
                                    <a href="package_details.php?id=<?= htmlspecialchars($package['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <h4><?= htmlspecialchars($package['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                    </a>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <p>No related packages available.</p>
                        <?php } ?>
                    </div>
                    
                    <div class="related-themes">
                        <h3>Related Themes</h3>
                        <?php if (count($themes) > 0) { ?>
                            <?php foreach ($themes as $theme) { ?>
                                <div class="related-theme-item">
                                    <a href="theme_details.php?id=<?= htmlspecialchars($theme['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <h4><?= htmlspecialchars($theme['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                    </a>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <p>No related themes available.</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include 'include/footer.php'; ?>
</body>
</html>

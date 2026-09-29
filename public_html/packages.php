<?php
// Start the session and include database connection
session_start();
include 'admin/db.php'; // Adjust the path to your database connection file

// Get the ID from the URL parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the category name
$sql2 = "SELECT * FROM `category` WHERE id = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$row2 = $result2->fetch_assoc();
$maintitle = $row2['categoryName'];
$meta_tags = $row2['meta_tags'];


// Fetch all packages for the given category
$sql = "SELECT * FROM yatra_package WHERE category = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Check if packages are found
if ($result->num_rows === 0) {
    // Handle case where no packages were found
    $packages = [];
} else {
    $packages = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!doctype html>
<html class="no-js" lang="zxx">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?= htmlspecialchars($maintitle) ?> | Holiday Guru Travel</title>
    <meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
     <?= ($meta_tags) ?>
    <link rel="canonical" href="https://holidaygurutravel.in/<?= ($id) ?>">
    
    <?php
    include "include/header.php";
    ?>
    <div class="breadcumb-wrapper" data-bg-src="assets/img/breadcumb-bg.jpg">
        <div class="container">
            <div class="breadcumb-content">
                <h1 class="breadcumb-title"><?= htmlspecialchars($maintitle) ?></h1>
                <ul class="breadcumb-menu">
                    <li><a href="/">Home</a></li>
                    <li><?= htmlspecialchars($maintitle) ?></li>
                </ul>
            </div>
        </div>
    </div>
    <section class="space">
        <div class="container">
            
            <div class="row">
                <div class="col-xxl-12 col-lg-12">
                    <div class="tab-content" id="nav-tabContent">
                        <div class="tab-pane fade active show" id="tab-grid" role="tabpanel"
                            aria-labelledby="tab-destination-grid">
                            <div class="row gy-30">
                             <?php foreach ($packages as $package): ?>
                                <div class="col-xxl-4 col-xl-3">
                                    <div class="tour-box th-ani">
                                        <div class="tour-box_img global-img"><img src="admin/ajax/<?= htmlspecialchars($package['thumb_img']) ?>"
                                                alt="<?= htmlspecialchars($package['title']) ?>"></div>
                                        <div class="tour-content">
                                            <h3 class="box-title"><a href="/package-details?id=<?= htmlspecialchars($package['id']) ?>"><?= htmlspecialchars($package['title']) ?></a></h3>
                                            <div class="tour-rating">
                                                <div class="star-rating" role="img" aria-label="Rated 5.00 out of 5">
                                                    <span style="width:100%">Rated <strong class="rating">5.00</strong>
                                                        out of 5 based on <span class="rating">4.8</span>(4.8
                                                        Rating)</span></div><a href="/package-details?id=<?= htmlspecialchars($package['id']) ?>"
                                                    class="woocommerce-review-link">(<span class="count">4.8</span>
                                                    Rating)</a>
                                            </div>
                                            <h4 class="tour-box_price"><span class="currency"><i class="fa-light fa-clock"></i><?= htmlspecialchars($package['days']) ?></span></h4>
                                            
                                            <div class="tour-action"><a href="/package-details?id=<?= htmlspecialchars($package['id']) ?>"
                                                    class="th-btn style4 th-icon">View More</a></div>
                                        </div>
                                    </div>
                                </div>
                                  <?php endforeach; ?>
                              
                                
                                
                            </div>
                        </div>
                        
                    </div>
                    
                </div>
                
            </div>
        </div>
    </section>
     <?php
    include "include/footer.php";
    ?>
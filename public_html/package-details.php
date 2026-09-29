<?php
// Start the session and include database connection
session_start();
include 'admin/db.php'; // Adjust the path to your database connection file

// Get the ID from the URL parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the package for the given ID
$sql = "SELECT * FROM yatra_package WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Query failed: " . $stmt->error);
}

if ($result->num_rows === 0) {
    $package = null; // No packages found
} else {
    $package = $result->fetch_assoc(); // Fetch a single package
}
$tourSql = "SELECT * FROM tour_table WHERE main_id = ?";
$tourStmt = $conn->prepare($tourSql);

if (!$tourStmt) {
    die("Prepare failed: " . $conn->error);
}

$tourStmt->bind_param("i", $id);
$tourStmt->execute();
$tourResult = $tourStmt->get_result();

if (!$tourResult) {
    die("Query failed: " . $tourStmt->error);
}

// Fetch all tour details into an array
$tours = $tourResult->fetch_all(MYSQLI_ASSOC);
$tourStmt->close();


// Fetch other info for the given ID
$infoSql = "SELECT * FROM other_info WHERE main_id = ?";
$infoStmt = $conn->prepare($infoSql);

if (!$infoStmt) {
    die("Prepare failed: " . $conn->error);
}

$infoStmt->bind_param("i", $id);
$infoStmt->execute();
$infoResult = $infoStmt->get_result();

if (!$infoResult) {
    die("Query failed: " . $infoStmt->error);
}

// Fetch all info details into an array
$infoDetails = $infoResult->fetch_all(MYSQLI_ASSOC);
$infoStmt->close();
?>


<!doctype html>
<html class="no-js" lang="zxx">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
     <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
    <title><?= htmlspecialchars($package['title']) ?> <?= htmlspecialchars($package['days']) ?> | Holiday Guru Travel</title>
         <?= ($package['meta_tags']) ?>
   
    <link rel="canonical" href="https://holidaygurutravel.in/package-details?id=<?= ($package['id']) ?>">

    <?php
    include "include/header.php";
    ?>
    
    
    <div class="breadcumb-wrapper" data-bg-src="admin/ajax/<?= htmlspecialchars($package['banner']) ?>">
        <div class="container">
            <div class="breadcumb-content">
                <h1 class="breadcumb-title"><?= htmlspecialchars($package['title']) ?></h1>
                <ul class="breadcumb-menu">
                    <li><a href="/">Home</a></li>
                    <li><?= htmlspecialchars($package['title']) ?></li>
                </ul>
            </div>
        </div>
    </div>
    <section class="space">
        <div class="container">
            <div class="row">
                <div class="col-xxl-8 col-lg-7">
                    <div class="page-single">
                        
                         <div class="page-content">
                            <h2 class="box-title"><?= htmlspecialchars($package['title']) ?> </h2>
                            <p class="blog-text mb-30"><i class="fa-light fa-clock"></i><?= htmlspecialchars($package['days']) ?></p>
                            <!--<p class="blog-text mb-35">Cities Covered Dubai-3Day</p>-->
                            <h2 class="box-title">Overview</h2>
                            <p class="blog-text mb-35"><?= html_entity_decode($package['description']) ?></p>
                             <h2 class="box-title mt-3">Itinerary</h2>
                            
        
            
                        <div class="row">
                           <div class="col-lg-12">
                                <div class="accordion-area accordion mb-30" id="faqAccordion">
                                    <?php foreach ($tours as $index => $tour): ?>
                                        <div class="accordion-card style2 <?php echo $index === 0 ? 'active' : ''; ?>">
                                            <div class="accordion-header" id="collapse-item-<?php echo $tour['id']; ?>">
                                                <button class="accordion-button <?php echo $index === 0 ? '' : 'collapsed'; ?>"
                                                        type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $tour['id']; ?>"
                                                        aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                                                        aria-controls="collapse-<?php echo $tour['main_id']; ?>">
                                                    <?php echo htmlspecialchars($tour['heading']); ?>
                                                </button>
                                            </div>
                                            <div id="collapse-<?php echo $tour['id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>"
                                                 aria-labelledby="collapse-item-<?php echo $tour['id']; ?>" data-bs-parent="#faqAccordion">
                                                <div class="accordion-body style2">
                                                    <p class="faq-text"><?php echo nl2br(htmlspecialchars($tour['description'])); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                         </div>
                        <?php if (!empty($infoDetails)): ?> 
                        <h2 class="box-title mt-3">Other Info</h2>
                        <div class="destination-checklist">
                            <div class="d-flex align-items-start">
                                <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                    <?php foreach ($infoDetails as $index => $info): ?>
                                        <a class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>" 
                                           id="v-pills-<?php echo $info['id']; ?>-tab" 
                                           data-bs-toggle="pill" 
                                           data-bs-target="#v-pills-<?php echo $info['id']; ?>" 
                                           type="button" 
                                           role="tab" 
                                           aria-controls="v-pills-<?php echo $info['id']; ?>" 
                                           aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                                            <?php echo htmlspecialchars($info['tab_head']); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                                <div class="tab-content" id="v-pills-tabContent">
                                    <?php foreach ($infoDetails as $index => $info): ?>
                                        <div class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?>" 
                                             id="v-pills-<?php echo $info['id']; ?>" 
                                             role="tabpanel" 
                                             aria-labelledby="v-pills-<?php echo $info['id']; ?>-tab">
                                            <div class="checklist style2">
                                                <ul>
                                                    <?php foreach (explode("\n", $info['tab_details']) as $item): ?>
                                                        <li><?php echo htmlspecialchars(trim($item)); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                            
                          <?php endif; ?>
 
                        
                        </div>
                        
                    </div>
                </div>
                <?php
                include "include/enquiry.php";
                ?>
            </div>
        </div>
        
    </section>
      <?php
    include "include/footer.php";
    ?>
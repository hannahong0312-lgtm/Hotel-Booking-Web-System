<?php
include '../Shared/header.php';

function formatFeature($text) {
    $text = trim($text);
    if(strpos($text, ':') !== false) {
        list($label, $value) = explode(':', $text, 2);
        return '<div class="detail-label">' . htmlspecialchars(trim($label)) . '</div>'
             . '<div class="detail-value">' . nl2br(htmlspecialchars(trim($value))) . '</div>';
    } else {
        return '<div class="detail-value">' . nl2br(htmlspecialchars($text)) . '</div>';
    }
}

$sql = "SELECT * FROM facilities WHERE is_active = 1 ORDER BY display_order ASC";
$result = $conn->query($sql);
$facilities = [];
while ($row = $result->fetch_assoc()) {
    $facilities[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facilities | Grand Hotel</title>
    <link rel="stylesheet" href="../Shared/main.css">
    <link rel="stylesheet" href="css/facilities.css">
</head>
<body>

<!-- HERO SECTION -->
<section class="hero-fullscreen">
    <div>
        <h1>World‑Class Facilities</h1>
        <p>Where every stay becomes an experience.</p>
    </div>
</section>

<!-- FEATURE LUXURY (PILLS NAVIGATION) -->
<section class="feature-luxury">
    <div class="container">
        <h2>ELEVATE YOUR GETAWAY</h2>
        <p>From sunrise swims to sunset cocktails, our world‑class facilities transform every moment into something extraordinary.</p>
        <div class="facility-pills">
            <?php foreach ($facilities as $f): 
                $anchor = strtolower(str_replace(' ', '-', $f['category']));
            ?>
                <div class="pill" data-target="facility-<?php echo $anchor; ?>">
                    <i class="fas <?php 
                        if(strpos($f['category'],'Fitness')!==false) echo 'fa-dumbbell';
                        elseif(strpos($f['category'],'Pool')!==false) echo 'fa-swimming-pool';
                        elseif(strpos($f['category'],'Spa')!==false) echo 'fa-spa';
                        elseif(strpos($f['category'],'Shop')!==false) echo 'fa-shopping-bag';
                        elseif(strpos($f['category'],'Rangers')!==false) echo 'fa-child';
                        elseif(strpos($f['category'],'EV')!==false) echo 'fa-charging-station';
                        else echo 'fa-building';
                    ?>"></i> <?php echo htmlspecialchars($f['category']); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FACILITY CARDS -->
<div class="container">
    <?php foreach ($facilities as $f): 
        $anchor = strtolower(str_replace(' ', '-', $f['category']));
        $reverse_class = $f['reverse_layout'] ? 'reverse' : '';
        $img_src = $f['image_path']; 
    ?>
        <div class="facility-row <?php echo $reverse_class; ?>" id="facility-<?php echo $anchor; ?>">
            <div class="facility-image">
                <img src="<?php echo htmlspecialchars($img_src); ?>" alt="<?php echo htmlspecialchars($f['category']); ?>">
            </div>
            <div class="facility-content">
                <h2><?php echo htmlspecialchars($f['category']); ?></h2>
                <p class="facility-desc"><?php echo nl2br(htmlspecialchars($f['description'])); ?></p>
                <div class="facility-details">
                    <?php if(!empty($f['hours'])): ?>
                        <div class="detail-block">
                            <div class="detail-label">OPERATING HOURS</div>
                            <div class="detail-value"><?php echo htmlspecialchars($f['hours']); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($f['feature1'])): ?>
                        <div class="detail-block">
                            <?php echo formatFeature($f['feature1']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($f['feature2'])): ?>
                        <div class="detail-block">
                            <?php echo formatFeature($f['feature2']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($f['feature3'])): ?>
                        <div class="detail-block">
                            <?php echo formatFeature($f['feature3']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    document.querySelectorAll('.pill').forEach(pill => {
        pill.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target');
            if(targetId){
                var targetEl = document.getElementById(targetId);
                if(targetEl){
                    targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    targetEl.classList.add('highlight');
                    setTimeout(function(){ targetEl.classList.remove('highlight'); }, 1000);
                }
            }
        });
    });
</script>

<?php include '../Shared/footer.php'; ?>
</body>
</html>
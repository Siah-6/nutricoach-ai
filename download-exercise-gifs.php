<?php
/**
 * Download all exercise GIFs locally
 * Run from browser: http://localhost/NutriCoachAI/download-exercise-gifs.php
 * Or terminal: c:\xampp\php\php.exe download-exercise-gifs.php
 */

// Set execution time limit
set_time_limit(300); // 5 minutes

// Output as HTML if accessed from browser
$isBrowser = php_sapi_name() !== 'cli';
if ($isBrowser) {
    echo '<!DOCTYPE html><html><head><title>Download Exercise GIFs</title>';
    echo '<style>body{background:#0A1628;color:#fff;font-family:monospace;padding:2rem;}</style></head><body>';
    echo '<h1>🏋️ Downloading Exercise GIFs...</h1><pre>';
}

// Create directories if they don't exist
$baseDir = __DIR__ . '/assets/videos/exercises/';
$muscleGroups = ['chest', 'back', 'legs', 'shoulders', 'arms', 'abs'];

foreach ($muscleGroups as $group) {
    $dir = $baseDir . $group;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ Created directory: $dir\n";
    }
}

// Exercise library with URLs
$exerciseLibrary = [
    'chest' => [
        ['name' => 'push-ups', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Push-Up.gif'],
        ['name' => 'bench-press', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Barbell-Bench-Press.gif'],
        ['name' => 'dumbbell-flyes', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Dumbbell-Fly.gif'],
        ['name' => 'incline-press', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Incline-Barbell-Bench-Press.gif'],
        ['name' => 'decline-press', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/06/Decline-Dumbbell-Press.gif'],
        ['name' => 'cable-crossover', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/cable-cross-over.gif'],
        ['name' => 'dumbbell-press', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Dumbbell-Bench-Press.gif'],
        ['name' => 'chest-dips', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Chest-Dips.gif'],
    ],
    'back' => [
        ['name' => 'pull-ups', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Pull-up.gif'],
        ['name' => 'barbell-rows', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Barbell-Row.gif'],
        ['name' => 'lat-pulldowns', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Lat-Pulldown.gif'],
        ['name' => 'deadlifts', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Barbell-Deadlift.gif'],
        ['name' => 't-bar-rows', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/T-Bar-Row.gif'],
        ['name' => 'seated-cable-rows', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Cable-Seated-Row.gif'],
        ['name' => 'dumbbell-rows', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Dumbbell-Row.gif'],
        ['name' => 'face-pulls', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Face-Pull.gif'],
    ],
    'legs' => [
        ['name' => 'squats', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/BARBELL-SQUAT.gif'],
        ['name' => 'lunges', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Dumbbell-Lunge.gif'],
        ['name' => 'leg-press', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2022/02/Leg-Press.gif'],
        ['name' => 'romanian-deadlifts', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2022/02/Barbell-Romanian-Deadlift.gif'],
        ['name' => 'leg-curls', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Lying-Leg-Curl.gif'],
        ['name' => 'leg-extensions', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/LEG-EXTENSION.gif'],
        ['name' => 'bulgarian-split-squats', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Dumbbell-Bulgarian-Split-Squat.gif'],
        ['name' => 'calf-raises', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Barbell-Standing-Calf-Raise.gif'],
    ],
    'shoulders' => [
        ['name' => 'shoulder-press', 'url' => 'https://newlife.com.cy/wp-content/uploads/2019/11/Dumbbell-Shoulder-Press_shoulder.gif'],
        ['name' => 'lateral-raises', 'url' => 'https://newlife.com.cy/wp-content/uploads/2019/11/dumbbell-lateral-raise.gif'],
        ['name' => 'front-raises', 'url' => 'https://newlife.com.cy/wp-content/uploads/2019/11/dumbbell-front-raise.gif'],
        ['name' => 'rear-delt-flyes', 'url' => 'https://newlife.com.cy/wp-content/uploads/2019/11/Dumbbell-Reverse-Fly.gif'],
        ['name' => 'arnold-press', 'url' => 'https://newlife.com.cy/wp-content/uploads/2019/11/Arnold-Dumbbell-Press.gif'],
        ['name' => 'upright-rows', 'url' => 'https://newlife.com.cy/wp-content/uploads/2019/11/barbell-upright-row.gif'],
        ['name' => 'cable-lateral-raises', 'url' => 'https://newlife.com.cy/wp-content/uploads/2019/11/Cable-Lateral-Raise.gif'],
        ['name' => 'shrugs', 'url' => 'https://newlife.com.cy/wp-content/uploads/2019/11/barbell-shrug.gif'],
    ],
    'arms' => [
        ['name' => 'bicep-curls', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Dumbbell-Curl.gif'],
        ['name' => 'tricep-dips', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Bench-Dips.gif'],
        ['name' => 'hammer-curls', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Hammer-Curl.gif'],
        ['name' => 'tricep-extensions', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Dumbbell-Triceps-Extension.gif'],
        ['name' => 'cable-curls', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Standing-Cable-Curl.gif'],
        ['name' => 'skull-crushers', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Barbell-Lying-Triceps-Extension.gif'],
        ['name' => 'preacher-curls', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Dumbbell-Preacher-Curl.gif'],
        ['name' => 'tricep-pushdowns', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Cable-Pushdown.gif'],
        ['name' => 'concentration-curls', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Concentration-Curl.gif'],
    ],
    'abs' => [
        ['name' => 'crunches', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Crunches.gif'],
        ['name' => 'plank', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Front-Plank.gif'],
        ['name' => 'russian-twists', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Russian-Twist.gif'],
        ['name' => 'leg-raises', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Leg-Raises.gif'],
        ['name' => 'bicycle-crunches', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Bicycle-Crunches.gif'],
        ['name' => 'mountain-climbers', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Mountain-Climbers.gif'],
        ['name' => 'ab-wheel-rollouts', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Ab-Rollout.gif'],
        ['name' => 'side-plank', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/Elbow-Side-Plank.gif'],
        ['name' => 'v-ups', 'url' => 'https://fitnessprogramer.com/wp-content/uploads/2021/02/V-up.gif'],
    ],
];

// Download function
function downloadGif($url, $savePath) {
    $ch = curl_init($url);
    $fp = fopen($savePath, 'wb');
    
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    fclose($fp);
    
    return $httpCode === 200;
}

// Download all GIFs
$totalDownloaded = 0;
$totalFailed = 0;

echo "\n🚀 Starting download of exercise GIFs...\n\n";

foreach ($exerciseLibrary as $muscleGroup => $exercises) {
    echo "📁 Downloading $muscleGroup exercises...\n";
    
    foreach ($exercises as $exercise) {
        $filename = $exercise['name'] . '.gif';
        $savePath = $baseDir . $muscleGroup . '/' . $filename;
        
        // Skip if already exists
        if (file_exists($savePath)) {
            echo "   ⏭️  Skipped: $filename (already exists)\n";
            continue;
        }
        
        echo "   ⬇️  Downloading: $filename... ";
        
        if (downloadGif($exercise['url'], $savePath)) {
            echo "✅ Success\n";
            $totalDownloaded++;
        } else {
            echo "❌ Failed\n";
            $totalFailed++;
            // Delete failed file
            if (file_exists($savePath)) {
                unlink($savePath);
            }
        }
        
        // Be nice to servers - small delay
        usleep(500000); // 0.5 second delay
    }
    
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Downloaded: $totalDownloaded GIFs\n";
echo "❌ Failed: $totalFailed GIFs\n";
echo "📁 Location: $baseDir\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if ($totalDownloaded > 0) {
    echo "🎉 SUCCESS! Now update workout-muscle.php to use local paths.\n";
    echo "Example: 'gif' => '../assets/videos/exercises/chest/push-ups.gif'\n";
}

if ($isBrowser) {
    echo '</pre></body></html>';
}

<?php

$section = 'landing-urls';
require('header.php');

if (\LeadMax\TrackYourStats\System\Session::userType() != \App\Privilege::ROLE_GOD) {
    send_to('home.php');
    exit;
}

$company = \App\Company::instance()->firstOrFail();
$error = null;
$urlValue = trim($_POST['url'] ?? '');
$statusValue = $_POST['status'] ?? '1';

if (isset($_POST['submit'])) {
    if (!\LeadMax\TrackYourStats\Offer\LandingLink::isValidBaseUrl($urlValue)) {
        $error = 'Enter a root http or https URL without a path or query.';
    } elseif (!in_array($statusValue, ['0', '1'], true)) {
        $error = 'Select a valid status.';
    } else {
        $company->landingUrls()->create([
            'url' => rtrim($urlValue, '/'),
            'status' => (int) $statusValue,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
        send_to('landing_urls.php');
        exit;
    }
}
?>

<div class="right_panel">
    <div class="white_box_outer large_table">
        <div class="heading_holder"><span class="lft value_span9">Create Landing URL</span></div>
        <div class="white_box_x_scroll white_box value_span8">
            <div class="left_con01">
                <?php if ($error): ?><p class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                <form action="add_landing_url.php" method="post">
                    <p><label for="url">URL:</label> <input id="url" type="url" name="url" required value="<?= htmlspecialchars($urlValue, ENT_QUOTES, 'UTF-8') ?>"></p>
                    <p><label for="status">Status:</label>
                        <select id="status" name="status">
                            <option value="1" <?= $statusValue === '1' ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= $statusValue === '0' ? 'selected' : '' ?>>In-Active</option>
                        </select>
                    </p>
                    <input class="btn btn-default btn-success" type="submit" value="Create" name="submit">
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

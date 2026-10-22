<?php

$section = 'landing-urls';
require('header.php');

if (\LeadMax\TrackYourStats\System\Session::userType() != \App\Privilege::ROLE_GOD) {
    send_to('home.php');
    exit;
}

$company = \App\Company::instance()->firstOrFail();
$landingUrl = $company->landingUrls()->findOrFail((int) ($_GET['id'] ?? 0));
$error = null;
$urlValue = trim($_POST['url'] ?? $landingUrl->url);
$statusValue = $_POST['status'] ?? (string) $landingUrl->status;

if (isset($_POST['submit'])) {
    if (!\LeadMax\TrackYourStats\Offer\LandingLink::isValidBaseUrl($urlValue)) {
        $error = 'Enter a root http or https URL without a path or query.';
    } elseif (!in_array($statusValue, ['0', '1'], true)) {
        $error = 'Select a valid status.';
    } else {
        $landingUrl->url = rtrim($urlValue, '/');
        $landingUrl->status = (int) $statusValue;
        $landingUrl->save();
        send_to('landing_urls.php');
        exit;
    }
}
?>

<div class="right_panel">
    <div class="white_box_outer large_table">
        <div class="heading_holder"><span class="lft value_span9">Edit Landing URL</span></div>
        <div class="white_box_x_scroll white_box value_span8">
            <div class="left_con01">
                <?php if ($error): ?><p class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
                <form action="edit_landing_url.php?id=<?= (int) $landingUrl->id ?>" method="post">
                    <p><label for="url">URL:</label> <input id="url" type="url" name="url" required value="<?= htmlspecialchars($urlValue, ENT_QUOTES, 'UTF-8') ?>"></p>
                    <p><label for="status">Status:</label>
                        <select id="status" name="status">
                            <option value="1" <?= $statusValue === '1' ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= $statusValue === '0' ? 'selected' : '' ?>>In-Active</option>
                        </select>
                    </p>
                    <p><label>Created On:</label> <?= \Carbon\Carbon::parse($landingUrl->timestamp)->toFormattedDateString() ?></p>
                    <input class="btn btn-default btn-success" type="submit" value="Save" name="submit">
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

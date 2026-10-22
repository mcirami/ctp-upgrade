<?php

$section = 'landing-urls';
require('header.php');

if (\LeadMax\TrackYourStats\System\Session::userType() != \App\Privilege::ROLE_GOD) {
    send_to('home.php');
    exit;
}

$company = \App\Company::instance()->firstOrFail();
$urls = $company->landingUrls()->orderBy('id', 'desc')->get();
?>

<div class="right_panel">
    <div class="white_box_outer large_table">
        <div class="heading_holder">
            <span class="lft value_span9">Landing URLs</span>
            <a href="add_landing_url.php" class="btn btn-sm btn-default">Create New</a>
        </div>
        <div class="white_box_x_scroll white_box manage_aff large_table value_span8">
            <table class="table table-bordered table_01" id="mainTable">
                <thead><tr>
                    <th class="value_span9">Landing URL</th>
                    <th class="value_span9">Status</th>
                    <th class="value_span9">Created On</th>
                    <th class="value_span9">Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($urls as $url): ?>
                    <tr>
                        <td><?= htmlspecialchars($url->url, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span style="color:<?= $url->status ? 'green' : 'red' ?>"><?= $url->status ? 'ACTIVE' : 'IN-ACTIVE' ?></span></td>
                        <td><?= \Carbon\Carbon::parse($url->timestamp)->toFormattedDateString() ?></td>
                        <td><a class="btn btn-default btn-sm" href="edit_landing_url.php?id=<?= (int) $url->id ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

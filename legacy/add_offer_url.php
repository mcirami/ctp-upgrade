<?php
/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/30/2017
 * Time: 11:16 AM
 */

use App\Privilege;
use App\User;

$section = "offer-urls";

require('header.php');


if (!\LeadMax\TrackYourStats\System\Session::permissions()->can("edit_offer_urls")) {
    send_to("home.php");
}

$managers = User::withRole(Privilege::ROLE_MANAGER)->select(['rep.idrep', 'rep.user_name'])->orderBy('rep.user_name')->get();

if (isset($_POST['submit'])) {
    $URLs = new \LeadMax\TrackYourStats\Offer\URLs(\LeadMax\TrackYourStats\System\Company::loadFromSession());
	$assignedManagerId = isset($_POST['assigned_manager_id']) && $_POST['assigned_manager_id'] !== ''
			? (int)$_POST['assigned_manager_id']
			: null;
	if ($URLs->createOfferURL($_POST["url"], $_POST["status"], $assignedManagerId)) {
		send_to("offer_urls.php");
	}
}


?>

<!--right_panel-->
<div class="right_panel">
    <div class="white_box_outer large_table ">
        <div class="heading_holder">
            <span class="lft value_span9">Create Offer URL</span>

        </div>

        <div class="white_box_x_scroll white_box  value_span8 ">
            <div class="left_con01">

                <div class="" style="margin-bottom:20px">
                    <span class="alert alert-info">Point URL to this IP: <?= $_SERVER["SERVER_ADDR"] ?></span>
                </div>

                <form action="add_offer_url.php" method="post">
                    <p>
                        <label for="url">URL:</label>
                        <input type="text" name="url" value="">
                    </p>

                    <p>
                        <label for="status">Status:</label>
                        <select name="status">
                            <?php


                            echo "<option  value=\"1\"><span color='green'>Active</span></option>";
                            echo "<option value=\"0\"><span color='red'>In-Active</span></option>";

                            ?>

                        </select>
                    </p>

	                <p>
		                <label for="assigned_manager_id">Assigned To:</label>
		                <input type="text" id="manager-search" placeholder="Search by ID or username" class="form-control" style="margin-bottom: 5px;">
		                <select id="assigned_manager_id" name="assigned_manager_id" class="form-control">
			                <option value="">All</option>
			                <?php foreach ($managers as $manager):
				                $searchValue = strtolower($manager->idrep . ' ' . $manager->user_name);
				                $label = $manager->user_name . ' (ID: ' . $manager->idrep . ')';
				                ?>
				                <option value="<?= (int)$manager->idrep ?>" data-search="<?= htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8') ?>">
					                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
				                </option>
			                <?php endforeach; ?>
		                </select>
	                </p>

                    <input class="btn btn-default btn-success" type="submit" value="Create" name="submit">

                </form>
            </div>
        </div>
    </div>
    <!--right_panel-->


    <?php include 'footer.php'; ?>

	<script type="text/javascript">
		(function () {
			const select = document.getElementById('assigned_manager_id');
			const searchInput = document.getElementById('manager-search');
			if (!select || !searchInput) {
				return;
			}

			const optionsData = Array.from(select.options).map(option => ({
				value: option.value,
				text: option.text,
				search: (option.getAttribute('data-search') || option.text).toLowerCase(),
			}));

			const renderOptions = (filterTerm) => {
				const currentValue = select.value;
				select.innerHTML = '';
				let selectionApplied = false;

				optionsData.forEach(option => {
					if (!filterTerm || option.search.indexOf(filterTerm) !== -1) {
						const newOption = new Option(option.text, option.value, false, option.value === currentValue);
						newOption.setAttribute('data-search', option.search);
						select.add(newOption);
						if (option.value === currentValue) {
							selectionApplied = true;
						}
					}
				});

				if (!selectionApplied && select.options.length) {
					select.selectedIndex = 0;
				}
			};

			searchInput.addEventListener('input', function () {
				renderOptions(this.value.toLowerCase());
			});
		})();
	</script>

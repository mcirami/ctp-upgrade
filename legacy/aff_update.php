<?php
use App\Http\Controllers\UserController;

$section = "affiliate-list";
require('header.php');


$assign = new \LeadMax\TrackYourStats\Table\Assignments(
	[
		"offerid"  => -1,
		"out"      => -1,
		"!idrep"   => \LeadMax\TrackYourStats\System\Session::userID(),
		"clearAtt" => -1
	]
);

$assign->getAssignments();
$assign->setGlobals();

$idrep = (int)$idrep;

//checks to see if this User is a child of logged in User, if not redirect
if (\LeadMax\TrackYourStats\System\Session::userID() !== $idrep)
{
	if (!$user->hasRep($idrep) && \LeadMax\TrackYourStats\System\Session::userType() == \App\Privilege::ROLE_MANAGER)
	{
		send_to("home.php");
	}
}

if ($idrep !== \LeadMax\TrackYourStats\System\Session::userID())
{
	
	if (!\LeadMax\TrackYourStats\System\Session::permissions()->can("edit_affiliates"))
	{
		send_to("home.php");
	}
}

$update = new \LeadMax\TrackYourStats\User\Update($assign);

$update->updateAffiliatePayout();


$update->selectUser();


//run update
if (\LeadMax\TrackYourStats\System\Session::userType() == \App\Privilege::ROLE_GOD || \LeadMax\TrackYourStats\System\Session::userType() == \App\Privilege::ROLE_ADMIN)
{
	$ezGawd = true;
}
else
{
	$ezGawd = false;
}


$insert = $user->Update("aff_update.php?idrep={$idrep}", $ezGawd);

$error = "";
if ($insert == "PWD_NO_MATCH")
{
	$error = "Passwords do not match.";
}


$update->dumpAssignablesToJavaScript();
$update->dumpPermissionsToJavascript();


?>
<script type = "text/javascript" src = "js/aff.js"></script>

<!--right_panel-->
<div class = "right_panel">
	<div class = "white_box_outer">
		<div class = "heading_holder value_span9"><span
					class = "lft">Edit User <?php echo $update->selectedUser->first_name . " " . $update->selectedUser->last_name; ?></span>
		</div>
		<div class = "white_box value_span8">
			<span class = "small_txt value_span10"><?PHP echo $error; ?></span>
			
			<form action = "<?php htmlspecialchars($_SERVER['PHP_SELF']); ?>" method = "post" id = "form"
				  enctype = "multipart/form-data">
				<input type = "hidden" name = "idrep" value = "<?php echo $update->selectedUser->idrep; ?>">
				<div class = "left_con01">
					<p>
						<label class = "value_span9">First Name</label>
						
						<input type = "text" class = "form-control" name = "first_name" maxlength = "155"
							   value = "<?php echo $update->selectedUser->first_name; ?>" id = "first_name"/>
					</p>
					<p>
						<label class = "value_span9">Last Name</label>
						
						<input type = "text" class = "form-control" name = "last_name" maxlength = "155"
							   value = "<?php echo $update->selectedUser->last_name; ?>" id = "last_name"/>
					</p>
					<p>
						<label class = "value_span9">Email</label>
						
						<input type = "text" class = "form-control input-sm" name = "email" maxlength = "155"
							   value = "<?php echo $update->selectedUser->email; ?>" id = "email"/>
					</p>
					<p>
						<label class = "value_span9">Cell Phone</label>
						
						<input type = "text" class = "form-control input-sm" name = "cell_phone" maxlength = "155"
							   placeholder = "(Optional)"
							   value = "<?php echo $update->selectedUser->cell_phone; ?>" id = "cell_phone"/>
					</p>
					<p>
						<label class = "value_span9">Company</label>
						<!-- TODO Link Referrer Payout -->
						<input type = "text" class = "form-control" name = "company_name" minlength = "5" maxlength = "255"
							   placeholder = "(Optional)"
							   v value = "<?php echo $update->selectedUser->company_name; ?>" id = "company_name"/>
					</p>
					<p>
						<label class = "value_span9">Skype</label>
						<!-- TODO Link Referrer Payout -->
						<input type = "text" class = "form-control" name = "skype" minlength = "5" maxlength = "255"
							   placeholder = "(Optional)"
							   v value = "<?php echo $update->selectedUser->skype; ?>" id = "skype"/>
					</p>
				
				
				</div>
				<div class = "right_con01">
					
					<?php
					if (\LeadMax\TrackYourStats\System\Session::userType() == \App\Privilege::ROLE_GOD)
					{
						echo "
                                   <p>
                        <label class=\"value_span9\">Username</label>

                        <input type=\"text\" class=\"form-control\" name=\"user_name\" maxlength=\"155\"
                               value=\"{$update->selectedUser->user_name}\" id=\"user_name\"/>
                    </p>
                            ";
					}
					else
					{
						echo "<input type=\"hidden\" name=\"user_name\" value=\"{$update->selectedUser->user_name}\" id=\"user_name\">";
					}
					?>
					
					
					
					<?PHP
					
					
					if ($ezGawd)
					{
						
						
						$update->printRadios();
						
						$update->notifyIfCanChangePriviliges();
						
						
					}
					
					
					?>
					
					<p id = "permissionsP">
					
					</p>
					
					<?php
					
					if (\LeadMax\TrackYourStats\System\Session::userType() == \App\Privilege::ROLE_GOD || \LeadMax\TrackYourStats\System\Session::userType() == \App\Privilege::ROLE_ADMIN)
					{
						$update->printReferrer();
					}
					?>
					<?php
					
					if (\LeadMax\TrackYourStats\System\Session::permissions()->can("edit_referrals") && $update->selectedUserType == \App\Privilege::ROLE_AFFILIATE)
					{
						echo " <p id=\"referralP\">

                        <label class=\"value_span9\">Referrals</label>
                        <a class=\"btn btn-default btn-sm\" href=\"aff_edit_ref.php?affid=$idrep\">
                            <img src=\"/images/icons/user_edit.png\">
                            Edit My Referrals
                        </a>
                        
                        
                      


                    </p>
                   
                    
                    ";
						\LeadMax\TrackYourStats\User\Referrals::printSelectBoxForEditAffiliate($idrep);
					}
					
					
					?>
					
					
					<?php
					
					?>
					
					<p>
						<label class = "value_span9">Status</label>
						<?php if ($update->selectedUser->status == 1)
						{
							
							echo "<select class=\"form-control input-sm \" id=\"status\" name=\"status\" value=\"1\"><option selected value=\"1\">Active</option>;<option value=\"0\">Disabled</option>;</select>";
						}
						else
						{
							
							echo "<select class=\"form-control input-sm \" id=\"status\" name=\"status\" value=\"1\"><option value=\"1\">Active</option>;<option selected value=\"0\">Disabled</option>;</select>";
							
						}
						?>
					
					
					</p>
					
					
					<p>
						<label class = "value_span9">Password</label>
						
						<input type = "text" class = "form-control" name = "password" minlength = "5" maxlength = "255"
							   value = ""
							   id = "password"/>
					</p>
					<p>
						<label class = "value_span9">Re-Enter Password</label>
						
						<input type = "text" class = "form-control" name = "confirmpassword" minlength = "5" maxlength = "255"
							   value = "" id = "confirmpassword"/>
					</p>
					<!--                    <!-- TODO Impliment Change Password-->
					<!--                    <span class="btn_yellow"> <input type="submit" name="buttonChangePassword"-->
					<!--                                                     class="value_span6-2 value_span2 value_span1-2"
					<!--                                                     value="Change Password"/></span>-->
				
				</div>
				<span class = "btn_yellow"> <input type = "submit" name = "button"
												   class = "value_span6-2 value_span2 value_span1-2"
												   value = "Update"/></span>
				<span class = "btn_yellow" style = "margin-left:2%;"> <a onclick = "history.go(-1);"
																		 class = "value_span6-2 value_span2 value_span1-2"
					>Cancel</a></span>

		</form>
		
		
		</div>
		<?php
		if(\LeadMax\TrackYourStats\System\Session::userType() == \App\Privilege::ROLE_GOD) :

		$userClass = new UserController;
		$subIds = $userClass->getUserSubIds();
		?>

		<div class = "heading_holder value_span9">
			<span class = "lft"><?php echo $update->selectedUser->first_name . " " . $update->selectedUser->last_name; ?>'s Sub Id's</span>
		</div>
		<div class="white_box value_span8">
			<table class="table_01 large_table sub_ids" id="mainTable">

				<thead>
				<tr>
					<th class="value_span9">Sub ID</th>
					<th class="value_span9">Action</th>
				</tr>
				</thead>
				<tbody>

				<?php foreach ($subIds as $subId) : ?>
					<tr>
						<td> <?php echo $subId["subId"]; ?> </td>
						<td class="button_wrap">
							<?php if ($subId["blocked"]) : ?>
								<button class="block_sub_id"
								        disabled="disabled"
								        data-subid=<?php echo $subId["subId"]; ?>
								>Blocked</button>
								<button class="unblock_button value_span6-2 value_span2 value_span1-2"
								        data-subid=<?php echo $subId["subId"]; ?>
								>UnBlock</button>
							<?php else : ?>
								<button class="block_sub_id value_span6-2 value_span2 value_span1-2"
								        data-subid=<?php echo $subId["subId"]; ?>
								>Block ID</button>
								<button style="display: none;"
								        disabled="disabled"
								        class="unblock_button value_span6-2 value_span2 value_span1-2"
								        data-subid=<?php echo $subId["subId"]; ?>
								>UnBlock</button>
							<?php endif; ?>

						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>

		<?php
		
		if (\LeadMax\TrackYourStats\System\Session::permissions()->can("edit_aff_payout") && $update->selectedUserType == \App\Privilege::ROLE_AFFILIATE)
		{
			
			echo " <div class=\"heading_holder value_span9\"><span
                    class=\"lft\">{$update->selectedUser->first_name} 's offers.</span></div>

        <div class=\"white_box_outer\">

            <div class=\"white_box manage_aff value_span8\">
                <p>
                <table class=\"table_01   large_table\" id=\"mainTable\">
                    <thead>

                    <tr>

                        <th class=\"value_span9\">Offer ID</th>
                        <th class=\"value_span9\">Offer Name</th>
                        <th class=\"value_span9\">Offer Payout</th>";
			
			
			if (\LeadMax\TrackYourStats\System\Session::permissions()->can("edit_aff_payout"))
			{
				echo "<th class=\"value_span9\">Change Aff Payout</th>";
			}
			
			
			echo "

                    </tr>
                    </thead>
                    <tbody>";
			
			
			$update->getaffiliatePayouts();
			
			
			echo "</tbody>
                </table>


            </div>


            </p>

        </div>";
		}
		
		?>
	
	
	</div>


</div>

<?php $update->checkBox() ?>

<!--right_panel-->

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script type = "text/javascript">
	
	$("#salaryCheckBox").click(function () {
		if ($("#salaryCheckBox").is(":checked"))
			$("#salaryPaid").prop("disabled", false);
		else
			$("#salaryPaid").prop("disabled", true);
	});
	
	// A $( document ).ready() block.
	$(document).ready(function () {

		$("#mainTable").tablesorter(
			{
				sortList: [[5, 1]],
				widgets: ['staticRow']
			});

		if ($('#affRadio').is(':checked'))
			$("#referralP").show();

		const blockButtons = document.querySelectorAll('.block_sub_id');
		if (blockButtons) {
			blockButtons.forEach((button) => {
				button.addEventListener('click', (e) => {
					e.preventDefault();
					const button = e.target;
					const userID = '<?php echo $idrep; ?>';
					const subID = button.dataset.subid;

					const packets = {
						user_id: userID,
						sub_id: subID
					}

					axios.post('user/block-sub-id', packets).then((response) => {
						if (response.data.success) {
							button.innerHTML = "Blocked"
							button.disabled = true;
							button.classList.remove("value_span6-2", "value_span2", "value_span1-2");
							const unblockButton = button.nextElementSibling;
							unblockButton.disabled = false;
							unblockButton.style.display = "block"
						} else {
							console.log(response);
						}
					})

				})
			});
		}

		const unblock_buttons = document.querySelectorAll('.unblock_button');
		if(unblock_buttons) {
			unblock_buttons.forEach((button) => {
				button.addEventListener('click', (e) => {
					e.preventDefault();
					const button = e.target;
					const userID = '<?php echo $idrep; ?>';
					const subID = button.dataset.subid;

					const packets = {
						user_id: userID,
						sub_id: subID
					}

					axios.post('user/unblock-sub-id', packets).then((response) => {
						if (response.data.success) {
							button.disabled = true;
							button.style.display = "none";
							const blockButton = button.previousElementSibling;
							blockButton.innerHTML = "Block ID";
							blockButton.disabled = false;
							blockButton.classList.add("value_span6-2", "value_span2", "value_span1-2");
						} else {
							console.log(response);
						}
					})
				})
			});
		}
	});
	
	function setTwoNumberDecimal(event) {
		this.value = parseFloat(this.value).toFixed(2);
	}

</script>
<?php include 'footer.php'; ?>

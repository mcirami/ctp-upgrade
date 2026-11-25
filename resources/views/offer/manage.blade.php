@php use LeadMax\TrackYourStats\System\Session; @endphp
@extends('layouts.master')

@section('content')

    <style>
        #offers_container .lander-url-heading {
            display: block;
            margin-top: 10px;
			font-size: 13px;
        }
        #offers_container button.lander-name-help {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            min-width: 16px;
            margin: -15px 0 0 4px;
            padding: 0;
            border: 1px solid currentColor;
            border-radius: 50%;
            background: transparent;
            color: inherit;
            font-size: 12px;
            line-height: 1;
            vertical-align: middle;
            box-shadow: none;
            cursor: help;
        }
        .ui-tooltip.lander-name-tooltip {
            max-width: min(440px, calc(100vw - 24px));
            padding: 12px;
            color: #333;
            background: #fff;
            font-size: 14px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }
        .lander-name-tooltip p { margin: 0 0 12px; }
        .lander-name-tooltip p:last-child { margin-bottom: 0; }
    </style>

    <form id="delete-offer-form" method="POST" hidden>
        @csrf
        @method('DELETE')
    </form>

	<!--right_panel-->
	<div class="right_panel">
		<div class="white_box_outer large_table">
            @if (session('message'))
                <div class="alert alert-success">{{ session('message') }}</div>
            @endif
			<div class="heading_holder">
				<span class="lft value_span9">Offers</span>
				@if (Session::permissions()->can("create_offers"))
					<a style='margin-left: 1%; margin-top:.3%;' href="/offer_add.php"
					   class='btn btn-default btn-sm value_span5-1 value_span6-5 value_span2'>Create New Offer</a>
				@endif
			</div>

            @if ($showArchived)
                <p class="alert alert-info">These offers were hidden by the old Delete action. Use Delete permanently to remove their settings and preserve their reporting history.</p>
                <a class="btn btn-default btn-sm" href="/offer/manage">Back to active offers</a>
            @elseif (Session::userType() !== \App\Privilege::ROLE_AFFILIATE)
                @include('report.options.active')
            @endif
            @if (Session::userType() === \App\Privilege::ROLE_GOD && !$showArchived)
                <a class="btn btn-default btn-sm" href="/offer/manage?showArchived=1">Previously hidden offers</a>
            @endif

			@if(Session::userType() == \App\Privilege::ROLE_AFFILIATE)
				<div class='form-group'>
					<p class='form-control'>
						Add up to 5 Sub variables as follows: http://domain.com/?repid=1&offerid=1&sub1=XXX&sub2=YYY&sub3=ZZZ&sub4=AAA&sub5=BBB
					</p>

				</div>
			@endif

			<script type="text/javascript">
				function handleSelect(elm) {
					const url = new URL(window.location.href);
					url.searchParams.set(elm.id === 'offer_url' ? 'url' : 'lander', elm.value);
					window.location = url.toString();
				}
			</script>


			<div style="display:flex; flex-wrap:wrap; gap:10px; margin:0 0 1px 0; padding:5px;">

				@if(Session::userType() == \App\Privilege::ROLE_AFFILIATE)
					<div style="width:250px;">
					<label class="value_span9">Offer URLS: </label>
					<select onchange='handleSelect(this);' class="form-control input-sm " id="offer_url"
							name="offer_url">


						@for ($i = 0; $i < count($urls); $i++)
							@if (request('url',0) == $i)
								<option selected value='{{$i}}'> {{$urls[$i]}}</option>
							@else
								<option value='{{$i}}'> {{$urls[$i]}}</option>
							@endif
						@endfor

					</select>
					</div>
					<div style="width:250px;">
						<label class="value_span9" for="lander_url">Lander URLs:</label>
						<select onchange="handleSelect(this);" class="form-control input-sm" id="lander_url" name="lander_url" @if($landingUrls->isEmpty()) disabled @endif>
							@if($landingUrls->isEmpty())
								<option>No active Lander URLs</option>
							@else
								@foreach($landingUrls as $landingUrl)
									<option value="{{ $landingUrl->id }}" @if($selectedLandingUrl->id == $landingUrl->id) selected @endif>{{ $landingUrl->url }}</option>
								@endforeach
							@endif
					</select>
					</div>

				@endif
			</div>


			<div class="form-group searchDiv">
				<input id="searchBox"
					   class="form-control"
					   type="text"
					   placeholder="Search offers...">
			</div>

			<div class="clear"></div>
			<div class="white_box manage_aff white_box_x_scroll large_table value_span8">


				<table class="table table-condensed table-bordered table_01" id="mainTable">
					<thead>

					<tr>
						<th class="value_span9">Offer ID</th>
						<th class="value_span9">Offer Name</th>
                        @if(Session::permissions()->can("view_payouts") )
                            <th class="value_span9">Payout</th>
                        @endif
						<th class="value_span9">Offer Type</th>

						@if (Session::userType() == \App\Privilege::ROLE_AFFILIATE)
							<th class="value_span9">Offer Link</th>
						@elseif(Session::permissions()->can("edit_affiliates") &&
                                Session::userType() != \App\Privilege::ROLE_AFFILIATE &&
                                Session::userType() != \App\Privilege::ROLE_MANAGER)
							<th class="value_span9">GEO Rules</th>
						@endif

						@if (Session::userType() !== \App\Privilege::ROLE_AFFILIATE &&
Session::userType() !== \App\Privilege::ROLE_MANAGER &&
Session::userType() !== \App\Privilege::ROLE_ADMIN)
							<th class="value_span9">Adv</th>
						@endif
						{{--@if (\LeadMax\TrackYourStats\System\Session::userType() == \App\Privilege::ROLE_AFFILIATE)
							<th class="value_span9">Postback Options</th>
						@endif--}}

						@if (Session::userType() != \App\Privilege::ROLE_AFFILIATE &&
Session::userType() !== \App\Privilege::ROLE_MANAGER &&
Session::userType() !== \App\Privilege::ROLE_ADMIN):
							<th class="value_span9">Offer Timestamp</th>
						@endif

						@if (Session::userType() != \App\Privilege::ROLE_AFFILIATE &&
Session::userType() !== \App\Privilege::ROLE_MANAGER &&
Session::userType() !== \App\Privilege::ROLE_ADMIN
)
							<th class="value_span9">Actions</th>
						@endif
					</tr>
					</thead>
					<tbody id="offers_container">


					@if(isset($requestableOffers))
						@foreach ($requestableOffers as $offer)
							<tr>
								<td>{{$offer->idoffer}}</td>
								<td>{{$offer->offer_name}}</td>
								<td>Requires Offer</td>
								<td>${{$offer->payout}}</td>
								<td>{{$offer->campaign_name}}</td>
								<td>Requires Offer</td>
								<td>
									<button id='btn_{{$offer->idoffer}}' class='btn btn-sm btn-default'
											onclick='requestOffer({{$offer->idoffer}})'>Request Offer
									</button>
								</td>
							</tr>
						@endforeach
					@endif


					</tbody>
				</table>

				<div id="pagination" class="pagination-container"></div>

			</div>
		</div>
	</div>
	<!--right_panel-->

@endsection

@section('footer')
	<script type="text/javascript">
		function requestOffer(id) {

			$("#btn_" + id).attr('disabled', true);

			$.ajax({
				url: "/offer/" + id + '/request?' <?= ( isset( $_GET["adminLogin"] ) ) ? " + '&adminLogin'" : "" ?>,
				success: function(result) {

					$.notify({

								title: 'Successfully',
								message: ' requested offer!'

							}, {
								placement: {
									from: 'top',
									align: 'center'
								},
								type: 'info',
								animate: {
									enter: 'animated fadeInDown',
									exit: 'animated fadeOutUp'
								},
							}
					);
				},

				error: function(result) {
					$("#btn_" + id).attr('disabled', false);

					$.notify({

								title: 'Failed to request offer!',
								message: ' Please try again later or contact an admin.'

							}, {
								placement: {
									from: 'top',
									align: 'center'
								},
								type: 'danger',
								animate: {
									enter: 'animated fadeInDown',
									exit: 'animated fadeOutUp'
								},
							}
					);
				}
			});

		}
	</script>

	<script type="text/javascript">

		$(document).ready(function () {
            // Delegate to include offers rebuilt by search and pagination.
            // Bootstrap also defines tooltip, so give the jQuery UI widget its own name.
            $.widget.bridge('landerNameTooltip', $.ui.tooltip);
            $('#offers_container').landerNameTooltip({
                items: '.lander-name-help',
                tooltipClass: 'lander-name-tooltip',
                content: function () {
                    return $(this).next('.lander-name-help-content').html();
                }
            });

            // Delegation also handles rows rebuilt by pagination and search.
            $(document).on('click', '.delete_offer', function (event) {
                event.preventDefault();
                const offerID = this.dataset.offer;
                if (window.confirm('Permanently delete offer #' + offerID + '? Assignments, caps and targeting settings will be removed. Historical reports will remain as Deleted offer #' + offerID + '. Other rules that redirect here will block traffic until a replacement is selected. This cannot be undone.')) {
                    const form = document.getElementById('delete-offer-form');
                    form.action = '/offer/' + encodeURIComponent(offerID);
                    form.submit();
                }
            });

			let itemsPerPage = 20;
			let offers = @json($offers);

			const paginationContainer = "#pagination";

			document.getElementById('searchBox').addEventListener('input', (e) => {
				const userInput = e.target.value.trim().toLowerCase();
				let filteredOffers = offers.filter((offer) => {
					return offer.offer_name.toLowerCase().includes(userInput) || offer.idoffer.toString().includes(userInput);
				})
				paginate(filteredOffers, itemsPerPage, paginationContainer);
			});

			paginate(offers, itemsPerPage, paginationContainer);

			function paginate(items, itemsPerPage, paginationContainer) {
				let currentPage = 1;
				const totalPages = Math.ceil(items.length / itemsPerPage);

				function showItems(page) {
					const startIndex = (page - 1) * itemsPerPage;
					const endIndex = startIndex + itemsPerPage;
					const pageItems = items.slice(startIndex, endIndex);

					const itemsContainer = document.querySelector("#offers_container");
					itemsContainer.innerHTML = "";

					let html = "";
					let userType = '<?php echo Session::userType(); ?>';

					let url = @json($selectedOfferUrl);
					let permissions = '<?php echo json_encode( Session::permissions() ); ?>'
					permissions = JSON.parse(permissions);
					const sessionUser = '<?php echo Session::userID(); ?>';
					pageItems.forEach((offer) => {
						html += `<tr id='offer_row'>` +
								`<td>` + offer['idoffer'] + `</td>` +
								`<td>` + offer['offer_name'];

						if (parseInt(userType) === 3) {
							html += `<br/><span class='link_label'>Offer Link:</span><br /> ` +
								`<span class='offer_link'>https://` + url +
								`/?repid=` + sessionUser +
								`&offerid=` + offer['idoffer'] + `&sub1=</span>`;
							if (offer['landing_link']) {
                                const landerUrl = $('<div>').text(offer['landing_link']).html();
                                const kateUrl = $('<div>').text(offer['landing_link'] + 'Kate').html();
                                const meganUrl = $('<div>').text(offer['landing_link'] + 'Megan439').html();
                                html += `<span class="lander-url-heading"><span class='link_label'>Lander URL:</span> (Fill "n=" to change model name)` +
                                    `<button type="button" class="lander-name-help" aria-label="How to change your model's name">?</button>` +
                                    `<span class="lander-name-help-content" hidden>` +
                                    `<p>To change your model's name on the Lander URL add the name you want to use to the end of the link after n=, for example:</p>` +
                                    `<p>${kateUrl}<br>-This changes the name on your Lander URL to Kate</p>` +
                                    `<p>${meganUrl}<br>-This changes the name on your Lander URL to Megan439</p>` +
                                    `<p>Use any name you like after n= to pass the name to the page.</p></span></span>` +
                                    `<span class='offer_link'>${landerUrl}</span>`;
							}
						}

						html += `</td>`;

						if (permissions.permissions.view_payouts) {
							html += `<td class='value_span10'>$` + offer['payout'] + `</td>`;
						}

						html += `<td>CPA</td>`;
								
						if (parseInt(userType) === 3) {
							html +=
									`<td class='value_span10'>` +
									`<p  style='display:none;' id='url_` + offer['idoffer'] + `'>http://` + url +
									`/?repid=` + sessionUser +
									`&offerid=` + offer['idoffer'] + `&sub1=</p>` +
									`<button data-url='https://` + url +
									`/?repid=` + sessionUser +
									`&offerid=` + offer['idoffer'] +
									`&sub1=' data-toggle='tooltip' title='Copy My Link' ` +
									`class='copy_button btn btn-default'>Copy My Link` +
									`</button></td>`;
						}

						if (permissions.permissions.edit_affiliates &&
                            (parseInt(userType) === 0 || parseInt(userType) === 1)) {
							html += `<td class='value_span10'>` +
									(offer['geo_rule_names'] || 'N/A') +
									`</td>`;
						}

						if (parseInt(userType) === 0) {
							html += `<td class='value_span10'>` + offer['campaign_name'] + `</td>`;
							/*if (offer['status'] === 1) {
								html += `Active`;
							} else {
								html += `Inactive`;
							}*/
						}

						/*if (userType == 3) {
							html += `<td class='value_span10'>` +
									`<a class='btn btn-default value_span6-1 value_span4' data-toggle='tooltip' title='Offer PostBack Options' ` +
									`href='/offer_edit_pb.php?offid='` + offer['idoffer'] + `'>` +
									`Edit Post Back</a>` +
									`</td>`;
						}*/

						if (parseInt(userType) === 0) {
							html += `<td class='value_span10'>` + offer['offer_timestamp'] + `</td>`;
						}
						if (permissions.permissions.edit_offer_rules) {
							html += `<td class='value_span10 action_column'>`;
						}
						if (parseInt(userType) === 0) {
							if (permissions.permissions.create_offers) {
								html += `<a class='btn btn-default btn-sm value_span6-1 value_span4' data-toggle='tooltip' title='Edit Offer' ` +
										`href='/offer_update.php?idoffer=` + offer['idoffer'] + `'>Edit</a>`;
							}
						}

						if (permissions.permissions.edit_offer_rules) {
							html += `<a class='btn btn-default btn-sm value_span6-1 value_span4' data-toggle='tooltip' title='Edit Offer Rules' ` +
									`href='/offer_edit_rules.php?offid=` + offer[`idoffer`] + `'> Rules</a>`;
						}

						if (parseInt(userType) === 0) {
							html += `<a class='btn btn-default btn-sm value_span6-1 value_span4' data-toggle='tooltip' title='View Offer' ` +
									`href='/offer_details.php?idoffer=` + offer['idoffer'] + `'> View</a>`;
						}/* else {
							html += `<td></td>`;
						}*/

						if (parseInt(userType) === 0) {
							html += `<a class='btn btn-default btn-sm value_span6-1 value_span4' data-toggle='tooltip' title='Duplicate Offer' ` +
									`href='/offer/` + offer['idoffer'] + `/dupe'> Duplicate </a>` +
									`<a class='delete_offer btn btn-default btn-sm value_span11 value_span4' data-toggle='tooltip' data-offer='` + offer['idoffer'] +`' title='Permanently Delete Offer' ` +
									`href='#'>Delete permanently</a>`;
						}
						if (permissions.permissions.edit_offer_rules) {
							html += `</td>`;
						}
						html += `</tr>`;
						itemsContainer.innerHTML = html;
					});
					copyLink()
				}

				function setupPagination() {
					const pagination = document.querySelector(paginationContainer);
					pagination.innerHTML = "";

					for (let i = 1; i <= totalPages; i++) {
						const link = document.createElement("a");
						link.href = "#";
						link.innerText = i;
						link.classList.add("value_span2-2", "value_span3-2", "value_span6-1", "value_span2",
								"value_span6");

						if (i === currentPage) {
							link.classList.add("value_span4", "active");
						}

						link.addEventListener("click", (event) => {
							event.preventDefault();
							currentPage = i;
							showItems(currentPage);

							const currentActive = pagination.querySelector(".active");
							currentActive.classList.remove("active", "value_span4");
							link.classList.add("active", "value_span4");
						});

						pagination.appendChild(link);
					}
				}

				copyLink();

				showItems(currentPage);
				setupPagination();
			}

			function copyLink() {
				document.querySelectorAll('.copy_button').forEach((button) => {
					button.addEventListener("click", (e) => {
						e.preventDefault();
						const url = e.target.dataset.url;
						const unsecuredCopyToClipboard = (text) => {
							const textArea = document.createElement("textarea");
							textArea.value = text;
							document.body.appendChild(textArea);
							textArea.focus();
							textArea.select();
							try {
								document.execCommand('copy')
							} catch (err) {
								console.error('Unable to copy to clipboard', err)
							}
							document.body.removeChild(textArea)
						};
						if (window.isSecureContext && navigator.clipboard) {
							navigator.clipboard.writeText(url);
						} else {
							unsecuredCopyToClipboard(url);
						}
					})
				})
			}

			$("#mainTable").tablesorter(
					{
						sortList: [[1, 0]],
						widgets: ['staticRow']
					});
		});
	</script>
@endsection

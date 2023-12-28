@extends('report.template')

@section('report-title')
    {{$user->user_name}}'s Clicks
@endsection

@section('table-options')
    @include('report.options.dates')
@endsection

@section('table')
	<div class="table_wrap">
		<table id="clicks" class="table table-condensed table-bordered table_01 tablesorter">
			<thead>
			<tr>
				@if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_fraud_data"))
					<th class="value_span9">Click ID</th>
				@endif
				<th class="value_span9">Timestamp</th>
				<th class="value_span9">Offer Name</th>
				<th class="value_span9">Conversion Timestamp</th>
				<th class="value_span9">Paid</th>
				<th class="value_span9">Sub 1</th>
				<th class="value_span9">Sub 2</th>
				<th class="value_span9">Sub 3</th>
				<th class="value_span9">Sub 4</th>
				<th class="value_span9">Sub 5</th>

				@if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_fraud_data"))
					<th class="value_span9">IP Address</th>
				@endif


				@php
					$report->printHeaders();
				@endphp


				<th class="value_span9">Iso Code</th>
				@if (\LeadMax\TrackYourStats\System\Session::permissions()->can("view_fraud_data"))
					<th class="value_span9">Sub Division</th>
					<th class="value_span9">City</th>
					<th class="value_span9">Postal</th>
					<th class="value_span9">Longitude</th>
					<th class="value_span9">Latitude</th>
				@endif
			</tr>
			</thead>
			<tbody>



			@php
				$report->process();
				$report->printR();
			@endphp

			</tbody>
		</table>
	</div>
	<div id="pager" class="pager">
		<form>
			<div class="navigation">
				<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-chevron-bar-left first" viewBox="0 0 16 16">
					<path fill-rule="evenodd" d="M11.854 3.646a.5.5 0 0 1 0 .708L8.207 8l3.647 3.646a.5.5 0 0 1-.708.708l-4-4a.5.5 0 0 1 0-.708l4-4a.5.5 0 0 1 .708 0zM4.5 1a.5.5 0 0 0-.5.5v13a.5.5 0 0 0 1 0v-13a.5.5 0 0 0-.5-.5z"/>
				</svg>
				<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-chevron-left prev" viewBox="0 0 16 16">
					<path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
				</svg>
				<!-- the "pagedisplay" can be any element, including an input -->
				<span class="pagedisplay" data-pager-output-filtered="{startRow:input} &ndash; {endRow} / {filteredRows} of {totalRows} total rows"></span>
				<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-chevron-right next" viewBox="0 0 16 16">
					<path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/>
				</svg>
				<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-chevron-bar-right last" viewBox="0 0 16 16">
					<path fill-rule="evenodd" d="M4.146 3.646a.5.5 0 0 0 0 .708L7.793 8l-3.647 3.646a.5.5 0 0 0 .708.708l4-4a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708 0zM11.5 1a.5.5 0 0 1 .5.5v13a.5.5 0 0 1-1 0v-13a.5.5 0 0 1 .5-.5z"/>
				</svg>
				<select class="pagesize">
					<option value="10">10</option>
					<option value="20">20</option>
					<option value="30">30</option>
					<option value="40">40</option>
					<option value="all">All Rows</option>
				</select>
			</div>
		</form>
	</div>
    {{--@include('report.options.pagination')--}}

@endsection

@section('footer')
    <script type="text/javascript">
		$(document).ready(function () {

			// **********************************
			//  Description of ALL pager options
			// **********************************
			var pagerOptions = {

				// target the pager markup - see the HTML block below
				container: $(".pager"),

				// use this url format "http:/mydatabase.com?page={page}&size={size}&{sortList:col}"
				ajaxUrl: null,

				// modify the url after all processing has been applied
				customAjaxUrl: function(table, url) { return url; },

				// ajax error callback from $.tablesorter.showError function
				// ajaxError: function( config, xhr, settings, exception ) { return exception; };
				// returning false will abort the error message
				ajaxError: null,

				// add more ajax settings here
				// see http://api.jquery.com/jQuery.ajax/#jQuery-ajax-settings
				ajaxObject: { dataType: 'json' },

				// process ajax so that the data object is returned along with the total number of rows
				ajaxProcessing: null,

				// Set this option to false if your table data is preloaded into the table, but you are still using ajax
				processAjaxOnInit: true,

				// output string - default is '{page}/{totalPages}'
				// possible variables: {size}, {page}, {totalPages}, {filteredPages}, {startRow}, {endRow}, {filteredRows} and {totalRows}
				// also {page:input} & {startRow:input} will add a modifiable input in place of the value
				// In v2.27.7, this can be set as a function
				// output: function(table, pager) { return 'page ' + pager.startRow + ' - ' + pager.endRow; }
				output: '{startRow:input} – {endRow} / {totalRows} rows',

				// apply disabled classname (cssDisabled option) to the pager arrows when the rows
				// are at either extreme is visible; default is true
				updateArrows: true,

				// starting page of the pager (zero based index)
				page: 0,

				// Number of visible rows - default is 10
				size: 10,

				// Save pager page & size if the storage script is loaded (requires $.tablesorter.storage in jquery.tablesorter.widgets.js)
				savePages : true,

				// Saves tablesorter paging to custom key if defined.
				// Key parameter name used by the $.tablesorter.storage function.
				// Useful if you have multiple tables defined
				storageKey:'tablesorter-pager',

				// Reset pager to this page after filtering; set to desired page number (zero-based index),
				// or false to not change page at filter start
				pageReset: 0,

				// if true, the table will remain the same height no matter how many records are displayed. The space is made up by an empty
				// table row set to a height to compensate; default is false
				fixedHeight: true,

				// remove rows from the table to speed up the sort of large tables.
				// setting this to false, only hides the non-visible rows; needed if you plan to add/remove rows with the pager enabled.
				removeRows: false,

				// If true, child rows will be counted towards the pager set size
				countChildRows: false,

				// css class names of pager arrows
				cssNext: '.next', // next page arrow
				cssPrev: '.prev', // previous page arrow
				cssFirst: '.first', // go to first page arrow
				cssLast: '.last', // go to last page arrow
				cssGoto: '.gotoPage', // select dropdown to allow choosing a page

				cssPageDisplay: '.pagedisplay', // location of where the "output" is displayed
				cssPageSize: '.pagesize', // page size selector - select dropdown that sets the "size" option

				// class added to arrows when at the extremes (i.e. prev/first arrows are "disabled" when on the first page)
				cssDisabled: 'disabled', // Note there is no period "." in front of this class name
				cssErrorRow: 'tablesorter-errorRow' // ajax error information row

			};

			$("#clicks")

				// Initialize tablesorter
				// ***********************
				.tablesorter({
					sortList: [[4, 1]],
					widgets: ['staticRow']
				})

				// bind to pager events
				// *********************
				.bind('pagerChange pagerComplete pagerInitialized pageMoved', function(e, c) {
					var msg = '"</span> event triggered, ' + (e.type === 'pagerChange' ? 'going to' : 'now on') +
						' page <span class="typ">' + (c.page + 1) + '/' + c.totalPages + '</span>';
					$('#display')
					.append('<li><span class="str">"' + e.type + msg + '</li>')
					.find('li:first').remove();
				})

				// initialize the pager plugin
				// ****************************
				.tablesorterPager(pagerOptions);
		});

    </script>
@endsection
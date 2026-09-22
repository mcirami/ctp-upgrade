@extends("layouts.master")
@section('content')

    <!--right_panel-->
    <div class="right_panel" id="manualSale">
        <div class="white_box_outer">
            <div class="heading_holder value_span9"><span class="lft">Add Sale</span></div>
            <div class="white_box value_span8">

                <form action="/sales/add" method="post" id="form" enctype="multipart/form-data">
                    {{csrf_field()}}


                    <div class="left_con01">
                        <p>
                            <label class="value_span9">Affiliate</label>
                            <select name="affiliate" id="affiliateSelect" required disabled data-selected="{{ old('affiliate') }}">
                                <option value="">Loading affiliates...</option>
                            </select>
                            <input type="text" id="affiliateSearch" placeholder="Search affiliates..."
                                   style="margin-top:10px;">
                            <span id="affiliateStatus" role="status"></span>
                        </p>


                        <p>
                            <label class="value_span9">Date</label>
                            <input type="text" name="date" id="date" value="{{ old('date', gmdate('Y-m-d H:i:s')) }}" required>
                            <span class="small_txt value_span10">timestamps stored in utc</span>
                        </p>


                        <span class="btn_yellow"> <input type="submit" name="button"
                                                         class="value_span6-2 value_span2 value_span1-2"
                                                         value="Create Sale" id="createSale" disabled/></span>

                    </div>

                    <div class="right_con01">
                        <p>
                            <label class="value_span9">Offer</label>
                            <select name="offer" id="offerSelect" required disabled data-selected="{{ old('offer') }}">
                                <option value="">Select an affiliate first</option>
                            </select>
                            <input type="text" id="offerSearch" placeholder="Search offers..."
                                   style="margin-top:10px;">
                            <span id="offerStatus" role="status"></span>
                        </p>

                        <p>
                            <label class="value_span9">
                                <input type="checkbox" class="fixCheckBox" id="customPayoutCheckBox"
                                       {{ old('customPayout') !== null ? 'checked' : '' }}>Custom
                                Payout</label>
                            <input {{ old('customPayout') === null ? 'disabled' : '' }}
                                   type="number" name="customPayout" id="customPayout"
                                   step="0.01" min="0"
                                   value="{{ old('customPayout', '0.00') }}">
                        </p>
                    </div>
                </form>
            </div>
        </div>


    </div>
@endsection

@section('footer')
    <script src="{{ asset('js/manual-sale.js') }}"></script>
@endsection

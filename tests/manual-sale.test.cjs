const vm = require('node:vm');
const fs = require('node:fs');
const assert = require('node:assert/strict');
const elements = {};
class Element {
  constructor() { this.value = ''; this.options = []; this.props = {}; this.events = {}; }
  val(value) { if (!arguments.length) return this.value; this.value = this.options.length && !this.options.some(o => String(o.value) === String(value)) ? null : String(value); return this; }
  empty() { this.options = []; return this; }
  append(option) { this.options.push(option); return this; }
  prop(k,v) { this.props[k] = v; return this; }
  attr() { return ''; }
  text(value) { this.message = value; return this; }
  on(name, callback) { this.events[name] = callback; return this; }
  datetimepicker() {}
}
function $(selector) { if (typeof selector === 'function') return selector(); return elements[selector] ||= new Element(); }
const requests = [];
$.getJSON = url => { const r = {url, done(fn) {this.success = fn;return this;}, fail(fn) {this.failure = fn;return this;}}; requests.push(r);return r; };
vm.runInNewContext(fs.readFileSync('public/js/manual-sale.js', 'utf8'), {$, Option: function(text,value) {this.text=text;this.value=value;}});
requests[0].success([{id:2,name:'Alice'},{id:3,name:'Bob'}]);
assert.equal($('#affiliateSelect').options.length,3);
assert.equal($('#affiliateSelect').props.disabled,false);
$('#affiliateSelect').val('2').events.change();
assert.equal(requests[1].url,'/sales/affiliate-offers/2');
$('#affiliateSelect').val('3').events.change();
requests[2].success([{id:20,name:'Offer B'}]);
requests[1].success([{id:10,name:'Stale Offer A'}]);
assert.equal($('#offerSelect').options[1].value,20);
$('#offerSelect').val('20').events.change();
assert.equal($('#createSale').props.disabled,false);
$('#affiliateSearch').events.input.call({value:'Alice'});
assert.equal($('#createSale').props.disabled,true);
assert.equal($('#offerSelect').props.disabled,true);
$('#affiliateSelect').val('2').events.change();
requests[3].failure();
assert.match($('#offerStatus').message,/Unable to load/);
assert.equal($('#createSale').props.disabled,true);
console.log('PASS: affiliate loading, dependent offers, stale responses, search reset, submission state, request failure');

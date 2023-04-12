// Focus
qtFocusAfter("kw");
["ref","tag-edit","user"].forEach( id => {
  if ( document.getElementById(id) && document.getElementById(id).value.length>0 ) qtFocusAfter(id);
});

// Search options
const optionsEl = document.getElementById("broadcasted-options");
optionsEl.addEventListener("change", (e)=>{
  e.stopPropagation();
  if ( e.target.tagName==="SELECT") broadcastOption(e.target.name,e.target.value);
});
function iconSpin() {
  const icon = document.getElementById("opt-icon");
  icon.classList.remove("spinning");
  if (document.getElementById("opt-s").value!=="*" || document.getElementById("opt-st").value!=="*") icon.classList.add("spinning");
}
function broadcastOption(option,value) {
  ["ref-","id-","kw-","btw-","user-","userm-","adv-"].forEach( id => {
     if ( document.getElementById(id+option) ) document.getElementById(id+option).setAttribute("value", value);
  });
  ["btn_recent","btn_news","btn_insp","btn_my"].forEach( id => {
    if ( document.getElementById(id) ) document.getElementById(id).setAttribute("data-"+option, value);
  });
  iconSpin();
}
function setToday() {
  const d = document;
  const dt = new Date();
  if (d.getElementById("date1")) d.getElementById("date1").value = dt.toJSON().substring(0,10);
  if (d.getElementById("date2")) d.getElementById("date2").value = dt.toJSON().substring(0,10);
}
function addHrefDataset(d, reject=[]) {
  if ( !d ) return;
  for(data in d.dataset) {
    if ( d.dataset[data]==="*" || d.dataset[data]==="" || reject.includes(data) ) continue;
    d.href += "&"+data+"="+d.dataset[data];
  }
}

// Specific autocomplete-click
acOnClicks["ref"] = function(focusInput,btn) {
  if ( focusInput.id=="ref" && focusInput.value.substring(0,1)=="#") window.location="qti_item.php?t="+focusInput.value.substring(1);
}
acOnClicks["user"] = function(focusInput,btn) {
  if ( focusInput.id==="user" ) document.getElementById("userid").value = btn.dataset.id;
}
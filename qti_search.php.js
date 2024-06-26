// Focus
qtFocusAfter("kw");
["ref","tag-edit","user"].forEach( id => {
  if ( document.getElementById(id) && document.getElementById(id).value.length>0 ) qtFocusAfter(id);
});

// Search options (for each SELECT changed)
const optionsEl = document.getElementById("broadcasted-options");
optionsEl.addEventListener("change", (e)=>{
  e.stopPropagation();
  if ( e.target.tagName==="SELECT") broadcastOption(e.target.name,e.target.value);
});
function iconSpin() {
  const icon = document.getElementById("opt-icon");
  icon.classList.remove("spinning");
  if ( document.getElementById("opt-s").value!=="-1" ) icon.classList.add("spinning");
}
function broadcastOption(option,value) {
  ["ref-","id-","kw-","btw-","user-","userm-","adv-"].forEach( id => {
     if ( document.getElementById(id+option) ) document.getElementById(id+option).setAttribute("value", value);
  });
  ["btn-recent","btn-news","btn_insp","btn-my"].forEach( id => {
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
function addHrefData(d, args) {
  for(const arg of args) {
    if ( d.dataset[arg]==="" || d.dataset[arg]===undefined ) continue;
    d.href += "&"+arg+"="+d.dataset[arg];
  }
}

// Specific autocomplete-click, requires acOnclicks check (must be VAR to be global)
if ( typeof acOnClicks==="undefined" ) { var acOnClicks = []; }
acOnClicks["ref"] = function(focusInput,btn) {
  if ( focusInput.id=="ref" && focusInput.value.substring(0,1)=="#") window.location="qti_item.php?t="+focusInput.value.substring(1);
}
acOnClicks["user"] = function(focusInput,btn) {
  if ( focusInput.id==="user" ) document.getElementById("userid").value = btn.dataset.id;
}
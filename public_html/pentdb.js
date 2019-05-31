/* PenTDB Javascript */

function ptdb_copytext( field_id ) {

  /* Get the text field */
  var copyText = document.getElementById(field_id);

  /* Select the text field */
  copyText.select();

  /* Copy the text inside the text field */
  document.execCommand("copy");

  /* Alert the copied text */
  /* alert("Copied the text: " + copyText.value); */
}

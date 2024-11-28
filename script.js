/* JavaScript function to create color toolbar in Dokuwiki */
/* see http://www.dokuwiki.org/plugin:color for more info */

color_icobase = "../../plugins/saberlink/images/";

if (window.toolbar != undefined) {
  toolbar[toolbar.length] = {
      "type" : "format",
      "title": "Saberlink Button",
      "icon": color_icobase + "icon.png",
      "open": "<saberlink>",
      "sample": "URL",
      "close": "</saberlink>"
  };
}
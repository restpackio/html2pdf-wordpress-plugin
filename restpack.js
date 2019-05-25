(function(d) {
  var s = d.createElement("script");
  s.type = "text/javascript";
  s.async = true;
  s.src = "https://restpack.io/save-as-pdf.js";
  var x = d.getElementsByTagName("script")[0];
  x.parentNode.insertBefore(s, x);
})(window.document);

window.document.addEventListener("click", function(event) {
  if (event.target.classList.contains("restpack-api")) {
    event.preventDefault();
    var elem = event.target;

    var props = elem.getAttribute("data-props");

    document.body.style.cursor = "wait";
    var ajaxcallurl = window.ajaxcallurl;

    fetch(ajaxcallurl, {
      method: "POST",
      body: "action=restpack_ajax",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded;"
      }
    })
      .then(function(data) {
        document.body.style.cursor = "default";
        return data.json();
      })
      .then(function(data) {
        if (data.error) return alert(data.error);
        window.open(data.image);
      })
      .catch(function(error) {
        alert(error);
      });

    try {
      JSON.parse(props);
    } catch (e) {}

    console.log("clicked");
  }
});

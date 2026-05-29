// Public loader for theme script (injects non-module script into page)
(function () {
  if (window.PSTheme) return; // already loaded
  var s = document.createElement("script");
  s.src = "/resources/ui/js/theme.js";
  s.defer = true;
  s.crossOrigin = "anonymous";
  document.head.appendChild(s);
})();

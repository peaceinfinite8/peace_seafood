// resources/ui/js/theme.js
(function () {
  const storageKey = "ps_theme";
  function applyTheme(theme) {
    if (theme === "dark")
      document.documentElement.setAttribute("data-theme", "dark");
    else document.documentElement.setAttribute("data-theme", "light");
  }
  function getPreferred() {
    const saved = localStorage.getItem(storageKey);
    if (saved) return saved;
    return window.matchMedia &&
      window.matchMedia("(prefers-color-scheme: dark)").matches
      ? "dark"
      : "light";
  }
  const current = getPreferred();
  applyTheme(current);
  window.PSTheme = {
    toggle() {
      const cur = document.documentElement.getAttribute("data-theme");
      const next = cur === "dark" ? "light" : "dark";
      applyTheme(next);
      localStorage.setItem(storageKey, next);
      return next;
    },
    set(theme) {
      applyTheme(theme);
      localStorage.setItem(storageKey, theme);
    },
    get() {
      return (
        localStorage.getItem(storageKey) ||
        document.documentElement.getAttribute("data-theme") ||
        "light"
      );
    },
  };
})();

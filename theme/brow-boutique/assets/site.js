import "./motion/motion.js";
import "./moments/moments.js";

const toggle = document.querySelector(".menu-toggle");
const menu = document.querySelector("#studio-menu");
const narrow = matchMedia("(max-width: 760px)");

if (toggle && menu) {
  const setOpen = (open) => {
    toggle.setAttribute("aria-expanded", String(open));
    menu.hidden = narrow.matches && !open;
  };

  const syncMenu = () => {
    toggle.hidden = !narrow.matches;
    setOpen(false);
  };

  syncMenu();
  narrow.addEventListener("change", syncMenu);
  toggle.addEventListener("click", () => {
    setOpen(toggle.getAttribute("aria-expanded") !== "true");
  });

  menu.addEventListener("click", (event) => {
    const link = event.target.closest("a");
    if (!narrow.matches || !link) return;
    setOpen(false);
    const target = link.hash && document.getElementById(link.hash.slice(1));
    if (target) {
      target.setAttribute("tabindex", "-1");
      target.focus({ preventScroll: true });
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && toggle.getAttribute("aria-expanded") === "true") {
      setOpen(false);
      toggle.focus();
    }
  });
}

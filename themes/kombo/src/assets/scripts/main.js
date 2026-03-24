import jQuery from "jquery";
import { Init } from "./Init";
import "bootstrap/dist/js/bootstrap.bundle.min.js";

document.addEventListener("DOMContentLoaded", () => {
  const heroSection = document.querySelector(".front_page_hero_section");
  const sideMenuButton = document.querySelector(".side_menu_button_hidden");

  const observer = new IntersectionObserver(
    ([entry]) => {
      if (!entry.isIntersecting) {
        // Hero section is out of view
        sideMenuButton?.classList.add("visible");
      } else {
        // Hero section is in view
        sideMenuButton?.classList.remove("visible");
      }
    },
    {
      threshold: 0,
      rootMargin: "0px 0px -1px 0px",
    }
  );

  if (heroSection) {
    observer.observe(heroSection);
  }
});

jQuery(function ($) {
  // new Init($);
  const mobileMenu = document.querySelector("#mobile-nav .menu-wrapper");
  const burgerIcon = document.querySelector("#mobile-nav .burger-icon");
  const closeIcon = document.querySelector("#mobile-nav .close-icon");

  burgerIcon.addEventListener("click", function () {
    mobileMenu.classList.add("opened");
    this.classList.add("hidden");
    closeIcon.classList.add("visible");
    document.body.classList.add("menu-opened");
  });

  closeIcon.addEventListener("click", function () {
    mobileMenu.classList.remove("opened");
    this.classList.remove("visible");
    burgerIcon.classList.remove("hidden");
    document.body.classList.remove("menu-opened");
  });
});

document.addEventListener("resize", () => {
  if (window.innerWidth > 600) {
    document.body.classList.remove("menu-opened");
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const desktopHeader = document.getElementById("desktop-menu");
  const mobileHeader = document.getElementById("mobile-menu");
  let lastScrollY = window.scrollY;
  let isScrollingDown = true;

  const handleScroll = () => {
    const currentScrollY = window.scrollY;

    if (currentScrollY > 0) {
      desktopHeader.classList.add("colored");
      mobileHeader.classList.add("colored");
    } else {
      desktopHeader.classList.remove("colored");
      mobileHeader.classList.remove("colored");
    }

    if (currentScrollY > lastScrollY && currentScrollY > 100) {
      // Scrolling down
      if (!isScrollingDown) {
        desktopHeader.classList.add("hidden");
        mobileHeader.classList.add("hidden");
        // desktopHeader.style.transform = "translateY(-100%)"; // Hide desktop header
        // mobileHeader.style.transform = "translateY(-100%)"; // Hide mobile header
        isScrollingDown = true;
      }
    } else {
      // Scrolling up
      if (isScrollingDown) {
        desktopHeader.classList.remove("hidden");
        mobileHeader.classList.remove("hidden");
        // desktopHeader.style.transform = "translateY(0)"; // Show desktop header
        // mobileHeader.style.transform = "translateY(0)"; // Show mobile header
        isScrollingDown = false;
      }
    }

    lastScrollY = currentScrollY;
  };

  window.addEventListener("scroll", handleScroll);
});

// Drag to scroll for price-wrapper
document.addEventListener("DOMContentLoaded", function () {
  const slider = document.querySelector(".price-wrapper");
  if (!slider) return;

  let isDown = false;
  let startX;
  let scrollLeft;

  slider.addEventListener("mousedown", (e) => {
    isDown = true;
    slider.style.cursor = "grabbing";
    startX = e.pageX - slider.offsetLeft;
    scrollLeft = slider.scrollLeft;
  });

  slider.addEventListener("mouseleave", () => {
    isDown = false;
    slider.style.cursor = "grab";
  });

  slider.addEventListener("mouseup", () => {
    isDown = false;
    slider.style.cursor = "grab";
  });

  slider.addEventListener("mousemove", (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - slider.offsetLeft;
    const walk = (x - startX) * 2;
    slider.scrollLeft = scrollLeft - walk;
  });
});

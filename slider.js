(() => {
    const header = document.querySelector("#siteHeader");
    const navToggle = document.querySelector(".nav-toggle");
    const mainNav = document.querySelector("#mainNav");

    if (navToggle && mainNav) {
        navToggle.addEventListener("click", () => {
            const expanded = navToggle.getAttribute("aria-expanded") === "true";
            navToggle.setAttribute("aria-expanded", String(!expanded));
            navToggle.classList.toggle("is-active");
            mainNav.classList.toggle("is-open");
        });

        mainNav.querySelectorAll("a").forEach((link) => {
            link.addEventListener("click", () => {
                if (window.innerWidth < 1080) {
                    navToggle.setAttribute("aria-expanded", "false");
                    navToggle.classList.remove("is-active");
                    mainNav.classList.remove("is-open");
                }
            });
        });
    }

    const setHeaderState = () => {
        if (!header) {
            return;
        }
        header.classList.toggle("is-scrolled", window.scrollY > 8);
    };

    window.addEventListener("scroll", setHeaderState, { passive: true });
    setHeaderState();

    const slides = Array.from(document.querySelectorAll("[data-slide]"));
    const dots = Array.from(document.querySelectorAll("[data-dot]"));
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    if (slides.length && dots.length) {
        let active = 0;
        let timer = null;
        const delay = 4800;

        const setSlide = (index) => {
            active = index;
            slides.forEach((slide, idx) => {
                slide.classList.toggle("is-active", idx === index);
            });
            dots.forEach((dot, idx) => {
                const current = idx === index;
                dot.classList.toggle("is-active", current);
                dot.setAttribute("aria-selected", String(current));
            });
        };

        const stop = () => {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
        };

        const start = () => {
            if (reducedMotion) {
                return;
            }
            stop();
            timer = window.setInterval(() => {
                const next = (active + 1) % slides.length;
                setSlide(next);
            }, delay);
        };

        dots.forEach((dot, idx) => {
            dot.addEventListener("click", () => {
                setSlide(idx);
                start();
            });
        });

        setSlide(0);
        start();

        document.addEventListener("visibilitychange", () => {
            if (document.hidden) {
                stop();
            } else {
                start();
            }
        });
    }

    const revealItems = document.querySelectorAll("[data-reveal]");
    if (revealItems.length) {
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("in-view");
                    obs.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.15,
            rootMargin: "0px 0px -40px 0px"
        });

        revealItems.forEach((item) => observer.observe(item));
    }
})();



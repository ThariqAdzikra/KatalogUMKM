// ============================================
// ENHANCED SCROLL REVEAL WITH INTERSECTION OBSERVER
// ============================================

const initScrollReveal = () => {
    const observerOptions = {
        threshold: 0.15,
        rootMargin: '0px 0px -80px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Add delay for owner card specifically
                const delay = entry.target.classList.contains('profile-card-owner') ? 200 : 50;

                setTimeout(() => {
                    entry.target.classList.add('is-visible');
                }, delay);

                // Unobserve after animation
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all scroll-reveal elements
    const revealElements = document.querySelectorAll('.scroll-reveal');

    revealElements.forEach((element, index) => {
        observer.observe(element);
    });
};

// ============================================
// ENHANCED PARALLAX EFFECT
// ============================================

const initParallaxEffect = () => {
    let ticking = false;
    let lastScrollY = 0;

    const updateParallax = () => {
        lastScrollY = window.pageYOffset;

        // Parallax for business section
        const businessSection = document.querySelector('.business-description-section');
        if (businessSection) {
            const businessBefore = businessSection.querySelector('::before');
            businessSection.style.transform = `translateY(${lastScrollY * 0.15}px)`;
        }

        // Parallax for owner section
        const ownerSection = document.querySelector('.owner-section');
        if (ownerSection) {
            ownerSection.style.transform = `translateY(${lastScrollY * 0.1}px)`;
        }

        // Parallax for developer section
        const developerSection = document.querySelector('.developer-team-section');
        if (developerSection) {
            developerSection.style.transform = `translateY(${lastScrollY * 0.12}px)`;
        }

        // Parallax for images
        const images = document.querySelectorAll('.rounded-image');
        images.forEach(img => {
            const rect = img.getBoundingClientRect();
            const scrollPercent = rect.top / window.innerHeight;
            if (scrollPercent < 1 && scrollPercent > -0.5) {
                img.style.transform = `translateY(${scrollPercent * -30}px) scale(1.05)`;
            }
        });

        ticking = false;
    };

    const requestTick = () => {
        if (!ticking) {
            window.requestAnimationFrame(updateParallax);
            ticking = true;
        }
    };

    window.addEventListener('scroll', requestTick, { passive: true });
};

// ============================================
// SMOOTH SCROLL FOR INTERNAL LINKS
// ============================================

const initSmoothScroll = () => {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');

            if (href === '#') return;

            e.preventDefault();

            const targetElement = document.querySelector(href);
            if (targetElement) {
                const offsetTop = targetElement.offsetTop - 80; // Account for navbar

                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
};

// ============================================
// ENHANCED MOUSE FOLLOW EFFECT WITH 3D (SUBTLE)
// ============================================

const initMouseFollowEffect = () => {
    // Disable on mobile/tablet
    if (window.innerWidth < 992) return;

    const profileCards = document.querySelectorAll('.profile-card, .profile-card-owner, .feature-card');

    profileCards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const centerX = rect.width / 2;
            const centerY = rect.height / 2;

            const rotateX = ((y - centerY) / centerY) * 3;
            const rotateY = ((centerX - x) / centerX) * 3;

            card.style.transform =
                `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0)';
        });
    });
};

// ============================================
// ENHANCED MOUSE GLOW EFFECT
// ============================================

const initMouseGlowEffect = () => {
    const cards = document.querySelectorAll('.card');

    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            card.style.setProperty('--mouse-x', `${x}px`);
            card.style.setProperty('--mouse-y', `${y}px`);
        });
    });
};

// ============================================
// COUNTER ANIMATION
// ============================================

const initCounterAnimation = () => {
    const counters = document.querySelectorAll('[data-count]');

    if (counters.length === 0) return;

    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.dataset.count);
                const duration = 2000;
                const start = Date.now();

                const easeOutQuart = t => 1 - Math.pow(1 - t, 4);

                const updateCounter = () => {
                    const now = Date.now();
                    const elapsed = now - start;
                    const progress = Math.min(elapsed / duration, 1);
                    const easedProgress = easeOutQuart(progress);

                    counter.textContent = Math.floor(target * easedProgress);

                    if (progress < 1) {
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target;
                    }
                };

                updateCounter();
                counterObserver.unobserve(counter);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => {
        counterObserver.observe(counter);
    });
};

// ============================================
// STAGGERED ANIMATION HELPER
// ============================================

const initStaggeredAnimation = (selector, delay = 0.1) => {
    const elements = document.querySelectorAll(selector);

    elements.forEach((element, index) => {
        element.style.setProperty('--stagger-delay', `${delay * index}s`);
    });
};

// ============================================
// NAVBAR SCROLL DETECTION
// ============================================

const initScrollDetection = () => {
    let lastScrollTop = 0;
    const navbar = document.querySelector('nav');

    if (!navbar) return;

    const handleScroll = () => {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (scrollTop > 100) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }

        // Hide/show navbar on scroll
        if (scrollTop > lastScrollTop && scrollTop > 300) {
            navbar.style.transform = 'translateY(-100%)';
        } else {
            navbar.style.transform = 'translateY(0)';
        }

        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    };

    window.addEventListener('scroll', throttle(handleScroll, 100), { passive: true });
};

// ============================================
// LAZY LOAD IMAGES
// ============================================

const initLazyLoad = () => {
    const images = document.querySelectorAll('img[data-src]');

    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
};

// ============================================
// PROFILE IMAGE HOVER GLOW
// ============================================

const initProfileImageGlow = () => {
    const profileImages = document.querySelectorAll('.profile-image');

    profileImages.forEach(img => {
        img.addEventListener('mouseenter', () => {
            img.style.filter = 'brightness(1.1) contrast(1.05)';
        });

        img.addEventListener('mouseleave', () => {
            img.style.filter = 'brightness(1) contrast(1)';
        });
    });
};

// ============================================
// TEXT ANIMATION ON SCROLL
// ============================================

const initTextAnimation = () => {
    const textElements = document.querySelectorAll('.section-header h1, .section-header h2, .content-text h2');

    const textObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.8s ease-out forwards';
                textObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });

    textElements.forEach(el => textObserver.observe(el));
};

// ============================================
// SCROLL PROGRESS INDICATOR (SIMPLIFIED)
// ============================================

const initScrollProgress = () => {
    // Create progress bar element
    const progressBar = document.createElement('div');
    progressBar.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 0%;
        height: 3px;
        background: linear-gradient(90deg, #6b7e4a 0%, #8a9b6e 100%);
        z-index: 9999;
        transition: width 0.1s ease;
    `;
    document.body.appendChild(progressBar);

    const updateProgress = () => {
        const scrollTop = window.pageYOffset;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollPercent = (scrollTop / docHeight) * 100;
        progressBar.style.width = scrollPercent + '%';
    };

    window.addEventListener('scroll', throttle(updateProgress, 50), { passive: true });
};

// ============================================
// ENHANCED CARD ENTRANCE ANIMATION
// ============================================

const initCardEntranceAnimation = () => {
    const cards = document.querySelectorAll('.card');

    const cardObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '0';
                entry.target.style.transform = 'translateY(40px)';

                setTimeout(() => {
                    entry.target.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, 100);

                cardObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    cards.forEach(card => cardObserver.observe(card));
};

// ============================================
// SECTION BACKGROUND ANIMATION
// ============================================

const initSectionBackgroundAnimation = () => {
    const sections = document.querySelectorAll('.business-description-section, .owner-section, .developer-team-section');

    sections.forEach(section => {
        let scrollEffect = 0;

        window.addEventListener('scroll', () => {
            const rect = section.getBoundingClientRect();
            const sectionMiddle = rect.top + rect.height / 2;
            const viewportMiddle = window.innerHeight / 2;

            scrollEffect = (sectionMiddle - viewportMiddle) / viewportMiddle;

            if (rect.top < window.innerHeight && rect.bottom > 0) {
                section.style.setProperty('--scroll-effect', scrollEffect);
            }
        }, { passive: true });
    });
};

// ============================================
// INIT ALL FUNCTIONS
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    console.log('✨ About page initialized');

    // Initialize essential animations and effects only
    initScrollReveal();
    initSmoothScroll();
    initMouseFollowEffect(); // Diperbarui untuk menyertakan .feature-card
    initScrollDetection();
    initLazyLoad();
    initScrollProgress();

    // Stagger animation for profile cards
    initStaggeredAnimation('.profile-card', 0.15);
    initStaggeredAnimation('.feature-card', 0.1); // Menambahkan stagger untuk kartu fitur

    // Add loaded class to body for CSS transitions
    setTimeout(() => {
        document.body.classList.add('page-loaded');
    }, 100);
});

// ============================================
// UTILITY FUNCTIONS
// ============================================

const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

const throttle = (func, limit) => {
    let inThrottle;
    return function (...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
};

const isInViewport = (element) => {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
};

// ============================================
// PERFORMANCE OPTIMIZATION
// ============================================

// Reduce animations on lower-end devices
const optimizePerformance = () => {
    if (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4) {
        document.documentElement.classList.add('reduced-motion');
    }
};

optimizePerformance();

// Pause animations when tab is not visible
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        document.documentElement.classList.add('paused-animations');
    } else {
        document.documentElement.classList.remove('paused-animations');
    }
});
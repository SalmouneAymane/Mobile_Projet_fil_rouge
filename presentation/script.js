const slides = Array.from(document.querySelectorAll('.slide'));
const currentSlide = document.querySelector('#current-slide');
const totalSlides = document.querySelector('#total-slides');
const currentTitle = document.querySelector('#current-title');
const progressBar = document.querySelector('#progress-bar');
const previousButton = document.querySelector('#previous-button');
const nextButton = document.querySelector('#next-button');
const overviewPanel = document.querySelector('#overview-panel');
const overviewButton = document.querySelector('#overview-button');
const overviewList = document.querySelector('#overview-list');

let activeIndex = 0;
let touchStartX = null;

totalSlides.textContent = String(slides.length).padStart(2, '0');

function showSlide(index) {
    activeIndex = Math.max(0, Math.min(index, slides.length - 1));
    slides.forEach((slide, slideIndex) => {
        const active = slideIndex === activeIndex;
        const formattedIndex = String(slideIndex + 1).padStart(2, '0');
        slide.hidden = !active;
        slide.classList.toggle('is-active', active);
        slide.setAttribute('aria-hidden', String(!active));
        slide.setAttribute('aria-label', `Diapositive ${slideIndex + 1} sur ${slides.length} : ${slide.dataset.title}`);
        const slideNumber = slide.querySelector('.slide-number, .cover-index');
        if (slideNumber) {
            slideNumber.textContent = `${formattedIndex} / ${String(slides.length).padStart(2, '0')}`;
        }
    });

    const number = String(activeIndex + 1).padStart(2, '0');
    currentSlide.textContent = number;
    currentTitle.textContent = slides[activeIndex].dataset.title;
    progressBar.style.width = `${((activeIndex + 1) / slides.length) * 100}%`;
    previousButton.disabled = activeIndex === 0;
    nextButton.disabled = activeIndex === slides.length - 1;
    document.title = `${slides[activeIndex].dataset.title} — 2TUP`;
    overviewList.querySelectorAll('.overview-item').forEach((item, itemIndex) => {
        if (itemIndex === activeIndex) item.setAttribute('aria-current', 'true');
        else item.removeAttribute('aria-current');
    });
}

function toggleOverview(force) {
    const open = typeof force === 'boolean' ? force : overviewPanel.hidden;
    overviewPanel.hidden = !open;
    overviewButton.setAttribute('aria-expanded', String(open));
    if (open) overviewPanel.querySelector('[aria-current="true"]')?.focus();
}

slides.forEach((slide, index) => {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'overview-item';
    button.innerHTML = `<span>${String(index + 1).padStart(2, '0')}</span>${slide.dataset.title}`;
    button.addEventListener('click', () => {
        showSlide(index);
        toggleOverview(false);
    });
    overviewList.append(button);
});

previousButton.addEventListener('click', () => showSlide(activeIndex - 1));
nextButton.addEventListener('click', () => showSlide(activeIndex + 1));
overviewButton.addEventListener('click', () => toggleOverview());
document.querySelector('#close-overview').addEventListener('click', () => toggleOverview(false));
document.querySelector('#fullscreen-button').addEventListener('click', async () => {
    try {
        if (document.fullscreenElement) await document.exitFullscreen();
        else await document.querySelector('.deck').requestFullscreen();
    } catch (error) {
        console.error('Unable to toggle fullscreen presentation mode.', error);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.altKey || event.ctrlKey || event.metaKey) return;
    if (event.key === 'ArrowRight' || event.key === ' ' || event.key === 'PageDown') {
        event.preventDefault();
        toggleOverview(false);
        showSlide(activeIndex + 1);
    } else if (event.key === 'ArrowLeft' || event.key === 'PageUp') {
        event.preventDefault();
        toggleOverview(false);
        showSlide(activeIndex - 1);
    } else if (event.key === 'Home') {
        showSlide(0);
    } else if (event.key === 'End') {
        showSlide(slides.length - 1);
    } else if (event.key === 'Escape') {
        toggleOverview(false);
    } else if (event.key.toLowerCase() === 'f') {
        document.querySelector('#fullscreen-button').click();
    }
});

document.querySelector('.slides').addEventListener('touchstart', (event) => {
    touchStartX = event.changedTouches[0].screenX;
}, { passive: true });

document.querySelector('.slides').addEventListener('touchend', (event) => {
    if (touchStartX === null) return;
    const distance = event.changedTouches[0].screenX - touchStartX;
    if (Math.abs(distance) > 60) showSlide(activeIndex + (distance < 0 ? 1 : -1));
    touchStartX = null;
}, { passive: true });

document.addEventListener('click', (event) => {
    if (!overviewPanel.hidden && !overviewPanel.contains(event.target) && !overviewButton.contains(event.target)) {
        toggleOverview(false);
    }
});

showSlide(0);

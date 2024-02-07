import './style.scss';

window.addEventListener('DOMContentLoaded', () => {
  if (!document.body.classList.contains('single-petition')) {
    return;
  }

  const toggle = document.querySelector('.petition-contentReveal dt');

  if (!toggle) {
    return;
  }

  toggle.addEventListener('click', function clickHandler() {
    this.closest('.petition-contentReveal').classList.toggle('is-open');
  });
});

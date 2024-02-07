const initialisePetitionInteractivity = () => {
  console.log('init');
  if (!document.body.classList.contains('single-petition')) {
    console.log('not single');
    return;
  }

  const toggle = document.querySelector('.petition-contentReveal dt');

  if (!toggle) {
    console.log('no toggle');
    return;
  }

  toggle.addEventListener('click', function clickHandler() {
    console.log('clicking');
    this.closest('.petition-contentReveal').classList.toggle('is-open');
  });
};

export default initialisePetitionInteractivity;

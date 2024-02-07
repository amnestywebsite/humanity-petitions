/* global jQuery */
/* eslint-disable prefer-arrow-callback,func-names */
jQuery(function ($) {
  const form = $('#cmb2-metabox-amnesty_petitions_settings');

  if (!form) {
    return;
  }

  const initial = form.find('.cmb2-option[name="send_email"]:checked').val();

  const repaint = function (selector, compare) {
    form.find(selector).each(function () {
      if (this.dataset.sendEmail.indexOf(compare) !== -1) {
        $(this).parents('.cmb-row').show();
      } else {
        $(this).parents('.cmb-row').hide();
      }
    });
  };

  repaint('[data-send-email]', initial);

  form.on('click', function (e) {
    if (!e.target.classList.contains('cmb2-option')) {
      return;
    }

    if (e.target.getAttribute('name') !== 'send_email') {
      return;
    }

    const selected = e.target.value;
    repaint('[data-send-email]', selected);
  });
});

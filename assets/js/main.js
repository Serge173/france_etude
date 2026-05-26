(function () {
  'use strict';

  // Menu mobile
  const toggle = document.querySelector('.nav-toggle');
  const nav = document.querySelector('.nav');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      const open = nav.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    nav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  // Soumission formulaire AJAX
  const form = document.getElementById('candidature-form');
  const alertBox = document.getElementById('form-alert');
  const submitBtn = document.getElementById('submit-btn');

  if (!form) return;

  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoading = submitBtn.querySelector('.btn-loading');

    submitBtn.disabled = true;
    if (btnText) btnText.hidden = true;
    if (btnLoading) btnLoading.hidden = false;

    hideAlert();

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { Accept: 'application/json' },
      });

      const data = await response.json();

      if (data.success) {
        showAlert(
          'success',
          data.message +
            (data.reference ? ' Référence : <strong>' + escapeHtml(data.reference) + '</strong>.' : '')
        );
        form.reset();
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } else {
        showAlert('error', data.message || 'Une erreur est survenue.');
      }
    } catch (err) {
      showAlert('error', 'Impossible de contacter le serveur. Vérifiez votre connexion.');
    } finally {
      submitBtn.disabled = false;
      if (btnText) btnText.hidden = false;
      if (btnLoading) btnLoading.hidden = true;
    }
  });

  function showAlert(type, html) {
    if (!alertBox) return;
    alertBox.hidden = false;
    alertBox.className = 'alert alert-' + (type === 'success' ? 'success' : 'error');
    alertBox.innerHTML = html;
  }

  function hideAlert() {
    if (alertBox) {
      alertBox.hidden = true;
      alertBox.innerHTML = '';
    }
  }

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  }
})();

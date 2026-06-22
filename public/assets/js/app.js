document.addEventListener('DOMContentLoaded', () => {

  // Lucide Icons
  if (window.lucide) {
    lucide.createIcons();
  }

  // Scroll Animations
  const animatedItems = document.querySelectorAll(
    '.pet-card, .stat, .form-shell, .auth-card, .dashboard-table, .filter-card, .empty-state, .team-card, .mission-box'
  );

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;

      entry.target.animate(
        [
          { opacity: 0, transform: 'translateY(20px)' },
          { opacity: 1, transform: 'translateY(0)' }
        ],
        {
          duration: 500,
          easing: 'cubic-bezier(.2,.8,.2,1)',
          fill: 'both'
        }
      );

      observer.unobserve(entry.target);
    });
  }, { threshold: 0.12 });

  animatedItems.forEach((item) => observer.observe(item));

  // Toast Close
  document.querySelectorAll('.toast-close').forEach((button) => {
    button.addEventListener('click', () => {
      button.closest('.toast-wrap')?.remove();
    });
  });

  // Auto hide toast
  const toast = document.querySelector('.toast-wrap');

  if (toast) {
    setTimeout(() => {
      toast.animate(
        [
          { opacity: 1, transform: 'translateY(0)' },
          { opacity: 0, transform: 'translateY(-10px)' }
        ],
        {
          duration: 250,
          fill: 'forwards'
        }
      ).onfinish = () => toast.remove();
    }, 3500);
  }

  // Search Filters
  const searchFilter = document.querySelector('.search-filter');

  if (searchFilter) {

    const submitFilter = () => {
      searchFilter.submit();
    };

    searchFilter.querySelectorAll('select').forEach((select) => {
      select.addEventListener('change', submitFilter);
    });

    const searchInput = searchFilter.querySelector('input[name="q"]');
    const searchBtn = searchFilter.querySelector('button[type="submit"]');

    if (searchBtn) {
      searchBtn.addEventListener('click', (e) => {
        e.preventDefault();
        submitFilter();
      });
    }

    if (searchInput) {
      searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          submitFilter();
        }
      });
    }
  }

  // PASSWORD TOGGLE (WORKING VERSION)
  document.querySelectorAll('.password-toggle, .toggle-password').forEach((button) => {

    button.addEventListener('click', () => {

      const field = button.closest('.password-field');

      if (!field) {
        console.log('No .password-field found');
        return;
      }

      const input = field.querySelector('input');

      if (!input) {
        console.log('No input found');
        return;
      }

      const isPassword = input.type === 'password';

      input.type = isPassword ? 'text' : 'password';

      button.innerHTML = `
        <i data-lucide="${isPassword ? 'eye-off' : 'eye'}"></i>
      `;

      if (window.lucide) {
        lucide.createIcons();
      }
    });

  });

  // Profile Sidebar Navigation
  const focusProfileSection = () => {

    if (!location.hash) return;

    const section = document.querySelector(location.hash);

    if (!section) return;

    document.querySelectorAll('.profile-menu a').forEach((link) => {

      const active =
        link.getAttribute('href') === location.hash;

      link.classList.toggle('active', active);
      link.classList.toggle('is-active', active);
    });

    setTimeout(() => {
      section.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }, 100);
  };

  focusProfileSection();

  window.addEventListener('hashchange', focusProfileSection);

  // Confirmation Modal
  let pendingConfirmForm = null;

  const confirmModalElement =
    document.getElementById('confirmActionModal');

  const confirmModal =
    confirmModalElement && window.bootstrap
      ? new bootstrap.Modal(confirmModalElement)
      : null;

  document.querySelectorAll('form.confirm-before-submit')
    .forEach((form) => {

      form.addEventListener('submit', (event) => {

        if (form.dataset.confirmed === 'true') return;

        event.preventDefault();

        pendingConfirmForm = form;

        const title =
          document.getElementById('confirmActionTitle');

        const message =
          document.getElementById('confirmActionMessage');

        const button =
          document.getElementById('confirmActionButton');

        if (title) {
          title.textContent =
            form.dataset.confirmTitle ||
            'Confirm changes?';
        }

        if (message) {
          message.textContent =
            form.dataset.confirmMessage ||
            'Please confirm this action.';
        }

        if (button) {
          button.textContent =
            form.dataset.confirmButton ||
            'Confirm';
        }

        if (confirmModal) {
          confirmModal.show();
        } else {

          const confirmed = confirm(
            form.dataset.confirmTitle ||
            'Confirm changes?'
          );

          if (confirmed) {
            form.dataset.confirmed = 'true';
            form.submit();
          }
        }
      });

    });

  document.getElementById('confirmActionButton')
    ?.addEventListener('click', () => {

      if (!pendingConfirmForm) return;

      pendingConfirmForm.dataset.confirmed = 'true';
      pendingConfirmForm.submit();
    });

  // Profile Photo Preview
  const profilePhotoInput =
    document.querySelector('input[name="profile_photo"]');

  if (profilePhotoInput) {

    profilePhotoInput.addEventListener('change', () => {

      const file =
        profilePhotoInput.files?.[0];

      const preview =
        document.querySelector('.profile-photo-preview');

      if (!file || !preview) return;

      const url =
        URL.createObjectURL(file);

      preview.innerHTML =
        `<img src="${url}" alt="Profile Preview">`;
    });
  }


  // Lightweight skeleton/loading overlay for page actions
  const skeletonOverlay = document.createElement('div');
  skeletonOverlay.className = 'page-skeleton-overlay';
  skeletonOverlay.innerHTML = `
    <div class="skeleton-card" aria-label="Loading">
      <div class="d-flex align-items-center gap-3">
        <span class="skeleton-circle"></span>
        <div class="flex-grow-1">
          <span class="skeleton-line medium"></span>
          <span class="skeleton-line short"></span>
        </div>
      </div>
      <span class="skeleton-line long"></span>
      <span class="skeleton-line medium"></span>
      <span class="skeleton-line long"></span>
    </div>`;
  document.body.appendChild(skeletonOverlay);

  const showSkeleton = () => skeletonOverlay.classList.add('is-visible');

  document.querySelectorAll('a[href]').forEach((link) => {
    const href = link.getAttribute('href') || '';
    if (
      href.startsWith('#') ||
      href.startsWith('javascript:') ||
      link.target === '_blank' ||
      link.hasAttribute('download') ||
      link.dataset.bsToggle
    ) return;
    link.addEventListener('click', () => showSkeleton());
  });

  document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', () => {
      if (form.classList.contains('confirm-before-submit') && form.dataset.confirmed !== 'true') return;
      showSkeleton();
    });
  });

});
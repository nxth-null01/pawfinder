document.addEventListener('DOMContentLoaded', () => {
  if (window.lucide) {
    lucide.createIcons();
  }

  const animatedItems = document.querySelectorAll(
    '.pet-card, .stat, .form-shell, .auth-card, .dashboard-table, .filter-card, .empty-state'
  );

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;

      entry.target.animate(
        [
          { opacity: 0, transform: 'translateY(18px)' },
          { opacity: 1, transform: 'translateY(0)' }
        ],
        {
          duration: 520,
          easing: 'cubic-bezier(.2,.8,.2,1)',
          fill: 'both'
        }
      );

      observer.unobserve(entry.target);
    });
  }, { threshold: .12 });

  animatedItems.forEach((item) => observer.observe(item));

  document.querySelectorAll('.toast-close').forEach((button) => {
    button.addEventListener('click', () => {
      button.closest('.toast-wrap')?.remove();
    });
  });



  document.querySelectorAll('.password-toggle').forEach((button) => {
    button.addEventListener('click', () => {
      const input = button.closest('.password-field')?.querySelector('input');
      if (!input) return;

      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      button.innerHTML = `<i data-lucide="${isPassword ? 'eye-off' : 'eye'}"></i>`;

      if (window.lucide) {
        lucide.createIcons();
      }
    });
  });

  const toast = document.querySelector('.toast-wrap');
  if (toast) {
    setTimeout(() => {
      toast.animate(
        [
          { opacity: 1, transform: 'translateY(0)' },
          { opacity: 0, transform: 'translateY(-8px)' }
        ],
        { duration: 260, easing: 'ease', fill: 'forwards' }
      ).onfinish = () => toast.remove();
    }, 3600);
  }
});

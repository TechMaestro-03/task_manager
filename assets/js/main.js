// Wait until DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
    // Auto-hide feedback messages (success, error)
    const feedbackMessages = document.querySelectorAll('.feedback');
    feedbackMessages.forEach(msg => {
      setTimeout(() => {
        msg.style.display = 'none';
      }, 4000);
    });
  
    // Mobile menu toggle (optional)
    const menuBtn = document.getElementById('menu-toggle');
    const nav = document.getElementById('mobile-nav');
  
    if (menuBtn && nav) {
      menuBtn.addEventListener('click', () => {
        nav.classList.toggle('show');
      });
    }
  
    // Task status select highlighting (optional)
    const statusSelects = document.querySelectorAll('select[name="status"]');
    statusSelects.forEach(select => {
      select.addEventListener('change', function () {
        const val = this.value;
        this.style.backgroundColor =
          val === 'Completed' ? '#d4edda' :
          val === 'In Progress' ? '#fff3cd' :
          '#f8d7da';
      });
    });
  
    // Auto-highlight current nav link (optional)
    const currentPath = window.location.pathname.split('/').pop();
    document.querySelectorAll('.navbar a').forEach(link => {
      if (link.getAttribute('href') === currentPath) {
        link.classList.add('active');
      }
    });
  });
  
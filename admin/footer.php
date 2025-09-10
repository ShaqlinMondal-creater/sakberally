  <script>
    // Sidebar controls
    function openSidebar() {
      document.getElementById('sidebar').style.transform = 'translateX(0)';
      document.getElementById('overlay').classList.remove('hidden');
    }
    function closeSidebar() {
      document.getElementById('sidebar').style.transform = 'translateX(-100%)';
      document.getElementById('overlay').classList.add('hidden');
    }
    // Init year
    document.getElementById('year').textContent = new Date().getFullYear();
  </script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.16/dist/sweetalert2.all.min.js"></script>
</body>
</html>
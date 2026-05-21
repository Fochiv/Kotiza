</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/i18n.js"></script>
<script src="/assets/js/main.js"></script>
<?= isset($extraScripts) ? $extraScripts : '' ?>

<script>
// Sidebar mobile
document.querySelector('.navbar-toggler')?.addEventListener('click', () => {
  const overlay = document.getElementById('sidebar-overlay');
  if (overlay) overlay.style.display = 'block';
});
</script>
</body>
</html>

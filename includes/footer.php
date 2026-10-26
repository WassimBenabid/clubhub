<?php
/* =====================================================================
   includes/footer.php : du <footer> jusqu'à </html>
   $footerImage (facultative) : une page peut définir une image à afficher dans le pied de page.
   ===================================================================== */
?>
  <footer class="site-footer">
    <?php if (!empty($footerImage)) : ?>
      <img src="<?= e($footerImage) ?>" alt="Image représentant l'événement du Club Robotique">
    <?php endif; ?>
    <p>ClubHub · La plateforme des clubs étudiants</p>
  </footer>

  <script src="js/app.js"></script>
</body>
</html>

    </main>
    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <strong><?= e(APP_NAME) ?></strong>
                <p><?= e(APP_TAGLINE) ?></p>
                <p class="footer-partner">En partenariat avec FIGS Education</p>
            </div>
            <div>
                <h3>Liens utiles</h3>
                <ul>
                    <li><a href="#candidature">Déposer une candidature</a></li>
                </ul>
            </div>
            <div>
                <h3>Contact</h3>
                <p>WhatsApp : <a href="<?= e(whatsapp_url()) ?>" target="_blank" rel="noopener noreferrer"><?= e(CONTACT_PHONE) ?></a></p>
                <p>Email : <a href="mailto:<?= e(CONTACT_EMAIL) ?>"><?= e(CONTACT_EMAIL) ?></a></p>
            </div>
        </div>
        <div class="container footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. Tous droits réservés.</p>
        </div>
    </footer>
    <script src="<?= e($basePath ?? '') ?>assets/js/main.js" defer></script>
</body>
</html>

<?php
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/helpers.php';

$pageTitle = APP_NAME . ' — Étudier en France';
$basePath = '';

$data = require __DIR__ . '/includes/data-schools.php';
$csrf = csrf_field();

require __DIR__ . '/includes/header.php';
?>

<section class="hero" id="accueil">
    <div class="container">
        <a href="#candidature" class="flash-offer" aria-label="Offre flash : suivi de dossier gratuit pour les 50 premiers inscrits">
            <span class="flash-offer-badge">Offre flash</span>
            <span class="flash-offer-text">
                <strong>Suivi de dossier gratuit</strong> pour les <strong>50 premiers inscrits</strong> !
            </span>
            <span class="flash-offer-cta">Candidater maintenant →</span>
        </a>
    </div>
    <div class="container hero-grid">
        <div class="hero-content">
            <p class="eyebrow">Rentrée septembre 2026 — Candidatures ouvertes</p>
            <h1>Votre projet d'études en <em>France</em> commence ici</h1>
            <p class="lead">
                Postulez en ligne pour intégrer l'une des <strong>17 écoles privées françaises</strong>
                du réseau <a href="<?= e(FIGS_URL) ?>" target="_blank" rel="noopener">FIGS Education</a>.
                Accompagnement gratuit de la candidature à l'arrivée sur campus.
            </p>
            <div class="hero-actions">
                <a href="#candidature" class="btn btn-primary">Déposer ma candidature</a>
                <a href="#procedure" class="btn btn-outline">Comment ça marche</a>
                <a href="<?= e(whatsapp_url()) ?>" class="btn btn-whatsapp" target="_blank" rel="noopener noreferrer">Nous contacter</a>
            </div>
            <ul class="hero-stats">
                <li><strong>17</strong> écoles</li>
                <li><strong>15+</strong> campus</li>
                <li><strong>100+</strong> programmes</li>
                <li><strong>13</strong> filières métiers</li>
            </ul>
        </div>
        <div class="hero-visual">
            <div class="hero-card hero-card-partner">
                <div class="hero-partner-accent" aria-hidden="true"></div>
                <div class="hero-partner-body">
                    <p class="hero-partner-unique">Unique partenaire</p>
                    <div class="hero-partner-line" aria-hidden="true"></div>
                    <a href="<?= e(FIGS_URL) ?>" target="_blank" rel="noopener noreferrer" class="hero-partner-link" title="FIGS Education">
                        <img src="<?= e($basePath . FIGS_LOGO) ?>" alt="FIGS Education" class="hero-figs-logo" width="220" height="80" decoding="async">
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section-alt" id="partenaire">
    <div class="container">
        <header class="section-header">
            <span class="section-tag">Partenariat</span>
            <h2>FIGS Education, votre référent en France</h2>
            <p>
                FIGS Education est le service international du réseau Compétences &amp; Développement.
                Il accompagne les étudiants du monde entier dans leur projet d'études en France :
                choix de formation, admission, visa et intégration sur le campus de votre choix.
            </p>
        </header>
        <div class="features-grid">
            <article class="feature-card">
                <div class="feature-icon" aria-hidden="true">✓</div>
                <h3>Accompagnement gratuit</h3>
                <p>Conseil personnalisé, préparation entretien, visa et logement — sans frais FIGS.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon" aria-hidden="true">🎓</div>
                <h3>Diplômes reconnus</h3>
                <p>Formations du bac au bac+5, reconnues par l'État français et valorisées à l'international.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon" aria-hidden="true">🌍</div>
                <h3>13 bureaux internationaux</h3>
                <p>Des équipes proches de vous sur tous les continents pour suivre votre dossier.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon" aria-hidden="true">📋</div>
                <h3>Procédure simplifiée</h3>
                <p>Une candidature en ligne : nous traitons votre demande et vous recontactons rapidement.</p>
            </article>
        </div>
    </div>
</section>

<section class="section" id="ecoles">
    <div class="container">
        <header class="section-header">
            <span class="section-tag">Réseau</span>
            <h2>17 écoles spécialisées</h2>
            <p>Choisissez l'établissement qui correspond à votre projet professionnel lors de votre candidature.</p>
        </header>
        <div class="schools-grid">
            <?php foreach ($data['schools'] as $code => $label): ?>
            <article class="school-card">
                <span class="school-code"><?= e($code) ?></span>
                <h3><?= e($label) ?></h3>
            </article>
            <?php endforeach; ?>
        </div>
        <p class="section-note">
            Liste issue du réseau <a href="<?= e(FIGS_URL) ?>ecoles" target="_blank" rel="noopener">figs-education.com/ecoles</a>.
        </p>
    </div>
</section>

<section class="section section-alt" id="campus">
    <div class="container">
        <header class="section-header">
            <span class="section-tag">Implantations</span>
            <h2>Campus en France</h2>
            <p>Plus de 15 villes pour étudier en France hexagonale. Indiquez votre campus préféré dans le formulaire.</p>
        </header>
        <ul class="campus-list">
            <?php foreach ($data['campuses'] as $code => $city): if ($code === 'autre') continue; ?>
            <li><?= e($city) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="section" id="logement">
    <div class="container">
        <header class="section-header">
            <span class="section-tag">Logement étudiant</span>
            <h2>Nous vous aidons à trouver un logement</h2>
            <p>
                En complément de votre inscription dans une école du réseau FIGS, notre équipe vous accompagne
                pour trouver un hébergement adapté à votre budget et à la ville de votre campus.
            </p>
        </header>
        <div class="features-grid logement-grid">
            <article class="feature-card">
                <div class="feature-icon" aria-hidden="true">🏠</div>
                <h3>Résidences &amp; studios</h3>
                <p>Studios, colocations et résidences étudiantes à proximité de votre école.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon" aria-hidden="true">📍</div>
                <h3>Proximité du campus</h3>
                <p>Propositions selon la ville choisie (Paris, Lyon, Bordeaux, Montpellier…).</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon" aria-hidden="true">🤝</div>
                <h3>Accompagnement FIGS</h3>
                <p>Comme pour l'admission, FIGS Education propose une aide à l'installation en France.</p>
            </article>
            <article class="feature-card">
                <div class="feature-icon" aria-hidden="true">✉️</div>
                <h3>Demande dans le formulaire</h3>
                <p>Cochez «&nbsp;Je demande un logement&nbsp;» lors de votre candidature : nous traiterons les deux dossiers.</p>
            </article>
        </div>
        <p class="section-cta">
            <a href="#candidature" class="btn btn-primary">Candidater et demander un logement</a>
        </p>
    </div>
</section>

<section class="section section-alt" id="procedure">
    <div class="container">
        <header class="section-header">
            <span class="section-tag">Étapes</span>
            <h2>Comment candidater</h2>
        </header>
        <ol class="steps">
            <li>
                <strong>Remplissez le formulaire</strong>
                <p>Coordonnées, parcours, école, campus et éventuelle demande de logement.</p>
            </li>
            <li>
                <strong>Confirmation immédiate</strong>
                <p>Vous recevez une référence de dossier (ex. FE-XXXXXXXX).</p>
            </li>
            <li>
                <strong>Traitement par notre équipe</strong>
                <p>Nous analysons votre profil et vous contactons par email ou téléphone.</p>
            </li>
            <li>
                <strong>Dossier FIGS &amp; inscription</strong>
                <p>Complétez les pièces (passeport, diplômes, niveau de français…) avec l'appui FIGS.</p>
            </li>
        </ol>
        <div class="docs-box">
            <h3>Pièces généralement demandées (FIGS)</h3>
            <ul>
                <li>Copie du passeport et photo d'identité</li>
                <li>CV et derniers relevés de notes</li>
                <li>Diplômes (bac et dernier diplôme obtenu)</li>
                <li>Preuve de niveau de français (TCF, DELF, DALF) si applicable</li>
            </ul>
        </div>
    </div>
</section>

<section class="section section-form" id="candidature">
    <div class="container">
        <header class="section-header">
            <span class="section-tag">Candidature</span>
            <h2>Formulaire de candidature</h2>
            <p>Tous les champs marqués * sont obligatoires. Vos données sont traitées de manière confidentielle.</p>
        </header>

        <div id="form-alert" class="alert" role="alert" hidden></div>

        <form id="candidature-form" class="form candidature-form" action="<?= e(url_path('api/submit.php')) ?>" method="post" novalidate>
            <?= $csrf ?>
            <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">

            <fieldset>
                <legend>Identité &amp; contact</legend>
                <div class="form-row">
                    <label>Prénom *<input type="text" name="prenom" required maxlength="100" autocomplete="given-name"></label>
                    <label>Nom *<input type="text" name="nom" required maxlength="100" autocomplete="family-name"></label>
                </div>
                <div class="form-row">
                    <label>Email *<input type="email" name="email" required maxlength="191" autocomplete="email"></label>
                    <label>Téléphone *<input type="tel" name="telephone" required maxlength="40" autocomplete="tel" placeholder="+33 6 12 34 56 78"></label>
                </div>
                <div class="form-row">
                    <label>WhatsApp (optionnel)<input type="tel" name="whatsapp" maxlength="40" placeholder="+212 6..."></label>
                    <label>Date de naissance<input type="date" name="date_naissance"></label>
                </div>
                <div class="form-row">
                    <label>Nationalité *<input type="text" name="nationalite" required maxlength="80" placeholder="Marocaine, Sénégalaise..."></label>
                    <label>Pays de résidence *<input type="text" name="pays_residence" required maxlength="80"></label>
                </div>
            </fieldset>

            <fieldset>
                <legend>Projet d'études</legend>
                <div class="form-row">
                    <label>Niveau d'études actuel *
                        <select name="niveau_etudes" required>
                            <option value="">— Choisir —</option>
                            <?php foreach ($data['levels'] as $k => $v): ?>
                            <option value="<?= e($k) ?>"><?= e($v) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Rentrée visée *
                        <select name="rentree" required>
                            <option value="">— Choisir —</option>
                            <?php foreach ($data['intakes'] as $k => $v): ?>
                            <option value="<?= e($k) ?>"><?= e($v) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div class="form-row">
                    <label>École FIGS souhaitée *
                        <select name="ecole" required>
                            <option value="">— Choisir une école —</option>
                            <?php foreach ($data['schools'] as $k => $v): ?>
                            <option value="<?= e($k) ?>"><?= e($v) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Campus *
                        <select name="campus" required>
                            <option value="">— Choisir un campus —</option>
                            <?php foreach ($data['campuses'] as $k => $v): ?>
                            <option value="<?= e($k) ?>"><?= e($v) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div class="form-row">
                    <label>Filière / domaine *
                        <select name="domaine" required>
                            <option value="">— Choisir —</option>
                            <?php foreach ($data['domains'] as $k => $v): ?>
                            <option value="<?= e($k) ?>"><?= e($v) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Langue d'études *
                        <select name="langue_etudes" required>
                            <option value="fr">Français</option>
                            <option value="en">Anglais</option>
                            <option value="bilingue">Bilingue</option>
                        </select>
                    </label>
                </div>
                <label>Niveau de français (si concerné)
                    <input type="text" name="niveau_francais" maxlength="80" placeholder="B2, TCF 450, débutant...">
                </label>
                <label>Message / précisions
                    <textarea name="message" rows="4" maxlength="2000" placeholder="Votre projet, questions particulières..."></textarea>
                </label>
            </fieldset>

            <fieldset>
                <legend>Logement</legend>
                <label class="checkbox-label checkbox-highlight">
                    <input type="checkbox" name="demande_logement" value="1">
                    Je demande un logement étudiant pour mon arrivée en France (accompagnement par notre équipe).
                </label>
                <p class="field-hint">Cochez cette case uniquement si vous souhaitez être contacté pour une proposition de logement près de votre campus.</p>
            </fieldset>

            <fieldset class="fieldset-rgpd">
                <label class="checkbox-label">
                    <input type="checkbox" name="rgpd" value="1" required>
                    J'accepte que mes données soient traitées par <?= e(APP_NAME) ?> et transmises à FIGS Education
                    dans le cadre de ma candidature. *
                </label>
            </fieldset>

            <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                <span class="btn-text">Envoyer ma candidature</span>
                <span class="btn-loading" hidden>Envoi en cours…</span>
            </button>
        </form>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

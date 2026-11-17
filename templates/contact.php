<section class="page-header">
    <div class="container">
        <h1>Contactez-nous</h1>
        <p>Une question, un projet ? Remplissez le formulaire ci-dessous.</p>
    </div>
</section>

<section class="contact">
    <div class="container contact__inner">

        <?php if (!empty($success)): ?>
            <div class="alert alert--success" role="alert">
                Votre message a bien été envoyé. Nous revenons vers vous rapidement !
            </div>
        <?php endif; ?>

        <?php if (!empty($errors['spam'])): ?>
            <div class="alert alert--error" role="alert">
                Une erreur est survenue. Merci de réessayer.
            </div>
        <?php endif; ?>

        <form class="contact-form" action="/contact.php" method="post" novalidate>

            <!-- Honeypot anti-spam (champ caché via CSS, ne pas remplir) -->
            <div class="form-group form-group--honeypot" aria-hidden="true">
                <label for="website">Site web</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="name">Nom complet</label>
                <input type="text" id="name" name="name" value="<?= $old['name'] ?? '' ?>" required>
                <?php if (!empty($errors['name'])): ?>
                    <span class="form-error"><?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" value="<?= $old['email'] ?? '' ?>" required>
                <?php if (!empty($errors['email'])): ?>
                    <span class="form-error"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="subject">Sujet</label>
                <input type="text" id="subject" name="subject" value="<?= $old['subject'] ?? '' ?>" required>
                <?php if (!empty($errors['subject'])): ?>
                    <span class="form-error"><?= htmlspecialchars($errors['subject'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="6" required><?= $old['message'] ?? '' ?></textarea>
                <?php if (!empty($errors['message'])): ?>
                    <span class="form-error"><?= htmlspecialchars($errors['message'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn--primary">Envoyer le message</button>
        </form>
    </div>
</section>
